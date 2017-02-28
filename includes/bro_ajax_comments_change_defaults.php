<?php

if (!defined('ABSPATH')) {
    exit;
}

class bro_ajax_comments_change_defaults
{

    public function __construct()
    {
        require_once("bro_ajax_walker_comment.php");
        add_filter('wp_list_comments_args', array($this, 'wp_list_comments_args_filter'));
        add_filter('comment_form_defaults', array($this, 'comment_form_defaults_filter'));
        add_filter('cancel_comment_reply_link', "__return_empty_string", 10, 3);
        add_filter('comment_form_default_fields', array($this, "comment_form_default_fields_filter"));

        add_action('comment_form_before_fields', array($this, "comment_form_before_fields_filter"));
        add_action('comment_form_after_fields', array($this, "comment_form_after_fields_filter"));
	    add_action('preprocess_comment', array($this,'allowed_tags'));

    }



	public function allowed_tags($dannie) {
		global $allowedtags;

		// Разрешаем новые HTML теги:
		$allowedtags['pre'] = array('class' => array());
		$allowedtags['ul'] = array('class' => array());
		$allowedtags['ol'] = array('class' => array());
		$allowedtags['li'] = array('class' => array());
		$allowedtags['p'] = array('class' => array());
		$allowedtags['li'] = array('id' => array());
		$allowedtags['b'] = array('id' => array());
		$allowedtags['strong'] = array('id' => array());
		$allowedtags['strike'] = array('id' => array());
		$allowedtags['em'] = array('id' => array());

		return $dannie;
	}

	public function comment_form_before_fields_filter()
    {
        echo "<div class='comments__fields'>";

    }

    public function comment_form_after_fields_filter()
    {
        echo "</div>";
    }

    public function comment_form_default_fields_filter($fields)
    {
        foreach ($fields as $key => $val) {
            $val = str_replace("<p", '<div', $val);
            $val = str_replace("</p", '</div', $val);
            $fields[$key] = $val;
        }
        return $fields;
    }

    public function comment_form_defaults_filter($args)
    {
//        $args['fields']['url'] = '';
        $args['comment_field'] = '<textarea id="comment" name="comment" maxlength="65525" aria-required="true" required="required"></textarea>';
        $args['logged_in_as'] = '';
        $args['format'] = 'html5';
        $args['submit_button'] = '<button name="%1$s" type="submit" id="%2$s" class="%3$s"  > %4$s </button>';
        $args['title_reply_before'] = '';
        $args['title_reply_after'] = '';

        $args['cancel_reply_before'] = "";
        $args['cancel_reply_after'] = "";
        $args['cancel_reply_link'] = "";
        $args['title_reply_to'] = "";
        $args['title_reply'] = "";
        $args['label_submit'] = "Отправить";

        return $args;
    }

    public function wp_list_comments_args_filter($args)
    {

        $args['walker'] = new bro_ajax_walker_comment;
        $args['format'] = "html5";
        $args['type'] = "comment";
        $args['avatar_size'] = 80;
        $args['reply_text'] = 'Ответить';
        $args['style'] = "div";
        return $args;
    }


}