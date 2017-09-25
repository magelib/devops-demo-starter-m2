/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
    [
        'jquery',
        'Magestore_Webpos/js/model/resource-model/indexed-db/abstract'
    ],
    function ($,Abstract) {
        "use strict";
        return Abstract.extend({
            mainTable: 'synchronization',
            keyPath: 'id',
            indexes: {
                id: {unique: true},
            }
        });
    }
);