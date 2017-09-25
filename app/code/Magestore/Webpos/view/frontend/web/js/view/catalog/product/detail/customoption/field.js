/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
    [
        'jquery',
        'ko',
        'Magestore_Webpos/js/view/catalog/product/detail/customoption'
    ],
    function ($,ko, customOptionDetail) {
        "use strict";
        return customOptionDetail.extend({
            defaults: {
                template: 'Magestore_Webpos/catalog/product/detail/customoption/field'
            },
            initialize: function () {
                this._super();
            }
        });
    }
);