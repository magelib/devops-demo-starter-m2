/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
    [
        'jquery',
        'ko',
        'Magestore_Webpos/js/model/abstract',
        'Magestore_Webpos/js/model/checkout/cart/discountpopup',
        'Magestore_Webpos/js/helper/general'
    ],
    function ($, ko, modelAbstract, DiscountModel, Helper) {
        "use strict";
        return modelAbstract.extend({
            initialize: function () {
                this._super();
                this.CUSTOM_PRICE_CODE = "price";
                this.CUSTOM_DISCOUNT_CODE = "discount";
                this.FIXED_AMOUNT_CODE = "$";
                this.PERCENTAGE_CODE = "%";
                this.APPLY_TAX_ON_CUSTOMPRICE = "0";
                this.APPLY_TAX_ON_ORIGINALPRICE = "1";
                this.itemFields = [
                    'product_id', 'product_name', 'item_id', 'tier_price', 'maximum_qty', 'minimum_qty', 'qty_increment',
                    'qty', 'unit_price', 'has_custom_price', 'custom_type', 'custom_price_type', 'custom_price_amount', 'image_url',
                    'super_attribute', 'super_group', 'options', 'bundle_option', 'bundle_option_qty', 'is_out_of_stock', 'row_total',
                    'tax_class_id', 'is_virtual', 'qty_to_ship', 'tax_amount', 'tax_amount_before_discount', 'tax_rates', 'tax_origin_rates', 'item_base_discount_amount',
                    'item_discount_amount', 'item_base_credit_amount', 'item_credit_amount', 'applied_catalog_rules', 'base_original_price', 'is_salable',
                    'online_tax_amount', 'online_base_tax_amount', 'has_error', 'saved_online_item', 'is_qty_decimal'
                ];

                /* S: Define the init fields - use to get data for item object */
                this.initFields = [
                    'product_id', 'product_name', 'item_id', 'tier_price', 'maximum_qty', 'minimum_qty', 'qty_increment',
                    'qty', 'unit_price', 'has_custom_price', 'custom_type', 'custom_price_type', 'custom_price_amount', 'image_url',
                    'super_attribute', 'super_group', 'options', 'bundle_option', 'bundle_option_qty', 'is_out_of_stock',
                    'tax_class_id', 'is_virtual', 'qty_to_ship', 'tax_rates', 'tax_origin_rates', 'sku', 'product_type', 'child_id',
                    'options_label', 'stocks', 'stock', 'id', 'type_id', 'bundle_childs_qty', 'item_base_discount_amount',
                    'item_discount_amount', 'applied_catalog_rules', 'base_original_price', 'is_salable',
                    'online_tax_amount', 'online_base_tax_amount', 'has_error', 'saved_online_item', 'is_qty_decimal'
                ];
                if (Helper.isStoreCreditEnable()) {
                    this.initFields.push('item_credit_amount');
                    this.initFields.push('item_base_credit_amount');
                }
                if (Helper.isRewardPointsEnable()) {
                    this.initFields.push('item_point_earn');
                    this.initFields.push('item_point_spent');
                    this.initFields.push('item_point_discount');
                    this.initFields.push('item_base_point_discount');
                }
                if (Helper.isGiftCardEnable()) {
                    this.initFields.push('item_giftcard_discount');
                    this.initFields.push('item_base_giftcard_discount');
                }
                /* E: Define the init fields */
            },
            init: function (data) {
                var self = this;
                self.id = (typeof data.id != "undefined") ? ko.observable(data.id) : ko.observable();
                self.product_id = (typeof data.product_id != "undefined") ? ko.observable(data.product_id) : ko.observable();
                self.product_name = (typeof data.product_name != "undefined") ? ko.observable(data.product_name) : ko.observable();
                self.type_id = (typeof data.type_id != "undefined") ? ko.observable(data.type_id) : ko.observable();
                self.is_salable = (typeof data.is_salable != "undefined") ? ko.observable(data.is_salable) : ko.observable();

                self.item_id = (typeof data.item_id != "undefined") ? ko.observable(data.item_id) : ko.observable();
                self.applied_catalog_rules = (typeof data.applied_catalog_rules != "undefined") ? ko.observable(data.applied_catalog_rules) : ko.observable(false);
                self.base_original_price = (typeof data.base_original_price != "undefined") ? ko.observable(data.base_original_price) : ko.observable();
                self.tier_prices = (typeof data.tier_prices != "undefined") ? ko.observable(data.tier_prices) : ko.observable();
                self.maximum_qty = (typeof data.maximum_qty != "undefined") ? ko.observable(data.maximum_qty) : ko.observable();
                self.minimum_qty = (typeof data.minimum_qty != "undefined") ? ko.observable(data.minimum_qty) : ko.observable();
                self.qty_increment = (typeof data.qty_increment != "undefined") ? ko.observable(data.qty_increment) : ko.observable(1);
                self.is_qty_decimal = (typeof data.is_qty_decimal != "undefined") ? ko.observable(data.is_qty_decimal) : ko.observable(false);
                self.qty = (typeof data.qty != "undefined") ? ko.observable(data.qty) : ko.observable();
                self.qty_to_ship = (typeof data.qty_to_ship != "undefined") ? ko.observable(data.qty_to_ship) : ko.observable(0);
                self.unit_price = (typeof data.unit_price != "undefined") ? ko.observable(data.unit_price) : ko.observable(0);
                self.has_custom_price = (typeof data.has_custom_price != "undefined") ? ko.observable(data.has_custom_price) : ko.observable(false);
                self.custom_type = (typeof data.custom_type != "undefined") ? ko.observable(data.custom_type) : ko.observable();
                self.custom_price_type = (typeof data.custom_price_type != "undefined") ? ko.observable(data.custom_price_type) : ko.observable();
                self.custom_price_amount = (typeof data.custom_price_amount != "undefined") ? ko.observable(data.custom_price_amount) : ko.observable();
                self.image_url = (typeof data.image_url != "undefined") ? ko.observable(data.image_url) : ko.observable();
                self.super_attribute = (typeof data.super_attribute != "undefined") ? ko.observable(data.super_attribute) : ko.observable();
                self.super_group = (typeof data.super_group != "undefined") ? ko.observable(data.super_group) : ko.observable();
                self.options = (typeof data.options != "undefined") ? ko.observable(data.options) : ko.observable();
                self.bundle_option = (typeof data.bundle_option != "undefined") ? ko.observable(data.bundle_option) : ko.observable();
                self.bundle_option_qty = (typeof data.bundle_option_qty != "undefined") ? ko.observable(data.bundle_option_qty) : ko.observable();
                self.is_out_of_stock = (typeof data.is_out_of_stock != "undefined") ? ko.observable(data.is_out_of_stock) : ko.observable(false);
                self.tax_class_id = (typeof data.tax_class_id != "undefined") ? ko.observable(data.tax_class_id) : ko.observable();
                self.is_virtual = (typeof data.is_virtual != "undefined") ? ko.observable(data.is_virtual) : ko.observable(false);
                self.tax_rates = (typeof data.tax_rates != "undefined") ? ko.observable(data.tax_rates) : ko.observable([]);
                self.tax_origin_rates = (typeof data.tax_origin_rates != "undefined") ? ko.observable(data.tax_origin_rates) : ko.observable([]);

                self.sku = (typeof data.sku != "undefined") ? ko.observable(data.sku) : ko.observable();
                self.product_type = (typeof data.product_type != "undefined") ? ko.observable(data.product_type) : ko.observable();
                self.child_id = (typeof data.child_id != "undefined") ? ko.observable(data.child_id) : ko.observable();
                self.options_label = (typeof data.options_label != "undefined") ? ko.observable(data.options_label) : ko.observable();
                self.tier_price = (typeof data.tier_price != "undefined") ? ko.observable(data.tier_price) : ko.observable();
                self.stock = (typeof data.stock != "undefined") ? ko.observable(data.stock) : ko.observable();
                self.stocks = (typeof data.stocks != "undefined") ? ko.observable(data.stocks) : ko.observable();
                self.bundle_childs_qty = (typeof data.bundle_childs_qty != "undefined") ? ko.observable(data.bundle_childs_qty) : ko.observable();
                self.item_discount_amount = (typeof data.item_discount_amount != "undefined") ? ko.observable(data.item_discount_amount) : ko.observable();
                self.item_base_discount_amount = (typeof data.item_base_discount_amount != "undefined") ? ko.observable(data.item_base_discount_amount) : ko.observable();

                self.online_tax_amount = (typeof data.online_tax_amount != "undefined") ? ko.observable(data.online_tax_amount) : ko.observable();
                self.online_base_tax_amount = (typeof data.online_base_tax_amount != "undefined") ? ko.observable(data.online_base_tax_amount) : ko.observable();
                self.has_error = (typeof data.has_error != "undefined") ? ko.observable(data.has_error) : ko.observable(false);
                self.saved_online_item = (typeof data.saved_online_item != "undefined") ? ko.observable(data.saved_online_item) : ko.observable(false);

                /* S: Integration custom discount per item - define variale to store the data */
                if (Helper.isStoreCreditEnable()) {
                    self.credit_price_amount = (typeof data.credit_price_amount != "undefined") ? ko.observable(data.credit_price_amount) : ko.observable();
                    self.amount = (typeof data.amount != "undefined") ? ko.observable(data.amount) : ko.observable();
                    self.item_credit_amount = (typeof data.item_credit_amount != "undefined") ? ko.observable(data.item_credit_amount) : ko.observable();
                    self.item_base_credit_amount = (typeof data.item_base_credit_amount != "undefined") ? ko.observable(data.item_base_credit_amount) : ko.observable();
                }
                if (Helper.isRewardPointsEnable()) {
                    self.item_point_earn = (typeof data.item_point_earn != "undefined") ? ko.observable(data.item_point_earn) : ko.observable();
                    self.item_point_spent = (typeof data.item_point_spent != "undefined") ? ko.observable(data.item_point_spent) : ko.observable();
                    self.item_point_discount = (typeof data.item_point_discount != "undefined") ? ko.observable(data.item_point_discount) : ko.observable();
                    self.item_base_point_discount = (typeof data.item_base_point_discount != "undefined") ? ko.observable(data.item_base_point_discount) : ko.observable();
                }
                if (Helper.isGiftCardEnable()) {
                    self.item_giftcard_discount = (typeof data.item_giftcard_discount != "undefined") ? ko.observable(data.item_giftcard_discount) : ko.observable();
                    self.item_base_giftcard_discount = (typeof data.item_base_giftcard_discount != "undefined") ? ko.observable(data.item_base_giftcard_discount) : ko.observable();
                }
                /* E: Integration custom discount per item */

                if (self.maximum_qty() && self.qty() > self.maximum_qty()) {
                    self.qty(Helper.toNumber(self.maximum_qty()));
                    Helper.alert({
                        priority: "warning",
                        title: "Warning",
                        message: self.product_name() + Helper.__(" has maximum quantity allow in cart is ") + Helper.toNumber(self.maximum_qty())
                    });
                }

                if (self.minimum_qty() && self.qty() < self.minimum_qty()) {
                    self.qty(Helper.toNumber(self.minimum_qty()));
                    Helper.alert({
                        priority: "warning",
                        title: "Warning",
                        message: self.product_name() + Helper.__(" has minimum quantity allow in cart is ") + Helper.toNumber(self.minimum_qty())
                    });
                }
                if (!self.item_price) {
                    self.item_price = ko.pureComputed(function () {
                        var itemPrice = self.item_price_origin();
                        if (Helper.isProductPriceIncludesTax()) {
                            var taxRates = (!Helper.isEnableCrossBorderTrade()) ? self.tax_origin_rates() : self.tax_rates();
                            var taxRates = self.tax_origin_rates();
                            if (taxRates && taxRates.length > 0) {
                                $.each(taxRates, function (index, rate) {
                                    itemPrice = itemPrice / (1 + rate / 100);
                                });
                            }
                        }
                        return Helper.correctPrice(itemPrice);
                    });
                }

                if (!self.base_item_price) {
                    self.base_item_price = ko.pureComputed(function () {
                        var itemPrice = self.base_item_price_origin();
                        if (Helper.isProductPriceIncludesTax()) {
                            var taxRates = (!Helper.isEnableCrossBorderTrade()) ? self.tax_origin_rates() : self.tax_rates();
                            if (taxRates && taxRates.length > 0) {
                                $.each(taxRates, function (index, rate) {
                                    itemPrice = itemPrice / (1 + rate / 100);
                                });
                            }
                        }
                        return Helper.correctPrice(itemPrice);
                    });
                }
                if (!self.item_price_origin) {
                    self.item_price_origin = ko.pureComputed(function () {
                        var itemPrice = (self.tier_price() && (self.tier_price() < self.unit_price())) ? self.tier_price() : self.unit_price();
                        var unitPrice = itemPrice;
                        var discountPercentage = 0;
                        var maximumPercent = Helper.toNumber(DiscountModel.maximumPercent());
                        var customAmount = (self.custom_price_type() == self.FIXED_AMOUNT_CODE) ? Helper.toBasePrice(self.custom_price_amount()) : self.custom_price_amount();
                        var validAmount = customAmount;
                        if (self.has_custom_price() == true && customAmount >= 0 && self.custom_price_type()) {
                            if (self.custom_type() == self.CUSTOM_PRICE_CODE) {
                                itemPrice = (self.custom_price_type() == self.FIXED_AMOUNT_CODE) ?
                                    customAmount :
                                    (customAmount * unitPrice / 100);
                                if (self.custom_price_type() == self.FIXED_AMOUNT_CODE) {
                                    discountPercentage = (100 - itemPrice / unitPrice * 100);
                                } else {
                                    discountPercentage = customAmount;
                                }
                                if (maximumPercent && discountPercentage > maximumPercent) {
                                    if (self.custom_price_type() == self.FIXED_AMOUNT_CODE) {
                                        validAmount = unitPrice - unitPrice * maximumPercent / 100;
                                    }
                                }
                            } else {
                                if (self.custom_type() == self.CUSTOM_DISCOUNT_CODE) {
                                    itemPrice = (self.custom_price_type() == self.FIXED_AMOUNT_CODE) ?
                                        (unitPrice - customAmount) :
                                        (unitPrice - customAmount * unitPrice / 100);
                                    if (self.custom_price_type() == self.FIXED_AMOUNT_CODE) {
                                        discountPercentage = (customAmount / unitPrice * 100);
                                    } else {
                                        discountPercentage = customAmount;
                                    }
                                }
                                if (maximumPercent && discountPercentage > maximumPercent) {
                                    if (self.custom_price_type() == self.FIXED_AMOUNT_CODE) {
                                        validAmount = unitPrice * maximumPercent / 100;
                                    }
                                }
                            }
                        }
                        if (maximumPercent && discountPercentage > maximumPercent) {
                            if (self.custom_price_type() == self.PERCENTAGE_CODE) {
                                self.custom_price_amount(maximumPercent);
                            } else {
                                self.custom_price_amount(Helper.convertPrice(validAmount));
                            }
                            if(self.custom_type() == self.CUSTOM_DISCOUNT_CODE){
                                itemPrice = unitPrice - unitPrice * maximumPercent / 100;
                                Helper.alert({
                                    priority: "warning",
                                    title: "Warning",
                                    message: Helper.__(" You are able to apply discount under ") + maximumPercent + "% " + Helper.__("only")
                                });
                            }else{
                                itemPrice = unitPrice * maximumPercent / 100;
                            }
                        }
                        itemPrice = (itemPrice > 0) ? itemPrice : 0;
                        if (window.webposConfig.currentCurrencyCode != window.webposConfig.baseCurrencyCode) {
                            if (self.has_custom_price() == true) {
                                itemPrice = Helper.roundPrice(itemPrice);
                                itemPrice = Helper.correctPrice(Helper.convertPrice(itemPrice));
                            } else {
                                itemPrice = Helper.roundPrice(itemPrice);
                                itemPrice = Helper.roundPrice(Helper.convertPrice(itemPrice));
                            }
                        }
                        return itemPrice;
                    });
                }

                if (!self.base_item_price_origin) {
                    self.base_item_price_origin = ko.pureComputed(function () {
                        var itemPrice = (self.tier_price() && (self.tier_price() < self.unit_price())) ? self.tier_price() : self.unit_price();
                        var unitPrice = itemPrice;
                        var discountPercentage = 0;
                        var maximumPercent = Helper.toNumber(DiscountModel.maximumPercent());
                        var customAmount = (self.custom_price_type() == self.FIXED_AMOUNT_CODE) ? Helper.toBasePrice(self.custom_price_amount()) : self.custom_price_amount();
                        var validAmount = customAmount;
                        if (self.has_custom_price() == true && customAmount >= 0 && self.custom_price_type()) {
                            if (self.custom_type() == self.CUSTOM_PRICE_CODE) {
                                itemPrice = (self.custom_price_type() == self.FIXED_AMOUNT_CODE) ?
                                    customAmount :
                                    (customAmount * unitPrice / 100);
                                if (self.custom_price_type() == self.FIXED_AMOUNT_CODE) {
                                    discountPercentage = (100 - itemPrice / unitPrice * 100);
                                } else {
                                    discountPercentage = customAmount;
                                }
                                if (maximumPercent && discountPercentage > maximumPercent) {
                                    if (self.custom_price_type() == self.FIXED_AMOUNT_CODE) {
                                        validAmount = unitPrice - unitPrice * maximumPercent / 100;
                                    }
                                }
                            } else {
                                if (self.custom_type() == self.CUSTOM_DISCOUNT_CODE) {
                                    itemPrice = (self.custom_price_type() == self.FIXED_AMOUNT_CODE) ?
                                        (unitPrice - customAmount) :
                                        (unitPrice - customAmount * unitPrice / 100);
                                    if (self.custom_price_type() == self.FIXED_AMOUNT_CODE) {
                                        discountPercentage = (customAmount / unitPrice * 100);
                                    } else {
                                        discountPercentage = customAmount;
                                    }
                                }
                                if (maximumPercent && discountPercentage > maximumPercent) {
                                    if (self.custom_price_type() == self.FIXED_AMOUNT_CODE) {
                                        validAmount = unitPrice * maximumPercent / 100;
                                    }
                                }
                            }
                        }
                        if (maximumPercent && discountPercentage > maximumPercent) {
                            if (self.custom_price_type() == self.PERCENTAGE_CODE) {
                                self.custom_price_amount(maximumPercent);
                            } else {
                                self.custom_price_amount(Helper.convertPrice(validAmount));
                            }
                            if(self.custom_type() == self.CUSTOM_DISCOUNT_CODE) {
                                itemPrice = unitPrice - unitPrice * maximumPercent / 100;
                                Helper.alert({
                                    priority: "warning",
                                    title: "Warning",
                                    message: Helper.__(" You are able to apply discount under ") + maximumPercent + "% " + Helper.__("only")
                                });
                            }else{
                                itemPrice = unitPrice * maximumPercent / 100;
                            }
                        }
                        itemPrice = (itemPrice > 0) ? itemPrice : 0;
                        return itemPrice;
                    });
                }
                if (!self.row_total) {
                    self.row_total = ko.pureComputed(function () {
                        var itemPrice = self.item_price();
                        if (Helper.isProductPriceIncludesTax() && (Helper.isEnableCrossBorderTrade() || self.isUseOriginalTax())) {
                            itemPrice = self.item_price_origin() - (self.tax_amount_before_discount() / self.qty());
                        }
                        var rowTotal = self.qty() * itemPrice;
                        return Helper.correctPrice(rowTotal);
                    });
                }
                if (!self.base_row_total) {
                    self.base_row_total = ko.pureComputed(function () {
                        var itemBasePrice = self.base_item_price();
                        if (Helper.isProductPriceIncludesTax() && (Helper.isEnableCrossBorderTrade() || self.isUseOriginalTax())) {
                            itemBasePrice = self.base_item_price_origin() - (self.base_tax_amount_before_discount() / self.qty());
                        }
                        var rowTotal = self.qty() * itemBasePrice;
                        return Helper.correctPrice(rowTotal);
                    });
                }
                if (!self.tax_amount) {
                    self.tax_amount = ko.pureComputed(function () {
                        if(Helper.isUseOnline('checkout') && Helper.getOnlineConfig('quote_id') && Helper.isOnCheckoutPage()){
                            return self.online_tax_amount();
                        }
                        var tax = self.tax_amount_before_discount();
                        /* temporary disable this functionality, because magento core is having a bug in here, currently they don't check this setting when creating order from backend also.
                         * ------------- *
                         var apply_tax_on = window.webposConfig['tax/calculation/apply_tax_on'];
                         if(apply_tax_on == self.APPLY_TAX_ON_ORIGINALPRICE){
                         total = self.row_total_without_discount();
                         }
                         * ------------- *
                         */

                        var discountItem = 0;
                        var apply_after_discount = window.webposConfig['tax/calculation/apply_after_discount'];
                        if (apply_after_discount == 1 && self.item_discount_amount() > 0) {
                            discountItem += self.item_discount_amount();
                        }

                        /* S: Integration custom discount per item - recalculate tax - tax after discount */
                        var allConfig = Helper.getBrowserConfig('plugins_config');
                        if (Helper.isStoreCreditEnable() && allConfig['os_store_credit']) {
                            var configs = allConfig['os_store_credit'];
                            if (configs['customercredit/spend/tax'] && configs['customercredit/spend/tax'] == '0') {
                                if (self.item_credit_amount() > 0) {
                                    discountItem += self.item_credit_amount();
                                }
                            }
                        }
                        if (Helper.isRewardPointsEnable() && apply_after_discount == 1) {
                            if (self.item_point_discount() > 0) {
                                discountItem += self.item_point_discount();
                            }
                        }
                        if (Helper.isGiftCardEnable() && allConfig['os_gift_card']) {
                            var configs = allConfig['os_gift_card'];
                            if (configs['giftvoucher/general/apply_after_tax'] == '0') {
                                if (self.item_giftcard_discount() > 0) {
                                    discountItem += self.item_giftcard_discount();
                                }
                            }

                        }
                        /* E: Integration custom discount per item */
                        if (discountItem > 0 && apply_after_discount == 1) {
                            if (Helper.isProductPriceIncludesTax()) {
                                tax = tax - discountItem / (self.row_total() + tax) * tax;
                            } else {
                                tax = tax - discountItem / self.row_total() * tax;
                            }
                        }
                        return Helper.correctPrice(tax);
                    });
                }
                if (!self.base_tax_amount) {
                    self.base_tax_amount = ko.pureComputed(function () {
                        if(Helper.isUseOnline('checkout') && Helper.getOnlineConfig('quote_id') && Helper.isOnCheckoutPage()){
                            return self.online_base_tax_amount();
                        }
                        var tax = self.base_tax_amount_before_discount();

                        /* temporary disable this functionality, because magento core is having a bug in here, currently they don't check this setting when creating order from backend also.
                         * ------------- *
                         var apply_tax_on = window.webposConfig['tax/calculation/apply_tax_on'];
                         if(apply_tax_on == self.APPLY_TAX_ON_ORIGINALPRICE){
                         total = self.row_total_without_discount();
                         }
                         * ------------- *
                         */

                        var discountItem = 0;
                        var apply_after_discount = window.webposConfig['tax/calculation/apply_after_discount'];
                        if (apply_after_discount == 1 && self.item_base_discount_amount() > 0) {
                            discountItem += self.item_base_discount_amount();
                        }

                        /* S: Integration custom discount per item - recalculate tax - tax after discount */
                        var allConfig = Helper.getBrowserConfig('plugins_config');
                        if (Helper.isStoreCreditEnable() && allConfig['os_store_credit']) {
                            var configs = allConfig['os_store_credit'];
                            if (configs['customercredit/spend/tax'] && configs['customercredit/spend/tax'] == '0') {
                                if (self.item_base_credit_amount() > 0) {
                                    discountItem += self.item_base_credit_amount();
                                }
                            }
                        }
                        if (Helper.isRewardPointsEnable() && apply_after_discount == 1) {
                            if (self.item_base_point_discount() > 0) {
                                discountItem += self.item_base_point_discount();
                            }
                        }
                        if (Helper.isGiftCardEnable() && allConfig['os_gift_card']) {
                            var configs = allConfig['os_gift_card'];
                            if (configs['giftvoucher/general/apply_after_tax'] == '0') {
                                if (self.item_base_giftcard_discount() > 0) {
                                    discountItem += self.item_base_giftcard_discount();
                                }
                            }

                        }
                        /* E: Integration custom discount per item */
                        if (discountItem > 0 && apply_after_discount == 1) {
                            if (Helper.isProductPriceIncludesTax()) {
                                tax = tax - discountItem / (self.base_row_total() + tax) * tax;
                            } else {
                                tax = tax - discountItem / self.base_row_total() * tax;
                            }
                        }
                        return Helper.correctPrice(tax);
                    });
                }
                if (!self.tax_amount_before_discount) {
                    self.tax_amount_before_discount = ko.pureComputed(function () {
                        var price = self.item_price();
                        var tax = 0;
                        var taxRates = self.tax_rates();
                        if (taxRates && taxRates.length > 0) {
                            $.each(taxRates, function (index, rate) {
                                var value = Helper.roundPrice(rate * price / 100);
                                tax += value;
                                price += value;
                            });
                        }
                        tax = Helper.correctPrice(tax);
                        tax = self.qty() * tax;
                        return Helper.correctPrice(tax);
                    });
                }
                if (!self.base_tax_amount_before_discount) {
                    self.base_tax_amount_before_discount = ko.pureComputed(function () {
                        var price = self.base_item_price();
                        var tax = 0;
                        var taxRates = self.tax_rates();
                        if (taxRates && taxRates.length > 0) {
                            $.each(taxRates, function (index, rate) {
                                var value = Helper.roundPrice(rate * price / 100);
                                tax += value;
                                price += value;
                            });
                        }
                        tax = Helper.correctPrice(tax);
                        tax = self.qty() * tax;
                        return Helper.correctPrice(tax);
                    });
                }
                if (!self.tax_amount_without_discount) {
                    self.tax_amount_without_discount = ko.pureComputed(function () {
                        var price = self.item_price();
                        var tax = 0;
                        var taxRates = self.tax_rates();
                        if (taxRates && taxRates.length > 0) {
                            $.each(taxRates, function (index, rate) {
                                tax += rate * price / 100;
                                price += tax;
                            });
                        }
                        tax = Helper.correctPrice(tax);
                        tax = self.qty() * tax;
                        return Helper.correctPrice(tax);
                    });
                }
                if (!self.base_tax_amount_without_discount) {
                    self.base_tax_amount_without_discount = ko.pureComputed(function () {
                        var price = self.base_item_price();
                        var tax = 0;
                        var taxRates = self.tax_rates();
                        if (taxRates && taxRates.length > 0) {
                            $.each(taxRates, function (index, rate) {
                                tax += rate * price / 100;
                                price += tax;
                            });
                        }
                        tax = Helper.correctPrice(tax);
                        tax = self.qty() * tax;
                        return Helper.correctPrice(tax);
                    });
                }
                if (!self.tax_amount_converted) {
                    self.tax_amount_converted = ko.pureComputed(function () {
                        return Helper.convertPrice(self.tax_amount());
                    });
                }
                if (!self.row_total_include_tax) {
                    self.row_total_include_tax = ko.pureComputed(function () {
                        var rowTotal = self.qty() * self.item_price();
                        if (!(Helper.isEnableCrossBorderTrade() || self.isUseOriginalTax())) {
                            rowTotal += self.tax_amount_without_discount();
                        } else {
                            rowTotal = self.qty() * self.item_price_origin();
                        }
                        return Helper.correctPrice(rowTotal);
                    });
                }
                if (!self.base_row_total_include_tax) {
                    self.base_row_total_include_tax = ko.pureComputed(function () {
                        var rowTotal = self.qty() * self.base_item_price();
                        if (!(Helper.isEnableCrossBorderTrade() || self.isUseOriginalTax())) {
                            rowTotal += self.base_tax_amount_without_discount();
                        } else {
                            rowTotal = self.qty() * self.base_item_price_origin();
                        }
                        return Helper.correctPrice(rowTotal);
                    });
                }
                if (!self.row_total_formated) {
                    self.row_total_formated = ko.pureComputed(function () {
                        var displayIncludeTax = Helper.isCartDisplayIncludeTax('price');
                        var rowTotal = self.row_total();
                        if (displayIncludeTax) {
                            rowTotal = self.row_total_include_tax();
                        }
                        return Helper.formatPrice(rowTotal);
                    });
                }
                if (!self.original_row_total_formated) {
                    self.original_row_total_formated = ko.pureComputed(function () {
                        var displayIncludeTax = Helper.isCartDisplayIncludeTax('price');
                        var rowTotal = self.qty() * self.unit_price();
                        if (!Helper.isEnableCrossBorderTrade() && !displayIncludeTax) {
                            var taxRates = self.tax_origin_rates();
                            if (taxRates && taxRates.length > 0) {
                                $.each(taxRates, function (index, rate) {
                                    rowTotal = rowTotal / (1 + rate / 100);
                                });
                            }
                        }
                        if(self.applied_catalog_rules() == true){
                            rowTotal = self.qty() * parseFloat(self.base_original_price());
                        }
                        return "Reg. " + Helper.convertAndFormatPrice(rowTotal);
                    });
                }
                if (!self.show_original_price) {
                    self.show_original_price = ko.pureComputed(function () {
                        return ((self.has_custom_price() == true && self.custom_price_amount() >= 0 && self.custom_price_type()) || (self.applied_catalog_rules() == true));
                    });
                }
            },
            setIndividualData:function(key, value){
                var self = this;
                if (typeof self[key] != "undefined") {
                    if (key == "qty") {
                        if (self.maximum_qty() && value > self.maximum_qty()) {
                            value = Helper.toNumber(self.maximum_qty());
                            Helper.alert({
                                priority: "warning",
                                title: "Warning",
                                message: self["product_name"]() + Helper.__(" has maximum quantity allow in cart is ") + value
                            });
                        }
                        if (self.minimum_qty() && value < self.minimum_qty()) {
                            value = Helper.toNumber(self.minimum_qty());
                            Helper.alert({
                                priority: "warning",
                                title: "Warning",
                                message: self["product_name"]() + Helper.__(" has minimum quantity allow in cart is ") + value
                            });
                        }
                    }
                    self[key](value);
                }
            },
            setData: function(key, value){
                var self = this;
                if($.type(key) == 'string') {
                    self.setIndividualData(key, value);
                }else{
                    $.each(key, function(index, val){
                        self.setIndividualData(index, val);
                    })
                }
            },
            getData: function (key) {
                var self = this;
                var data = {};
                if (typeof key != "undefined") {
                    data = self[key]();
                } else {
                    var data = {};
                    $.each(this.initFields, function () {
                        data[this] = self[this]();
                    });
                }
                return data;
            },
            getInfoBuyRequest: function () {
                var infobuy = {};
                infobuy.id = this.product_id();
                infobuy.item_id = this.item_id();
                infobuy.qty = this.qty();
                infobuy.qty_to_ship = this.qty_to_ship();
                infobuy.use_discount = Helper.isUseOnline('checkout')?1:0;

                if (this.product_id() == "customsale") {
                    infobuy.is_custom_sale = true;
                }

                if (this.has_custom_price() == true && this.custom_price_amount() >= 0) {
                    infobuy.custom_price = this.item_price_origin();
                }
                if (this.super_attribute()) {
                    infobuy.super_attribute = this.super_attribute();
                }
                if (this.options()) {
                    var options = this.options();
                    if (options && $.isArray(options) && options.length > 0) {
                        $.each(options, function (index, option) {
                            if (option.value && $.isArray(option.value)) {
                                $.each(option.value, function (index, value) {
                                    options.push({code: option.code, value: value});
                                });
                                delete options[index];
                            }
                        });
                    }
                    infobuy.options = options;
                } else {
                    if (this.product_id() == "customsale") {
                        infobuy.options = [
                            {code: "tax_class_id", value: this.tax_class_id()},
                            {code: "price", value: this.unit_price()},
                            {code: "is_virtual", value: this.is_virtual()},
                            {code: "name", value: this.product_name()}
                        ];
                    }
                }
                if (this.super_group()) {
                    infobuy.super_group = this.super_group();
                }
                if (this.bundle_option() && this.bundle_option_qty()) {
                    var bundleOptions = this.bundle_option();
                    if (bundleOptions && $.isArray(bundleOptions) && bundleOptions.length > 0) {
                        $.each(bundleOptions, function (index, option) {
                            if (option.value && $.isArray(option.value)) {
                                $.each(option.value, function (index, value) {
                                    bundleOptions.push({code: option.code, value: value});
                                });
                                delete bundleOptions[index];
                            }
                        });
                    }
                    infobuy.bundle_option = bundleOptions;
                    infobuy.bundle_option_qty = this.bundle_option_qty();
                }
                var itemPrice = this.item_price();
                var baseItemPrice = this.base_item_price();
                // if(!Helper.isEnableCrossBorderTrade()){
                //     var taxRates = this.tax_origin_rates();
                //     if(taxRates && taxRates.length > 0){
                //         $.each(taxRates, function (index, rate) {
                //             itemPrice = itemPrice / (1 + rate/100);
                //             baseItemPrice = baseItemPrice / (1 + rate/100);
                //         });
                //     }
                // }
                var rowTotalInclTax = this.row_total_include_tax();
                var baseRowTotalInclTax = this.base_row_total_include_tax();
                var priceInclTax = Helper.correctPrice(rowTotalInclTax) / this.qty();
                var basePriceInclTax = Helper.correctPrice(baseRowTotalInclTax) / this.qty();
                var discountTaxCompensationAmount = this.tax_amount_before_discount() - this.tax_amount();
                var baseDiscountTaxCompensationAmount = this.base_tax_amount_before_discount() - this.base_tax_amount();

                infobuy.extension_data = [
                    {key: "row_total", value: Helper.correctPrice(this.row_total())},
                    {key: "base_row_total", value: Helper.correctPrice(this.base_row_total())},
                    {key: "row_total_incl_tax", value: Helper.correctPrice(rowTotalInclTax)},
                    {key: "base_row_total_incl_tax", value: Helper.correctPrice(baseRowTotalInclTax)},
                    {key: "price", value: Helper.correctPrice(itemPrice)},
                    {key: "base_price", value: Helper.correctPrice(baseItemPrice)},
                    {key: "price_incl_tax", value: priceInclTax},
                    {key: "base_price_incl_tax", value: basePriceInclTax},
                    {key: "discount_amount", value: Helper.correctPrice(this.item_discount_amount())},
                    {key: "base_discount_amount", value: Helper.correctPrice(this.item_base_discount_amount())},
                    {key: "tax_amount", value: Helper.correctPrice(this.tax_amount())},
                    {key: "base_tax_amount", value: Helper.correctPrice(this.base_tax_amount())},
                    {key: "custom_tax_class_id", value: Helper.correctPrice(this.tax_class_id())},
                    {
                        key: "discount_tax_compensation_amount",
                        value: Helper.correctPrice(discountTaxCompensationAmount)
                    },
                    {
                        key: "base_discount_tax_compensation_amount",
                        value: Helper.correctPrice(baseDiscountTaxCompensationAmount)
                    },
                ];
                /* S: Integration custom discount per item - add item discount data to save on server database */
                if (Helper.isStoreCreditEnable()) {
                    infobuy.amount = this.amount();
                    infobuy.credit_price_amount = this.credit_price_amount();
                    infobuy.extension_data.push({
                        key: "customercredit_discount",
                        value: Helper.correctPrice(this.item_credit_amount())
                    });
                    infobuy.extension_data.push({
                        key: "base_customercredit_discount",
                        value: Helper.correctPrice(this.item_base_credit_amount())
                    });
                    if (this.credit_price_amount()) {
                        infobuy.extension_data.push({
                            key: "original_price",
                            value: Helper.convertPrice(this.credit_price_amount())
                        });
                        infobuy.extension_data.push({
                            key: "base_original_price",
                            value: this.credit_price_amount()
                        });
                    }
                    if (!infobuy.options) {
                        infobuy.options = [];
                    }
                    infobuy.options.push({
                        code: "credit_price_amount",
                        value: this.credit_price_amount()
                    });
                    infobuy.options.push({
                        code: "amount",
                        value: this.amount()
                    });
                }
                if (Helper.isRewardPointsEnable()) {
                    infobuy.extension_data.push({
                        key: "rewardpoints_earn",
                        value: Helper.correctPrice(this.item_point_earn())
                    });
                    infobuy.extension_data.push({
                        key: "rewardpoints_spent",
                        value: Helper.correctPrice(this.item_point_spent())
                    });
                    infobuy.extension_data.push({
                        key: "rewardpoints_discount",
                        value: Helper.correctPrice(this.item_point_discount())
                    });
                    infobuy.extension_data.push({
                        key: "rewardpoints_base_discount",
                        value: Helper.correctPrice(this.item_base_point_discount())
                    });
                }
                if (Helper.isGiftCardEnable()) {
                    infobuy.extension_data.push({
                        key: "gift_voucher_discount",
                        value: Helper.correctPrice(this.item_giftcard_discount())
                    });
                    infobuy.extension_data.push({
                        key: "base_gift_voucher_discount",
                        value: Helper.correctPrice(this.item_base_giftcard_discount())
                    });
                }
                /* E: Integration custom discount per item  */

                return infobuy;
            },
            getDataForOrder: function () {
                var rowTotalInclTax = this.row_total_include_tax();
                var baseRowTotalInclTax = this.base_row_total_include_tax();
                var priceInclTax = Helper.correctPrice(rowTotalInclTax) / this.qty();
                var basePriceInclTax = Helper.correctPrice(baseRowTotalInclTax) / this.qty();
                var discountTaxCompensationAmount = this.tax_amount_before_discount() - this.tax_amount();
                var baseDiscountTaxCompensationAmount = this.base_tax_amount_before_discount() - this.base_tax_amount();

                var data = {
                    item_id: this.item_id(),
                    name: this.product_name(),
                    product_id: this.product_id(),
                    product_type: this.product_type(),
                    sku: this.sku(),
                    qty_canceled: 0,
                    qty_invoiced: 0,
                    qty_ordered: this.qty(),
                    qty_refunded: 0,
                    qty_shipped: 0,
                    discount_amount: Helper.correctPrice(this.item_discount_amount()),
                    base_discount_amount: Helper.correctPrice(this.item_base_discount_amount()),
                    original_price: Helper.convertPrice(this.unit_price()),
                    base_original_price: this.unit_price(),
                    tax_amount: this.tax_amount(),
                    base_tax_amount: this.base_tax_amount(),
                    price: this.item_price(),
                    base_price: this.base_item_price(),
                    price_incl_tax: priceInclTax,
                    base_price_incl_tax: basePriceInclTax,
                    row_total: this.row_total(),
                    base_row_total: this.base_row_total(),
                    row_total_incl_tax: rowTotalInclTax,
                    base_row_total_incl_tax: baseRowTotalInclTax,
                    discount_tax_compensation_amount: discountTaxCompensationAmount,
                    base_discount_tax_compensation_amount: baseDiscountTaxCompensationAmount
                };
                /* S: Integration custom discount per item - add item data for offline order */
                if (Helper.isStoreCreditEnable()) {
                    data.amount = this.amount();
                    data.credit_price_amount = this.credit_price_amount();
                    data.customercredit_discount = Helper.correctPrice(this.item_credit_amount());
                    data.base_customercredit_discount = Helper.correctPrice(this.item_base_credit_amount());
                    if (this.credit_price_amount()) {
                        data.original_price = Helper.convertPrice(this.credit_price_amount());
                        data.base_original_price = this.credit_price_amount();
                    }
                }
                if (Helper.isRewardPointsEnable()) {
                    data.rewardpoints_earn = Helper.correctPrice(this.item_point_earn());
                    data.rewardpoints_spent = Helper.correctPrice(this.item_point_spent());
                    data.rewardpoints_discount = Helper.correctPrice(this.item_point_discount());
                    data.rewardpoints_base_discount = Helper.correctPrice(this.item_base_point_discount());
                }
                if (Helper.isGiftCardEnable()) {
                    data.gift_voucher_discount = Helper.correctPrice(this.item_giftcard_discount());
                    data.base_gift_voucher_discount = Helper.correctPrice(this.item_base_giftcard_discount());
                }
                /* E: Integration custom discount per item  */

                return data;
            },
            isUseOriginalTax: function () {
                var self = this;
                var taxOriginalRates = self.tax_origin_rates();
                var taxRates = self.tax_rates();
                if ((taxOriginalRates && (taxOriginalRates.length == 0)) && (taxRates && (taxRates.length == 0))) {
                    return true;
                }
                if ((taxRates && (taxRates.length > 0)) && (taxOriginalRates && (taxOriginalRates.length > 0))) {
                    if (taxRates.length == taxOriginalRates.length) {
                        var notEqual = false;
                        $.each(taxRates, function (index, rate) {
                            if (!taxOriginalRates[index] || (taxOriginalRates[index] && (taxOriginalRates[index] != rate))) {
                                notEqual = true;
                            }
                        });
                        return !notEqual;
                    }
                }
                return false;
            }
        });
    }
);
