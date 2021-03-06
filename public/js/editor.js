var $ = jQuery.noConflict();

bro_ajax_editor = {
    text_area_selector: 'textarea#comment',
    run: function () {
        var this_class = this;

        if (!('ontouchstart' in document.documentElement)) {
            this_class.editorRout();
            this_class.initEditor();
        }

        $(window).on('resize', function () {
            this_class.windowSize();
        });

    },

    windowSize: function () {
        var this_class = this;
        var thr = false;
        var funct = function () {
            if ($(window).width() <= '991') {
                this_class.destroyEditor();
            } else {
                this_class.initEditor();
            }
        };

        if (!thr) {
            thr = true;
            funct();
            setTimeout(function () {
                thr = false;
            }, 250);
        }

    },

    editorRout: function () {
        var this_class = this;
        $(window).on('bro_ajax_comments_beforeSubmitForm', function () {
            this_class.disableEditor();
            this_class.insertEditor();
        });
        $(window).on('bro_ajax_comments_complete', function () {
            this_class.enableEditor();
        });

        $(window).on('bro_ajax_comments_add_comment', function () {
            this_class.emptyEditor();
        });

        $(this_class.text_area_selector).on('tbwchange', function () {
            $(window).trigger('bro_ajax_comments_change_text_data');
        });


    },

    initEditor: function () {
        var this_class = this;

        $(this_class.text_area_selector).trumbowyg({
            svgPath: '/wp-content/plugins/bro_ajax_comments/public/js/vendor/trumbowyg/dist/ui/icons.svg',
            lang: 'ru',
            btns: [
                'strong',
                'em',
                'del',
                'unorderedList',
                'orderedList'
            ],
            removeformatPasted: true
        });

    },

    insertEditor: function () {
        var this_class = this;
        var html = $(this_class.text_area_selector).trumbowyg('html');
        $(this_class.text_area_selector).trumbowyg('html', html);
    },

    destroyEditor: function () {
        var this_class = this;
        $(this_class.text_area_selector).trumbowyg('destroy');
    },
    emptyEditor: function () {
        var this_class = this;
        $(this_class.text_area_selector).trumbowyg('empty');
    },
    disableEditor: function () {
        var this_class = this;
        $(this_class.text_area_selector).trumbowyg('disable');
    },
    enableEditor: function () {
        var this_class = this;
        $(this_class.text_area_selector).trumbowyg('enable');
    }

};
$(document).ready(function () {
    bro_ajax_editor.run();
});


