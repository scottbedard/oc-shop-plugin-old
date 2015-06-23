/*
 * Inventory Selector
 *
 * The inventory selector is a convenient way to prevent users from attempting
 * to add out-of-stock or invalid inventories to their cart, without having to
 * make validating ajax requests. For usage and markup examples, please see
 * the demonstration partial (inventory-selector.htm).
 */
+function($) { 'use strict';
    var InventorySelector = function(el) {
        var self = this;

        self.$container     = $(el);
        self.$options       = self.$container.find('select');
        self.$clear         = self.$container.find('[data-clear]');
        self.available      = self.$container.data('available');
        self.disabledMsg    = self.$container.data('disabled') || 'Out of stock';

        self.updateAvailable();

        // Update the selected inventories when something changes
        self.$options.on('change', function() {
            self.updateAvailable();
        });

        // Allow the user to clear out a selection
        self.$clear.on('click', function() {
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

            // Loop through this option's values and see what is available
            $values.each(function() {
                var valueId = parseInt($(this).val());
                if (!valueId) return true;

                // Push this value onto the selection being checked
                var selection = otherOptions.slice();
                selection.push(valueId);

                // Enable or disable the option based on availability, and add
                // the out-of-stock label if needed
                var isDisabled = !self.isAvailable(selection)
                $(this).prop('disabled', isDisabled);

                if (isDisabled) {
                    $(this).html($(this).data('name') + ' - ' + self.disabledMsg)
                } else {
                    $(this).html($(this).data('name'));
                }
            });
        });
    }

    /*
     * Checks if a selected inventory is available or not
     *
     * @param   array       selection
     * @return  boolean
     */
    InventorySelector.prototype.isAvailable = function(selection) {
        var self = this,
            selectionLength = selection.length;

        // Loop through the available inventories
        for (var i = 0, iStop = self.available.length; i < iStop; i++) {

            // The selection is available only if all values are present in the inventory
            var isAvailable = (function(needles, haystack) {
                for (var j = 0, jStop = needles.length; j < jStop; j++) {
                    if($.inArray(needles[j], haystack) == -1) return false;
                }
                return true;
            }(selection, self.available[i]));

            // Once we find a match, go ahead and return true
            if (isAvailable) return true;
        }

        return false;
    }

    /*
     * Clear a selection
     *
     * @param   string      optionId
     */
    InventorySelector.prototype.clearSelection = function(optionId) {
        var self = this,
            $select = self.$container.find('select[data-option="' + optionId + '"]');

        // Reset the select to it's default value
        $select.find('option').prop('selected', function() {
            return this.defaultSelected;
        });

        $select.blur();
    }

    /*
     * Bind InventorySelector as a jQuery plugin
     */
    $.fn.inventorySelector = function () {
        new InventorySelector(this);
    }

    $(document).ready(function() {
        $('[data-control="inventory-selector"]').inventorySelector();
    });

}(window.jQuery);
