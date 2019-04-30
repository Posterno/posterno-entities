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
	 * Field form label.
	 *
	 * @access protected
	 * @var string
	 */
	protected $label = null;

	/**
	 * Field description.
	 *
	 * @access protected
	 * @var string
	 */
	protected $description = null;

	/**
	 * Field placeholder.
	 *
	 * @access protected
	 * @var string
	 */
	protected $placeholder = null;

	/**
	 * Determine wether the field is readonly or not.
	 *
	 * @var boolean
	 */
	protected $readonly = false;

	/**
	 * Determine wether the field is admin only or not.
	 *
	 * @var boolean
	 */
	protected $admin_only = false;

	/**
	 * Selectable options for dropdown fields.
	 *
	 * @var mixed
	 */
	protected $options = [];

	/**
	 * Allowed mime types for upload of file fields.
	 *
	 * @var boolean|array
	 */
	protected $allowed_mime_types = false;

	/**
	 * Determine if the field can store multiple values eg: arrays.
	 *
	 * @var boolean
	 */
	protected $multiple = false;

	/**
	 * Holds the max size for files uploadable through this field.
	 *
	 * @var string
	 */
	protected $maxsize = null;

	/**
	 * Value associated to the field.
	 *
	 * @var mixed
	 */
	protected $value = false;

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
	 * Get the form label for this field.
	 *
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * Get the description for forms assigned to the field.
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Get a placeholder for the field within forms if specified.
	 *
	 * @return mixed
	 */
	public function getPlaceholder() {
		return $this->placeholder;
	}

	/**
	 * Flag to detect if the field is readonly or not.
	 *
	 * @return boolean
	 */
	public function isReadOnly() {
		return (bool) $this->readonly;
	}

	/**
	 * Flag to detect whether the field is admin only.
	 *
	 * @return boolean
	 */
	public function isAdminOnly() {
		return (bool) $this->admin_only;
	}

	/**
	 * Get the value associated with the field.
	 *
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Retrieve selectable options for this field if needed.
	 *
	 * @return mixed
	 */
	public function getOptions() {
		return apply_filters( "pno_field_{$this->object_meta_key}_selectable_options", $this->options );
	}

	/**
	 * Retrieve mime types defined for the field.
	 *
	 * @return mixed
	 */
	public function getAllowedMimeTypes() {
		return $this->allowed_mime_types;
	}

	/**
	 * Verify if the field can store multiple values eg: arrays.
	 *
	 * @return boolean
	 */
	public function isMultiple() {
		return (bool) $this->multiple;
	}

	/**
	 * Retrieve the specified max size allowed for files within this field.
	 *
	 * @return null|string
	 */
	public function getMaxSize() {
		return $this->maxsize;
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
	 * Setup the priority of the field.
	 *
	 * @param string $priority the new priority level for the field.
	 * @return void
	 */
	public function setPriority( $priority ) {
		carbon_set_post_meta( $this->getPostID(), $this->getFieldSettingsPrefix() . 'priority', absint( $priority ) );
	}

	/**
	 * Parse settings retrieve for the field.
	 *
	 * @param mixed $settings the settings retrieved,
	 * @return void
	 */
	abstract public function parseSettings( $settings );

	/**
	 * Create a field.
	 *
	 * @param array $args details about the new field.
	 * @return void
	 */
	abstract public static function create( $args = [] );

	/**
	 * Delete a field from the database.
	 *
	 * @param string $post_id the id of the post to delete.
	 * @return void
	 */
	abstract public static function delete( $post_id );

	/**
	 * Get a field from a post id.
	 *
	 * @param string $post_id the post id.
	 * @return void
	 */
	abstract public static function getFromID( $post_id );

}
