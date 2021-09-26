define([
        'ko',
        'uiComponent',
        'mage/url',
        'mage/storage',
        'Magento_Customer/js/model/customer',
        'Magento_Ui/js/model/messageList',
        'Vnext_RewardPoints/js/view/checkout/summary/fee',
        'jquery',
        'Magento_Checkout/js/action/get-totals',
        'Magento_Checkout/js/action/get-payment-information'
    ],
    function (ko, Component, urlBuilder, storage, customer,messageList,fee,$,getTotalsAction,getPaymentInformation) {
        'use strict';

        var check = customer.isLoggedIn()
        return Component.extend({

            defaults: {
                template: 'Vnext_RewardPoints/payment/discount'
            },
            /** @inheritdoc */
            initialize: function () {
                this._super();
                return this;
            },
            getKeyword: function () {
                var self = this;
                var serviceUrl = urlBuilder.build('rewardpoints/ajax/index');
                var data = document.getElementById('search-example').value;
                return storage.post(
                    serviceUrl,
                    JSON.stringify({'keyword': data}),
                    false
                ).done(function (response) {
                    alert(response.keyword);
                    window.location.reload();
                    var deferred = $.Deferred();
                    getTotalsAction([], deferred);
                    getPaymentInformation().done(function () {
                        self.isVisible(true);
                    });
                    }

                ).fail(function (response) {
                    // code khi fail
                });
            },
            isLoggedIn: function () {
                return customer.isLoggedIn();
            },
            isDisplayed: function () {
                return true;
            }
        });
    }
);

