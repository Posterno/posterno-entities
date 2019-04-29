<?php
/**
 * Defines a representation of a Posterno entity.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Entities;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Basic representation of each entity.
 */
abstract class AbstractEntity {

	/**
	 * Populate the object.
	 *
	 * @param mixed $args data with which we're going to populate the object.
	 */
	public function __construct( $args = null ) {
		$this->set_vars( $args );
	}

	/**
	 * Determine if a key has been set or not.
	 *
	 * @param string $key the key to verify.
	 * @return boolean
	 */
	public function __isset( $key = '' ) {

		if ( 'ID' === $key ) {
			$key = 'id';
		}

		$method = "get_{$key}";

		if ( method_exists( $this, $method ) ) {
			return true;

		} elseif ( property_exists( $this, $key ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get a property.
	 *
	 * @param string $key the property to retrieve.
	 * @return mixed
	 */
	public function __get( $key = '' ) {

		if ( 'ID' === $key ) {
			$key = 'id';
		}

		$method = "get_{$key}";

		if ( method_exists( $this, $method ) ) {
			return call_user_func( array( $this, $method ) );

		} elseif ( property_exists( $this, $key ) ) {
			return $this->{$key};
		}

		return null;
	}

	/**
	 * Conver the object properties to an array.
	 *
	 * @return array
	 */
	public function to_array() {
		return get_object_vars( $this );
	}

	/**
	 * Setup properties into the object.
	 *
	 * @param array $args properties and values to set.
	 * @return void
	 */
	protected function set_vars( $args = array() ) {

		if ( empty( $args ) ) {
			return;
		}

		if ( ! is_array( $args ) ) {
			$args = (array) $args;
		}

		foreach ( $args as $key => $value ) {
			$this->{$key} = $value;
		}
	}

}
