(function(){
    'use strict';
    var module = angular.module('kids.bookroom');
    module.component('booksearch', {
        templateUrl: '/js/angular/bookroom/book-search.html',
        controller: 'Booksearch',
        bindings: {
            itemWidth: '@',
            itemHeight: '@',
            viewportItems: '@'
        }
    });
    module.controller('Booksearch', ['bookSearchService', 'BoundedScrollModel', '$scope', '$window', 'KeyCodes',
        function(bookSearchService, BoundedScrollModel, $scope, $window, KeyCodes) {
            var ctrl = this;
            ctrl.autocompleteSource = '/api/search-suggest';
            ctrl.searchResults = [];
            ctrl.areRecommendedBooks = false;
            ctrl.failedSearchTerms = "";
            ctrl.hasBeenASearch = false

            ctrl.$onInit = function () {
                angular.element($window).on('resize', windowResizeHandler);
            };

            ctrl.$onDestroy = function () {
                angular.element($window).off('resize', undefined, windowResizeHandler);
            };


            ctrl.onKeydown = function(event) {
                event.stopPropagation();
                if (event.keyCode === KeyCodes.enter) {
                    ctrl.submitSearch();
                }
            };

            ctrl.submitSearch = function() {
                angular.element('#hsearchTerms').autocomplete('close');
                bookSearchService.searchForBooks(ctrl.searchTerms)
                    .then(function(resources) {
                        if (resources.length === 0) {
                            return getRecommenedBookList();
                        } else {
                            updateSliderWithResults(resources);
                            ctrl.failedSearchTerms = false;
                        }
                    });
            }

            ctrl.resultsAreRecommended = function() {
                return ctrl.failedSearchTerms !== false;
            }

            ctrl.hasSearchResults = function() {
                return ctrl.searchResults && ctrl.searchResults.length !== 0;
            }

            ctrl.autocompleteSelect = function (event, ui) {
                ctrl.searchTerms = ui.item.value;
            }

            var getRecommenedBookList = function() {
                return bookSearchService.getRecommendedBooklist()
                    .then(function(recommendedBooks) {
                        updateSliderWithResults(recommendedBooks);
                        ctrl.failedSearchTerms = ctrl.searchTerms;
                    });
            }

            var updateSliderWithResults = function(resources) {
                ctrl.searchResults = resources;
                initSlidePane();
                updateViewportItemsByWindowWidth($window.innerWidth);
                ctrl.hasBeenASearch = true;
            }

            var windowResizeHandler = function (event) {
                updateViewportItemsByWindowWidth(this.innerWidth);
            };

            var updateViewportItemsByWindowWidth = function (windowWidth) {
                var buttonBuffer = 2*80;
                var prevViewportItems = parseInt(ctrl.viewportItems);
                var prevItemwidth = parseInt(ctrl.itemWidth);
                if (windowWidth >= 1920) {
                    ctrl.viewportItems = 6;
                } else if (windowWidth >= 1366) {
                    ctrl.viewportItems = 5;
                } else if (windowWidth >= 1024) {
                    ctrl.viewportItems = 4;
                } else if (windowWidth >= 800) {
                    ctrl.viewportItems = 3;
                } else {
                    ctrl.viewportItems = 2;
                }
                ctrl.itemWidth = (windowWidth - buttonBuffer) / ctrl.viewportItems;
                if (prevViewportItems !== ctrl.viewportItems || prevItemwidth !== ctrl.itemWidth) {
                    initSlidePane();
                    try {
                        $scope.$digest();
                    } catch (error) {
                        // digest already in progress..
                    }
                }
            };

            var initSlidePane = function() {
                ctrl.scrollModel = new BoundedScrollModel({
                    itemWidth: parseInt(ctrl.itemWidth),
                    viewportWidth: parseInt(ctrl.itemWidth)*parseInt(ctrl.viewportItems),
                    stepSize: 21,
                    itemsToScroll: parseInt(ctrl.viewportItems),
                    displayItemBuffer: parseInt(ctrl.viewportItems)
                }, $scope);
                ctrl.scrollModel.setItems(ctrl.searchResults);
            };

    }]);
})();
