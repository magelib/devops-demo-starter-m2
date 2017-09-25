/*
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Customercredit
 * @copyright   Copyright (c) 2017 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */
define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magestore_Customercredit/js/model/creditdiscount',
        'Magestore_Customercredit/js/action/set-credit-amount',
        'Magestore_Customercredit/js/action/cancel-credit-amount'
    ],
    function ($, ko, Component, quote, creditdata, setCreditAmountAction, cancelCreditAmountAction) {
        'use strict';
        var totals = quote.getTotals();
        var creditdata = window.customercreditInfo;
        var amount = ko.observable(null);
        var isApplied = ko.observable(false);
        if (creditdata.credit_amount != null) {
            amount(creditdata.credit_amount);
            isApplied(true);
        }
        var isLoading = ko.observable(false);
        return Component.extend({
            defaults: {
                template: 'Magestore_Customercredit/payment/creditdiscount'
            },
            amount: amount,
            /**
             * Applied flag
             */
            isApplied: isApplied,
            isLoading: isLoading,
            creditdata: creditdata,
            /**
             * Coupon code application procedure
             */
            apply: function () {
                if (this.validate()) {
                    isLoading(true);
                    setCreditAmountAction(amount, isApplied, isLoading, creditdata);
                }
            },
            /**
             * Cancel using coupon
             */
            cancel: function () {
                if (this.validate()) {
                    isLoading(true);
                    cancelCreditAmountAction(isApplied, isLoading);
                }
            },
            /**
             * Coupon form validation
             *
             * @returns {boolean}
             */
            validate: function () {
                var form = '#discount-credit-form';
                return $(form).validation() && $(form).validation('isValid');
            },

            isLoggedin: function () {
                return creditdata.is_logged_in;
            },

            isEnable: function () {
                if(creditdata.isEnable == 1){
                    return true;
                }else{
                    return false;
                }
            },

            loginLink: function(){
                return creditdata.login_link;
            },

            isInGroup: function () {
                return creditdata.in_group_credit;
            },

            isHasCreditItem: function () {
                return creditdata.has_credit_item;
            },

            isCreditItemOnly: function () {
                return creditdata.credit_item_only;
            },

            creditBalance: function () {
                return creditdata.credit_balance;
            }
        });
    }
);
