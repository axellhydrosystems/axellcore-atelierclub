<?php
/**
 * REST endpoints for the Atelier Club application form:
 *  - GET  /axellcore-atelierclub/v1/cities?uf=SP  — cities for the state/
 *    city cascading select (assets/js/frontend.js), public/read-only.
 *  - POST /axellcore-atelierclub/v1/members        — the real form
 *    submission handler: creates an `aac_member` post, resolves/assigns
 *    its Country > State > City term, and stores every other field as
 *    post meta.
 *
 * @package Axellcore_Atelierclub
 */

namespace Axellcore_Atelierclub;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST route registration + handlers.
 */
final class Rest {

	/**
	 * REST namespace.
	 */
	const NAMESPACE = 'axellcore-atelierclub/v1';

	/**
	 * Required fields for a member submission.
	 *
	 * @var string[]
	 */
	const REQUIRED_FIELDS = array(
		'nome',
		'escritorio',
		'email',
		'telefone',
		'atuacao',
		'tipoDoc',
		'documento',
		'rua',
		'numero',
		'bairro',
		'cidade',
		'uf',
		'cep',
		'regulamento',
	);

	/**
	 * Optional text-meta fields, stored verbatim (sanitize_text_field) under
	 * `_aac_{field}`. `email` and `portfolio` are handled separately (their
	 * own sanitizers); `uf`/`cidade` are handled by the location-resolution
	 * step, not stored as plain meta.
	 *
	 * @var string[]
	 */
	const TEXT_META_FIELDS = array(
		'escritorio',
		'telefone',
		'registro',
		'atuacao',
		'tipoDoc',
		'documento',
		'rua',
		'numero',
		'complemento',
		'bairro',
		'referencia',
		'cep',
		'loja1',
		'loja2',
		'loja3',
		'loja4',
		'loja5',
	);

	/**
	 * Singleton instance.
	 *
	 * @var Rest|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Rest
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
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register the routes.
	 */
	public function register_routes() {
		register_rest_route(
			self::NAMESPACE,
			'/cities',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_cities' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'uf' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/members',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_member' ),
				// Public by design: an unauthenticated application form,
				// same trust boundary as any public HTML form posting to a
				// server endpoint — there's no authenticated session/action
				// to protect with a nonce here.
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * GET /cities?uf=XX — cities for one state, for the cascading select.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function get_cities( \WP_REST_Request $request ) {
		$uf     = strtoupper( (string) $request->get_param( 'uf' ) );
		$cities = Locations::instance()->cities_for_state( $uf );

		$items = array();
		foreach ( $cities as $code => $name ) {
			$items[] = array(
				'value' => $code,
				'label' => $name,
			);
		}

		usort(
			$items,
			function ( $a, $b ) {
				return strcmp( $a['label'], $b['label'] );
			}
		);

		return rest_ensure_response( $items );
	}

	/**
	 * POST /members — create an aac_member post from a form submission.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_member( \WP_REST_Request $request ) {
		$params = $request->get_json_params();
		if ( ! is_array( $params ) || empty( $params ) ) {
			$params = $request->get_body_params();
		}

		foreach ( self::REQUIRED_FIELDS as $field ) {
			if ( empty( $params[ $field ] ) ) {
				return new \WP_Error(
					'aac_missing_field',
					/* translators: %s: form field name. */
					sprintf( __( 'Missing required field: %s', 'axellcore-atelierclub' ), $field ),
					array( 'status' => 400 )
				);
			}
		}

		$email = sanitize_email( $params['email'] );
		if ( '' === $email || ! is_email( $email ) ) {
			return new \WP_Error( 'aac_invalid_email', __( 'Invalid email address.', 'axellcore-atelierclub' ), array( 'status' => 400 ) );
		}

		$uf        = strtoupper( sanitize_text_field( $params['uf'] ) );
		$city_code = absint( $params['cidade'] );
		$city_term = Locations::instance()->resolve_city_term( $uf, $city_code );
		if ( null === $city_term ) {
			return new \WP_Error( 'aac_invalid_location', __( 'Invalid state/city.', 'axellcore-atelierclub' ), array( 'status' => 400 ) );
		}

		$post_id = wp_insert_post(
			array(
				'post_type'   => Member::POST_TYPE,
				'post_title'  => sanitize_text_field( $params['nome'] ),
				'post_status' => 'publish',
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return new \WP_Error( 'aac_insert_failed', __( 'Could not save your application.', 'axellcore-atelierclub' ), array( 'status' => 500 ) );
		}

		wp_set_object_terms( $post_id, array( $city_term ), Locations::TAXONOMY );

		update_post_meta( $post_id, '_aac_email', $email );
		if ( ! empty( $params['portfolio'] ) ) {
			update_post_meta( $post_id, '_aac_portfolio', esc_url_raw( $params['portfolio'] ) );
		}
		update_post_meta( $post_id, '_aac_uf', $uf );

		foreach ( self::TEXT_META_FIELDS as $field ) {
			if ( ! empty( $params[ $field ] ) ) {
				update_post_meta( $post_id, '_aac_' . $field, sanitize_text_field( $params[ $field ] ) );
			}
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'id'      => $post_id,
			)
		);
	}
}
