<?php

namespace Webling\Cache;

use Webling\API\IClient;

class WordpressCache implements ICache {

	public static $TABLE_NAME = 'webling_cache';

	// seconds to wait until next /replicate call is made
	public static $PAUSE_BETWEEN_SYNC = 60;

	protected $client;

	protected $options;

	function __construct(IClient $client, $options = []) {
		global $wpdb;

		if (get_class($wpdb) != 'wpdb') {
			throw new CacheException('Not in Wordpress Context!');
		}

		$this->client = $client;
		$this->options = $options;

		$this->updateCache();
	}

	public function updateCache() {

		$cache_state = get_option('webling-cache-state');

		if ($cache_state) {
			if (isset($cache_state['revision'])) {
				if (isset($cache_state['timestamp'])) {
					if ($cache_state['timestamp'] > time() - self::$PAUSE_BETWEEN_SYNC) {
						// wait a bit more for the next replication
						return;
					}
				}
				$replicate = $this->client->get('/replicate/'.$cache_state['revision'])->getData();
				if (isset($replicate['revision'])) {

					if ($replicate['revision'] < 0) {
						// if revision is set to -1, clear cache and make a complete sync
						// this happens when the users permission have changed
						$this->clearCache();
					} else {
						// delete cached objects
						foreach ($replicate['objects'] as $objCategory) {
							foreach ($objCategory as $obj) {
								$this->deleteObjectCache($obj);
							}
						}

						// delete all root cache objects if the revision has changed
						// this could be done more efficient, but lets keep it simple for simplicity
						if ($cache_state['revision'] != $replicate['revision']) {
							$this->deleteRootCache();
						}

						// update cache state
						$cache_state['revision'] = $replicate['revision'];
						$cache_state['timestamp'] = time();
						update_option('webling-cache-state', $cache_state);
					}

				} else {
					$this->clearCache();
					throw new CacheException('Error in replication. No revision found.');
				}
				return;
			}
		}

		// write initial cache state
		$replicate = $this->client->get('/replicate')->getData();
		if (isset($replicate['revision'])) {
			$data = [
				'revision' => $replicate['revision'],
				'timestamp' => time(),
			];
			update_option('webling-cache-state', $data);
		}
	}

	public function clearCache() {
		global $wpdb;
		$wpdb->get_row("TRUNCATE {$wpdb->prefix}" . self::$TABLE_NAME);
		$this->deleteRootCache();
		update_option('webling-cache-state', null);
		return;
	}

	public function getObject($type, $objectId) {
		$cached = $this->getObjectCache($objectId);
		if ($cached != null) {
			return json_decode($cached, true);
		} else {
			$response = $this->client->get($type.'/'.$objectId);

			// only cache 2XX responses
			if ($response->getStatusCode() <= 200 && $response->getStatusCode() < 300) {
				$data = $response->getData();
				$this->setObjectCache($objectId, $data);
				return $data;
			} else {
				return null;
			}
		}
	}

	public function getRoot($type) {
		$type = preg_replace('/[^a-z]/i', '', strtolower($type));
		$cached = $this->getRootCache($type);
		if ($cached != null) {
			return json_decode($cached, true);
		} else {
			$response = $this->client->get($type);

			// only cache 2XX responses
			if ($response->getStatusCode() <= 200 && $response->getStatusCode() < 300) {
				$data = $response->getData();
				$this->setRootCache($type, $data);
				return $data;
			} else {
				return null;
			}
		}
	}

	private function getObjectCache($id) {
		global $wpdb;

		$id = intval($id);
		$entry = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}" . self::$TABLE_NAME . ' WHERE id = '.$id, 'ARRAY_A');

		if ($entry) {
			return $entry['data'];
		} else {
			return null;
		}
	}

	private function setObjectCache($id, $data) {
		global $wpdb;

		$id = intval($id);
		$type = esc_sql($data['type']);
		$data = esc_sql(json_encode($data));

		if (strlen($type) > 0 && strlen($data) > 4) {
			$sql = "INSERT INTO {$wpdb->prefix}" . self::$TABLE_NAME
				. " (id, type, data) VALUES('".$id. "', '".$type. "', '".$data. "')"
				. " ON DUPLICATE KEY UPDATE id = '".$id."'";
			$wpdb->query($sql);
		}
	}

	private function deleteObjectCache($id) {
		global $wpdb;

		$id = intval($id);
		$wpdb->query("DELETE FROM {$wpdb->prefix}" . self::$TABLE_NAME . " WHERE id = ".$id);
	}

	private function getRootCache($type) {
		$type = preg_replace('/[^a-z]/i', '', strtolower($type));
		$optionname = 'webling-cache-root-'.$type;
		return get_option($optionname, null);
	}

	private function setRootCache($type, $data) {
		$type = preg_replace('/[^a-z]/i', '', strtolower($type));
		$optionname = 'webling-cache-root-'.$type;
		update_option($optionname, json_encode($data), false);
	}

	private function deleteRootCache() {
		global $wpdb;

		$sql = "SELECT option_name FROM {$wpdb->prefix}options WHERE option_name LIKE 'webling-cache-root-%'";
		$options = $wpdb->get_results($sql, 'ARRAY_A');
		foreach ($options as $option) {
			delete_option($option['option_name']);
		}
	}

}
