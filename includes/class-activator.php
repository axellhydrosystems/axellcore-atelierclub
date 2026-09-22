<?php
/**
 * Runs on plugin activation: creates the `/atelier-club` page (assigned to
 * our FSE template) if it doesn't already exist yet, so the plugin is
 * self-provisioning — a fresh install (including a WordPress Playground
 * preview) lands on a working page with zero manual setup.
 *
 * @package Axellcore_Atelierclub
 */

namespace Axellcore_Atelierclub;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activation provisioning.
 */
final class Activator {

	/**
	 * The page slug this plugin owns.
	 */
	const PAGE_SLUG = 'atelier-club';

	/**
	 * Create the Atelier Club page if it isn't already there.
	 *
	 * Idempotent: safe to run on every activation (e.g. deactivate/reactivate)
	 * without creating duplicates.
	 */
	public static function activate() {
		$existing = get_page_by_path( self::PAGE_SLUG, OBJECT, 'page' );
		if ( $existing instanceof \WP_Post ) {
			return;
		}

		$page_id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_title'   => __( 'Atelier Axell Club', 'axellcore-atelierclub' ),
				'post_name'    => self::PAGE_SLUG,
				'post_status'  => 'publish',
				'post_content' => self::placeholder_content(),
			),
			true
		);

		if ( is_wp_error( $page_id ) || ! $page_id ) {
			return;
		}

		// Bare slug, not the `plugin//slug` registration name — see
		// Plugin::TEMPLATE_SLUG for why.
		update_post_meta( $page_id, '_wp_page_template', Plugin::TEMPLATE_SLUG );
	}

	/**
	 * Minimal placeholder content shown until the full landing-page sections
	 * are authored in the block editor. Deliberately plain core blocks.
	 *
	 * @return string Serialized block markup.
	 */
	private static function placeholder_content(): string {
		return implode(
			"\n",
			array(
				'<!-- wp:heading {"level":1,"className":"aac-display"} -->',
				'<h1 class="wp-block-heading aac-display">' . esc_html__( 'Atelier Axell Club', 'axellcore-atelierclub' ) . '</h1>',
				'<!-- /wp:heading -->',
				'<!-- wp:paragraph -->',
				'<p>' . esc_html__( 'This page is provisioned by the axellcore-atelierclub plugin. Replace this placeholder with the full landing-page sections in the block editor.', 'axellcore-atelierclub' ) . '</p>',
				'<!-- /wp:paragraph -->',
			)
		);
	}
}
