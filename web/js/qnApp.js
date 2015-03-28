(function () {
    var qnApp = angular.module('QuidNovi', ['ngRoute', 'ngMaterial',
        'qnEntry', 'qnFeed', 'qnCategory']);

    qnApp.controller('AppController', function ($scope, $location, $mdSidenav, $mdBottomSheet, Category) {
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

        $scope.categories = [];
        Category.query(function (data) {
            $scope.categories = data;
        });
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
        };

        $scope.showListBottomSheet = function ($event) {
            $mdBottomSheet.show({
                templateUrl: 'partials/add-bottom-sheet.html',
                controller: 'ListBottomSheetController',
                targetEvent: $event
            });
        };
    });

    qnApp.controller('ListBottomSheetController', function ($scope, $mdBottomSheet, $mdDialog) {
        $scope.showFeedSubscribeDialog = function($event) {
            $mdBottomSheet.hide();
            $mdDialog.show({
                controller: 'SubscribeFeedDialogController',
                templateUrl: 'partials/feed-subscribe-dialog.html',
                targetEvent: $event
            }).then(function (answer) {

            }, function () {
                console.log('Canceled feed subscription.');
            });
        };
    });

    qnApp.controller('SubscribeFeedDialogController', function ($scope, $mdDialog, Feed) {
        $scope.feed = {
            name: '',
            source: ''
        };
        $scope.hide = function () {
            $mdDialog.hide();
        };
        $scope.cancel = function () {
            $mdDialog.cancel();
        };
        $scope.confirm = function() {
            if ($scope.feed.name && $scope.feed.source) {
                Feed.subscribe($scope.feed);
                $mdDialog.hide();
            }
        }
    });

    qnApp.config(function ($routeProvider, $httpProvider) {
        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';

        // Route configuration
        $routeProvider
            // By default, go to list of last entries
            .when('/', {redirectTo: '/entries'})
            .when('/entries', {
                templateUrl: 'partials/entry-list.html',
                controller: 'EntriesController'
            })

            .when('/feeds/:feed', {
                templateUrl: 'partials/feed-detail.html',
                controller: 'FeedController'
            })

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