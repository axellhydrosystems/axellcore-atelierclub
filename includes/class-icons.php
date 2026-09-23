<?php
/**
 * Registers the plugin's small icon set (WP 7.1's Icons API —
 * wp_register_icon_collection()/wp_register_icon(), rendered via the core
 * `core/icon` block) for the two icons that appear as standalone content
 * next to text (the tier "mystery" lock-mark and the notice-pill lock/clock
 * icons) — so editors pick these from the block inserter's icon picker
 * instead of the markup being frozen as raw Custom HTML.
 *
 * The repeated CTA-button trailing arrow is deliberately NOT registered
 * here: it's decorative chrome bound to the button link's own hover state
 * (see assets/css/blocks-bridge.css's ::after rule) rather than editable
 * content, so a CSS pseudo-element is the right tool for that one.
 *
 * @package Axellcore_Atelierclub
 */

namespace Axellcore_Atelierclub;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Icon collection + icon registration.
 */
final class Icons {

	const COLLECTION = 'axellcore';

	/**
	 * Singleton instance.
	 *
	 * @var Icons|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Icons
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
		add_action( 'init', array( $this, 'register_icons' ) );
	}

	/**
	 * Register the "axellcore" icon collection and its icons.
	 */
	public function register_icons() {
		if ( ! function_exists( 'wp_register_icon_collection' ) ) {
			return; // WP < 7.1 — no Icons API; the lock/clock stay as raw markup wherever seeded.
		}

		wp_register_icon_collection(
			self::COLLECTION,
			array(
				'label'       => __( 'Atelier Axell Club', 'axellcore-atelierclub' ),
				'description' => __( 'Icons used by the Atelier Axell Club landing page.', 'axellcore-atelierclub' ),
			)
		);

		wp_register_icon(
			self::COLLECTION . '/lock',
			array(
				'label'   => __( 'Lock', 'axellcore-atelierclub' ),
				'content' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14 14" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="3" y="6" width="8" height="6" rx="0.5"/><path d="M4.5 6 V4 a2.5 2.5 0 0 1 5 0 V6"/></svg>',
			)
		);

		wp_register_icon(
			self::COLLECTION . '/clock',
			array(
				'label'   => __( 'Clock', 'axellcore-atelierclub' ),
				'content' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14 14" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.4"><circle cx="7" cy="7" r="5.5"/><path d="M7 4 v3 l2 1.5"/></svg>',
			)
		);
	}
}
