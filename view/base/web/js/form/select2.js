define(['jquery', 'Magento_Ui/js/form/element/abstract'], function($, Element) {
    'use strict';

    return Element.extend({

        defaults: {
            elementTmpl: 'Reload_Seo/form/element/select2'
        },

        initSelect: function() {
            var self = this;

            $(function () {
                $(self.formElement + '[name=\'' + self.inputName + '\']').select2({
                    tags: true,
                    placeholder: '',
                    tokenSeparators: [","],
                    minimumInputLength: 1,
                    maximumSelectionLength: 2,
                    initSelection : function (element, callback) {
                        var asString = $reloadseo(self.formElement + '[name=\'' + self.inputName + '\']').val();
                        var data = [];
                        $reloadseo.each(asString.split(','), function(k, v)
                        {
                            data.push({id: v, text: v});
                        });
                        callback(data);
                    },
                    ajax: {
                        url: "https://suggestqueries.google.com/complete/search?callback=?",
                        dataType: 'jsonp',
                        data: function (term, page) {
                            return {q: term, hl: 'en', client: 'firefox' };
                        },
                        results: function (data, page) {
                            var items = {};

                            $reloadseo.each(data[1], function(k, v)
                            {
                                items[v.toLowerCase()] = v.toLowerCase();

                            });

                            var input = $reloadseo(self.formElement + '[name=\'' + self.inputName + '\']').data().select2.search.val();
                            $reloadseo.each(input.split(','), function(k, v)
                            {
                                items[v.toLowerCase()] = v.toLowerCase();
                            });

                            var results = [];
                            results.push({id: input.toLowerCase(), text: input.toLowerCase()});
                            $reloadseo.each(items, function(k, v)
                            {
                                results.push({id: v, text: v});
                            });

                            return { results: results };
                        },
                    }
                });
            });
        },
    });
});