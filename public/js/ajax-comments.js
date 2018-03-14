var $ = jQuery.noConflict();
bro_ajax_comments = {
    comment_text_area: "textarea#comment, .trumbowyg-editor",
    ajax_form_data: $("#commentform").serialize(),
    run: function () {
        var this_class = this;
        this_class.comment_submit("#commentform");
        this_class.reply();
        this_class.auto_reply();
        $(window).on('hashchange', function () {
            this_class.auto_reply();
        });
        this_class.save_in_local_storge();
        // this_class.preloader();
        this_class.scrill_to_comment_article();


        $(window).on('bro_ajax_comments_beforeSubmitForm', function () {
            this_class.set_ajax_form_data();
        });


    },
    set_ajax_form_data: function () {
        var this_class = this;
        this_class.ajax_form_data = $("#commentform").serialize()
    },
    save_in_local_storge: function () {
        $("#commentform").sisyphus({
            customKeySuffix: "bro_ajax_comments_",
            locationBased: false,
            excludeFields: $("#_wp_unfiltered_html_comment_disabled , #comment_post_ID , #comment_parent"),
            timeout: 2,
            autoRelease: true
        });
    },
    comment_submit: function (selector) {
        var this_class = this;
        this_class.ajax_submit("#commentform");
        $(selector).keydown(function (e) {
            if (e.altKey && e.keyCode === 13) {
                $(selector).trigger("submit");
            }
        });
    },
    form_detach: function (comment_ID) {
        var this_class = this;
        var form = $("#respond").detach();
        form.find("#comment_parent").val(comment_ID);
        return form;
    },
    reply: function () {
        var this_class = this;
        $(".comment-reply-link").on("click", function () {
            $(window).trigger('bro_ajax_comments_reply_before');
            var json = JSON.parse($(this).attr("data-reply-info"));
            var comment_ID = json.comment_ID;
            $(this_class.form_detach(comment_ID)).appendTo("#article-comment-" + json.comment_ID);
            $(window).trigger('bro_ajax_comments_reply_after');
        });
    },
    auto_reply: function () {
        var hash = window.location.hash;
        if (hash !== "") {
            if (hash.indexOf('#article-comment-') + 1) {
                $("[href='" + hash + "']").click();
            }
        }
    },
    ajax_submit: function (selector) {
        var this_class = this;
        $(selector).attr('action', bro_ajax_comments_data.form_action);
        $(selector).find("[type='submit']").removeAttr('disabled');
        if (!$(selector).hasClass("load")) {
            $(window).trigger('bro_ajax_comments_beforeSubmitForm');

            $(selector).submit(function (e) {
                e.preventDefault();
                this_class.set_ajax_form_data();
                var form_method = $(this).attr('method');
                var form_action = $(this).attr('action');
                var form_elem = $(this);
                var form_data = this_class.ajax_form_data;

                console.log(form_data);
                $.ajax({
                    type: form_method,
                    url: form_action,
                    data: form_data,
                    beforeSend: function (jqXHR, status) {
                        $(window).trigger('bro_ajax_comments_beforeSend', {data: [jqXHR, status]});
                        form_elem.addClass("load");
                        form_elem.find("[type='submit']").attr('disabled', 'disabled');
                    },
                    success: function (json) {


                        $(".bro_ajax_comments__exceptions-message").remove();

                        if (json.hasOwnProperty('exceptions')) {
                            if (json.hasOwnProperty('exception_code')) {

                                $(this_class.comment_text_area).removeClass('empty');

                                if (json.exception_code === 403 || json.exception_code === 200) {
                                    $(window).trigger('bro_ajax_comments_error', {type: 'no-login', data: [json]});
                                } else if (json.exception_code === 411) {
                                    $(this_class.comment_text_area).addClass('empty')
                                }

                            } else {
                                $(selector).prepend(json.html_exceptions);
                            }

                        } else {

                            if (json.html !== undefined && json.comment !== undefined) {
                                var html = json.html;
                                var comment = json.comment;

                                if (form_elem.closest('.comment__article-wrap').length) {
                                    $("#comment-wrap-" + comment.comment_parent).after(html);
                                } else {
                                    $("#respond").before(html);
                                }
                                form_elem.trigger("reset");
                            }
                        }

                        $(window).trigger('bro_ajax_comments_success', json);
                    }
                    ,
                    complete: function (jqXHR, status) {
                        if (status === 'success') {
                            if (jqXHR !== undefined && !jqXHR.responseJSON.hasOwnProperty('exceptions')) {
                                var current_comment_ID = jqXHR.responseJSON.comment.comment_ID;
                                $(this_class.form_detach(current_comment_ID)).appendTo("#article-comment-" + current_comment_ID);
                                $(window).trigger('bro_ajax_comments_add_comment', {data: [jqXHR, status]});
                            }
                        }
                        $(window).trigger('bro_ajax_comments_complete', {data: [jqXHR, status]});

                        form_elem.find("[type='submit']").removeAttr('disabled');
                        form_elem.removeClass("load");
                    }
                });
            });
        }
    },

    scrill_to_comment_article: function () {
        $(window).on('bro_ajax_comments_complete', function (event, params) {

            var data = params['data'][0]['responseJSON'];
            if (!data.hasOwnProperty('exceptions')) {
                var comment_ID = data.comment.comment_ID;
                var destination = $("#comment-wrap-" + comment_ID).offset().top;
                if ($.browser.safari) {
                    $('body').animate({scrollTop: destination}, 900);
                } else {
                    $('html').animate({scrollTop: destination}, 900);
                }
            }
        });
    },
    preloader: function () {
        var timerId;
        var base_text = $("#submit").text();

        $(window).on("bro_ajax_comments_beforeSend", function () {
            var i = '';
            timerId = setInterval(function () {
                i = i + '.';

                $("#submit").text(base_text + i);
                if (i.length >= 3) {
                    i = '';
                }

            }, 1000);
        });
        $(window).on("bro_ajax_comments_complete", function () {
            clearInterval(timerId);
            $("#submit").text(base_text);
        });

    }


};

$(document).ready(function () {
    bro_ajax_comments.run();
});