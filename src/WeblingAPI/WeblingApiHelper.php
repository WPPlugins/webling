<?php

/**
 * Singleton class
 *
 */
class WeblingApiHelper {

	private $client;

	private $cache;

	public static $invisibleFields = ['file', 'image'];

	public static $immutableFields = ['file', 'image', 'autoincrement'];

	/**
	 * Call this method to get singleton
	 *
	 * @return WeblingApiHelper
	 */
	public static function Instance() {
		static $inst = null;
		if ($inst === null) {
			$inst = new WeblingApiHelper();
		}
		return $inst;
	}

	/**
	 * Private constructor so nobody else can instance it
	 *
	 */
	private function __construct() {
		$options = get_option('webling-options', array());
		if (!isset($options['host'])) {
			$options['host'] = '';
		}
		if (!isset($options['apikey'])) {
			$options['apikey'] = '';
		}
		$this->client = new \Webling\API\Client($options['host'], $options['apikey'], array('useragent' => $this->getUserAgent()));
		$this->cache = new \Webling\Cache\WordpressCache($this->client);
	}

	public function client() {
		return $this->client;
	}

	public function clearCache() {
		$this->cache->clearCache();
	}

	public function hasMemberReadAccess() {
		// check if /membergroup has root objects
		$rootmembergroup = $this->cache->getRoot('membergroup');
		if (isset($rootmembergroup['roots']) && is_array($rootmembergroup['roots']) && count($rootmembergroup['roots']) > 0) {
			return true;
		}
		return false;
	}

	public function hasMemberWriteAccess() {
		// check if any of the membergroups is writeable
		$tree = $this->getMembergroupTree();
		$writeable_count = 0;
		array_walk_recursive($tree, function($value, $key) use (&$writeable_count) {
			if ($key == 'writeable' && $value === true) {
				$writeable_count++;
			}
		});
		if ($writeable_count > 0) {
			return true;
		}
		return false;
	}

	public function getMembergroupTree() {
		$rootmembergroup = $this->cache->getRoot('membergroup');
		$roots = array();
		if (is_array($rootmembergroup['roots'])) {
			foreach ($rootmembergroup['roots'] as $rootGroupId) {
				$roots[$rootGroupId] = array(
					'title' => $this->getObjectLabel('membergroup', $rootGroupId),
					'writeable' => $this->objectIsWriteable('membergroup', $rootGroupId),
					'childs' => $this->recursiveMembergroupChilds($rootGroupId)
				);
			}
		}
		return $roots;
	}

	public function getMemberFields() {
		$definitions = $this->cache->getRoot('definition');
		$properties = array();
		if (isset($definitions['member']['properties'])) {
			foreach ($definitions['member']['properties'] as $propertyName => $propertyConf) {
				$properties[$propertyConf['id']] = $propertyName;
			}
		}
		return $properties;
	}

	public function getVisibleMemberFields() {
		$definitions = $this->cache->getRoot('definition');
		$properties = array();
		if (isset($definitions['member']['properties'])) {
			foreach ($definitions['member']['properties'] as $propertyName => $propertyConf) {
				if (!in_array($propertyConf['datatype'], self::$invisibleFields)) {
					$properties[$propertyConf['id']] = $propertyName;
				}
			}
		}
		return $properties;
	}

	public function getMutableMemberFields() {
		$definitions = $this->cache->getRoot('definition');
		$properties = array();
		if (isset($definitions['member']['properties'])) {
			foreach ($definitions['member']['properties'] as $propertyName => $propertyConf) {
				if (!in_array($propertyConf['datatype'], self::$immutableFields)) {
					$properties[$propertyConf['id']] = $propertyName;
				}
			}
		}
		return $properties;
	}

	public function getMemberFieldDefinitionsById() {
		$definitions = $this->cache->getRoot('definition');
		$properties = array();
		if (isset($definitions['member']['properties'])) {
			foreach ($definitions['member']['properties'] as $propertyName => $propertyConf) {
				$properties[$propertyConf['id']] = $propertyConf;
			}
		}
		return $properties;
	}

	public function getMemberProperties() {
		$definitions = $this->cache->getRoot('definition');
		return $definitions['member']['properties'];
	}

	public function getMemberTitleFields() {
		$definitions = $this->cache->getRoot('definition');
		$properties = array();
		if (isset($definitions['member']['label'])) {
			foreach ($definitions['member']['label'] as $propertyName) {
				$properties[] = $propertyName;
			}
		}
		return $properties;
	}

	private function recursiveMembergroupChilds($objectId) {
		$childs = array();

		$obj = $this->cache->getObject('membergroup', $objectId);
		if (isset($obj['children']['membergroup'])) {
			foreach ($obj['children']['membergroup'] as $childId) {
				$childs[$childId] = array(
					'title' => $this->getObjectLabel('membergroup', $childId),
					'writeable' => $this->objectIsWriteable('membergroup', $childId),
					'childs' => $this->recursiveMembergroupChilds($childId)
				);
			}
		}
		return $childs;
	}

	public function getObjectLabel($type, $objectId) {
		$obj = $this->cache->getObject($type, $objectId);
		if (isset($obj['properties']['title'])) {
			return $obj['properties']['title'];
		}
		return $type.'_'.$objectId;
	}

	public function objectIsWriteable($type, $objectId) {
		$obj = $this->cache->getObject($type, $objectId);
		if (isset($obj['readonly'])) {
			return !$obj['readonly'];
		}
		return false;
	}

	public function getUserAgent() {
		return 'Webling-WP-Plugin/' . WEBLING_DB_VERSION;
	}
}
