<?php
/**
 * Registers the `aac_member` post type — one record per submitted Atelier
 * Club application (created by Rest::create_member() on form submission).
 * Internal record-keeping only (not a public post type): admins review
 * submissions in wp-admin, nothing here is ever queried on the frontend.
 *
 * @package Axellcore_Atelierclub
 */

namespace Axellcore_Atelierclub;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * `aac_member` post type registration.
 */
final class Member {

	/**
	 * Post type slug.
	 */
	const POST_TYPE = 'aac_member';

	/**
	 * Singleton instance.
	 *
	 * @var Member|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Member
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor — use instance().
	 */
	private function __construct() {}

	/**
	 * Register hooks.
	 */
	public function register_hooks() {
		add_action( 'init', array( $this, 'register_post_type' ) );
	}

	/**
	 * Register the post type.
	 */
	public function register_post_type() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'          => array(
					'name'               => __( 'Members', 'axellcore-atelierclub' ),
					'singular_name'      => __( 'Member', 'axellcore-atelierclub' ),
					'add_new_item'       => __( 'Add New Member', 'axellcore-atelierclub' ),
					'edit_item'          => __( 'Edit Member', 'axellcore-atelierclub' ),
					'view_item'          => __( 'View Member', 'axellcore-atelierclub' ),
					'search_items'       => __( 'Search Members', 'axellcore-atelierclub' ),
					'not_found'          => __( 'No members found.', 'axellcore-atelierclub' ),
					'not_found_in_trash' => __( 'No members found in Trash.', 'axellcore-atelierclub' ),
					'all_items'          => __( 'All Members', 'axellcore-atelierclub' ),
				),
				'description'     => __( 'A submitted Atelier Axell Club application.', 'axellcore-atelierclub' ),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => true,
				'show_in_rest'    => false,
				'menu_icon'       => 'dashicons-groups',
				'menu_position'   => 26,
				'supports'        => array( 'title' ),
				'capability_type' => 'post',
				'map_meta_cap'    => true,
			)
		);
	}
}
