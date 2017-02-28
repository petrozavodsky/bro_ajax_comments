<?php
if (!defined('ABSPATH')) {
    exit;
}

class bro_ajax_comments_access
{

    public function __construct()
    {
        add_action('comment_form_before', function () {
            add_filter("option_comment_registration", array($this, "change_option_comment_registration"), 10, 1);
        });

        add_action('comment_form_after', function () {
            remove_filter("option_comment_registration", array($this, "change_option_comment_registration"), 10);
        });

        add_filter('comment_form_field_author', "__return_empty_string");
        add_filter('comment_form_field_email', "__return_empty_string");
        add_filter('comment_form_field_url', "__return_empty_string");
        add_filter('comment_form_defaults', array($this, 'comment_form_defaults_filter'));
    }

    public function comment_form_defaults_filter($args)
    {
        $args['comment_notes_before']='';
        return $args;
    }

    public function change_option_comment_registration($option)
    {
        if ($option == '1') {
            return __return_empty_string();
        }
        return $option;
    }

}