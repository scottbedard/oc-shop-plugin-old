+function($) { 'use strict';

    /*
     * Api Password Form Widget
     */
    var ApiPassword = function(el) {
        var $input      = $(el),
            $form       = $input.closest('form'),
            data        = $form.data('request-data'),
            token       = $input.data('token'),
            changed     = false;

        // Remove the token and set the value of our input
        $input.removeAttr('data-token').val(token);

        // Set the changed flag to true when the user presses a key
        $input.on('keypress paste change', function() {
            changed = true;
        });

        // Clear the input when it's focused
        $input.on('focus', function() {
            if (!changed) {
                $(this).val('');
            }
        });

        // If nothing has changed, reset the input when it's blurred
        $input.on('blur', function() {
            if (!changed) {
                $(this).val(token);
            }
        });
    }

    /*
     * Non-conflicting jquery plugin
     */
    var old = $.fn.driverSettings;

    $.fn.apiPassword = function () {
        new ApiPassword(this);
    }

    $.fn.apiPassword.noConflict = function () {
        $.fn.apiPassword = old;
        return this;
    }

}(window.jQuery);
