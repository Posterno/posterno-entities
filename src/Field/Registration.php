<?php
/**
 * Defines a representation of a Posterno registration field entity.
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
class Registration extends AbstractEntityField {

	/**
	 * The prefix used by Carbon Fields to store field's settings.
	 *
	 * @var string
	 */
	protected $field_setting_prefix = 'registration_field_';

	/**
	 * The post type where these type of fields are stored.
	 *
	 * @var string
	 */
	protected $post_type = 'pno_signup_fields';

	/**
	 * Registration fields are associated to a profile field when
	 * they're not default fields.
	 *
	 * @var boolean|string
	 */
	protected $profile_field_id = false;

	/**
	 * Retrieve the profile field id attached to the registration field.
	 *
	 * @return string|boolean
	 */
	public function getProfileFieldID() {
		return $this->profile_field_id;
	}

	/**
	 * Parse settings.
	 *
	 * @param mixed $settings settings found.
	 * @return void
	 */
	public function parseSettings( $settings ) {

		$settings = maybe_unserialize( $settings );

		if ( is_array( $settings ) ) {
			$this->settings = $settings;

			foreach ( $settings as $setting => $value ) {
				$setting = $this->removeSettingPrefix( '_registration_field_', $setting );
				switch ( $setting ) {
					case 'is_required':
						$this->required = $value;
						break;
					case 'is_default':
						$this->object_meta_key = $value;
						break;
					default:
						$this->{$setting} = $value;
						break;
				}
			}
		}

		$this->can_delete = pno_is_default_field( $this->object_meta_key ) ? false : true;

		$types = pno_get_registered_field_types();

		if ( ! $this->canDelete() ) {

			$type = 'text';

			switch ( $this->getObjectMetaKey() ) {
				case 'password':
					$type = 'password';
					break;
				case 'email':
					$type = 'email';
					break;
			}

			$this->type          = $type;
			$this->type_nicename = isset( $types[ $this->type ] ) ? $types[ $this->type ] : false;

			// Force requirement for the email field.
			if ( $this->getObjectMetaKey() === 'email' ) {
				$this->required = true;
			}
		}

		// Attach profile field to the registration field if not a default field.
		if ( ! empty( $this->getProfileFieldID() ) ) {

			$profile_field = new \PNO\Database\Queries\Profile_Fields();
			$profile_field = $profile_field->get_item_by( 'post_id', $this->getProfileFieldID() );

			if ( $profile_field instanceof Profile ) {
				$this->type            = $profile_field->getType();
				$this->type_nicename   = isset( $types[ $profile_field->getType() ] ) ? $types[ $profile_field->getType() ] : false;
				$this->object_meta_key = $profile_field->getObjectMetaKey();

				if ( in_array( $profile_field->getType(), pno_get_multi_options_field_types() ) ) {
					$this->multiple = true;
				}

				if ( is_array( $profile_field->getOptions() ) && ! empty( $profile_field->getOptions() ) ) {
					$this->options = $profile_field->getOptions();
				}
			}
		}

	}

	/**
	 * Create a new field and save it into the database.
	 *
	 * @param array $args list of arguments to create a new field.
	 * @throws InvalidArgumentException When missing arguments.
	 * @return string
	 */
	public static function create( $args = [] ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! isset( $args['name'] ) || empty( $args['name'] ) ) {
			throw new InvalidArgumentException( sprintf( __( 'Can\'t find property %s', 'posterno' ), 'name' ) );
		}

		if ( ! isset( $args['profile_field_id'] ) || empty( $args['profile_field_id'] ) ) {
			throw new InvalidArgumentException( sprintf( __( 'Can\'t find property %s', 'posterno' ), 'profile_field_id' ) );
		}

		$field_args = [
			'post_type'   => 'pno_signup_fields',
			'post_title'  => $args['name'],
			'post_status' => 'publish',
		];

		$field_id = wp_insert_post( $field_args );

		if ( ! is_wp_error( $field_id ) ) {

			$field = new \PNO\Database\Queries\Registration_Fields();
			$field->add_item(
				[
					'post_id'          => $field_id,
					'profile_field_id' => isset( $args['profile_field_id'] ) ? absint( $args['profile_field_id'] ) : false,
				]
			);

			if ( isset( $args['profile_field_id'] ) ) {
				carbon_set_post_meta( $field_id, 'registration_field_profile_field_id', $args['profile_field_id'] );
			}

			if ( isset( $args['priority'] ) && ! empty( $args['priority'] ) ) {
				carbon_set_post_meta( $field_id, 'registration_field_priority', $args['priority'] );
			}

			return $field->get_item_by( 'post_id', $field_id );

		}

		return false;

	}

	/**
	 * Delete a field from the database and delete it's associated settings too.
	 *
	 * @param string  $post_id the id of the post belonging to the field.
	 * @param boolean $force whether to force cancellation or not.
	 * @return mixed
	 */
	public static function delete( $post_id, $force = false ) {

		if ( ! current_user_can( 'manage_options' ) && ! $force ) {
			return;
		}

		wp_delete_post( $post_id, true );

		$field = new \PNO\Database\Queries\Registration_Fields();

		$found_field = $field->get_item_by( 'post_id', $post_id );

		if ( $found_field->getPostID() > 0 && ( $found_field->canDelete() || $force === true ) ) {
			$field->delete_item( $found_field->getEntityID() );
		} else {
			return new WP_Error( 'cannot_delete', esc_html__( 'Default fields cannot be deleted.', 'posterno' ) );
		}
	}

	/**
	 * Get a registration field from a post id.
	 *
	 * @param string $post_id the id of the registration field.
	 * @return Registration
	 */
	public static function getFromID( $post_id ) {

		$field = new \PNO\Database\Queries\Registration_Fields();

		return $field->get_item_by( 'post_id', $post_id );

	}

}
