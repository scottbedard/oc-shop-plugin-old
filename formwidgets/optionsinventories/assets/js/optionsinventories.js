+function ($) { "use strict";
    var OptionsInventories = function ($el) {
        var self = this;

        this.$el = $el;
        this.alias = 'formOptionsinventories';

        // Trigger the popup with a new item
        $('a[data-control="add"]').unbind().on('click', function() {
            var $list = $(this).parent().find('ol').first();
            $list.append('<li></li>');
            var $li = $list.find('li').last();

            self.triggerPopup($list, $li, true);
        });

        // Handle item delete and update clicks
        $('ol[data-model]').unbind().on('click', 'div.delete', function() {
            self.deleteItem($(this).closest('li'));
            return false;
        }).on('click', 'li', function() {
            var $list = $(this).parent();
            self.triggerPopup($list, $(this), false);
            return false;
        });
    }

    //
    // Trigger a new popup
    //
    OptionsInventories.prototype.triggerPopup = function($list, $li, newItem) {
        var self = this;

        // Show popup
        $li.one('show.oc.popup', function(e) {
            self.showPopup(e, $list, $li, newItem);
            return false;
        });

        // Hide popup
        $li.one('hide.oc.popup', function() {
            if (newItem) {
                $list.find('li').last().remove();
            }
            return false;
        })

        // Determine which handler to send this to
        $li.popup({
            extraData: {
                id: $li.data('id') || null
            },
            handler: this.alias + '::onDisplay' + $list.data('model')
        });
    }

    //
    // Handles the opening of a popup
    //
    OptionsInventories.prototype.showPopup = function(e, $list, $li, newItem) {
        var self    = this,
            $popup  = $(e.relatedTarget),
            $form   = $popup.find('form'),
            $apply  = $popup.find('[data-control="apply-btn"]').first(),
            $loadingIndicator = $popup.find('div.loading-indicator');

        // Determine the option ID
        var modelId = typeof $li.data('id') != 'undefined'
            ? $li.data('id')
            : 0;

        // Submit the form on apply button click
        $apply.on('click', function() {
            $loadingIndicator.show();
            $form.request(self.alias + '::onProcess' + $list.data('model'), {
                success: function(data) {
                    this.success(data).done(function() {
                        $popup.trigger('close.oc.popup');
                        $(document).trigger('render');
                    });
                },
                complete: function(data) {
                    $loadingIndicator.hide();
                }
            });
            return false;
        })

        $form.find('select').select2()
        $(document).trigger('render')
    }

    //
    // Delete an existing item
    //
    OptionsInventories.prototype.deleteItem = function($li)
    {
        var self = this,
            model = $li.closest('ol').data('model'),
            handler = this.alias + '::onDelete' + model,
            title   = lang['relation.delete_confirm'] || 'Are you sure?',
            text    = lang[model + '.delete_text'] || false,
            confirm = lang['form.confirm'] || 'Yes',
            cancel  = lang['form.cancel'] || 'No';

        $li.addClass('pre-delete');
        swal({
            title: title,
            text: text,
            showCancelButton: true,
            closeOnConfirm: true,
            confirmButtonText: confirm,
            cancelButtonText: cancel
        }, function() {
            $.request(handler, {
                data: {
                    id: $li.data('id'),
                },
                complete: function() {
                    $li.removeClass('pre-delete')
                }
            })
        });

        // Catch delete cancelations
        $('.sweet-alert').on('click', '.cancel', function() {
            $li.removeClass('pre-delete');
        });
    }

    //
    // Bind to widget container
    //
    $.fn.OptionsInventories = function () {
        return new OptionsInventories(this);
    }

    $(document).on('render', function() {
        $('div#options-inventories').OptionsInventories();
    });

}(window.jQuery);
