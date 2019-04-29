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

/**
 * Representation of a Posterno field entity.
 */
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
	 * Field title.
	 *
	 * @access protected
	 * @var string
	 */
	protected $title = null;

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

		if ( ! empty( $this->getPostID() ) ) {
			$this->setTitle( $this->getPostID() );
		}
	}

	/**
	 * Get the entity id number.
	 *
	 * @return string
	 */
	public function getEntityID() {
		return $this->id;
	}

	/**
	 * Field id number associated to the entity.
	 *
	 * @return string
	 */
	public function getPostID() {
		return $this->post_id;
	}

	/**
	 * Get the meta key used to store data into the database.
	 *
	 * @return string
	 */
	public function getObjectMetaKey() {
		return $this->object_meta_key;
	}

	/**
	 * Prefix used by Carbon field to store settings into the database.
	 *
	 * @return string
	 */
	public function getFieldSettingsPrefix() {
		return $this->field_setting_prefix;
	}

	/**
	 * The postype to which the field belongs to.
	 *
	 * @return string
	 */
	public function getPostType() {
		return $this->post_type;
	}

	/**
	 * Priority number assigned to the field.
	 *
	 * @return string
	 */
	public function getPriority() {
		return $this->priority;
	}

	/**
	 * Whether the field can be deleted or not.
	 *
	 * @return boolean
	 */
	public function canDelete() {
		return (bool) $this->can_delete;
	}

	/**
	 * Field type.
	 *
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Field type human readable name.
	 *
	 * @return string
	 */
	public function getTypeNicename() {
		return $this->type_nicename;
	}

	/**
	 * All settings that belong to the field.
	 *
	 * @return string|array
	 */
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
	 * Set the title of the field by using a post id.
	 *
	 * @param string $id the id of the post.
	 * @return void
	 */
	public function setTitle( $id ) {
		$this->title = get_the_title( $id );
	}

	/**
	 * Get field's title.
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Retrieve a specific setting.
	 *
	 * @param string $setting the setting to retrieve.
	 * @return mixed
	 */
	public function getSetting( $setting ) {

		$value = false;

		$setting = '_' . $this->getFieldSettingsPrefix() . $setting;

		if ( isset( $this->getSettings()[ $setting ] ) ) {
			return $this->getSettings()[ $setting ];
		}

		return $value;

	}

	/**
	 * Determine if a field is required.
	 *
	 * @return boolean
	 */
	public function isRequired() {
		return $this->getSetting( 'is_required' ) ? true : false;
	}

	/**
	 * Parse settings retrieve for the field.
	 *
	 * @param mixed $settings the settings retrieved,
	 * @return void
	 */
	abstract public function parseSettings( $settings );

	/**
	 * Delete a field from the database.
	 *
	 * @param string $post_id the id of the post to delete.
	 * @return void
	 */
	abstract public static function delete( $post_id );

}
