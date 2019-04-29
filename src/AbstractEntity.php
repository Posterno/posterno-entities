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
	 * Load the entity either via ID or via an object and then populate the properties.
	 *
	 * @param mixed $_id_or_object item that we're going to load.
	 * @return AbstractEntity
	 */
	abstract public function load( $_id_or_object );

	/**
	 * Add a new entity to the database.
	 *
	 * @param mixed $args data to save.
	 * @return void
	 */
	abstract public function add( $args );

	/**
	 * Update an existing entity into the database.
	 *
	 * @return void
	 */
	abstract public function save();

	/**
	 * Delete an entity from the database.
	 *
	 * @return void
	 */
	abstract public function delete();

}
