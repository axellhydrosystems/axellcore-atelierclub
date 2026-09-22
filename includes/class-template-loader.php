<?php
/**
 * Registers a plugin-owned FSE block template ("Atelier — Blank Canvas") so the
 * landing page renders with zero theme chrome (no header/footer template parts)
 * while remaining fully visible and editable in the Site Editor's Templates list.
 *
 * Deliberately NOT a classic `Template Name:` PHP file wired through
 * `template_include` — this uses core's register_block_template() (WP 6.7+),
 * confirmed present in wp-includes/block-template.php on this install.
 *
 * @package Axellcore_Atelierclub
 */

namespace Axellcore_Atelierclub;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FSE template registration.
 */
final class Template_Loader {

	/**
	 * Singleton instance.
	 *
	 * @var Template_Loader|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Template_Loader
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
		add_action( 'init', array( $this, 'register_template' ) );
	}

	/**
	 * Register the "Atelier — Blank Canvas" block template.
	 */
	public function register_template() {
		if ( ! function_exists( 'register_block_template' ) ) {
			return;
		}

		$template_path = AXELLCORE_ATELIERCLUB_PATH . 'templates/atelier-club.html';

		if ( ! file_exists( $template_path ) ) {
			return;
		}

		register_block_template(
			Plugin::TEMPLATE_NAME,
			array(
				'title'       => __( 'Atelier — Blank Canvas', 'axellcore-atelierclub' ),
				'description' => __( 'Self-contained canvas for the Atelier Axell Club landing page. No header/footer template parts — the page content renders alone.', 'axellcore-atelierclub' ),
				'content'     => file_get_contents( $template_path ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				'post_types'  => array( 'page' ),
			)
		);
	}
}
