+function ($) { "use strict";

    //
    // ReorderCategories
    //
    var ReorderCategories = function ($el) {
        var self = this;

        this.$el = $el;

        this.$el.unbind().on('click', function() {
            self.popup();
        });
    }

    //
    // Popup form
    //
    ReorderCategories.prototype.popup = function() {

        this.$el.on('show.oc.popup', function(e) {
            var $popup = $(e.relatedTarget),
                $list = $popup.find('ol[data-control="treeview"]').first(),
                adjustment;

            // Inline the list height to prevent the modal from
            // shrinking while categories are being re-ordered
            $list.css('height', $list.height());

            // Destroy and bind sortable, and animate the transitions
            $list.sortable('destroy');
            var group = $list.sortable({
                onDrop: function(item, container, _super) {
                    var clonedItem = $('<li/>').css({height: 0});
                    item.before(clonedItem);
                    clonedItem.animate({'height': item.height()});
                    item.animate(clonedItem.position(), function() {
                        clonedItem.detach();
                        _super(item);
                    });
                },
                onDragStart: function ($item, container, _super) {
                    var offset = $item.offset(),
                        pointer = container.rootGroup.pointer;
                    adjustment = {
                        left: pointer.left - offset.left,
                        top: pointer.top - offset.top
                    };
                    _super($item, container);
                },
                onDrag: function ($item, position) {
                    $item.css({
                        left: position.left - adjustment.left,
                        top: position.top - adjustment.top
                    });
                },
            });

            // Submit the request on apply button clicks
            $popup.find('button[data-control="apply-btn"]').on('click', function() {
                var $loadingIndicator = $popup.find('div.loading-indicator'),
                    $buttons = $popup.find('button'),
                    i = 1,
                    data = [];

                // Cycle through the list items and create the array of
                // data to send to the ajax handler.
                $list.find('li').each(function() {
                    data.push({
                        id: parseInt($(this).data('id')) || null,
                        parent_id: parseInt($(this).parent().data('parent')) || null,
                        position: i
                    });
                    i++;
                });

                // Toggle the loading indicator and pass the request off
                // to our ajax handler.
                $loadingIndicator.show();
                $.request('reorderCategories::onUpdateCategories', {
                    data: {
                        bedard_shop_categories: data,
                    },
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
            });
        });

        // Trigger the popup
        this.$el.popup({
            handler: 'reorderCategories::onLoadPopup'
        });
    }

    //
    // Attach ReorderCategories to the element
    //
    $.fn.ReorderCategories = function () {
        return new ReorderCategories(this);
    }

    $(document).on('render', function() {
        $('[data-control="reorderCategories"]').ReorderCategories();
    });

}(window.jQuery);
