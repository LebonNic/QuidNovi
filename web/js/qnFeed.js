(function () {
    var qnFeed = angular.module('qnFeed', []);

    qnFeed.controller('FeedController', function ($scope, $routeParams, $location, $mdDialog, Entry, Feed) {
        $scope.feed = Feed.find($routeParams.feed);

        if ($scope.feed === undefined) {
            $location.url('/error');
        }

        $scope.showEditDialog = function ($event) {
            $mdDialog.show({
                controller: 'DialogController',
                templateUrl: 'partials/feed-dialog.html',
                targetEvent: $event
            }).then(function (answer) {
                $scope.alert = 'You said the information was "' + answer + '".';
            }, function () {
                $scope.alert = 'You cancelled the dialog.';
            });
        };
    });

    qnFeed.factory('Feed', function () {
        var feeds = [{
            id: 1,
            name: 'Awesome Feed',
            url: '/feeds/1'
        }, {
            id: 2,
            name: '"IT" Website',
            url: '/feeds/2'
        }, {
            id: 3,
            name: 'Random website',
            url: '/feeds/3'
        }];

        return {
            findAll: function (offset, max) {
                offset = parseInt(offset) || 0;
                max = parseInt(max) || 20;
                return feeds.slice(offset, offset + max);
            },
            find: function (id) {
                id = parseInt(id);
                var searchedFeed = undefined;
                angular.forEach(feeds, function (feed) {
                    if (feed.id === id) {
                        searchedFeed = feed;
                    }
                });
                return searchedFeed;
            }
        }
    });
})();