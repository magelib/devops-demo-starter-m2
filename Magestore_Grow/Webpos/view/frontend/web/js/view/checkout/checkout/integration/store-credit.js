/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
    [
        'jquery',
        'ko',
        'Magestore_Webpos/js/view/checkout/checkout/integration/abstract',
        'Magestore_Webpos/js/view/settings/general/storecredit/auto-sync-balance',
        'Magestore_Webpos/js/helper/general',
        'Magestore_Webpos/js/model/checkout/cart',
        'Magestore_Webpos/js/model/checkout/integration/store-credit-factory',
    ],
    function ($, ko, Abstract, AutoSyncBalance, Helper, CartModel, StoreCreditFactory) {
        "use strict";
        return Abstract.extend({
            defaults: {
                template: 'Magestore_Webpos/checkout/checkout/integration/storecredit'
            },
            initialize: function () {
                this._super();
                this.model = StoreCreditFactory.get();
                if(!this.addedData){
                    this.initData();
                }
            },
            initData: function(){
                var self = this;
                self.addedData = true;
                self.balance = ko.pureComputed(function(){
                    return self.convertAndFormatPrice(self.model.balanceAfterApply());
                });
                self.currentAmount = ko.pureComputed(function(){
                    return self.convertAndFormatWithoutSymbol(self.model.currentAmount());
                });
                self.canApply = ko.pureComputed(function(){
                    return (self.model.balance() > 0)?true:false;
                });
                self.useMaxPoint = self.model.useMaxPoint;
                self.updatingBalance = self.model.updatingBalance;
                self.visible = self.model.visible;
                self.observerEvent('go_to_checkout_page', $.proxy(function(){
                    if(CartModel.customerId() && Helper.isStoreCreditEnable()){
                        self.updateStorageBalance();
                    }
                }, self));
            },
            pointUseChange: function(el, event){
                var amount = this.getPriceHelper().toNumber(event.target.value);
                amount = (amount > 0)?amount:0;
                event.target.value = amount;
                amount = this.toBasePrice(amount);
                amount = (amount > 0)?amount:0;
                if(amount >= this.model.balance()){
                    amount = this.model.balance();
                    this.model.useMaxPoint(true);
                }else{
                    this.model.useMaxPoint(false);
                }
                this.model.currentAmount(amount);
            },
            useMaxPointChange: function(el, event){
                this.useMaxPoint(event.target.checked);
                this.model.useMaxPoint(event.target.checked);
            },
            apply: function(){
                this.model.apply();
            },
            updateBalance: function(){
                if(this.updatingBalance() == false){
                    this.model.updateBalance();
                }
            },
            updateStorageBalance: function(){
                this.model.updateStorageBalance();
                var autoSyncBalance = Helper.getLocalConfig(AutoSyncBalance().configPath);
                if(autoSyncBalance == true){
                    this.model.updateBalance();
                }
            }
        });
    }
);
