<?php

if (!defined('ABSPATH')) {
    exit;
}

class bro_ajax_walker_comment extends Walker_Comment
{

    public function __construct()
    {
        add_filter('bro_ajax_walker_comment_item', array($this, "get_html5_comment_content"), 10, 4);

    }

    protected function html5_comment($comment, $depth, $args)
    {
        $this->html5_comment_content($comment, $depth, $args);
    }

    public function get_html5_comment_content($comment, $depth, $args)
    {

        $res = "";
        ob_start();
        $this->html5_comment_content($comment, $depth, $args);
        $res .= ob_get_contents();
        ob_clean();
        return $res;
    }

    public function html5_comment_content($comment, $depth, $args)
    {
        update_option("tt", [
            $comment,
            $depth,
            $args
        ]);
        $tag = ('div' === $args['style']) ? 'div' : 'li';
        $comment_class = $this->comment_class_children($args, $this->has_children);
        ?>

        <<?php echo $tag; ?> id="comment-wrap-<?php comment_ID(); ?>" <?php comment_class($comment_class, $comment); ?>>
        <article id="article-comment-<?php comment_ID(); ?>" class="comment__article">

            <div class="comment__article-item-top">
                <div class="comment__article-avatar">
                    <?php if (0 != $args['avatar_size']) {
                        echo get_avatar($comment, $args['avatar_size']);
                    } ?>
                </div><!-- .comment-author -->
                <div class="comment__article-name">
                    <?php printf(__('%s <span class="says">says:</span>'), sprintf('<b class="fn">%s</b>', get_comment_author_link($comment))); ?>
                </div>
            </div>

            <div class="comment__article-item-center">
                <?php comment_text(); ?>
            </div>

            <div class="comment__article-item-bottom">
                <time class="comment__article-meta date" datetime="<?php comment_time('c'); ?>">
                    <?php
                    /* translators: 1: comment date, 2: comment time */
                    printf(__('%1$s at %2$s'), get_comment_date(get_option("date_format", ''), $comment), get_comment_time());
                    ?>
                </time>
                <?php if ('0' == $comment->comment_approved) : ?>
                    <div class="comment__article-meta comment-awaiting-moderation">
                        <?php _e('Your comment is awaiting moderation.'); ?>
                    </div>
                <?php endif; ?>
                <div class="comment__article-meta reply">
                    <?php
                    echo $this->comment_reply_link(array_merge($args, array(
                        'add_below' => 'div-comment',
                        'depth' => $depth,
                        'before' => '<div class="reply">',
                        'after' => '</div>'
                    )));
                    ?>
                </div>
                <?php edit_comment_link(__('Edit'), '<div class="comment__article-meta edit-link">', '</div>'); ?>
                <?php do_action('bro_ajax_comment_rating', $comment);?>
            </div>

        </article>
        <?php
    }

    private function comment_class_children($args, $children)
    {
        global $comment_depth;
        $class = 'comment__article-wrap ';

        if (intval($comment_depth) == intval($args['max_depth'])) {
            $class .= 'tail ';

        } else {
            $class .= 'no-tail ';
        }

        $class .= $this->has_children ? 'parent' : 'children';

        return $class;
    }

    private function comment_reply_link($args = array(), $comment = null, $post = null)
    {
        global $comment_depth;
        $defaults = array(
            'add_below' => 'comment',
            'respond_id' => 'respond',
            'reply_text' => __('Reply'),
            'reply_to_text' => __('Reply to %s'),
            'login_text' => __('Log in to Reply'),
            'depth' => 0,
            'before' => '',
            'after' => ''
        );

        $args = wp_parse_args($args, $defaults);


        $comment = get_comment($comment);

        if (empty($post)) {
            $post = $comment->comment_post_ID;
        }

        $post = get_post($post);

        if (!comments_open($post->ID)) {
            return false;
        }

        $args = apply_filters('comment_reply_link_args', $args, $comment, $post);

        if (intval($comment_depth) !== intval($args['max_depth'])) {
            $max_depth = false;
        } else {
            $max_depth = true;
        }

        $json = json_encode(array(
            'comment_ID' => (int)$comment->comment_ID,
            'comment_parent' => (int)$comment->comment_parent,
            'comment_depth' => (int)$comment_depth,
            'max_depth' => $max_depth,
        ));

        if (apply_filters('bro_ajax_comments_no_register_comment', true)) {
            $link = "<a rel='nofollow' class='comment-reply-link'  data-reply-info='{$json}'  href='#article-comment-{$comment->comment_ID}' data-registration='{$args['respond_id']}' >{$args['reply_text']}</a>";
        } else if(get_option('comment_registration') && !is_user_logged_in() ) {
            $link = "<a rel='nofollow' class='comment-reply-login' data-reply-info='{$json}' href='#article-comment-{$comment->comment_ID}'>{$args['login_text']}</a>";
        }

        return apply_filters('comment_reply_link', $args['before'] . $link . $args['after'], $args, $comment, $post);
    }

}
