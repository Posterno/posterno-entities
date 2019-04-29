<?php
/**
 * Defines a representation of a Posterno field entity.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Entities;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

abstract class AbstractEntityField extends AbstractEntity {

	/**
	 * ID number of the entity details stored into the database.
	 *
	 * @access protected
	 * @var string
	 */
	protected $id = null;

	/**
	 * Field post ID from the database.
	 *
	 * @access protected
	 * @var int
	 */
	protected $post_id = 0;

	/**
	 * Meta key that will be used to store values into the database.
	 *
	 * @access protected
	 * @var mixed
	 */
	protected $object_meta_key = null;

	/**
	 * The prefix used by Carbon Fields to store field's settings.
	 *
	 * @var string
	 */
	protected $field_setting_prefix = '';

	/**
	 * The post type where the field is stored.
	 *
	 * @var boolean|string
	 */
	protected $post_type = false;

	/**
	 * Field priority number used to determine the order of the field.
	 *
	 * @access protected
	 * @var int
	 */
	protected $priority = 0;

	/**
	 * Wether the field is a default field or not.
	 *
	 * @var boolean
	 */
	protected $can_delete = true;

	/**
	 * The field type.
	 *
	 * @var boolean
	 */
	protected $type = false;

	/**
	 * The human readable name of the set field type.
	 *
	 * @var string
	 */
	protected $type_nicename = null;

	/**
	 * All settings related to the field.
	 *
	 * @var string
	 */
	protected $settings = null;

	/**
	 * Populate the field properties.
	 *
	 * @param mixed $args items with which we're going to populate the field properties.
	 */
	public function __construct( $args = null ) {

		parent::__construct( $args );

		$this->parseSettings( $this->getSettings() );

	}

	public function getEntityID() {
		return $this->id;
	}

	public function getPostID() {
		return $this->post_id;
	}

	public function getObjectMetaKey() {
		return $this->object_meta_key;
	}

	public function getFieldSettingsPrefix() {
		return $this->field_setting_prefix;
	}

	public function getPostType() {
		return $this->post_type;
	}

	public function getPriority() {
		return $this->priority;
	}

	public function canDelete() {
		return (bool) $this->can_delete;
	}

	public function getType() {
		return $this->type;
	}

	public function getTypeNicename() {
		return $this->type_nicename;
	}

	public function getSettings() {
		return $this->settings;
	}

	/**
	 * Remove a prefix from the field id.
	 *
	 * @param string $prefix string to remove.
	 * @param string $id field id.
	 * @return string
	 */
	public function removeSettingPrefix( $prefix, $id ) {
		return str_replace( $prefix, '', $id );
	}

	/**
	 * Parse settings retrieve for the field.
	 *
	 * @param mixed $settings the settings retrieved,
	 * @return void
	 */
	abstract public function parseSettings( $settings );

}
