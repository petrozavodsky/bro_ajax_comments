var $ = jQuery.noConflict();

bro_ajax_stickers = {
    run: function () {
        var this_class = this;
        this_class.click_in_sticker();
        $(window).on('bro_ajax_comments_complete', function () {
            this_class.activate_stickers()
        });
    },
    click_in_sticker: function () {
        var this_class = this;
        $(".comments__stickers-box-item-wrap").on("click", function () {
            var sticker_id = $(this).find("[data-stickers-id]").attr("data-stickers-id");
            $(this).closest(".comments__stickers-box-wrap").addClass('stickers-submit');

            this_class.insert_sticker(sticker_id);
        });
    },
    activate_stickers: function () {
        $(".comments__stickers-box-wrap").removeClass('stickers-submit');
    },
    insert_sticker: function (id) {
        var this_class = this;
        var sticker_name = '[bro_sticker id="'+id+'" ]';
        $("#comment").val(sticker_name);
        $(window).trigger('bro_ajax_comments_change_text_data');
        $("#commentform").trigger("submit");
    }

};

$(document).ready(function () {
    bro_ajax_stickers.run();
});
