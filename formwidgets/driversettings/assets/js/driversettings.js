/*
 * Driver Settings Widget
 */
+function($) { 'use strict';

    /*
     * Constructor
     */
    var DriverSettings = function(el, config) {
        var self = this;

        self.$container = $(el);
        self.alias      = config.alias;

        self.$container.on('click', 'div[data-control="driver"]', function() {
            self.openPopup($(this));
        });
    }

    /*
     * Open a popup
     *
     * @param   jQuery  $driver
     */
    DriverSettings.prototype.openPopup = function($driver) {
        var self = this;

        // Handle the popup
        self.$container.one('show.oc.popup', function() {
            self.handlePopup();
            return false;
        });

        // Create the popup
        self.$container.popup({
            extraData: {
                id: $driver.data('id'),
            },
            handler: self.alias + '::onPopup'
        });
    }

    /*
     * Handles a popup
     */
    DriverSettings.prototype.handlePopup = function() {
        var self        = this,
            $popup      = $('.driversettings-modal'),
            $form       = $popup.find('form'),
            $indicator  = $popup.find('div.loading-indicator');

        // Update the container when apply button is clicked
        $popup.on('click', 'button[data-action="apply-btn"]', function() {
            $indicator.show();
            $form.request(self.alias + '::onUpdateDriver', {
                success: function() {
                    $popup.trigger('close.oc.popup');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    if (jqXHR.status == 406 && jqXHR.responseJSON) {
                        $.oc.flashMsg({
                            'text': jqXHR.responseJSON.result,
                            'class': 'error',
                            'interval': 3
                        });
                    }

                    this.error(jqXHR, textStatus, errorThrown);
                },
                complete: function() {
                    $indicator.hide();
                },
            });
        });
    }

    /*
     * Non-conflicting jquery plugin
     */
    var old = $.fn.driverSettings;

    $.fn.driverSettings = function (config) {
        new DriverSettings(this, config);
    }

    $.fn.driverSettings.noConflict = function () {
        $.fn.driverSettings = old;
        return this;
    }

}(window.jQuery);
