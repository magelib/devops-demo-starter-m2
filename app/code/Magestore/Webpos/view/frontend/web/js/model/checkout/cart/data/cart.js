/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define([
    'ko',
    'Magestore_Webpos/js/helper/general'
], function (ko, Helper) {
    'use strict';
    return {
        KEY: {
            QUOTE_INIT:'quote_init',
            ITEMS:'items',
            SHIPPING:'shipping',
            PAYMENT:'payment',
            TOTALS:'totals',
            QUOTE_ID:"quote_id",
            TILL_ID:"till_id",
            CURRENCY_ID:"currency_id",
            CUSTOMER_ID:"customer_id",
            CUSTOMER_DATA:"customer_data",
            BILLING_ADDRESS:"billing_address",
            SHIPPING_ADDRESS:"shipping_address",
            STORE_ID:"store_id",
            STORE:"store",
        },
        DATA:{
            STATUS: {
                SUCCESS: '1',
                ERROR: '0'
            }
        },
        PAGE:{
            CART:"cart",
            CHECKOUT:"checkout"
        },
        apply_tax_after_discount: (Helper.getBrowserConfig('tax/calculation/apply_after_discount') == 1)?true:false,
        items: ko.observableArray(),
        totals: ko.observableArray(),
        extraTotals: ko.observableArray(),
        quoteTotals: ko.observableArray(),
        hasErrors: ko.observable(false),
        errorMessages: ko.observable(),
        getItem: function(itemId){
            var self = this;
            var item = false;
            var foundItem = ko.utils.arrayFirst(self.items(), function(item) {
                return (item.item_id() == itemId);
            });
            if(foundItem){
                item = foundItem;
            }
            return item;
        },
        getMaxDiscountAmount: function(taxAfterDiscount){
            var self = this;
            var max = 0;
            var appliedDiscount = 0;
            if(self.items().length > 0){
                taxAfterDiscount = (typeof taxAfterDiscount != undefined)?taxAfterDiscount:self.apply_tax_after_discount;
                ko.utils.arrayForEach(self.items(), function (item) {
                    max += (taxAfterDiscount == false)?(item.base_row_total() + item.base_tax_amount()):item.base_row_total();
                });
            }
            max -= appliedDiscount;
            return max;
        },
        getMaxItemDiscountAmount: function(item_id, taxAfterDiscount){
            var self = this;
            var max = 0;
            var item = self.getItem(item_id);
            if(item !== false){
                taxAfterDiscount = (typeof taxAfterDiscount != undefined)?taxAfterDiscount:self.apply_tax_after_discount;
                max = (taxAfterDiscount == false)?(item.base_row_total() + item.base_tax_amount()):item.base_row_total();
            }
            return max;
        }
    };
});