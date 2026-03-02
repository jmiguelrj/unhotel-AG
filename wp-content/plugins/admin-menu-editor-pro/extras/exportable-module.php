<?php

namespace YahnisElsts\AdminMenuEditor\ImportExport;

interface ameBasicExportableModule {
	/**
	 * @return ameExportableComponent[]
	 */
	public function getExportableComponents(): array;
}

interface ameExportableModule extends ameBasicExportableModule {
	/**
	 * @return array|null
	 */
	public function exportSettings();

	/**
	 * @param array $newSettings
	 * @return bool|string|\WP_Error
	 */
	public function importSettings($newSettings);

	/**
	 * @return string
	 */
	public function getExportOptionLabel();

	public function getExportOptionDescription();
}

abstract class ameExportableComponentConfig {
	protected $label = '';
	protected $description = '';
	protected $displayPriority = 10;
	protected $importPriority = 10;

	protected $exportCallback = null;
	protected $importCallback = null;
	protected $importConfigHtmlCallback = null;

	protected $advanced = false;
}

class ameExportableComponent extends ameExportableComponentConfig {
	public function __construct(ameExportableComponentConfig $config) {
		$this->label = $config->label;
		$this->description = $config->description;
		$this->displayPriority = $config->displayPriority;
		$this->importPriority = $config->importPriority;
		$this->exportCallback = $config->exportCallback;
		$this->importCallback = $config->importCallback;
		$this->importConfigHtmlCallback = $config->importConfigHtmlCallback;
		$this->advanced = $config->advanced;
	}

	public function getLabel(): string {
		return $this->label;
	}

	public function getDescription(): string {
		return $this->description;
	}

	public function getDisplayPriority(): int {
		return $this->displayPriority;
	}

	public function getImportPriority(): int {
		return $this->importPriority;
	}

	public function isAdvanced(): bool {
		return $this->advanced;
	}

	public function export(): ?array {
		if ( is_callable($this->exportCallback) ) {
			$result = call_user_func($this->exportCallback);

			if ( !is_array($result) && ($result !== null) ) {
				$dataType = gettype($result);
				if ( $dataType === 'object' ) {
					$dataType = get_class($result);
				}
				throw new \RuntimeException(sprintf(
					'Invalid export data type "%s" for component "%s". Export callback must return an array or null.',
					$dataType,
					$this->label
				));
			}

			return $result;
		}
		return null;
	}

	/**
	 * @param array $data
	 * @param mixed|null $configFieldData
	 * @return ameImportResult
	 */
	public function import(array $data, $configFieldData = null): ameImportResult {
		if ( is_callable($this->importCallback) ) {
			$result = call_user_func($this->importCallback, $data, $configFieldData);

			if ( $result instanceof ameImportResult ) {
				return $result;
			} else if ( is_wp_error($result) ) {
				return ameImportResult::fromWpError($result);
			} else if ( $result === false ) {
				return ameImportResult::nothing();
			} else if ( $result === true ) {
				return ameImportResult::success();
			} else if ( is_string($result) ) {
				return ameImportResult::success($result);
			} else {
				return ameImportResult::error('Unknown result from import callback');
			}
		}
		return ameImportResult::error('No import callback defined for this component.');
	}

	public function generateImportConfigurationUi($importedData, string $configFieldName): string {
		if ( is_callable($this->importConfigHtmlCallback) ) {
			return call_user_func($this->importConfigHtmlCallback, $importedData, $configFieldName);
		}
		return '';
	}

	public static function builder(string $label): ameExportableComponentBuilder {
		return new ameExportableComponentBuilder($label);
	}
}

class ameExportableComponentBuilder extends ameExportableComponentConfig {
	public function __construct(string $label = '') {
		$this->label = $label;
	}

	public function description(string $text): self {
		$this->description = $text;
		return $this;
	}

	public function displayPriority(int $number): self {
		$this->displayPriority = $number;
		return $this;
	}

	public function importPriority(int $number): self {
		$this->importPriority = $number;
		return $this;
	}

	/**
	 * @param callable():array|null $callback
	 * @return $this
	 */
	public function exportCallback(callable $callback): self {
		$this->exportCallback = $callback;
		return $this;
	}

	/**
	 * @param callable(array):bool|string|\WP_Error $callback
	 * @return $this
	 */
	public function importCallback(callable $callback): self {
		$this->importCallback = $callback;
		return $this;
	}

	public function importConfigHtmlCallback(callable $callback): self {
		$this->importConfigHtmlCallback = $callback;
		return $this;
	}

	public function advanced(bool $isAdvanced = true): self {
		$this->advanced = $isAdvanced;
		return $this;
	}

	public function build(): ameExportableComponent {
		return new ameExportableComponent($this);
	}
}

class ameImportResult {
	const STATUS_UNKNOWN = 'unknown';
	const STATUS_SUCCESS = 'success';
	const STATUS_FAILURE = 'failure';
	const STATUS_NOTHING_TO_IMPORT = 'nothing_to_import';
	const STATUS_SKIPPED = 'skipped';

	private $status = self::STATUS_UNKNOWN;
	private $primaryMessage = '';
	private $detailedErrors = [];

	public function isAnySuccess(): bool {
		return $this->status === self::STATUS_SUCCESS;
	}

	public function getPrimaryMessage(): string {
		return $this->primaryMessage;
	}

	public function hasErrorDetails(): bool {
		return !empty($this->detailedErrors);
	}

	/**
	 * @return array{code?:string,message:string}[]
	 */
	public function getErrorDetails(): array {
		$result = [];
		foreach ($this->detailedErrors as $error) {
			if ( $error instanceof \WP_Error ) {
				foreach ($error->errors as $code => $messages) {
					foreach ($messages as $message) {
						$result[] = ['code' => $code, 'message' => $message];
					}
				}
			} else {
				$result[] = ['message' => (string)$error];
			}
		}
		return $result;
	}

	/**
	 * @param \WP_Error[] $errors
	 * @return $this
	 */
	public function addErrors(array $errors): self {
		//Note that this doesn't automatically make the result a failure.
		//Some components may support partial success with warnings.
		foreach ($errors as $error) {
			$this->detailedErrors[] = $error;
		}
		return $this;
	}

	public static function success($message = ''): self {
		$result = new self();
		$result->status = self::STATUS_SUCCESS;
		$result->primaryMessage = $message ?: 'OK';
		return $result;
	}

	public static function nothing($message = ''): ameImportResult {
		$result = new self();
		$result->status = self::STATUS_NOTHING_TO_IMPORT;
		$result->primaryMessage = $message ?: 'Nothing to import';
		return $result;
	}

	public static function skipped(): ameImportResult {
		$result = new self();
		$result->status = self::STATUS_SKIPPED;
		$result->primaryMessage = 'Skipped';
		return $result;
	}

	public static function error($message = ''): self {
		$result = new self();
		$result->status = self::STATUS_FAILURE;
		$result->primaryMessage = $message ?: 'Error';
		return $result;
	}

	public static function fromWpError(\WP_Error $error): self {
		$result = self::error($error->get_error_message());
		if ( count($error->errors) > 1 ) {
			$result->addErrors([$error]);
		}
		return $result;
	}
}