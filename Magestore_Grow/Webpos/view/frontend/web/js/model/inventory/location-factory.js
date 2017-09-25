/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define(
    [
        'Magestore_Webpos/js/model/factory',
        'Magestore_Webpos/js/model/inventory/location',
    ],
    function(Factory, ModelClass){
         "use strict";
        return {
            get: function(){
                var key = 'model/inventory/location';
                return Factory.getSingleton(key, ModelClass);              
            },
            
            create: function(){
                return ModelClass();
            }
        }
    }
);