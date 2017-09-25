/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'Magestore_Webpos/js/helper/price',
        'Magestore_Webpos/js/model/directory/country',
        'Magestore_Webpos/js/helper/datetime',
        'Magestore_Webpos/js/action/notification/add-notification',
    ],
    function ($, ko, Component, priceHelper, countryModel, datetimeHelper, notification) {
        "use strict";
        return Component.extend({
            setData: function() {

            },

            formatPrice: function(price){
                return priceHelper.formatPrice(price);
            },

            getCountryName: function(code, deferred){
                countryModel().load(code, deferred);

            },

            /**
             * return a date with format: Thursday 4 May, 2016
             *
             * @param dateString
             * @returns {string}
             */
            getFullDate: function (dateString) {
                return datetimeHelper.getFullDate(dateString);
            },


            /**
             * return a date time with format: Thursday 4 May, 2016 15:26PM
             * @param dateString
             * @returns {string}
             */
            getFullDatetime: function (dateString) {
                return datetimeHelper.getFullDatetime(dateString);
            },

            addNotification: function(message, isShowToaster, priority, title){
                return notification(message, isShowToaster, priority, title);
            }
        });
    }
);