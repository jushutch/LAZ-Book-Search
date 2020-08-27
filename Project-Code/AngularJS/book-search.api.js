(function () {
    "use strict";

    angular.module('kids.bookroom')

        .service('bookSearchApi', ['createApi',
            function(createApi) {

                var bookSearchApi = createApi('/api');

                function getBookSearch(searchText) {
                    return bookSearchApi.get('/books', {
                        params: {
                            search : searchText
                        }
                    });
                }

                function getRecommendedBooklist() {
                    return bookSearchApi.get('/recommended');
                }

                return {
                    getBookSearch: getBookSearch,
                    getRecommendedBooklist: getRecommendedBooklist
                }

            }]);
})();