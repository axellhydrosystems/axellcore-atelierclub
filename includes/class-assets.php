<?php
/**
 * Loads the plugin's own design-token/section CSS and frontend JS on the
 * Atelier landing page template, and strips the theme's/core's own
 * unconditional style output so the page renders fully self-contained.
 *
 * @package Axellcore_Atelierclub
 */

namespace Axellcore_Atelierclub;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Asset enqueue + theme/core CSS suppression, both gated to our template only.
 */
final class Assets {

	/**
	 * Singleton instance.
	 *
	 * @var Assets|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Assets
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
		add_action( 'template_redirect', array( $this, 'strip_theme_and_core_assets' ), 5 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_filter( 'wp_resource_hints', array( $this, 'add_google_fonts_preconnect' ), 10, 2 );
	}

	/**
	 * Whether the current request is rendering our landing page template.
	 *
	 * @return bool
	 */
	private function is_our_template() {
		return is_page_template( Plugin::TEMPLATE_SLUG );
	}

	/**
	 * Remove the active theme's stylesheet and WP core's unconditional
	 * global-styles/block-library CSS + emoji script on our template only.
	 * Handles confirmed by directly curl-ing this install's homepage <head>.
	 */
	public function strip_theme_and_core_assets() {
		if ( ! $this->is_our_template() ) {
			return;
		}

		// twentytwentyfive's own stylesheet — named, removable callback
		// (confirmed at wp-content/themes/twentytwentyfive/functions.php:65).
		remove_action( 'wp_enqueue_scripts', 'twentytwentyfive_enqueue_styles' );

		// theme.json-derived global styles (custom properties + presets) — hooked
		// twice by core, once for <head> and once as a footer fallback.
		remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
		remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );

		// Core block-library common CSS + block-supports inline CSS.
		remove_action( 'wp_enqueue_scripts', 'wp_common_block_scripts_and_styles' );

		// Emoji detection script/styles.
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );

		// Avoid the 32px admin-bar offset pushing our position:fixed nav down,
		// so a logged-in pixel check matches a logged-out visitor.
		add_filter( 'show_admin_bar', '__return_false' );
	}

	/**
	 * Enqueue our own design tokens, section CSS, and frontend JS.
	 */
	public function enqueue_frontend_assets() {
		if ( ! $this->is_our_template() ) {
			return;
		}

		wp_enqueue_style(
			'aac-google-fonts',
			'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500&family=Inter:wght@300;400;500;600&display=swap',
			array(),
			null // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- external URL, WP doesn't own its versioning.
		);

		wp_enqueue_style( 'aac-tokens', AXELLCORE_ATELIERCLUB_URL . 'assets/css/tokens.css', array(), AXELLCORE_ATELIERCLUB_VERSION );
		wp_enqueue_style( 'aac-sections', AXELLCORE_ATELIERCLUB_URL . 'assets/css/sections.css', array( 'aac-tokens' ), AXELLCORE_ATELIERCLUB_VERSION );
		wp_enqueue_style( 'aac-blocks-bridge', AXELLCORE_ATELIERCLUB_URL . 'assets/css/blocks-bridge.css', array( 'aac-sections' ), AXELLCORE_ATELIERCLUB_VERSION );

		wp_enqueue_script(
			'aac-frontend',
			AXELLCORE_ATELIERCLUB_URL . 'assets/js/frontend.js',
			array(),
			AXELLCORE_ATELIERCLUB_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}

	/**
	 * Add the Google Fonts preconnect hints the source mockup uses, matching
	 * the original <link rel="preconnect"> tags exactly.
	 *
	 * @param array  $urls          Resource hint URLs.
	 * @param string $relation_type The relation type ('preconnect', etc.).
	 * @return array
	 */
	public function add_google_fonts_preconnect( $urls, $relation_type ) {
		if ( 'preconnect' !== $relation_type || ! $this->is_our_template() ) {
			return $urls;
		}

		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array(
			'href' => 'https://fonts.gstatic.com',
			'crossorigin',
		);

		return $urls;
	}
}
