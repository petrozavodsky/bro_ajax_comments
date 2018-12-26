<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class bro_ajax_comments_editor {

	private $version = "1.1.5";
	private $file;

	public function __construct( $file ) {
		$this->file = $file;
		add_action( "wp", array( $this, "add_js_css" ), 10, 1 );
	}

	public function add_js_css() {

		if ( is_singular() ) {

			wp_enqueue_style( "trumbowyg", plugin_dir_url( $this->file ) . "public/js/vendor/trumbowyg/dist/ui/trumbowyg.css", array(), '2.12.0', 'all' );

			wp_enqueue_style( "bro_ajax_comments_editor_css", plugin_dir_url( $this->file ) . "public/css/ajax-comments-editor.css", array( 'trumbowyg' ), $this->version, 'all' );

			wp_enqueue_script(
				"trumbowyg",
				plugin_dir_url( $this->file ) . "public/js/vendor/trumbowyg/dist/trumbowyg.min.js",
				array(
					"jquery"
				),
				' 2.12.0',
				true
			);

			wp_enqueue_script(
				"trumbowyg_ru",
				plugin_dir_url( $this->file ) . "public/js/vendor/trumbowyg/dist/langs/ru.min.js",
				array(
					"trumbowyg"
				),
				$this->version,
				true
			);


			wp_enqueue_script( "bro_ajax_comments_editor", plugin_dir_url( $this->file ) . "public/js/editor.js", array(
				"bro_ajax_comments_js",
				"trumbowyg"
			), $this->version, true );
		}

	}


}