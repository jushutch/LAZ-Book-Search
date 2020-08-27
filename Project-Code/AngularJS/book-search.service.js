(function () {
    "use strict";

    angular.module('kids.bookroom')

        .service('bookSearchService', ['bookSearchApi', 'modelUtils', 'Resource',
            function(bookSearchApi, modelUtils, Resource) {

                function searchForBooks(searchText) {
                    return bookSearchApi.getBookSearch(searchText)
                        .then(function(books) {
                            return modelUtils.mapConstructor(books, Resource);
                        });
                    //good place to handle errors
                }

                function getRecommendedBooklist() {
                    return bookSearchApi.getRecommendedBooklist()
                        .then(function(books) {
                            return modelUtils.mapConstructor(books, Resource);
                        });
                }

                return {
                    searchForBooks: searchForBooks,
                    getRecommendedBooklist: getRecommendedBooklist
                }
            }]);

})();