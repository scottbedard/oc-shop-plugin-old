+function ($) { "use strict";

    var OptionsInventories = function (el) {
        var self = this;

        self.$widget = $(el);

        // Trigger the popup with a new item
        $('a[data-control="add"]').unbind().on('click', function() {
            var $list = $(this).parent().find('ol')
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

            self.setToolbarVisibility('hidden');
            return false;
        });

        // Hide popup
        $li.one('hide.oc.popup', function() {
            if (newItem) {
                $list.find('li').last().remove();
            }

            self.setToolbarVisibility('visible');
            return false;
        })

        // Determine which handler to send this to
        $li.popup({
            extraData: {
                id: $li.data('id') || null,
                sessionKey: self.$widget.data('session-key'),
            },
            handler: OptionsInventoriesAlias + '::onDisplay' + $list.data('model')
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
            $form.request(OptionsInventoriesAlias + '::onProcess' + $list.data('model'), {
                data: {
                    sessionKey: self.$widget.data('session-key')
                },
                success: function(data) {
                    this.success(data).done(function() {
                        self.setToolbarVisibility('visible');
                        $popup.trigger('close.oc.popup');
                    });
                },
                complete: function(data) {
                    $loadingIndicator.hide();
                }
            });
            return false;
        });

        $form.find('select').select2();
        $(document).trigger('render');
    }

    //
    // Delete an existing item
    //
    OptionsInventories.prototype.deleteItem = function($li)
    {
        var self = this,
            model = $li.closest('ol').data('model'),
            handler = OptionsInventoriesAlias + '::onDelete' + model,
            title   = OptionsInventoriesLang['relation.delete_confirm'] || 'Are you sure?',
            text    = OptionsInventoriesLang[model + '.delete_text'] || false,
            confirm = OptionsInventoriesLang['form.confirm'] || 'Yes',
            cancel  = OptionsInventoriesLang['form.cancel'] || 'No';

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
                    sessionKey: self.$widget.data('session-key'),
                },
                complete: function() {
                    $li.removeClass('pre-delete')
                }
            });
        });

        // Catch delete cancelations
        $('.sweet-alert').on('click', '.cancel', function() {
            $li.removeClass('pre-delete');
        });
    }

    //
    // Adjust the parent form's toolbar visibility. This is done to
    // prevent hotkey conflicts. When a user hits "ctrl+s", we only
    // want to save the popup and not the parent product.
    //
    OptionsInventories.prototype.setToolbarVisibility = function(visibility)
    {
        $('div.form-buttons').css('visibility', visibility);
    }

    //
    // Bind to widget container
    //
    $.fn.OptionsInventories = function () {
        return new OptionsInventories(this);
    }

    $(document).on('render', function() {
        $('div#options-inventories').OptionsInventories();
        $('div#options-inventories ol[data-model="Option"]')
            .sortable()
            .bind('sortstart', function(e, ui) {
                $(this).addClass('sorting');
            })
            .bind('sortstop', function(e, ui) {
                $(this).removeClass('sorting')
            });
    });

}(window.jQuery);
