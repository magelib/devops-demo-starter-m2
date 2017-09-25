/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
    [
        'jquery',
        'ko',
        'Magestore_Webpos/js/model/checkout/cart/items/item',
        'Magestore_Webpos/js/helper/general',
        'Magestore_Webpos/js/model/checkout/cart/data/cart'
    ],
    function ($, ko, Item, Helper, CartData) {
        "use strict";
        var Items = {
            apply_tax_after_discount: CartData.apply_tax_after_discount,
            items: CartData.items,
            isEmpty: ko.pureComputed(function(){
                return (CartData.items().length > 0)?false:true;
            }),
            initialize: function () {
                var self = this;
                Helper.observerEvent('load_items_online_after', function(event, data){
                    if(data && data.items){
                        self.updateItemsFromQuote(data.items);
                    }
                });
                return self;
            },
            getItems: function(){
                return this.items();
            },
            getAddedItem: function(data){
                var isNew = false;
                if(typeof data.item_id != "undefined"){
                    var foundItem = ko.utils.arrayFirst(this.items(), function(item) {
                        return (item.item_id() == data.item_id);
                    });
                    if(foundItem){
                        return foundItem;
                    }
                }else{
                    if(typeof data.hasOption !== "undefined"){
                        var foundItem = ko.utils.arrayFirst(this.items(), function(item) {
                            return (
                                    (data.hasOption === false && item.product_id() == data.product_id) || 
                                    (data.hasOption === true && item.product_id() == data.product_id && item.options_label() == data.options_label )
                                   );
                        });
                        if(foundItem){
                            return foundItem;
                        }
                    }
                }
                return isNew;
            },
            addItem: function(data){
                var item = this.getAddedItem(data);
                if(item === false){
                    data.item_id = (data.item_id)?data.item_id:$.now();
                    data.qty = (data.qty)?data.qty:1;
                    var item = new Item();
                    item.init(data);
                    this.items.push(item);
                }else{
                    var qty = item.qty();
                    qty += data.qty;
                    this.setItemData(item.item_id(), "qty", qty);
                }
            },
            getItem: function(itemId){
                var item = false;
                var foundItem = ko.utils.arrayFirst(this.items(), function(item) {
                    return (item.item_id() == itemId);
                });
                if(foundItem){
                    item = foundItem;
                }
                return item;
            },
            getItemData: function(itemId, key){
                var item = this.getItem(itemId);
                if(item != false && typeof item[key] != "undefined"){
                    return item[key]();
                }
                return "";
            },
            setItemData: function(itemId, key, value){
                var item = this.getItem(itemId);
                if(item != false){
                    item.setData(key,value);
                }
            },
            removeItem: function(itemId){
                this.items.remove(function (item) {
                    return item.item_id() == itemId; 
                });
            },
            totalItems: function(){
                var total = 0;
                if(this.items().length > 0){
                    ko.utils.arrayForEach(this.items(), function(item) {
                        total += item.qty();
                    });
                }
                return total;
            },
            totalShipableItems: function(){
                var total = 0;
                if(this.items().length > 0){
                    var shipItems = ko.utils.arrayFilter(this.items(), function(item) {
                        return (item.is_virtual() == false);
                    });
                    if(shipItems.length > 0){
                        ko.utils.arrayForEach(shipItems, function(item) {
                            total += item.qty();
                        });
                    }
                }
                return total;
            },
            updateItemsFromQuote: function(quoteItems){
                if(quoteItems){
                    var self = this;
                    $.each(quoteItems, function(index, itemData){
                        if(itemData.offline_item_id){
                            var itemId = itemData.item_id;
                            // var unitPrice = (Helper.isProductPriceIncludesTax())?itemData.base_price_incl_tax:itemData.base_price;
                            var unitPrice = (itemData.base_calculation_price)?itemData.base_calculation_price:((Helper.isProductPriceIncludesTax())?itemData.base_calculation_price:itemData.base_price);
                            //unitPrice = (itemData.base_original_price)?parseFloat(itemData.base_original_price):unitPrice;
                            // var unitPrice = itemData.base_price;
                            var elementItemId = itemData.offline_item_id;
                            var data = {};
                            data.item_id = itemId;
                            data.unit_price = parseFloat(unitPrice);
                            data.item_discount_amount = itemData.discount_amount;
                            data.item_base_discount_amount = itemData.base_discount_amount;
                            data.product_name = itemData.name;
                            data.qty = parseFloat(itemData.qty);
                            data.online_base_tax_amount = parseFloat(itemData.base_tax_amount);
                            data.online_tax_amount = parseFloat(itemData.tax_amount);
                            data.tax_class_id = parseFloat(itemData.tax_class_id);
                            data.tax_rates = [parseFloat(itemData.tax_percent)];
                            data.is_virtual = itemData.is_virtual;
                            data.has_error = itemData.has_error;
                            data.qty_increment = parseFloat(itemData.qty_increment);
                            data.maximum_qty = parseFloat(itemData.maximum_qty);
                            data.minimum_qty = parseFloat(itemData.minimum_qty);
                            data.saved_online_item = true;
                            if(itemData.product_type !== "customsale") {
                                data.image_url = itemData.image_url;
                            }
                            var added = self.getAddedItem({item_id: itemData.offline_item_id}) || self.getAddedItem({item_id: itemId});
                            if(added === false){
                                data.item_id = itemId;
                                self.addItem(data);
                            }else{
                                data.item_id = itemId;
                                self.setItemData(elementItemId, data);
                                self.setItemData(itemId, data);
                            }
                        }
                    });
                }
            }
        };
        return Items.initialize();
    }
);