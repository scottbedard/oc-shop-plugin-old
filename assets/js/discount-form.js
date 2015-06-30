+function ($) { "use strict";
    var DiscountForm = function (el) {
        var self = this;

        self.$form = $(el);

        // Highlight the relationselector rows when clicked
        self.$form.on('click', '.relationselector-partial li', function() {
            self.highlightRelationSelection($(this));
        });

        // Calculate the prices when the document is ready, and when an
        // ajax request is completed.
        self.calculatePrices();
        self.$form.on('ajaxComplete', function() {
            self.calculatePrices();
        });

        // Calculate prices when the exact amount changes
        self.$form.on('change keyup paste', 'input[name="Discount[amount_exact]"]', function() {
            self.calculateExactPrices();
        });

        // Calculate prices when the knob widget changes
        self.$form.on('change keyup paste', 'input[name="Discount[amount_percentage]"]', function(e, value) {
            value = typeof value != 'undefined' ? value : parseInt($(this).val());
            self.calculatePercentagePrices(Math.round(value));
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
     * Determines which type of discount is selected, and passes to
     * the appropriate price calculator
     */
    DiscountForm.prototype.calculatePrices = function() {
        var self            = this,
            $exact          = $('input[name="Discount[amount_exact]"]');

        if (typeof $exact.val() != 'undefined') {
            self.calculateExactPrices();
        } else {
            self.calculatePercentagePrices(parseInt($('input[name="Discount[amount_percentage]"]').val()));
        }
    }

    /*
     * Calculates the price of products using an "exact" discount
     */
    DiscountForm.prototype.calculateExactPrices = function() {
        var self        = this,
            $products   = self.$form.find('#formProducts .attached ul li'),
            amount      = $('input[name="Discount[amount_exact]"]').val(),
            discount    = amount ? parseFloat(amount) : 0;

        $products.each(function() {
            var $base               = $(this).find('span.base-price'),
                $discounted         = $(this).find('span.discounted-price'),
                base_price          = $base.data('base-price'),
                discounted_price    = base_price - discount;

            self.updatePrice($base, $discounted, base_price, discounted_price);
        });
    }

    /*
     * Calculates the price of products using a "relative" discount
     *
     * @param   integer
     */
    DiscountForm.prototype.calculatePercentagePrices = function(discount) {
        var self        = this,
            $products   = self.$form.find('#formProducts .attached ul li');

        $products.each(function() {
            var $base               = $(this).find('span.base-price'),
                $discounted         = $(this).find('span.discounted-price'),
                base_price          = $base.data('base-price'),
                discounted_price    = base_price - (base_price * (discount / 100));

            self.updatePrice($base, $discounted, base_price, discounted_price);
        });
    }

    /*
     * Updates the price preview for a product
     *
     * @param   <span.base-price>       $base
     * @param   <span.discount-price>   $discounted
     * @param   float                   base_price
     * @param   float                   discounted_price
     */
    DiscountForm.prototype.updatePrice = function($base, $discounted, base_price, discounted_price) {
        if (discounted_price < 0) {
            discounted_price = 0;
        }

        if (discounted_price > base_price) {
            discounted_price = base_price;
        }

        if (isNaN(discounted_price) || discounted_price == base_price) {
            $base.removeClass('discounted');
            $discounted.html('');
        } else {
            $base.addClass('discounted');
            $discounted.html(discounted_price.toFixed(2));
        }
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
