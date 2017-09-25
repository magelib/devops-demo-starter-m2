/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
    [
        'jquery',
        'ko',
        'Magestore_Webpos/js/view/catalog/product/detail-popup',
        'Magestore_Webpos/js/helper/price'
    ],
    function ($,ko, detailPopup, priceHelper) {
        "use strict";
        return detailPopup.extend({
            defaults: {
                template: 'Magestore_Webpos/catalog/product/detail/storecredit'
            },
            initialize: function () {
                this._super();
            },
            isFixed: function () {
                return this.itemData().storecredit_type == 1 ? true:false;
            },
            isDropdown: function () {
                return this.itemData().storecredit_type == 3 ? true:false;
            },
            isRange: function () {
                return this.itemData().storecredit_type == 2 ? true:false;
            },
            getCreditMin: function(){
                return priceHelper.convertAndFormat(this.itemData().storecredit_min);
            },
            getCreditMax: function(){
                return priceHelper.convertAndFormat(this.itemData().storecredit_max);
            },
            getDropdownValues: function () {
                var self = this;
                var options = [];
                if(self.isDropdown()){
                    var values = this.itemData().customercredit_value;
                    //var rate = this.getProductData().storecredit_rate;
                    var childIds = values.split(',');
                    $.each(childIds, function(index, value){
                        options.push({
                            'key': parseFloat(value),
                            'value': priceHelper.convertAndFormat(parseFloat(value))
                        });
                    });
                }
                return options;
            },
            updatePrice: function (dataObject) {
                var self = this;
                if(self.isDropdown()){
                    var rate = this.itemData().storecredit_rate;
                    if($('#storecredit_' + dataObject.id).length > 0){
                        var value = parseFloat($('#storecredit_' + dataObject.id).attr('value'));
                        var priceUpdate = parseFloat(rate) * value;
                        var product = detailPopup().getProductData();
                        detailPopup().basePriceAmount(priceUpdate);
                        detailPopup().creditValue(value);
                        detailPopup().defaultPriceAmount(priceHelper.convertAndFormat(priceUpdate));
                    }
                }
                
            },
            creditValueFormated: function(value){
                return priceHelper.convertAndFormat(value);
            }
        });
    }
);