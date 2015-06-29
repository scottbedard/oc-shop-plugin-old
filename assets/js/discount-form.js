+function ($) { "use strict";
    var DiscountForm = function (el) {
        var self = this;

        self.$form = $(el);

        self.$form.on('click', '.relationselector-partial li', function() {
            self.highlightRelationSelection($(this));
        });
    }

    /*
     * Highlight / Unhighlight the selected row for deletion
     */
    DiscountForm.prototype.highlightRelationSelection = function($li) {
        var self = this,
            $checkbox = $li.find('input[type="checkbox"]'),
            checked = !$checkbox.prop('checked');

        $checkbox.prop('checked', checked);
        $li.toggleClass('delete');
    }

    /*
     * Bind form as a jQuery plugin
     */
    $.fn.discountForm = function () {
        new DiscountForm(this);
    }

    $(document).ready(function() {
        $('#discount-form').discountForm();
    });

}(window.jQuery);
