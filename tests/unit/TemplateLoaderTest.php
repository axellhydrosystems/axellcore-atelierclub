<?php
/**
 * @package Axellcore_Atelierclub\Tests
 */

declare( strict_types=1 );

namespace Axellcore_Atelierclub\Tests;

use Axellcore_Atelierclub\Template_Loader;
use Axellcore_Atelierclub\Plugin;
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

final class TemplateLoaderTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_register_template_registers_with_the_plugins_template_name(): void {
		Functions\when( 'file_exists' )->justReturn( true );
		Functions\when( 'file_get_contents' )->justReturn( "<!-- wp:post-content /-->\n" );
		Functions\when( '__' )->returnArg( 1 );

		Functions\expect( 'register_block_template' )
			->once()
			->with(
				Plugin::TEMPLATE_NAME,
				\Mockery::on(
					function ( $args ) {
						return isset( $args['content'] )
							&& false !== strpos( $args['content'], 'wp:post-content' )
							&& array( 'page' ) === $args['post_types'];
					}
				)
			);

		Template_Loader::instance()->register_template();

		$this->addToAssertionCount( 1 );
	}

	public function test_register_template_is_a_no_op_when_the_template_file_is_missing(): void {
		Functions\when( 'file_exists' )->justReturn( false );

		Functions\expect( 'register_block_template' )->never();

		Template_Loader::instance()->register_template();

		$this->addToAssertionCount( 1 );
	}
}
