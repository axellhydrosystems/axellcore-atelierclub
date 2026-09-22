<?php
/**
 * Registers the plugin's two custom blocks (axellcore/form, axellcore/form-input)
 * and a handful of core Button style variations used for the repeated CTA/
 * chip affordances — keeping everything else as plain core blocks per the
 * "minimize custom blocks" direction.
 *
 * @package Axellcore_Atelierclub
 */

namespace Axellcore_Atelierclub;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block + block-style registration.
 */
final class Blocks {

	/**
	 * Singleton instance.
	 *
	 * @var Blocks|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Blocks
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
		add_action( 'init', array( $this, 'register_blocks' ) );
	}

	/**
	 * Register the two custom blocks from their block.json metadata.
	 *
	 * Every other section uses plain core blocks with the original (now
	 * `aac-`-prefixed) classes applied via "Additional CSS class(es)" —
	 * including core/button, which gets `aac-btn aac-btn-primary`/`aac-btn-ghost`
	 * directly, reusing assets/css/sections.css's already-ported `.aac-btn*`
	 * rules verbatim instead of duplicating them under new block-style-variation
	 * selectors.
	 */
	public function register_blocks() {
		register_block_type_from_metadata( AXELLCORE_ATELIERCLUB_PATH . 'includes/blocks/form/form' );
		register_block_type_from_metadata( AXELLCORE_ATELIERCLUB_PATH . 'includes/blocks/form/form-input' );
	}
}
