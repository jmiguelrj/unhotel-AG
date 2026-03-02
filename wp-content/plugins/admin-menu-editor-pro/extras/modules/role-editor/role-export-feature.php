<?php

namespace YahnisElsts\AdminMenuEditor\RoleEditor\ImportExport;

class RoleDataTransfer implements \JsonSerializable {
	/**
	 * @var array
	 */
	private $capabilityIndex;
	/**
	 * @var array<string,SerializableRoleDetails>
	 */
	private $roles;

	protected function __construct(array $roles, ?array $capabilityIndex = null) {
		$this->roles = $roles;

		if ( $capabilityIndex !== null ) {
			$this->capabilityIndex = $capabilityIndex;
		} else {
			$allCaps = [];
			foreach ($roles as $role) {
				$allCaps = array_merge($allCaps, $role->getCapabilities());
			}

			//Note: Sorting capabilities was tried, but it slightly hurt compression when roles
			//were similar to WP defaults. A more advanced approach that keeps the default capability
			//order for core capabilities could work better, but for now we'll keep it simple.

			$capNames = array_keys($allCaps);
			$this->capabilityIndex = array_flip($capNames);
		}
	}

	/**
	 * @return array<string,SerializableRoleDetails>
	 */
	public function getRoles(): array {
		return $this->roles;
	}

	/**
	 * @return string[]
	 */
	public function getAllCapabilities(): array {
		return array_keys($this->capabilityIndex);
	}

	public static function create(\WP_Roles $wpRoles, \WPMenuEditor $menuEditor): self {
		$roles = [];
		$allCaps = [];

		foreach ($wpRoles->role_objects as $roleId => $role) {
			$capabilities = [];
			if ( !empty($role->capabilities) && is_array($role->capabilities) ) {
				$capabilities = $menuEditor->castValuesToBool($role->capabilities);
			}

			$allCaps = array_merge($allCaps, $capabilities);

			$roles[$roleId] = new SerializableRoleDetails(
				$roleId,
				\ameUtils::get($wpRoles->role_names, $roleId, $roleId),
				$capabilities
			);
		}

		return new self($roles);
	}

	public function serialize($compression = false): array {
		return [
			//We include the capability index even if compression is disabled because it doubles
			//as a list of all capabilities present on the original site. This is useful when merging
			//roles because it helps distinguish between "capability intentionally not set for [role]"
			//and "capability didn't exist on the source site".
			'capabilityIndex' => array_values(array_flip($this->capabilityIndex)),
			'roles'           => array_map(function ($roleDetails) use ($compression) {
				return $roleDetails->serialize($compression, $compression ? $this->capabilityIndex : null);
			}, $this->roles),
		];
	}

	public static function deserialize(array $data): self {
		if ( !isset($data['roles']) || !is_array($data['roles']) ) {
			throw new \InvalidArgumentException('Invalid or missing roles data');
		}
		if ( !isset($data['capabilityIndex']) || !is_array($data['capabilityIndex']) ) {
			throw new \InvalidArgumentException('Invalid capability index data');
		}
		$invertedCapabilityIndex = $data['capabilityIndex'];

		$roles = [];
		foreach ($data['roles'] as $roleName => $serializedRoleData) {
			$roles[$roleName] = SerializableRoleDetails::deserialize(
				$serializedRoleData,
				$roleName,
				$invertedCapabilityIndex
			);
		}

		if ( $invertedCapabilityIndex ) {
			$capabilityIndex = array_flip($invertedCapabilityIndex);
		} else {
			$capabilityIndex = null;
		}

		return new self($roles, $capabilityIndex);
	}

	public function jsonSerialize(): array {
		return $this->serialize(false);
	}
}

class SerializableRoleDetails implements \JsonSerializable {
	const COMPRESSED_CAPS_KEY = 'compCaps';

	private $name;
	private $displayName;
	private $capabilities;

