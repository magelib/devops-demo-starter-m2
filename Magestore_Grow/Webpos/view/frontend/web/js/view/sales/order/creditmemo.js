/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
    [
        'jquery',
        'ko',
        'Magestore_Webpos/js/model/sales/order-factory',
        'mage/translate',
        'Magestore_Webpos/js/view/sales/order/action',
        'Magestore_Webpos/js/model/sales/order/creditmemo',
        'Magestore_Webpos/js/model/event-manager',
        'Magestore_Webpos/js/action/sales/order/creditmemo/create',
        'Magento_Ui/js/modal/confirm',
        'Magestore_Webpos/js/helper/general',
        
    ],
    function ($, ko, OrderFactory, $t, Component, creditmemo, eventmanager, createCreditmemoAction, Confirm, Helper) {
        "use strict";

        return Component.extend({
            isVisible: ko.observable(false),
            classIn: ko.observable(''),
            stypeDisplay: ko.observable('none'),
            formId: 'creditmemo-popup-form',
            submitArray: [],
            defaults: {
                template: 'Magestore_Webpos/sales/order/creditmemo',
            },
            creditAmount: ko.observable(0),
            maxCreditAmount: ko.observable(0),
            giftcardAmount: ko.observable(0),
            maxGiftcardAmount: ko.observable(0),
            initialize: function () {
                var self = this;
                this._super();
                this.shippingRefunded = ko.pureComputed(function(){
                    if(!self.orderData().base_shipping_refunded)
                        return self.currencyConvert(self.orderData().base_shipping_amount);
                    else if(self.orderData().base_shipping_amount - self.orderData().base_shipping_refunded > 0)
                        return self.currencyConvert(self.orderData().base_shipping_amount - self.orderData().base_shipping_refunded);
                    return 0;
                });
                eventmanager.observer('sales_order_creditmemo_afterSave', function(event, data){
                    if(data.response && data.response.entity_id>0){
                        var deferedSave = $.Deferred();
                        OrderFactory.get().setData(data.response).setMode('offline').save(deferedSave);
                        self.parentView().updateOrderListData(data.response);
                    }
                });
                if(Helper.isStoreCreditEnable()) {
                    self.isVisible.subscribe(function (value) {
                        self.validateAdditionalAmount();
                        if (value && self.orderData()) {
                            var baseTotalRefunded = (self.orderData().base_total_refunded)?parseFloat(self.orderData().base_total_refunded):0;
                            var baseCreditDiscount = (self.orderData().base_customercredit_discount)?parseFloat(self.orderData().base_customercredit_discount):0;
                            var creditDiscount = parseFloat(Helper.convertPrice(baseCreditDiscount - baseTotalRefunded));
                            creditDiscount = (creditDiscount > 0)?creditDiscount:0;
                            creditDiscount = Helper.correctPrice(creditDiscount);
                            if(self.maxCreditAmount() && creditDiscount > self.maxCreditAmount()){
                                creditDiscount = self.maxCreditAmount();
                            }
                            self.creditAmount(creditDiscount);
                            self.orderData().credit_amount_to_refund = creditDiscount;
                        }else{
                            self.creditAmount(0);
                        }

                    });
                    self.maxCreditAmount.subscribe(function (value) {
                        value = Helper.correctPrice(value);
                        if(value > self.creditAmount()){
                            self.creditAmount(value);
                        }
                    });
                }
                if(Helper.isGiftCardEnable()) {
                    self.isVisible.subscribe(function (value) {
                        self.validateAdditionalAmount();
                        if (value && self.orderData()) {
                            var baseGiftcardDiscount = self.orderData().base_gift_voucher_discount;
                            var giftcardDiscount = parseFloat(Helper.convertPrice(baseGiftcardDiscount));
                            self.giftcardAmount(-giftcardDiscount);
                            self.orderData().giftcard_amount_to_refund = -giftcardDiscount;
                        }else{
                            self.giftcardAmount(0);
                        }
                    });
                    self.maxGiftcardAmount.subscribe(function (value) {
                        self.giftcardAmount(value);
                    });
                }

                self.canRefundGiftcard = ko.pureComputed(function(){
                    var moduleEnable = Helper.isGiftCardEnable();
                    var orderUsedVoucher = false;
                    if (self.orderData()) {
                        orderUsedVoucher = (self.orderData().base_gift_voucher_discount < 0)?true:false;
                    }
                    return (moduleEnable && orderUsedVoucher);
                });
            },

            validateQty: function(data, event){
                var qty = $(event.currentTarget).val();
                var maxQty = data.qty_invoiced - data.qty_refunded;
                if(qty == '' || isNaN(qty) || parseInt(qty) !== parseFloat(qty) || parseFloat(qty)<0 || parseFloat(qty)>maxQty){
                    qty = maxQty;
                }
                $(event.currentTarget).val(qty);
                this.validateAdditionalAmount();
            },

            submit: function(data, event){
                event.target.disabled = true;
                var self = this;
                Confirm({
                    content: $t('Are you sure you want to refund this order?'),
                    actions: {
                        confirm: function (confirmEvent) {
                            confirmEvent.target.disabled = true;
                            self.submitArray = $('#' + self.formId).serializeArray();
                            createCreditmemoAction.execute(self.submitArray, self.orderData(), $.Deferred(), self);
                        },
                        always: function (confirmEvent) {
                            event.target.disabled = false;
                            confirmEvent.stopImmediatePropagation();
                        }
                    }
                });
            },

            canRefundByStorecredit: function(){
                var moduleEnable = Helper.isStoreCreditEnable();
                return (moduleEnable);
            },
            saveCreditAmount: function(data, event){
                var amount = Helper.toBasePrice(Helper.toNumber(event.target.value));
                var maxCreditAmount = this.maxCreditAmount();
                var maxCreditAmountFormated = Helper.convertAndFormatPrice(maxCreditAmount);
                if(amount > maxCreditAmount){
                    amount = maxCreditAmount;
                    Helper.alert('warning', 'Message', 'Maximum credit amount to refund is '+maxCreditAmountFormated);
                }
                this.orderData().credit_amount_to_refund = amount;
                amount = Helper.convertAndFormatWithoutSymbol(amount);
                this.creditAmount(amount);
            },
            saveGiftcardAmount: function(data, event){
                var amount = Helper.toBasePrice(Helper.toNumber(event.target.value));
                amount = (amount > 0)?amount:0;
                var maxGiftcardAmount = this.maxGiftcardAmount();
                var giftcardAmount = Helper.convertAndFormatPrice(maxGiftcardAmount);
                if(amount > maxGiftcardAmount){
                    amount = maxGiftcardAmount;
                    Helper.alert('warning', 'Message', 'Maximum gift voucher amount to refund is '+giftcardAmount);
                }
                this.orderData().giftcard_amount_to_refund = amount;
                amount = Helper.convertAndFormatWithoutSymbol(amount);
                this.giftcardAmount(amount);
            },
            validateAdditionalAmount: function(){
                var self = this;
                var inputs = $("input[name^='items'].refund-input-qty");
                if(inputs.length > 0){
                    var maxCredit = 0
                    var maxGiftcard = 0;
                    var items = this.orderData().items;
                    $.each(inputs, function(){
                        var qty = this.value;
                        var itemid = this.getAttribute('itemid');
                        if(itemid){
                            var item = self.getOrderItemById(itemid);
                            if(item){
                                var credit = item.base_price_incl_tax * item.qty_ordered  - item.base_discount_amount;
                                // if(window.webposConfig['tax/calculation/price_includes_tax'] == 0){
                                //     credit += item.base_tax_amount;
                                // }
                                if (item.base_gift_voucher_discount) {
                                    credit -= item.base_gift_voucher_discount;
                                }
                                if (item.rewardpoints_base_discount) {
                                    credit -= item.rewardpoints_base_discount;
                                }
                                credit = (qty / item.qty_ordered) * credit;
                                maxCredit += credit;

                                if(Helper.isGiftCardEnable() && item.base_gift_voucher_discount){
                                    var giftcard = (qty/item.qty_ordered)*item.base_gift_voucher_discount;
                                    maxGiftcard += giftcard;
                                }
                            }
                        }
                    });
                    if(Helper.isStoreCreditEnable()) {
                        maxCredit = Helper.convertPrice(maxCredit);
                        self.maxCreditAmount(Helper.correctPrice(maxCredit));
                    }
                    if(Helper.isGiftCardEnable()) {
                        maxGiftcard = Helper.convertPrice(maxGiftcard);
                        self.maxGiftcardAmount(Helper.correctPrice(maxGiftcard));
                    }
                }else{
                    if(Helper.isStoreCreditEnable()) {
                        var maxAmount = (self.orderData())?self.orderData().base_customercredit_discount:0;
                        maxAmount = (maxAmount)?Helper.correctPrice(Helper.convertPrice(maxAmount)):0;
                        self.maxCreditAmount(maxAmount);
                    }
                    if(Helper.isGiftCardEnable()) {
                        var maxAmount = (self.orderData())?self.orderData().base_gift_voucher_discount:0;
                        maxAmount = (maxAmount)?Helper.correctPrice(Helper.convertPrice(maxAmount)):0;
                        self.maxGiftcardAmount(maxAmount);
                    }
                }
            },
            getOrderItemById: function(itemId){
                var items = this.orderData().items;
                if(itemId && items.length > 0) {
                    var itemFound = ko.utils.arrayFirst(items, function (item) {
                        return item.item_id == itemId;
                    });
                    return (itemFound)?itemFound:false;
                }
                return false
            }
        });
    }
);