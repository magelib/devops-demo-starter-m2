/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
    [
        'jquery',
        'ko',
        'Magestore_Webpos/js/view/settings/general/abstract',
        'Magestore_Webpos/js/helper/general'
    ],
    function ($, ko, Component, Helper) {
        "use strict";

        return Component.extend({
            defaults: {
                template: 'Magestore_Webpos/settings/general/element/select',
                elementName: '',
                configPath: '',
                defaultValue: 0,
                optionsArray: ko.observableArray([])
            },
            initialize: function () {
                this._super();
                var self = this;
                if(self.optionsArray().length == 0){
                    self.optionsArray([{value: 0, text: Helper.__('No')},
                        {value: 1, text: Helper.__('Yes')}
                    ]);
                }
                var savedConfig = Helper.getLocalConfig(self.configPath);
                if(typeof savedConfig == 'undefined' || savedConfig == null){
                    Helper.saveLocalConfig(self.configPath, self.defaultValue);
                }
                self.value = ko.pureComputed(function(){
                    return Helper.getLocalConfig(self.configPath);
                });
            },
            saveConfig: function (data, event) {
                var value = $('select[name="' + data.elementName + '"]').val();
                Helper.saveLocalConfig(this.configPath, value);
            }
        });
    }
);