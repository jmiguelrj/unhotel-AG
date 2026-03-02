<?php

namespace YahnisElsts\AdminMenuEditor\QuickSearch;

class EntityItemDefinition extends SearchableItemDefinition {
	/**
	 * @var string
	 */
	private $url;
	/**
	 * @var string
	 */
	private $kind;
	/**
	 * @var null|string
	 */
	private $contentType;

	/**
	 * @var int
	 */
	private $internalId;

	public function __construct($label, $url, $kind, $internalId, $contentType = null, $location = []) {
		parent::__construct($label, $location);
		$this->url = $url;
		$this->kind = $kind;
		$this->contentType = $contentType;
		$this->internalId = $internalId;
	}

	public function jsonSerialize(): array {
		$data = parent::jsonSerialize();
		$data['type'] = 'entity';
		$data['url'] = $this->url;
		$data['internalId'] = $this->internalId;
		$data['kind'] = $this->kind;
		if ( !empty($this->contentType) ) {
			$data['contentType'] = $this->contentType;
		}
		return $data;
	}

	public function getInternalId(): int {
		return $this->internalId;
	}
}

class PostSearchEngine implements ItemSearchEngine {
	/**
	 * @var array<string,boolean>
	 */
	private $configEnabledPostTypes;

	public function __construct(array $configEnabledPostTypes) {
		$this->configEnabledPostTypes = $configEnabledPostTypes;
	}

	public function getRecentItems(array $itemRefs, int $desiredResults = 20): array {
		$definitions = [];

		//Load recently used posts by ID.
		$postIds = [];
		foreach ($itemRefs as $ref) {
			if ( isset($ref['id']) && is_numeric($ref['id']) ) {
				$postIds[] = (int)$ref['id'];
			}
		}
		if ( !empty($postIds) ) {
			$definitions = $this->searchPosts('', $desiredResults, $postIds);
			//Did we get enough results?
			if ( count($definitions) >= $desiredResults ) {
				return array_slice($definitions, 0, $desiredResults);
			}
		}

		$existingPostIds = [];
		foreach ($definitions as $def) {
			$existingPostIds[$def->getInternalId()] = true;
		}

		//Fill the rest with the most recently modified posts.
		$recentPosts = $this->searchPosts('', $desiredResults);
		foreach ($recentPosts as $postDef) {
			if ( !isset($existingPostIds[$postDef->getInternalId()]) ) {
				$definitions[] = $postDef;
				$existingPostIds[$postDef->getInternalId()] = true;
			}
			if ( count($definitions) >= $desiredResults ) {
				break;
			}
		}

		return array_slice($definitions, 0, $desiredResults);
	}

	public function searchItems(string $query, int $maxResults = 100): array {
		$query = trim($query);
		if ( $query === '' ) {
			return [];
		}

		return $this->searchPosts($query, $maxResults);
	}

	/**
	 * @param string $query
	 * @param int $maxResults
	 * @param array $postIds
	 * @return EntityItemDefinition[]
	 */
	private function searchPosts(string $query, int $maxResults, array $postIds = []): array {
		$query = trim($query);

		$postTypes = $this->getEnabledPostTypes();
		$args = [
			'post_type'      => $postTypes,
			'posts_per_page' => $maxResults * 2, //Get more results and filter out those the user can't edit.
			'fields'         => ['id', 'post_title', 'post_type', 'post_status', 'post_date'],
			'search_columns' => ['post_title'], //Only search post titles, not the content.

			//Apparently, setting 'post_status' is necessary to include post types like WooCommerce Orders
			//which have non-standard statuses with parameters that would usually exclude them from the status
			//list that WP_Query generates by default.
			'post_status'    => 'any',

			'orderby' => 'modified',
			'order'   => 'DESC',
		];

		if ( $query !== '' ) {
			$args['s'] = $query;
		}

		if ( !empty($postIds) ) {
			$args['post__in'] = $postIds;
			$args['posts_per_page'] = count($postIds);
		}

		$wpQuery = new \WP_Query();
		$posts = $wpQuery->query($args);
		if ( is_wp_error($posts) || !is_array($posts) || empty($posts) ) {
			return [];
		}

		$definitions = [];
		foreach ($posts as $post) {
			//Skip posts the current user can't edit.
			if ( !current_user_can('edit_post', $post->ID) ) {
				continue;
			}

			$location = [];
			if ( !empty($post->post_type) ) {
				$postTypeObject = get_post_type_object($post->post_type);
				if ( isset($postTypeObject->labels->singular_name) && !empty($postTypeObject->labels->singular_name) ) {
					$location[] = $postTypeObject->labels->singular_name;
				} else {
					$location[] = $post->post_type;
				}
			}

			$label = (string)$post->post_title;
			if ( empty($label) ) {
				$label = sprintf('(no title) #%d', $post->ID);
			} else {
				$label = $this->mapSafeEntitiesToCharacters($label);
			}

			$definition = new EntityItemDefinition(
				$label,
				get_edit_post_link($post->ID, 'raw'), //Note: Absolute URL.
				'postType',
				$post->ID,
				$post->post_type ?? null,
				$location
			);

			$definitions[] = $definition;
		}

		//Trim to the requested number of results.
		if ( count($definitions) > $maxResults ) {
			$definitions = array_slice($definitions, 0, $maxResults);
		}

		return $definitions;
	}

