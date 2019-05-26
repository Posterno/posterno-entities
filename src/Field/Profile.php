<?php
/**
 * Defines a representation of a Posterno profile field entity.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Pressmodo, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO\Entities\Field;

use PNO\Entities\AbstractEntityField;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Representation of a registration field.
 */
class Profile extends AbstractEntityField {

	/**
	 * The user id assigned to the field.
	 *
	 * @var string
	 */
	public $user_id;

	/**
	 * The prefix used by Carbon Fields to store field's settings.
	 *
	 * @var string
	 */
	protected $field_setting_prefix = 'profile_field_';

	/**
	 * The post type where these type of fields are stored.
	 *
	 * @var string
	 */
	protected $post_type = 'pno_users_fields';

	/**
	 * Parse settings.
	 *
	 * @param mixed $settings settings to parse.
	 * @return void
	 */
	public function parseSettings( $settings ) {

		$settings = maybe_unserialize( $settings );

		if ( is_array( $settings ) ) {
			$this->settings = $settings;

			foreach ( $settings as $setting => $value ) {
				$setting = $this->removeSettingPrefix( '_profile_field_', $setting );
				switch ( $setting ) {
					case 'is_required':
						$this->required = $value;
						break;
					case 'meta_key':
						$this->object_meta_key = $value;
						break;
					case 'is_read_only':
						$this->readonly = $value;
						break;
					case 'is_admin_only':
						$this->admin_only = $value;
						break;
					case 'selectable_options':
						$this->options = pno_parse_selectable_options( $value );
						break;
					case 'file_max_size':
						$this->maxsize = $value;
						break;
					case 'file_extensions':
						$this->allowed_mime_types = maybe_unserialize( $value );
						break;
					case 'file_is_multiple':
						$this->multiple = $this->getType() === 'file' ? true : false;
						break;
					default:
						$this->{$setting} = $value;
						break;
				}
			}
		}

		$types               = pno_get_registered_field_types();
		$this->type_nicename = isset( $types[ $this->getType() ] ) ? $types[ $this->getType() ] : false;

		if ( in_array( $this->getType(), pno_get_multi_options_field_types() ) ) {
			$this->multiple = true;
		}

		$this->can_delete = pno_is_default_field( $this->getObjectMetaKey() ) ? false : true;

	}

	/**
	 * Crete a new profile field.
	 *
	 * @param array $args details about the field.
	 * @return mixed
	 */
	public static function create( $args = [] ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! isset( $args['name'] ) || empty( $args['name'] ) ) {
			throw new \InvalidArgumentException( sprintf( __( 'Can\'t find property %s', 'posterno' ), 'name' ) );
		}

		if ( empty( $args['meta'] ) ) {
			$meta         = sanitize_title( $args['name'] );
			$meta         = str_replace( '-', '_', $meta );
			$args['meta'] = $meta;
		}

		$field_args = [
			'post_type'   => 'pno_users_fields',
			'post_title'  => $args['name'],
			'post_status' => 'publish',
		];

		if ( isset( $args['meta'] ) && ! empty( $args['meta'] ) ) {
			if ( self::fieldMetaKeyExists( $args['meta'] ) ) {
				return new \WP_Error( 'field-meta-exists', esc_html__( 'A field with the same meta key has been found. Please choose a different name.', 'posterno' ) );
			}
		}

		$field_id = wp_insert_post( $field_args );

		if ( ! is_wp_error( $field_id ) ) {

			$field = new \PNO\Database\Queries\Profile_Fields();
			$field->add_item( [ 'post_id' => $field_id ] );

			if ( isset( $args['priority'] ) && ! empty( $args['priority'] ) ) {
				carbon_set_post_meta( $field_id, 'profile_field_priority', $args['priority'] );
			}

			if ( isset( $args['meta'] ) && ! empty( $args['meta'] ) ) {
				carbon_set_post_meta( $field_id, 'profile_field_meta_key', $args['meta'] );
			}

			if ( isset( $args['type'] ) && ! empty( $args['type'] ) ) {
				carbon_set_post_meta( $field_id, 'profile_field_type', $args['type'] );
			}

			return $field->get_item_by( 'post_id', $field_id );

		}

		return false;

	}

	/**
	 * Determine if a field using the same meta key already exists.
	 *
	 * @param string $meta the meta key to verify.
	 * @return boolean
	 */
	private static function fieldMetaKeyExists( $meta ) {

		$exists = false;

		$profile_field = new \PNO\Database\Queries\Profile_Fields();

		$query = $profile_field->get_item_by( 'user_meta_key', $meta );

		if ( isset( $query->post_id ) && $query->post_id > 0 ) {
			$exists = true;
		}

		return $exists;

	}

	/**
	 * Delete a profile field from the database.
	 *
	 * @param string  $post_id the id of the field to delete.
	 * @param boolean $force whether to force cancellation or not.
	 * @return mixed
	 */
	public static function delete( $post_id, $force = false ) {

		if ( ! current_user_can( 'manage_options' ) && ! $force ) {
			return;
		}

		wp_delete_post( $post_id, true );

		$field = new \PNO\Database\Queries\Profile_Fields();

		$found_field = $field->get_item_by( 'post_id', $post_id );

		if ( $found_field instanceof \PNO\Entities\Field\Profile && $found_field->getPostID() > 0 && ( $found_field->canDelete() || $force === true ) ) {

			$field->delete_item( $found_field->getEntityID() );

			// Delete registration field automatically if found attached.
			$reg_field_query             = new \PNO\Database\Queries\Registration_Fields();
			$attached_registration_field = $reg_field_query->get_item_by( 'profile_field_id', $post_id );

			if ( isset( $attached_registration_field->post_id ) && $attached_registration_field->getPostID() > 0 ) {
				if ( $attached_registration_field->canDelete() ) {
					$attached_registration_field::delete( $attached_registration_field->getPostID() );
				}
			}
		} else {

			return new \WP_Error( 'cannot_delete', esc_html__( 'Default fields cannot be deleted.', 'posterno' ) );

		}

	}

	/**
	 * Get a profile field by the post id.
	 *
	 * @param string $post_id post id of the field.
	 * @return Profile
	 */
	public static function getFromID( $post_id ) {

		$field = new \PNO\Database\Queries\Profile_Fields();

		return $field->get_item_by( 'post_id', $post_id );

	}

	/**
	 * Load the value associated with the field if a user id is given.
	 *
	 * @return void
	 */
	public function loadValue( $user_id ) {

		$meta_lookup = $this->getObjectMetaKey();

		if ( $meta_lookup === 'avatar' ) {
			$meta_lookup = 'current_user_avatar';
		}

		if ( pno_is_default_field( $meta_lookup ) ) {
			$this->value = get_user_meta( $user_id, $meta_lookup, true );
		} else {
			$this->value = carbon_get_user_meta( $user_id, $meta_lookup );
		}
	}

}
