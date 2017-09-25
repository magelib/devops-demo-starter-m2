/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
        [
            'jquery',
            'ko',
            'Magestore_Webpos/js/model/catalog/product/type/simple',
        ],
        function ($, ko, typeAbstract) {
            "use strict";
            return typeAbstract.extend({
                childStocks: {},               
            });
        }
);