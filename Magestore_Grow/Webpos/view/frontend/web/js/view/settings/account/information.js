/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
    [
        'uiComponent'
    ],
    function (Component) {
        "use strict";

        return Component.extend({
            staff: {
                name: window.webposConfig.staffName
            }
        });
    }
);