+function ($) { "use strict";

    var ValueManager = function ($el) {
        var self        = this,
            $list       = $el.find('ol'),
            $input      = $el.find('[data-control="add-value"]'),
            $template   = $el.find('[data-control="template"]');

        $input.unbind().on('keydown', function(e) {
            if (e.keyCode == 9 || e.keyCode == 13) {
                e.preventDefault();
                self.addValue($list, $(this), $template);
            }
        });

        $(document).on('render', function() {
            $list.unbind()
                .sortable({
                    handle: '.handle',
                })
                .on('click', '.delete', function() {
                    self.deleteValue($(this).closest('li'));
                })
        });
    }

    //
    // Add a new value to the list
    //
    ValueManager.prototype.addValue = function($list, $input, $template) {
        if ($input.val().length == 0) return;

        var name = $input.val().toLowerCase(),
            exists = false;

        // Prevent duplicate entries
        $list.find('li').each(function() {
            if ($(this).data('name') == name) {
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
        $item.data('name', name);
        $item.find('input[data-control="name"]').val($input.val());
        $list.append($item);
        $input.val('');

        $list.sortable('destroy').sortable();
    }

    //
    // Delete a value from the list
    //
    ValueManager.prototype.deleteValue = function($li) {
        var title   = OptionsInventoriesLang['relation.delete_confirm'] || 'Are you sure?',
            text    = OptionsInventoriesLang['value.delete_text'] || false,
            confirm = OptionsInventoriesLang['form.confirm'] || 'Yes',
            cancel  = OptionsInventoriesLang['form.cancel'] || 'No';

        swal({
            title: title,
            text: text,
            showCancelButton: true,
            closeOnConfirm: true,
            confirmButtonText: confirm,
            cancelButtonText: cancel
        }, function() {
            $li.remove();
        });
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
