<?php
/**
 * WPB Metas
 *
 * Class to create location, service, app metas
 *
 * Adapted from WP Core
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */	
 
 if ( ! defined( 'ABSPATH' ) ) exit;
 
 if ( ! class_exists( 'WpBMeta' ) ) {
	 
class WpBMeta{
	
	/**
	 * Add metadata for the specified object.
	 *
	 * @param string $meta_type  Type of object metadata is for (e.g., app, service, location)
	 * @param int    $object_id  ID of the object metadata is for
	 * @param string $meta_key   Metadata key
	 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
	 * @param bool   $unique     Optional, default is false.
	 *                           Whether the specified metadata key should be unique for the object.
	 *                           If true, and the object already has a value for the specified metadata key,
	 *                           no change will be made.
	 * @return int|false The meta ID on success, false on failure.
	 */
	public static function add_metadata($meta_type, $object_id, $meta_key, $meta_value, $unique = false) {
		global $wpdb;
		
		if ( ! $meta_type || ! $meta_key || ! is_numeric( $object_id ) ) {
			return false;
		}

		$object_id = absint( $object_id );
		if ( ! $object_id ) {
			return false;
		}

		$table = BASe()->meta_table;
		$column = 'object_id';

		// expected_slashed ($meta_key)
		$meta_key = wp_unslash($meta_key);
		$meta_value = wp_unslash($meta_value);
		$meta_value = self::sanitize_meta( $meta_key, $meta_value, $meta_type );

		/**
		 * Filters whether to add metadata of a specific type.
		 *
		 * The dynamic portion of the hook, `$meta_type`, refers to the meta
		 * object type (app, service, or location). Returning a non-null value
		 * will effectively short-circuit the function.
		 *
		 * @param null|bool $check      Whether to allow adding metadata for the given type.
		 * @param int       $object_id  Object ID.
		 * @param string    $meta_key   Meta key.
		 * @param mixed     $meta_value Meta value. Must be serializable if non-scalar.
		 * @param bool      $unique     Whether the specified meta key should be unique
		 *                              for the object. Optional. Default false.
		 */
		$check = apply_filters( "app_add_{$meta_type}_metadata", null, $object_id, $meta_key, $meta_value, $unique );
		if ( null !== $check )
			return $check;

		if ( $unique && $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM $table WHERE meta_type = %s AND meta_key = %s AND $column = %d",
			$meta_type, $meta_key, $object_id ) ) )
			return false;

		$_meta_value = $meta_value;
		$meta_value = maybe_serialize( $meta_value );

		/**
		 * Fires immediately before meta of a specific type is added.
		 *
		 * The dynamic portion of the hook, `$meta_type`, refers to the meta
		 * object type (app, service, or location).
		 *
		 * @param int    $object_id  Object ID.
		 * @param string $meta_key   Meta key.
		 * @param mixed  $meta_value Meta value.
		 */
		do_action( "app_add_{$meta_type}_meta", $object_id, $meta_key, $_meta_value );

		$result = $wpdb->insert( $table, array(
			$column => $object_id,
			'meta_type' => $meta_type,
			'meta_key' => $meta_key,
			'meta_value' => $meta_value
		) );

		if ( ! $result )
			return false;

		$mid = (int) $wpdb->insert_id;

		wp_cache_delete($object_id, $meta_type . '_meta');

		/**
		 * Fires immediately after meta of a specific type is added.
		 *
		 * The dynamic portion of the hook, `$meta_type`, refers to the meta
		 * object type (app, service, or location).
		 *
		 * @param int    $mid        The meta ID after successful update.
		 * @param int    $object_id  Object ID.
		 * @param string $meta_key   Meta key.
		 * @param mixed  $meta_value Meta value.
		 */
		do_action( "app_added_{$meta_type}_meta", $mid, $object_id, $meta_key, $_meta_value );

		return $mid;
	}

	/**
	 * Update metadata for the specified object. If no value already exists for the specified object
	 * ID and metadata key, the metadata will be added.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $meta_type  Type of object metadata is for (e.g., app, service, location)
	 * @param int    $object_id  ID of the object metadata is for
	 * @param string $meta_key   Metadata key
	 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
	 * @param mixed  $prev_value Optional. If specified, only update existing metadata entries with
	 * 		                     the specified value. Otherwise, update all entries.
	 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
	 */
	public static function update_metadata($meta_type, $object_id, $meta_key, $meta_value, $prev_value = '') {
		global $wpdb;

		if ( ! $meta_type || ! $meta_key || ! is_numeric( $object_id ) ) {
			return false;
		}

		$object_id = absint( $object_id );
		if ( ! $object_id ) {
			return false;
		}

		$table = BASe()->meta_table;
		$column = 'object_id';
		$id_column = 'meta_id';

		// expected_slashed ($meta_key)
		$raw_meta_key = $meta_key;
		$meta_key = wp_unslash($meta_key);
		$passed_value = $meta_value;
		$meta_value = wp_unslash($meta_value);
		$meta_value = self::sanitize_meta( $meta_key, $meta_value, $meta_type );

		/**
		 * Filters whether to update metadata of a specific type.
		 *
		 * The dynamic portion of the hook, `$meta_type`, refers to the meta
		 * object type (app, service, or location). Returning a non-null value
		 * will effectively short-circuit the function.
		 *
		 *
		 * @param null|bool $check      Whether to allow updating metadata for the given type.
		 * @param int       $object_id  Object ID.
		 * @param string    $meta_key   Meta key.
		 * @param mixed     $meta_value Meta value. Must be serializable if non-scalar.
		 * @param mixed     $prev_value Optional. If specified, only update existing
		 *                              metadata entries with the specified value.
		 *                              Otherwise, update all entries.
		 */
		$check = apply_filters( "app_update_{$meta_type}_metadata", null, $object_id, $meta_key, $meta_value, $prev_value );
		if ( null !== $check )
			return (bool) $check;

		// Compare existing value to new value if no prev value given and the key exists only once.
		if ( empty($prev_value) ) {
			$old_value = self::get_metadata($meta_type, $object_id, $meta_key);
			if ( count($old_value) == 1 ) {
				if ( $old_value[0] === $meta_value )
					return false;
			}
		}

		$meta_ids = $wpdb->get_col( $wpdb->prepare( "SELECT $id_column FROM $table WHERE meta_type = %s AND meta_key = %s AND $column = %d", $meta_type, $meta_key, $object_id ) );
		if ( empty( $meta_ids ) ) {
			return self::add_metadata( $meta_type, $object_id, $raw_meta_key, $passed_value );
		}

		$_meta_value = $meta_value;
		$meta_value = maybe_serialize( $meta_value );

		$data  = compact( 'meta_value' );
		$where = array( $column => $object_id, 'meta_key' => $meta_key );

		if ( !empty( $prev_value ) ) {
			$prev_value = maybe_serialize($prev_value);
			$where['meta_value'] = $prev_value;
		}

		foreach ( $meta_ids as $meta_id ) {
			/**
			 * Fires immediately before updating metadata of a specific type.
			 *
			 * The dynamic portion of the hook, `$meta_type`, refers to the meta
			 * object type (app, service, or location).
			 *
			 * @param int    $meta_id    ID of the metadata entry to update.
			 * @param int    $object_id  Object ID.
			 * @param string $meta_key   Meta key.
			 * @param mixed  $meta_value Meta value.
			 */
			do_action( "app_update_{$meta_type}_meta", $meta_id, $object_id, $meta_key, $_meta_value );

		}

		$result = $wpdb->update( $table, $data, $where );
		if ( ! $result )
			return false;

		wp_cache_delete($object_id, $meta_type . '_meta');

		foreach ( $meta_ids as $meta_id ) {
			/**
			 * Fires immediately after updating metadata of a specific type.
			 *
			 * The dynamic portion of the hook, `$meta_type`, refers to the meta
			 * object type (app, service, or location).
			 *
			 * @param int    $meta_id    ID of updated metadata entry.
			 * @param int    $object_id  Object ID.
			 * @param string $meta_key   Meta key.
			 * @param mixed  $meta_value Meta value.
			 */
			do_action( "app_updated_{$meta_type}_meta", $meta_id, $object_id, $meta_key, $_meta_value );

		}

		return true;
	}

	/**
	 * Delete metadata for the specified object.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $meta_type  Type of object metadata is for (e.g., app, service, location)
	 * @param int    $object_id  ID of the object metadata is for
	 * @param string $meta_key   Metadata key
	 * @param mixed  $meta_value Optional. Metadata value. Must be serializable if non-scalar. If specified, only delete
	 *                           metadata entries with this value. Otherwise, delete all entries with the specified meta_key.
	 *                           Pass `null, `false`, or an empty string to skip this check. (For backward compatibility,
	 *                           it is not possible to pass an empty string to delete those entries with an empty string
	 *                           for a value.)
	 * @param bool   $delete_all Optional, default is false. If true, delete matching metadata entries for all objects,
	 *                           ignoring the specified object_id. Otherwise, only delete matching metadata entries for
	 *                           the specified object_id.
	 * @return bool True on successful delete, false on failure.
	 */
	public static function delete_metadata($meta_type, $object_id, $meta_key, $meta_value = '', $delete_all = false) {
		global $wpdb;

		if ( ! $meta_type || ! $meta_key || ! is_numeric( $object_id ) && ! $delete_all ) {
			return false;
		}

		$object_id = absint( $object_id );
		if ( ! $object_id && ! $delete_all ) {
			return false;
		}

		$table = BASe()->meta_table;
		$type_column = 'object_id';
		$id_column = 'meta_id';
		// expected_slashed ($meta_key)
		$meta_key = wp_unslash($meta_key);
		$meta_value = wp_unslash($meta_value);

		/**
		 * Filters whether to delete metadata of a specific type.
		 *
		 * The dynamic portion of the hook, `$meta_type`, refers to the meta
		 * object type (app, service, or location). Returning a non-null value
		 * will effectively short-circuit the function.
		 *
		 * @param null|bool $delete     Whether to allow metadata deletion of the given type.
		 * @param int       $object_id  Object ID.
		 * @param string    $meta_key   Meta key.
		 * @param mixed     $meta_value Meta value. Must be serializable if non-scalar.
		 * @param bool      $delete_all Whether to delete the matching metadata entries
		 *                              for all objects, ignoring the specified $object_id.
		 *                              Default false.
		 */
		$check = apply_filters( "app_delete_{$meta_type}_metadata", null, $object_id, $meta_key, $meta_value, $delete_all );
		if ( null !== $check )
			return (bool) $check;

		$_meta_value = $meta_value;
		$meta_value = maybe_serialize( $meta_value );

		$query = $wpdb->prepare( "SELECT $id_column FROM $table WHERE meta_type = %s AND meta_key = %s", $meta_type, $meta_key );

		if ( !$delete_all )
			$query .= $wpdb->prepare(" AND $type_column = %d", $object_id );

		if ( '' !== $meta_value && null !== $meta_value && false !== $meta_value )
			$query .= $wpdb->prepare(" AND meta_value = %s", $meta_value );

		$meta_ids = $wpdb->get_col( $query );
		if ( !count( $meta_ids ) )
			return false;

		if ( $delete_all ) {
			$value_clause = '';
			if ( '' !== $meta_value && null !== $meta_value && false !== $meta_value ) {
				$value_clause = $wpdb->prepare( " AND meta_value = %s", $meta_value );
			}

			$object_ids = $wpdb->get_col( $wpdb->prepare( "SELECT $type_column FROM $table WHERE meta_type = %s AND meta_key = %s $value_clause", $meta_type, $meta_key ) );
		}

		/**
		 * Fires immediately before deleting metadata of a specific type.
		 *
		 * The dynamic portion of the hook, `$meta_type`, refers to the meta
		 * object type (app, service, or location).
		 *
		 * @param array  $meta_ids   An array of metadata entry IDs to delete.
		 * @param int    $object_id  Object ID.
		 * @param string $meta_key   Meta key.
		 * @param mixed  $meta_value Meta value.
		 */
		do_action( "app_delete_{$meta_type}_meta", $meta_ids, $object_id, $meta_key, $_meta_value );

		$query = "DELETE FROM $table WHERE $id_column IN( " . implode( ',', $meta_ids ) . " )";

		$count = $wpdb->query($query);

		if ( !$count )
			return false;

		if ( $delete_all ) {
			foreach ( (array) $object_ids as $o_id ) {
				wp_cache_delete($o_id, $meta_type . '_meta');
			}
		} else {
			wp_cache_delete($object_id, $meta_type . '_meta');
		}

		/**
		 * Fires immediately after deleting metadata of a specific type.
		 *
		 * The dynamic portion of the hook name, `$meta_type`, refers to the meta
		 * object type (app, service, or location).
		 *
		 * @param array  $meta_ids   An array of deleted metadata entry IDs.
		 * @param int    $object_id  Object ID.
		 * @param string $meta_key   Meta key.
		 * @param mixed  $meta_value Meta value.
		 */
		do_action( "app_deleted_{$meta_type}_meta", $meta_ids, $object_id, $meta_key, $_meta_value );


		return true;
	}

	/**
	 * Retrieve metadata for the specified object.
	 *
	 * @param string $meta_type Type of object metadata is for (e.g., app, service, location)
	 * @param int    $object_id ID of the object metadata is for
	 * @param string $meta_key  Optional. Metadata key. If not specified, retrieve all metadata for
	 * 		                    the specified object.
	 * @param bool   $single    Optional, default is false.
	 *                          If true, return only the first value of the specified meta_key.
	 *                          This parameter has no effect if meta_key is not specified.
	 * @return mixed Single metadata value, or array of values
	 */
	public static function get_metadata($meta_type, $object_id, $meta_key = '', $single = false) {
		if ( ! $meta_type || ! is_numeric( $object_id ) ) {
			return false;
		}

		$object_id = absint( $object_id );
		if ( ! $object_id ) {
			return false;
		}

		/**
		 * Filters whether to retrieve metadata of a specific type.
		 *
		 * The dynamic portion of the hook, `$meta_type`, refers to the meta
		 * object type (app, service, or location). Returning a non-null value
		 * will effectively short-circuit the function.
		 *
		 * @since 3.1.0
		 *
		 * @param null|array|string $value     The value get_metadata() should return - a single metadata value,
		 *                                     or an array of values.
		 * @param int               $object_id Object ID.
		 * @param string            $meta_key  Meta key.
		 * @param bool              $single    Whether to return only the first value of the specified $meta_key.
		 */
		$check = apply_filters( "app_get_{$meta_type}_metadata", null, $object_id, $meta_key, $single );
		if ( null !== $check ) {
			if ( $single && is_array( $check ) )
				return $check[0];
			else
				return $check;
		}

		$meta_cache = wp_cache_get($object_id, $meta_type . '_meta');
	
		if ( !$meta_cache ) {
			$meta_cache = self::update_meta_cache( $meta_type, array( $object_id ) );
			$meta_cache = $meta_cache[$object_id];
		}

		if ( ! $meta_key ) {
			return $meta_cache;
		}

		if ( isset($meta_cache[$meta_key]) ) {
			if ( $single )
				return maybe_unserialize( $meta_cache[$meta_key][0] );
			else
				return array_map('maybe_unserialize', $meta_cache[$meta_key]);
		}

		if ($single)
			return '';
		else
			return array();
	}

	/**
	 * Update the metadata cache for the specified objects.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string    $meta_type  Type of object metadata is for (e.g., app, service, location)
	 * @param int|array $object_ids Array or comma delimited list of object IDs to update cache for
	 * @return array|false Metadata cache for the specified objects, or false on failure.
	 */
	public static function update_meta_cache($meta_type, $object_ids) {
		global $wpdb;

		if ( ! $meta_type || ! $object_ids ) {
			return false;
		}

		$table = BASe()->meta_table;
		$column = 'object_id';

		if ( !is_array($object_ids) ) {
			$object_ids = preg_replace('|[^0-9,]|', '', $object_ids);
			$object_ids = explode(',', $object_ids);
		}

		$object_ids = array_map('intval', $object_ids);

		$cache_key = $meta_type . '_meta';
		$ids = array();
		$cache = array();
		foreach ( $object_ids as $id ) {
			$cached_object = wp_cache_get( $id, $cache_key );
			if ( false === $cached_object )
				$ids[] = $id;
			else
				$cache[$id] = $cached_object;
		}

		if ( empty( $ids ) )
			return $cache;

		// Get meta info
		$id_list = join( ',', $ids );
		$id_column = 'meta_id';
		$q = $wpdb->prepare( "SELECT $column, meta_type, meta_key, meta_value FROM $table WHERE meta_type = %s AND $column IN ($id_list) ORDER BY $id_column ASC", $meta_type );		
		$meta_list = $wpdb->get_results( $q, ARRAY_A );

		if ( !empty($meta_list) ) {
			foreach ( $meta_list as $metarow) {
				$mpid = intval($metarow[$column]);
				$mkey = $metarow['meta_key'];
				$mval = $metarow['meta_value'];

				// Force subkeys to be array type:
				if ( !isset($cache[$mpid]) || !is_array($cache[$mpid]) )
					$cache[$mpid] = array();
				if ( !isset($cache[$mpid][$mkey]) || !is_array($cache[$mpid][$mkey]) )
					$cache[$mpid][$mkey] = array();

				// Add a value to the current pid/key:
				$cache[$mpid][$mkey][] = $mval;
			}
		}

		foreach ( $ids as $id ) {
			if ( ! isset($cache[$id]) )
				$cache[$id] = array();
			wp_cache_add( $id, $cache[$id], $cache_key );
		}

		return $cache;
	}

	/**
	 * Sanitize meta value.
	 *
	 * @param string $meta_key       Meta key.
	 * @param mixed  $meta_value     Meta value to sanitize.
	 * @param string $object_type    Type of object the meta is registered to.
	 *
	 * @return mixed Sanitized $meta_value.
	 */
	public static function sanitize_meta( $meta_key, $meta_value, $object_type ) {
		/**
		 * Filters the sanitization of a specific meta key of a specific meta type.
		 *
		 * The dynamic portions of the hook name, `$meta_type`, and `$meta_key`,
		 * refer to the metadata object type (app, service, or location) and the meta
		 * key value, respectively.
		 *
		 * @since 3.3.0
		 *
		 * @param mixed  $meta_value      Meta value to sanitize.
		 * @param string $meta_key        Meta key.
		 * @param string $object_type     Object type.
		 */
		return apply_filters( "app_sanitize_{$object_type}_meta_{$meta_key}", $meta_value, $meta_key, $object_type );
	}
	
	/**
	 * Delete meta data by object ID
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $meta_type Type of object metadata is for (e.g., app, service, location).
	 * @param int    $object_id   ID for a specific meta row
	 * @return bool True on successful delete, false on failure.
	 */
	public static function delete_metadata_by_oid( $meta_type, $object_id ) {
		global $wpdb;

		// Make sure everything is valid.
		if ( ! $meta_type || ! is_numeric( $object_id ) ) {
			return false;
		}

		$object_id = absint( $object_id );
		if ( ! $object_id ) {
			return false;
		}

		$table = BASe()->meta_table;

		// Run the query, will return true if deleted, false otherwise
		$result = (bool) $wpdb->delete( $table, array( 'meta_type' => $meta_type, 'object_id' => $object_id ) );

		// Clear the caches.
		wp_cache_delete($object_id, $meta_type . '_meta');

		return $result;
	}

}
}