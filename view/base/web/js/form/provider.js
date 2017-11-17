/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'ko',
    'Magento_Ui/js/form/provider',
    'jquery'
], function (ko, Provider, $) {
    'use strict';

    return Provider.extend({

        /**
         * Initializes provider component.
         *
         * @returns {Provider} Chainable.
         */
        initialize: function () {
            this._super();

            var self = this;
            var timer = null;

            var changed = function()
            {
                if(timer !== null)
                {
                    clearTimeout(timer);
                }

                reloadseo.updateSeoData(self.data);

            };

            $('body').on('keyup', 'input,textarea,select', function()
            {
                if(timer !== null)
                {
                    clearTimeout(timer);
                }

                timer = setTimeout(changed, 300);
            });

            $('body').on('change', 'input,textarea,select', changed);

            return this;
        },
    });
});
