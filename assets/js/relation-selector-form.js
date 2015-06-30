+function($) { 'use strict';

    /*
     * General script for the RelationSelector widget extension
     */
    var RelationSelectorForm = function(el) {

        // Highlight the relationselector rows when clicked
        $(el).on('click', '.relationselector-partial li', function() {
            var $checkbox = $(this).find('input[type="checkbox"]'),
                checked = !$checkbox.prop('checked');

            $checkbox.prop('checked', checked);
            $(this).toggleClass('delete');
        });

    }

    /*
     * Non-conflicting jquery plugin
     */
    var old = $.fn.relationSelectorForm;

    $.fn.relationSelectorForm = function () {
        new RelationSelectorForm(this);
    }

    $.fn.relationSelectorForm.noConflict = function () {
        $.fn.relationSelectorForm = old;
        return this;
    }

    $(document).ready(function() {
        $('form div.relationselector').relationSelectorForm();
    });

}(window.jQuery);
