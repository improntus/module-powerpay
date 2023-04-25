define([
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/action/place-order',
    'Magento_Checkout/js/model/full-screen-loader',
    'jquery'
], function (
    Component,
    quote,
    additionalValidators,
    placeOrderAction,
    fullScreenLoader,
    $
) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Improntus_PowerPay/payment/method',
            code: 'powerpay',
            active: false
        },

        getCode: function() {
            return this.code;
        },

        getTitle: function () {
            return window.checkoutConfig.payment[this.getCode()].title;
        },

        getBanner: function () {
            return window.checkoutConfig.payment[this.getCode()].banner;
        },

        getWidgets: function () {
            return window.checkoutConfig.payment[this.getCode()].widgets_active;
        },

        getCheckoutWidget: function () {
            return window.checkoutConfig.payment[this.getCode()].checkout_widget;
        },

        getClientId: function () {
            return window.checkoutConfig.payment[this.getCode()].client_id;
        },

        getOrderTotal: function () {
            return quote.totals()['grand_total'];
        },

        afterPlaceOrder: function () {
            fullScreenLoader.startLoader();
            window.location.href = window.checkoutConfig.payment[this.getCode()].redirect_url;
        },

        placeOrder: function (data, event) {
            let self = this;
            if (event) {
                event.preventDefault();
            }
            if (this.validate() && additionalValidators.validate()) {
                this.isPlaceOrderActionAllowed(false);
                this.getPlaceOrderDeferredObject()
                    .fail(function () {
                        self.isPlaceOrderActionAllowed(true);
                    })
                    .done(function () {
                        self.afterPlaceOrder();
                    })
                    .always(function () {
                        self.isPlaceOrderActionAllowed(true);
                    });
                return true;
            }
            return false;
        },
        getPlaceOrderDeferredObject: function () {
            $('button.checkout').attr('disabled', 'disabled');
            return $.when(
                placeOrderAction(this.getData(), this.messageContainer)
            );
        },
    });
});
