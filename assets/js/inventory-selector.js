/*
 * Inventory Selector
 *
 * The inventory selector is a convenient way to prevent users from attempting
 * to add out-of-stock or invalid inventories to their cart, without having to
 * make validating ajax requests. For a usage example, please see the demo
 * partial (inventory-selector.htm).
 */
+function($) { 'use strict';
    var InventorySelector = function(el) {
        var self = this;

        self.$container     = $(el);
        self.$options       = self.$container.find('select');
        self.available      = self.$container.data('available');
        self.disabledMsg    = self.$container.data('disabled') || 'Out of stock';

        self.updateAvailable();

        // Update the selected inventories when something changes
        self.$options.on('change', function() {
            self.updateAvailable();
        });

        // Allow the user to clear out a selection
        self.$container.find('[data-clear]').on('click', function() {
            self.clearSelection($(this).data('clear'));
            self.updateAvailable();
        });
    }

    /*
     * Update the available inventories
     */
    InventorySelector.prototype.updateAvailable = function() {
        var self = this;

        // Loop through the options, and check each of it's value's availability
        self.$options.each(function() {
            var $values         = $(this).find('option'),
                thisOption      = $(this).data('option'),
                otherOptions    = [];

            // Build an array of the other selected options
            self.$options.each(function() {
                var valueId = parseInt($(this).val());
                if ($(this).data('option') != thisOption && valueId) {
                    otherOptions.push(valueId);
                }
            });

            // Loop through this option's values and check their availability
            $values.each(function() {
                var valueId = parseInt($(this).val());

                // Ignore options with no value
                if (!valueId) {
                    return true;
                }

                // Push this value onto the selection being checked
                var selection = otherOptions.slice();
                selection.push(valueId);

                // Loop through the available inventories until we find a match
                var isDisabled = !(function(selection) {
                    for (var i = 0, stop = self.available.length; i < stop; i++) {
                        if (self.arrayMatch(selection, self.available[i], false)) {
                            return true;
                        }
                    }
                    return false;
                }(selection));

                $(this).prop('disabled', isDisabled);

                if (isDisabled) {
                    $(this).html($(this).data('name') + ' - ' + self.disabledMsg)
                } else {
                    $(this).html($(this).data('name'));
                }
            });
        });

        // Lastly update our button and trigger an event for the theme to hook into
        self.updateButton();
        $(document).trigger('bedard.shop::inventory.selected');
    }

    /*
     * Checks if the first array values are present in the second array. If strict
     * mode is enabled, the length of the two arrays must also match.
     *
     * @param   array       first
     * @param   array       second
     * @param   boolean     strict
     * @return  boolean
     */
    InventorySelector.prototype.arrayMatch = function(first, second, strict) {
        for (var i = 0, stop = first.length; i < stop; i++) {
            if ($.inArray(first[i], second) == -1) {
                return false;
            }
        }

        return !strict || first.length == second.length;
    }

    /*
     * Clear a selection
     *
     * @param   string      optionId
     */
    InventorySelector.prototype.clearSelection = function(optionId) {
        var self    = this,
            $select = self.$container.find('select[data-option="' + optionId + '"]');

        // Reset the select to it's default value
        $select.find('option').prop('selected', function() {
            return this.defaultSelected;
        });

        $select.blur();
    }

    /**
     * Enables and disables the add to cart button
     */
    InventorySelector.prototype.updateButton = function() {
        var self        = this,
            $button     = self.$container.find('[data-control="cart-add"]'),
            isDisabled  = true,
            selected    = [];

        // First, create an array of the selected inventories
        self.$options.each(function() {
            if ($(this).val()) {
                selected.push(parseInt($(this).val()));
            }
        });

        // Loop through the available inventories and look for an exact match
        for (var i = 0, stop = self.available.length; i < stop; i++) {
            if (self.arrayMatch(selected, self.available[i], true)) {
                isDisabled = false;
            }
        }

        // Lastly, enable or disable the button
        $button.prop('disabled', isDisabled);
    }

    /*
     * Non-conflicting jquery plugin
     */
    var old = $.fn.inventorySelector;

    $.fn.inventorySelector = function () {
        new InventorySelector(this);
    }

    $.fn.inventorySelector.noConflict = function () {
        $.fn.inventorySelector = old;
        return this;
    }

    $(document).ready(function() {
        $('[data-control="inventory-selector"]').inventorySelector();
    });

}(window.jQuery);
