
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        let type = 'powerpay';
        if(window.checkoutConfig.payment[type].active) {
            rendererList.push(
                {
                    type: type,
                    component: 'Improntus_PowerPay/js/view/payment/method-renderer/method'
                }
            );
        }
        return Component.extend({});
    }
);
