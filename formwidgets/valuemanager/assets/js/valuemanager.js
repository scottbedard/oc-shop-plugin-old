+function ($) { "use strict";

    var ValueManager = function ($el) {
        var self        = this,
            $list       = $el.find('ol'),
            $input      = $el.find('[data-control="add-value"]'),
            $template   = $el.find('[data-control="template"]');

        $list.sortable({
            forcePlaceholderSize: true
        });

        $input.unbind().on('keydown', function(e) {
            if (e.keyCode == 9 || e.keyCode == 13) {
                e.preventDefault();
                self.addValue($list, $(this), $template);
            }
        });
    }

    ValueManager.prototype.addValue = function($list, $input, $template) {
        if ($input.val().length == 0) return;

        var value = $input.val().toLowerCase(),
            exists = false;

        // Prevent duplicate entries
        $list.find('li').each(function() {
            if ($(this).data('value') == value) {
                var $li = $(this);
                exists = true;
                $li.addClass('flash');
                setTimeout(function() {
                    $li.removeClass('flash');
                }, 300);
            }
        });

        if (exists == true) return;

        var $item = $($template.html());
        $item.data('value', value);
        $item.find('input').val($input.val());
        $list.append($item);
        $input.val('');
    }

    //
    // Bind to widget container
    //
    $.fn.ValueManager = function () {
        return new ValueManager(this);
    }

    $(document).on('render', function() {
        $('div#valuemanager').ValueManager();
    });

}(window.jQuery);
