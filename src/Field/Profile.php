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
	 * Delete a profile field from the database.
	 *
	 * @param string $post_id the id of the field to delete.
	 * @return void
	 */
	public static function delete( $post_id ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_delete_post( $post_id, true );

		$field = new \PNO\Database\Queries\Profile_Fields();

		$found_field = $field->get_item_by( 'post_id', $post_id );

		$field->delete_item( $found_field->getEntityID() );

		// Delete registration field automatically if found attached.
		$reg_field_query             = new \PNO\Database\Queries\Registration_Fields();
		$attached_registration_field = $reg_field_query->get_item_by( 'profile_field_id', $post_id );

		if ( $attached_registration_field->getPostID() > 0 ) {
			if ( $attached_registration_field->canDelete() ) {
				$attached_registration_field::delete( $attached_registration_field->getPostID() );
			}
		}

	}

}
