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
	 * Get things started.
	 *
	 * @param mixed $object id or object of a field entity.
	 */
	public function __construct( $object = null ) {
		parent::__construct( $object );
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

	}

}
