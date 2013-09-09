/**
###lit###
 */

(function($) {
    var popup;

    $.fn.eauth = function(options) {
        options = $.extend({
            id: '',
            width: 450,
            height: 380
        }, options);

        return this.each(function() {
            var el = $(this);
            el.click(function() {

                if (popup !== undefined)
                    popup.close();

                var redirect_uri, url = redirect_uri = this.href;
                url += (url.indexOf('?') >= 0 ? '&' : '?') + 'redirect_uri=' + encodeURIComponent(redirect_uri);
                url += '&js';

                var centerWidth = ($(window).width() - options.width) / 2;
                var centerHeight = ($(window).height() - options.height) / 2;

                popup = window.open(url, "ruoauth_popup", "width=" + options.width + ",height=" + options.height + ",left=" + centerWidth + ",top=" + centerHeight + ",resizable=yes,scrollbars=no,toolbar=no,menubar=no,location=no,directories=no,status=yes");
                popup.focus();

                return false;
            });
        });
    };
}) (jQuery);