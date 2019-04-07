<?php
/*
Plugin Name: Bro AJAX Comments
Plugin URI: https://alkoweb.ru
Author: Petrozavodsky
Author URI: https://alkoweb.ru/
Version: 1.0.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class bro_ajax_comments {
	private $version = "2.1.5";
	private $ajax_action_field = 'bro_ajax_comment';

	public function __construct() {
		add_action( "wp_enqueue_scripts", [this, "add_js_css"] );
		$this->init_ajax_action();
	}


	public function add_js_css() {
		$form_action = admin_url( "admin-ajax.php?action={$this->ajax_action_field}" );

		wp_enqueue_script( 'bro_ajax_comments_save_in_local_storage', plugin_dir_url( __FILE__ ) . "public/js/vendor/sisyphus/sisyphus.min.js", array( "jquery" ), $this->version, true );
		wp_enqueue_script( 'bro_ajax_comments_js', plugin_dir_url( __FILE__ ) . "public/js/ajax-comments.min.js", array(
			"jquery",
			"bro_ajax_comments_save_in_local_storage"
		), $this->version, true );

		wp_localize_script( 'bro_ajax_comments_js', 'bro_ajax_comments_data', array(
			'form_action'=>$form_action
		) );

		wp_enqueue_style( 'bro_ajax_comments_css', plugin_dir_url( __FILE__ ) . "public/css/ajax-comments.css", array(), $this->version );
	}

	private function init_ajax_action() {
		$action_name = $this->ajax_action_field;
		add_action( 'wp_ajax_' . $action_name, array( $this, 'ajax_action_callback' ) );
		add_action( 'wp_ajax_nopriv_' . $action_name, array( $this, 'ajax_action_callback' ) );
	}

	public function ajax_action_callback() {
		$request = $_REQUEST;
		$request = array_map( 'trim', $request );
		$json    = array();


		$response = $this->insert_comment( $request );

		if ( empty( $request['comment'] ) ) {
			$response = new WP_Error( 'comment_empty', __( 'Sorry, you comment is empty).' ), 411 );
		}

		$GLOBALS['comment'] = $response;

		if ( is_wp_error( $response ) ) {
			$json['exception_code']  = array_shift( $response->error_data );
			$json['exceptions']      = $response->get_error_message();
			$json['html_exceptions'] = "<div class='bro_ajax_comments__exceptions-message'>{$response->get_error_message()}</div>";
		}
		if ( is_string( $response ) ) {
			$json['error'] = $response;
		}

		if ( is_object( $response ) ) {
			$current_comment_depth          = isset( $request['current_comment_depth'] ) ? (int) $request['current_comment_depth'] : 0;
			$defaults_wp_list_comments_args = $this->get_wp_list_comments_args();

			$json['comment'] = $response;
			$json['html']    = apply_filters(
				'bro_ajax_walker_comment_item',
				$response,
				$current_comment_depth,
				$defaults_wp_list_comments_args
			);
		}

		wp_send_json( $json );
	}

	private function get_wp_list_comments_args() {
		$defaults = array(
			'walker'            => null,
			'max_depth'         => '',
			'style'             => 'ul',
			'callback'          => null,
			'end-callback'      => null,
			'type'              => 'all',
			'page'              => '',
			'per_page'          => '',
			'avatar_size'       => 32,
			'reverse_top_level' => null,
			'reverse_children'  => '',
			'format'            => current_theme_supports( 'html5', 'comment-list' ) ? 'html5' : 'xhtml',
			'short_ping'        => false,
			'echo'              => true,
		);

		return apply_filters( 'wp_list_comments_args', $defaults );
	}

	private function insert_comment( $request ) {

		$comment_post_ID = isset( $request['comment_post_ID'] ) ? (int) $request['comment_post_ID'] : 0;
		$author          = ( isset( $request['author'] ) ) ? trim( strip_tags( $request['author'] ) ) : null;
		$email           = ( isset( $request['email'] ) ) ? trim( $request['email'] ) : null;
		$url             = ( isset( $request['url'] ) ) ? trim( $request['url'] ) : null;
		$comment         = ( isset( $request['comment'] ) ) ? trim( $request['comment'] ) : null;
		$comment         = shortcode_unautop( $comment );
		$comment         = wp_kses_stripslashes( $comment );
		$comment_parent  = isset( $request['comment_parent'] ) ? absint( $request['comment_parent'] ) : 0;


		$comment_submission_args = array(
			'comment_post_ID'             => $comment_post_ID,
			'author'                      => $author,
			'email'                       => $email,
			'url'                         => $url,
			'comment'                     => $comment,
			'comment_parent'              => $comment_parent,
			'_wp_unfiltered_html_comment' => trim( $request['_wp_unfiltered_html_comment'] )
		);

		return wp_handle_comment_submission( $comment_submission_args );
	}

}


function bro_ajax_comments_init() {
	require_once( "includes/bro_ajax_comments_access.php" );
	require_once( "includes/bro_ajax_comments_change_defaults.php" );

	require_once( "includes/bro_ajax_comments_editor.php" );
	new bro_ajax_comments_editor( __FILE__ );

//    require_once("includes/bro_ajax_comments_stickers.php");
//    new bro_ajax_comments_stickers(__FILE__);

	new bro_ajax_comments_access();
	new bro_ajax_comments_change_defaults();
	new bro_ajax_comments();
}

add_action( "plugins_loaded", "bro_ajax_comments_init" );
