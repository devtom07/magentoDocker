define(
    [
        'Vnext_RewardPoints/js/view/checkout/summary/fee'
    ],
    function (Component) {
        'use strict';

        return Component.extend({

            /**
             * @override
             */
            isDisplayed: function () {
                return true;
            },
            getValue: function() {
                var price = 10;
                if (this.totals()) {
                    price = totals.getSegment('custom_discount').value;
                }
                return this.getFormattedPrice(price);
            }
        });
    }
);
