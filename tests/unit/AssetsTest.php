<?php
/**
 * @package Axellcore_Atelierclub\Tests
 */

declare( strict_types=1 );

namespace Axellcore_Atelierclub\Tests;

use Axellcore_Atelierclub\Assets;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

final class AssetsTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_strip_theme_and_core_assets_does_nothing_off_our_template(): void {
		Functions\when( 'is_page_template' )->justReturn( false );
		Functions\expect( 'remove_action' )->never();
		Functions\expect( 'add_filter' )->never();

		Assets::instance()->strip_theme_and_core_assets();

		$this->addToAssertionCount( 1 );
	}

	public function test_strip_theme_and_core_assets_removes_theme_and_core_hooks_on_our_template(): void {
		Functions\when( 'is_page_template' )->justReturn( true );

		Functions\expect( 'remove_action' )
			->with( 'wp_enqueue_scripts', 'twentytwentyfive_enqueue_styles' )
			->once();
		Functions\expect( 'remove_action' )
			->with( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' )
			->once();
		Functions\expect( 'remove_action' )
			->with( 'wp_footer', 'wp_enqueue_global_styles', 1 )
			->once();
		Functions\expect( 'remove_action' )
			->with( 'wp_enqueue_scripts', 'wp_common_block_scripts_and_styles' )
			->once();
		Functions\expect( 'remove_action' )
			->with( 'wp_head', 'print_emoji_detection_script', 7 )
			->once();
		Functions\expect( 'remove_action' )
			->with( 'wp_print_styles', 'print_emoji_styles' )
			->once();

		Functions\expect( 'add_filter' )
			->with( 'show_admin_bar', '__return_false' )
			->once();

		Assets::instance()->strip_theme_and_core_assets();

		$this->addToAssertionCount( 1 );
	}

	public function test_enqueue_frontend_assets_does_nothing_off_our_template(): void {
		Functions\when( 'is_page_template' )->justReturn( false );
		Functions\expect( 'wp_enqueue_style' )->never();
		Functions\expect( 'wp_enqueue_script' )->never();

		Assets::instance()->enqueue_frontend_assets();

		$this->addToAssertionCount( 1 );
	}

	public function test_enqueue_frontend_assets_enqueues_fonts_tokens_sections_and_frontend_js(): void {
		Functions\when( 'is_page_template' )->justReturn( true );

		Functions\expect( 'wp_enqueue_style' )
			->with( 'aac-google-fonts', \Mockery::type( 'string' ), array(), null )
			->once();
		Functions\expect( 'wp_enqueue_style' )
			->with( 'aac-tokens', \Mockery::type( 'string' ), array(), AXELLCORE_ATELIERCLUB_VERSION )
			->once();
		Functions\expect( 'wp_enqueue_style' )
			->with( 'aac-sections', \Mockery::type( 'string' ), array( 'aac-tokens' ), AXELLCORE_ATELIERCLUB_VERSION )
			->once();
		Functions\expect( 'wp_enqueue_style' )
			->with( 'aac-blocks-bridge', \Mockery::type( 'string' ), array( 'aac-sections' ), AXELLCORE_ATELIERCLUB_VERSION )
			->once();
		Functions\expect( 'wp_enqueue_script' )
			->with( 'aac-frontend', \Mockery::type( 'string' ), array(), AXELLCORE_ATELIERCLUB_VERSION, \Mockery::type( 'array' ) )
			->once();

		Functions\when( 'rest_url' )->justReturn( 'https://example.com/wp-json/axellcore-atelierclub/v1' );
		Functions\when( 'trailingslashit' )->justReturn( 'https://example.com/wp-json/axellcore-atelierclub/v1/' );
		Functions\when( 'esc_url_raw' )->returnArg( 1 );
		Functions\expect( 'wp_localize_script' )
			->with( 'aac-frontend', 'aacRest', array( 'root' => 'https://example.com/wp-json/axellcore-atelierclub/v1/' ) )
			->once();

		Assets::instance()->enqueue_frontend_assets();

		$this->addToAssertionCount( 1 );
	}
}
