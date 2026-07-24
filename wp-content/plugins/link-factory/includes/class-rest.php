<?php
/**
 * REST controller.
 *
 * @package LinkFactory
 */

namespace LinkFactory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rest {

	const NONCE_TRANSIENT_PREFIX = 'lf_nonce_';

	/**
	 * @var Repository
	 */
	private $repository;

	public function __construct( Repository $repository ) {
		$this->repository = $repository;
	}

	public function register_routes() {
		register_rest_route(
			LINK_FACTORY_NAMESPACE,
			'/health',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'handle_health' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			LINK_FACTORY_NAMESPACE,
			'/sentences',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_create_sentence' ),
				'permission_callback' => array( $this, 'check_signature' ),
				'args'                => array(
					'html' => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'wp_kses_post',
					),
				),
			)
		);

		register_rest_route(
			LINK_FACTORY_NAMESPACE,
			'/sentences/(?P<id>[a-zA-Z0-9-]+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'handle_delete_sentence' ),
				'permission_callback' => array( $this, 'check_signature' ),
				'args'                => array(
					'id' => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}

	/**
	 * Permission callback for Ed25519-signed routes (BR-SEC-03).
	 *
	 * Verifies the four signature headers and the detached Ed25519 signature
	 * over the canonical string `<METHOD>\n<HOST>\n<PATH>\n<TIMESTAMP>\n<NONCE>\n<BODY_SHA256_HEX>`
	 * using the trusted public key constant.
	 *
	 * @return true|\WP_Error
	 */
	public function check_signature( \WP_REST_Request $request ) {
		$signature        = $request->get_header( 'X-Link-Factory-Signature' );
		$timestamp_header = $request->get_header( 'X-Link-Factory-Timestamp' );
		$nonce            = $request->get_header( 'X-Link-Factory-Nonce' );
		$protocol_version = $request->get_header( 'X-Link-Factory-Protocol-Version' );

		if (
			! is_string( $signature ) || $signature === '' ||
			! is_string( $timestamp_header ) || $timestamp_header === '' ||
			! is_string( $nonce ) || $nonce === '' ||
			! is_string( $protocol_version ) || $protocol_version === ''
		) {
			return new \WP_Error( 'rest_forbidden', 'Required signature headers missing', array( 'status' => 403 ) );
		}

		$timestamp = intval( $timestamp_header );
		if ( $timestamp <= 0 ) {
			return new \WP_Error( 'rest_forbidden', 'Invalid X-Link-Factory-Timestamp', array( 'status' => 403 ) );
		}

		if ( abs( time() - $timestamp ) > LINK_FACTORY_SIGNATURE_TOLERANCE_SECONDS ) {
			return new \WP_Error( 'rest_forbidden', 'Timestamp outside tolerance window', array( 'status' => 403 ) );
		}

		$nonce_key = self::NONCE_TRANSIENT_PREFIX . $nonce;
		if ( get_transient( $nonce_key ) !== false ) {
			return new \WP_Error( 'rest_forbidden', 'Nonce replay detected', array( 'status' => 403 ) );
		}
		set_transient( $nonce_key, 1, LINK_FACTORY_SIGNATURE_TOLERANCE_SECONDS );

		$method = strtoupper( $request->get_method() );
		$host   = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$path   = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$body   = (string) $request->get_body();

		$canonical = $method . "\n" . $host . "\n" . $path . "\n" . $timestamp . "\n" . $nonce . "\n" . hash( 'sha256', $body );

		$signature_raw  = base64_decode( $signature, true );
		$public_key_raw = base64_decode( LINK_FACTORY_TRUSTED_PUBLIC_KEY, true );
		if ( $signature_raw === false || $public_key_raw === false || strlen( $public_key_raw ) !== 32 ) {
			return new \WP_Error( 'rest_forbidden', 'Malformed signature or public key', array( 'status' => 403 ) );
		}

		if ( ! function_exists( 'sodium_crypto_sign_verify_detached' ) ) {
			return new \WP_Error( 'rest_forbidden', 'libsodium not available on this host', array( 'status' => 500 ) );
		}

		$ok = sodium_crypto_sign_verify_detached( $signature_raw, $canonical, $public_key_raw );
		if ( ! $ok ) {
			return new \WP_Error( 'rest_forbidden', 'Invalid request signature', array( 'status' => 403 ) );
		}
		return true;
	}

	public function handle_health() {
		return rest_ensure_response(
			array(
				'status'          => 'ok',
				'version'         => LINK_FACTORY_VERSION,
				'protocolVersion' => LINK_FACTORY_PROTOCOL_VERSION,
			)
		);
	}

	public function handle_create_sentence( \WP_REST_Request $request ) {
		$html = $request->get_param( 'html' );
		if ( ! is_string( $html ) || trim( $html ) === '' ) {
			return new \WP_Error( 'invalid_html', 'Parameter "html" is required', array( 'status' => 400 ) );
		}

		$id     = wp_generate_uuid4();
		$result = $this->repository->insert( $id, $html );
		if ( false === $result ) {
			return new \WP_Error( 'db_error', 'Failed to persist sentence', array( 'status' => 500 ) );
		}

		return rest_ensure_response(
			array(
				'id' => $id,
			)
		);
	}

	public function handle_delete_sentence( \WP_REST_Request $request ) {
		$id = $request->get_param( 'id' );
		if ( ! is_string( $id ) || $id === '' ) {
			return new \WP_Error( 'invalid_id', 'Parameter "id" is required', array( 'status' => 400 ) );
		}
		$deleted = $this->repository->delete_by_id( $id );
		if ( 0 === $deleted ) {
			return new \WP_Error( 'not_found', 'Sentence not found', array( 'status' => 404 ) );
		}
		return rest_ensure_response( array( 'status' => 'deleted' ) );
	}
}