	public function __construct(string $name, string $displayName, array $capabilities) {
		$this->name = $name;
		$this->displayName = $displayName;
		$this->capabilities = $capabilities;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getDisplayName(): string {
		return $this->displayName;
	}

	public function getCapabilities(): array {
		return $this->capabilities;
	}

	public function serialize($compression = false, ?array $capabilityIndex = null): array {
		$data = [];
		if ( !$compression ) {
			$data['name'] = $this->name;
		}
		$data['displayName'] = $this->displayName;


		if ( $compression ) {
			if ( !is_array($capabilityIndex) ) {
				throw new \InvalidArgumentException('Capability index is required for compressed serialization');
			}
			$data[self::COMPRESSED_CAPS_KEY] = $this->compressCapabilityMap($this->capabilities, $capabilityIndex);
		} else {
			$data['capabilities'] = $this->capabilities;
		}

		return $data;
	}

	public static function deserialize($serializedData, $name, ?array $invertedCapabilityIndex = null): self {
		if ( !isset($serializedData['displayName']) || !is_string($serializedData['displayName']) ) {
			throw new \InvalidArgumentException('Invalid role display name');
		}

		if ( isset($serializedData['capabilities']) ) {
			if ( !is_array($serializedData['capabilities']) ) {
				throw new \InvalidArgumentException('Invalid role capabilities data - must be an array');
			}
			$capabilities = $serializedData['capabilities'];
		} else if ( isset($serializedData[self::COMPRESSED_CAPS_KEY]) ) {
			if ( !is_array($serializedData[self::COMPRESSED_CAPS_KEY]) ) {
				throw new \InvalidArgumentException('Invalid role compressed capabilities data - must be an array');
			}
			if ( $invertedCapabilityIndex === null ) {
				throw new \InvalidArgumentException('Inverted capability index is required for compressed capabilities deserialization');
			}
			$capabilities = self::decompressCapabilityMap(
				$serializedData[self::COMPRESSED_CAPS_KEY],
				$invertedCapabilityIndex
			);
		} else {
			throw new \InvalidArgumentException('Role capabilities data is missing');
		}

		return new self(
			$name,
			$serializedData['displayName'],
			$capabilities
		);
	}

	private function compressCapabilityMap(array $caps, array $capabilityIndex): array {
		$rleThreshold = max(count($capabilityIndex) / 2, 10);

		if ( count($caps) > $rleThreshold ) {
			//Use RLE (run-length encoding) for roles with many capabilities.
			$values = array_fill(0, count($capabilityIndex), 2); //2 = "not set"
			foreach ($caps as $capName => $isGranted) {
				$index = $capabilityIndex[$capName];
				$values[$index] = $isGranted ? 1 : 0;
			}
			return [
				'format' => 'rle',
				'data'   => $this->rleCompress($values),
			];
		} else {
			//Use sparse arrays for roles with few capabilities.
			$granted = [];
			$denied = [];
			foreach ($caps as $capName => $isGranted) {
				$index = $capabilityIndex[$capName];
				if ( $isGranted ) {
					$granted[] = $index;
				} else {
					$denied[] = $index;
				}
			}

			$compressedCaps = ['format' => 'sparse'];
			if ( !empty($granted) ) {
				$compressedCaps['granted'] = $granted;
			}
			if ( !empty($denied) ) {
				$compressedCaps['denied'] = $denied;
			}
			return $compressedCaps;
		}
	}

	private static function decompressCapabilityMap($compressedCaps, $invertedCapabilityIndex): array {
		$result = [];
		if ( !is_array($compressedCaps) || !isset($compressedCaps['format']) ) {
			throw new \InvalidArgumentException('Invalid capability map data');
		}

		if ( $compressedCaps['format'] === 'sparse' ) {
			if ( isset($compressedCaps['granted']) && is_array($compressedCaps['granted']) ) {
				foreach ($compressedCaps['granted'] as $index) {
					if ( isset($invertedCapabilityIndex[$index]) ) {
						$result[$invertedCapabilityIndex[$index]] = true;
					}
				}
			}
			if ( isset($compressedCaps['denied']) && is_array($compressedCaps['denied']) ) {
				foreach ($compressedCaps['denied'] as $index) {
					if ( isset($invertedCapabilityIndex[$index]) ) {
						$result[$invertedCapabilityIndex[$index]] = false;
					}
				}
			}
		} else if ( $compressedCaps['format'] === 'rle' ) {
			if ( isset($compressedCaps['data']) && is_array($compressedCaps['data']) ) {
				$values = self::rleDecompress($compressedCaps['data']);
				foreach ($values as $i => $value) {
					if ( $value === 2 ) { //2 = "not set"
						continue;
					}
					if ( isset($invertedCapabilityIndex[$i]) ) {
						$result[$invertedCapabilityIndex[$i]] = ($value === 1); //1 = granted, 0 = denied
					}
				}
			}
		} else {
			throw new \InvalidArgumentException('Invalid capability map format: ' . $compressedCaps['format']);
		}

		return $result;
	}

	private function rleCompress(array $valuesArray): array {
		$minRunLength = 3;
		$currentValue = null;
		$count = 0;
		$result = [];
		foreach ($valuesArray as $value) {
			if ( $value === $currentValue ) {
				$count++;
			} else {
				if ( $currentValue !== null ) {
					if ( $count >= $minRunLength ) {
						$result[] = [$currentValue, $count];
					} else {
						for ($i = 0; $i < $count; $i++) {
							$result[] = $currentValue;
						}
					}
				}
				$currentValue = $value;
				$count = 1;
			}
		}

		if ( $currentValue !== null ) {
			if ( $count >= $minRunLength ) {
				$result[] = [$currentValue, $count];
			} else {
				for ($i = 0; $i < $count; $i++) {
					$result[] = $currentValue;
				}
			}
		}

		return $result;
	}

	private static function rleDecompress($compressedValues): array {
		$result = [];
		foreach ($compressedValues as $item) {
			if ( is_array($item) && (count($item) === 2) ) {
				list($value, $count) = $item;
				for ($i = 0; $i < $count; $i++) {
					$result[] = $value;
				}
			} else {
				$result[] = $item;
			}
		}
		return $result;
	}

	public function jsonSerialize(): array {
		return $this->serialize(false);
	}
}