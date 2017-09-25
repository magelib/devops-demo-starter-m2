
/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
        [
            'jquery',
            'Magestore_Webpos/js/model/event-manager',
            'Magestore_Webpos/js/action/inventory/stock-item/update-product-list',
        ],
        function ($, eventManager, updateProductList) {
            "use strict";

            return {
                /*
                 * Update stock data to product list view after mass updated stock-item 
                 * 
                 */
                execute: function () {
                    eventManager.observer('stock_item_massupdate_after', function (event, eventData) {
                        return;
                        var items = eventData.items;
                        updateProductList(items);
                    });
                }
            }
        }
);