/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
        [
            'jquery',
            'ko',
            'Magestore_Webpos/js/view/base/list/abstract',
        ],
        function ($, ko, listAbstract) {
            "use strict";
            return listAbstract.extend({
                defaults: {
                    template: 'Magestore_Webpos/base/grid/abstract',
                },

                initialize: function () {
                    this.isShowHeader = true;
                    this._super();
                },
            });
        }
);