	/**
	 * @return string[]
	 */
	private function getEnabledPostTypes(): array {
		$postTypes = get_post_types(array('public' => true, 'show_ui' => true), 'objects', 'or');

		$enabledPostTypes = [];
		foreach ($postTypes as $postType) {
			//Is it enabled in plugin configuration? Defaults to yes.
			$isEnabled = \ameUtils::get($this->configEnabledPostTypes, [$postType->name], true);
			if ( !$isEnabled ) {
				continue;
			}

			//Can the current user edit posts of this type?
			if ( current_user_can($postType->cap->edit_posts) ) {
				$enabledPostTypes[] = $postType->name;
			}
		}
		return $enabledPostTypes;
	}

	/**
	 * Convert a subset of HTML entities back to their character equivalents.
	 *
	 * This is to improve readability of post titles in search results while avoiding potential
	 * security issues with arbitrary HTML entities.
	 *
	 * Post titles may contain HTML entities. For example, WooCommerce orders have titles like
	 * "Order &ndash; July 1, 2025 @ 12:34". On the other hand, the labels in search results are
	 * displayed as plain text, so entities would be shown literally. I'm not sure if it's safe to
	 * decode all HTML entities in this context, so only a small set of "safe" entities are mapped
	 * here. This can be expanded if users report other common entities that should be included.
	 *
	 * @param string $title
	 * @return string
	 */
	private function mapSafeEntitiesToCharacters(string $title): string {
		static $entityToCharMap = [
			'&ndash;' => '–',
			'&mdash;' => '—',
			'&#39;'   => "'",
			'&quot;'  => '"',
		];

		return strtr($title, $entityToCharMap);
	}
}

class UserSearchEngine implements ItemSearchEngine {
	public function getRecentItems(array $itemRefs, int $desiredResults = 20): array {
		$definitions = [];

		$userIds = [];
		foreach ($itemRefs as $ref) {
			if ( isset($ref['id']) && is_numeric($ref['id']) ) {
				$userIds[] = (int)$ref['id'];
			}
		}

		if ( !empty($userIds) ) {
			$definitions = $this->searchUsers('', $desiredResults, $userIds);
			if ( count($definitions) >= $desiredResults ) {
				return array_slice($definitions, 0, $desiredResults);
			}
		}

		$existingUserIds = [];
		foreach ($definitions as $def) {
			$existingUserIds[$def->getInternalId()] = true;
		}
		$generalUsers = $this->searchUsers('', $desiredResults);
		foreach ($generalUsers as $userDef) {
			if ( !isset($existingUserIds[$userDef->getInternalId()]) ) {
				$definitions[] = $userDef;
				$existingUserIds[$userDef->getInternalId()] = true;
			}
			if ( count($definitions) >= $desiredResults ) {
				break;
			}
		}

		return array_slice($definitions, 0, $desiredResults);
	}

	public function searchItems(string $query, int $maxResults = 100): array {
		$query = trim($query);
		if ( $query === '' ) {
			return [];
		}

		return $this->searchUsers($query, $maxResults);
	}

	/**
	 * @param string $query
	 * @param int $maxResults
	 * @param array $userIds
	 * @return EntityItemDefinition[]
	 */
	private function searchUsers(string $query, int $maxResults, array $userIds = []): array {
		$args = [
			'fields'         => ['ID', 'display_name', 'user_login', 'user_email'],
			'number'         => $maxResults * 2, //Get more results and filter out those the user can't edit.
			'search_columns' => ['display_name', 'user_login', 'user_email'],
		];

		if ( $query !== '' ) {
			$args['search'] = '*' . esc_attr($query) . '*';
			$args['search_columns'] = ['display_name', 'user_login'];
		}

		if ( !empty($userIds) ) {
			$args['include'] = $userIds;
			$args['number'] = count($userIds);
		}

		$users = get_users($args);
		if ( is_wp_error($users) || !is_array($users) || empty($users) ) {
			return [];
		}

		$definitions = [];
		foreach ($users as $user) {
			if ( !current_user_can('edit_user', $user->ID) ) {
				continue;
			}

			if ( !empty($user->display_name) ) {
				$label = $user->display_name . ' (' . $user->user_login . ')';
			} elseif ( !empty($user->user_login) ) {
				$label = $user->user_login;
			} else {
				$label = $user->user_email;
			}

			$definitions[] = new EntityItemDefinition(
				$label,
				get_edit_user_link($user->ID),
				'user',
				$user->ID,
				null,
				['User']
			);
		}

		return array_slice($definitions, 0, $maxResults);
	}
}
