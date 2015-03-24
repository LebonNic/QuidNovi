// Definition of QuidNovi module.
(function () {
    var qnApp = angular.module('QuidNovi', ['ngRoute', 'ngMaterial',
        'qnEntry', 'qnFeed', 'qnCategory']);

    qnApp.controller('AppController', function ($scope, $location, $mdSidenav, Category) {
        $scope.sections = [{
            name: 'All',
            url: '/entries'
        }, {
            name: 'Unread',
            url: '/entries?read=false'
        }, {
            name: 'Saved',
            url: '/entries?saved=true'
        }];

        $scope.categories = Category.findAll();
        $scope.selectedSection = undefined;

        $scope.toggleSidenav = function (menuId) {
            $mdSidenav(menuId).toggle();
        };

        $scope.isSelected = function () {
            return $scope.section.url === $location.url();
        };

        $scope.toggle = function () {
            if (!$scope.isOpen()) {
                $scope.selectedSection = $scope.section;
            } else {
                $scope.selectedSection = undefined;
            }
        };

        $scope.isOpen = function () {
            return $scope.selectedSection === $scope.section;
        }
    });

    qnApp.controller('DialogController', function ($scope, $mdDialog) {
        $scope.hide = function () {
            $mdDialog.hide();
        };
        $scope.cancel = function () {
            $mdDialog.cancel();
        };
        $scope.answer = function (answer) {
            $mdDialog.hide(answer);
        };
    });

    qnApp.config(function ($routeProvider) {
        //$location.html5Mode(true);
        // Route configuration
        $routeProvider
            // By default, go to list of last entries
            .when('/', {redirectTo: '/entries'})
            .when('/entries', {templateUrl: 'partials/entry-list.html', controller: 'EntriesController'})
            // ENTRY LIST
            // Display a list of most recent entries sorted by descending date.
            // Each line of the list contains entry's source feed, entry title, followed by the beginning of entry summary and entry publish date.
            // Unread entries are displayed in bold font. Read entries are in normal font.
            // A button allows to mark all items as read.
            // User can use 'j' (next item) and 'k' (previous item) keys to browse entries faster.
            // On scroll, more entries can be loaded. An indicator is then displayed to notify user about it.

            // ENTRY DETAIL
            // When an entry title is clicked, entry line expands and entry's details are shown.
            // The entry is mark as read. User can click on "Mark as unread" button to switch it back to unread. The button toggles to "Mark as read" and allows to mark entry as read.
            // When another entry title is clicked, previous entry collapses and clicked entry expands.
            // When the feed of the entry is clicked, redirect to /feeds/:id
            // When entry publish date is clicked, redirect to entry url

            // FEED DETAIL
            // Display feed entries ordered by descending date.
            // The options available on entry list are available here.
            // The feed title and description are displayed above the entry list.
            // A button next to feed title allows feed edition.
            // On click, a modal is revealed with current feed properties.
            // User can edit feed title, feed description and can unsubscribe feed.
            .when('/feeds/:feed', {templateUrl: 'partials/feed-detail.html', controller: 'FeedController'})

            // CATEGORY DETAIL
            // Display category entries ordered by descending date.
            // The options available on entry list are available here.
            // The category title and description are displayed above the entry list.
            // A button next to category title allows category edition.
            // On click, a modal is revealed with current category properties.
            // User can change category title, category description and can delete category.
            .when('/categories/:category', {
                templateUrl: 'partials/category-detail.html',
                controller: 'CategoryController'
            })

            .when('/error', {
                templateUrl: 'partials/error.html'
            })

            .otherwise({redirectTo: '/error'})
    });

    qnApp.directive('menuLink', function () {
        return {
            restrict: 'E',
            scope: {
                section: '='
            },
            templateUrl: 'partials/menu-link.html',
            controller: 'AppController'
        };
    });

    qnApp.directive('menuToggle', function (RecursionHelper) {
        return {
            restrict: 'E',
            scope: {
                section: '='
            },
            templateUrl: 'partials/menu-toggle.html',
            controller: 'AppController',
            compile: function (element) {
                return RecursionHelper.compile(element);
            }
        };
    });

    qnApp.factory('RecursionHelper', ['$compile', function ($compile) {
        return {
            compile: function (element, link) {
                // Normalize the link parameter
                if (angular.isFunction(link)) {
                    link = {post: link};
                }

                // Break the recursion loop by removing the contents
                var contents = element.contents().remove();
                var compiledContents;
                return {
                    pre: (link && link.pre) ? link.pre : null,
                    post: function (scope, element) {
                        // Compile the contents
                        if (!compiledContents) {
                            compiledContents = $compile(contents);
                        }
                        // Re-add the compiled contents to the element
                        compiledContents(scope, function (clone) {
                            element.append(clone);
                        });

                        // Call the post-linking function, if any
                        if (link && link.post) {
                            link.post.apply(null, arguments);
                        }
                    }
                };
            }
        };
    }]);
})();