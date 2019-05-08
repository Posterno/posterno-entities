<?php
/**
 * Defines a representation of a Posterno listing field entity.
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
 * Representation of a listing field.
 */
class Listing extends AbstractEntityField {

	/**
	 * The prefix used by Carbon Fields to store field's settings.
	 *
	 * @var string
	 */
	protected $field_setting_prefix = 'listing_field_';

	/**
	 * The post type where these type of fields are stored.
	 *
	 * @var string
	 */
	protected $post_type = 'pno_listings_fields';

	/**
	 * Holds the taxonomy id if the field is a terms selector.
	 *
	 * @var string
	 */
	protected $taxonomy = null;

	protected $disable_branch_nodes = false;

	/**
	 * Retrieve the attached taxonomy id to the field.
	 *
	 * @return string
	 */
	public function getTaxonomy() {
		return $this->taxonomy;
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
				$setting = $this->removeSettingPrefix( '_listing_field_', $setting );
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
					case 'taxonomy':
						$this->taxonomy = $value;
						$this->options  = pno_parse_selectable_options( $this->taxonomy );
						break;
					case 'file_extensions':
						$this->allowed_mime_types = maybe_unserialize( $value );
						break;
					case 'file_is_multiple':
						$this->multiple = $this->getType() === 'file' ? true : false;
						break;
					case 'chain_is_multiple':
						$this->multiple = $this->getType() === 'term-chain-dropdown' ? true : false;
						break;
					default:
						$this->{$setting} = $value;
						break;
				}
			}
		}

		$types               = pno_get_registered_field_types();
		$this->type_nicename = isset( $types[ $this->getType() ] ) ? $types[ $this->getType() ] : false;

		if ( empty( $this->getLabel() ) ) {
			$this->label = $this->getTitle();
		}

		if ( in_array( $this->getType(), pno_get_multi_options_field_types() ) ) {
			$this->multiple = true;
		}

		$this->can_delete = pno_is_default_field( $this->getObjectMetaKey() ) ? false : true;

	}

	/**
	 * Determine if the taxonomy chain dropdown field has branch nodes disabled.
	 *
	 * @return string
	 */
	public function isBranchNodesDisabled() {
		return (bool) $this->disable_branch_nodes;
	}

	/**
	 * Create a new field.
	 *
	 * @param array $args args.
	 * @return void
	 */
	public static function create( $args = [] ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! isset( $args['name'] ) || empty( $args['name'] ) ) {
			throw new \InvalidArgumentException( sprintf( __( 'Can\'t find property %s', 'posterno' ), 'name' ) );
		}

		if ( ! isset( $args['meta'] ) || empty( $args['meta'] ) ) {
			$meta         = sanitize_title( $args['name'] );
			$meta         = str_replace( '-', '_', $meta );
			$args['meta'] = $meta;
		}

		$field_args = [
			'post_type'   => 'pno_listings_fields',
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

			$field = new \PNO\Database\Queries\Listing_Fields();
			$field->add_item( [ 'post_id' => $field_id ] );

			if ( isset( $args['priority'] ) && ! empty( $args['priority'] ) ) {
				carbon_set_post_meta( $field_id, 'listing_field_priority', $args['priority'] );
			}

			if ( isset( $args['meta'] ) && ! empty( $args['meta'] ) ) {
				carbon_set_post_meta( $field_id, 'listing_field_meta_key', $args['meta'] );
			}

			if ( isset( $args['type'] ) && ! empty( $args['type'] ) ) {
				carbon_set_post_meta( $field_id, 'listing_field_type', $args['type'] );
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

		$listing_field = new \PNO\Database\Queries\Listing_Fields();

		$query = $listing_field->get_item_by( 'listing_meta_key', $meta );

		if ( isset( $query->post_id ) && $query->post_id > 0 ) {
			$exists = true;
		}

		return $exists;

	}

	/**
	 * Delete a listing field.
	 *
	 * @param string $post_id the id of the field to delete.
	 * @return void
	 */
	public static function delete( $post_id ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_delete_post( $post_id, true );

		$field = new \PNO\Database\Queries\Listing_Fields();

		$found_field = $field->get_item_by( 'post_id', $post_id );

		if ( $found_field instanceof \PNO\Entities\Field\Listing && $found_field->getPostID() > 0 && $found_field->canDelete() ) {
			$field->delete_item( $found_field->getEntityID() );
		} else {
			return new WP_Error( 'cannot_delete', esc_html__( 'Default fields cannot be deleted.' ) );
		}
	}

	/**
	 * Get a listing field by the post id.
	 *
	 * @param string $post_id the id of the post.
	 * @return Listing
	 */
	public static function getFromID( $post_id ) {

		$field = new \PNO\Database\Queries\Listing_Fields();

		return $field->get_item_by( 'post_id', $post_id );

	}

}
