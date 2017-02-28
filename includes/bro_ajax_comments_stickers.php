<?php
if (!defined('ABSPATH')) {
    exit;
}

class bro_ajax_comments_stickers
{
    private $version = "1.0.0";
    private $file;
    private $shortcode_name = "bro_sticker";

    public function __construct($file)
    {
        $this->file = $file;
        add_action('comment_form_submit_field', array($this, 'add_button'), 50, 1);
        add_action("wp_enqueue_scripts", array($this, "add_js_css"));
        add_shortcode($this->shortcode_name, array($this, "shortcode_render"));
        add_action('get_comment_text', array($this, "grep_shortcode_in_comment"), 10, 3);
    }

    public function grep_shortcode_in_comment($comment_text, $comment, $args)
    {

        return do_shortcode($comment_text);
    }

    public function shortcode_render($attr)
    {

        $attr = shortcode_atts(
            array(
                'id' => false,
            ),
            $attr
        );

        return "<img src='{$this->get_sticker_img($attr['id'])}'>";
    }

    private function get_sticker_img($id)
    {
        $path = plugin_dir_url($this->file) . "public/images/stikers/";
        return $path . $id . ".png";
    }

    public function add_js_css()
    {
        wp_enqueue_style("bro_ajax_stickers", plugin_dir_url($this->file) . "public/css/comments-stickers.css", array(), $this->version, 'all');
        wp_enqueue_script("bro_ajax_stickers_js", plugin_dir_url($this->file) . "public/js/stickers-js.js", array("jquery","bro_ajax_comments_js"), $this->version, true);
    }

    public function add_button($submit_field)
    {
        $res = "";
        $res .= "<div class='comments__stickers-button-wrap'>";
        $res .= "<div class='comments__stickers-button'> :-) </div>";

        $res .= "<div class='comments__stickers-box-wrap'>";
        $res .= "<div class='comments__stickers-box'>";
        $res .= $this->stickers();
        $res .= "</div>";
        $res .= "</div>";

        $res .= "</div>";
        return $submit_field . $res;
    }


    private function stickers()
    {
        $images_url = plugin_dir_url($this->file) . "public/images/stikers/";
        $res = "";
        $res .= "<div class='comments__stickers-box-items'>";
        for ($i = 1; $i < 17; $i++) {
            $images_url_current = $images_url . $i . ".png";
            $res .= "<div class='comments__stickers-box-item-wrap'>";
            $res .= "<div class='comments__stickers-box-item' data-stickers-id='{$i}' style='background-image: url({$images_url_current});' ></div>";
            $res .= "</div>";
        }
        $res .= "</div>";
        return $res;
    }
}