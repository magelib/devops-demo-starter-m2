/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
    [
        'jquery',
        'ko',
        'Magestore_Webpos/js/model/collection/abstract',
        'Magestore_Webpos/js/model/resource-model/magento-rest/integration/rewardpoints/reward-points',
        'Magestore_Webpos/js/model/resource-model/indexed-db/integration/rewardpoints/reward-points'

    ],
    function ($,ko, collectionAbstract, restResource, indexedDbResource) {
        "use strict";

        return collectionAbstract.extend({
            /* Query Params*/
            queryParams: {
                filterParams: [],
                orderParams: [],
                pageSize: '',
                currentPage: '',
                paramToFilter: [],
                paramOrFilter: []
            },
            initialize: function () {
                this._super();
                this.setResource(restResource(), indexedDbResource());
            }
        });
    }
);