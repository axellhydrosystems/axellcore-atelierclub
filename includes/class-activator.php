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

		// The seed content is our own bundled, fully-trusted markup (not user
		// input) — it includes <select>/<input>/<form> tags that KSES strips
		// from post_content by default for accounts (or WP-CLI/no-user
		// contexts) without unfiltered_html. Bypass KSES for this one insert.
		kses_remove_filters();

		$page_id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_title'   => __( 'Atelier Axell Club', 'axellcore-atelierclub' ),
				'post_name'    => self::PAGE_SLUG,
				'post_status'  => 'publish',
				'post_content' => self::seed_content(),
			),
			true
		);

		kses_init_filters();

		if ( is_wp_error( $page_id ) || ! $page_id ) {
			return;
		}

		// Bare slug, not the `plugin//slug` registration name — see
		// Plugin::TEMPLATE_SLUG for why.
		update_post_meta( $page_id, '_wp_page_template', Plugin::TEMPLATE_SLUG );
	}

	/**
	 * The full landing-page content, hand-authored as core-block markup
	 * (see content/seed-content.html) so the page is pixel-accurate on first
	 * activation yet still 100% panel-editable afterward. Falls back to a
	 * minimal placeholder if the seed file is ever missing.
	 *
	 * @return string Serialized block markup.
	 */
	private static function seed_content(): string {
		$seed_path = AXELLCORE_ATELIERCLUB_PATH . 'content/seed-content.html';

		if ( file_exists( $seed_path ) ) {
			$content = file_get_contents( $seed_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( false !== $content && '' !== trim( $content ) ) {
				return $content;
			}
		}

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
