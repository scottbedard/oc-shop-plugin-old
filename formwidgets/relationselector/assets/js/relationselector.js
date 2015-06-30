+function($) { 'use strict';
    var RelationSelector = function(el, config) {
        var self = this;
        self.$container = $(el);
        self.alias      = config.alias;

        self.$container.find('a[data-control="add"]').on('click', function() {
            self.openPopup();
        });

        self.$container.find('a[data-control="remove"]').on('click', function() {
            self.removeSelected();
        });

        self.$container.on('syncSelected', function() {
            self.syncSelected();
        });
    }

    /*
     * Displays a popup where relations can be selected
     */
    RelationSelector.prototype.openPopup = function() {
        var self = this;

        self.$container.one('show.oc.popup', function() {
            self.handlePopup();
            return false;
        });

        // Open the new popup
        self.$container.popup({
            handler: self.alias + '::onSelectRelations'
        });
    }

    /*
     * Handles the already opened popup
     */
    RelationSelector.prototype.handlePopup = function() {
        var self    = this,
            $popup  = $('.relationselector-modal'),
            $search = $popup.find('input[type="search"]');

        // Pull the selected models from the container
        $popup.data('selected', self.$container.data('attached'));
        self.syncSelected();

        // Toggle the checkbox and active class when a row is clicked
        $popup.on('click', 'table tbody tr', function() {
            self.selectRow($(this), 'toggle', $popup);
        });

        // Handle the "select all" button
        $popup.on('click', 'table thead th[data-action="selectAll"]', function() {
            var $checkbox   = $(this).find('input[type="checkbox"]'),
                checked     = !$checkbox.prop('checked');

            $checkbox.prop('checked', checked);
            $popup.find('table tbody tr').each(function() {
                self.selectRow($(this), checked, $popup);
            });
        });

        // Update the container when apply button is clicked
        $popup.on('click', 'button[data-action="apply"]', function() {
            $popup.find('div.loading-indicator').show();
            self.$container.request(self.alias + '::onUpdateAttached', {
                data: { attached: $popup.data('selected') },
                complete: function() {
                    self.$container.data('attached', $popup.data('selected'));
                    self.$container.trigger('updated');
                    $popup.trigger('close.oc.popup');
                },
            });
        });
    }

    /*
     * Selects a row
     *
     * @param   <tr>            The row being selected
     * @param   boolean|string  The value to set the row to (true, false, 'toggle')
     * @param   <div>           The popup container
     */
    RelationSelector.prototype.selectRow = function($row, checked, $popup) {
        checked = typeof checked !== 'undefined' ? checked : 'toggle';
        $popup = typeof $popup !== 'undefined' ? $popup : false;

        var self        = this,
            $checkbox   = $row.find('input[type="checkbox"]'),
            id          = $checkbox.data('id');

        if (checked == 'toggle') {
            checked = !$checkbox.prop('checked');
        }

        $checkbox.prop('checked', checked);

        // Add or remove the active class
        if (checked) {
            $row.addClass('active');
        } else {
            $row.removeClass('active');
        }

        // Add or remove the element from the selected data
        if ($popup != false) {
            var index = $popup.data('selected').indexOf(id);
            if (checked && index == -1) {
                $popup.data('selected').push(id);
            } else if (!checked && index != -1) {
                $popup.data('selected').splice(index, 1);
            }
        }
    }

    /*
     * Keep the checkboxes synchronized with the selected data
     */
    RelationSelector.prototype.syncSelected = function() {
        var self    = this,
            $popup  = $('.relationselector-modal');

        $popup.find('table tbody tr').each(function() {
            var $checkbox   = $(this).find('input[type="checkbox"]'),
                id          = $checkbox.data('id'),
                checked     = $popup.data('selected').indexOf(id) != -1;

            $checkbox.prop('checked', checked);

            if (checked) {
                $(this).addClass('active');
            } else {
                $(this).removeClass('active');
            }
        });
    }

    /*
     * Remove selected items from the widget
     */
    RelationSelector.prototype.removeSelected = function() {
        var self        = this,
            attached    = self.$container.data('attached');

        self.$container.find('input[type="checkbox"][data-remove]').each(function() {
            if (!$(this).prop('checked')) return true;

            var index = attached.indexOf($(this).data('remove'));
            if (index != -1) {
                attached.splice(index, 1);
            }
        });

        self.$container.request(self.alias + '::onUpdateAttached', {
            data: { attached: attached },
            complete: function() {
                self.$container.data('attached', attached);
            },
        });
    }

    /*
     * Non-conflicting jquery plugin
     */
    var old = $.fn.relationSelector;

    $.fn.relationSelector = function (config) {
        new RelationSelector(this, config);
    }

    $.fn.relationSelector.noConflict = function () {
        $.fn.relationSelector = old;
        return this;
    }

}(window.jQuery);
