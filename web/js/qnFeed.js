(function () {
    var qnFeed = angular.module('qnFeed', []);

    qnFeed.controller('FeedController', function ($scope, $location, $routeParams, $mdDialog, Feed) {
        $scope.feed = undefined;
        Feed.get($routeParams.feed, function (data) {
            if (undefined === data) {
                $location.url('/error');
            }
            $scope.feed = data;
        });

        $scope.showEditDialog = function ($event) {
            $mdDialog.show({
                controller: 'FeedEditionDialogController',
                templateUrl: 'partials/feed-dialog.html',
                targetEvent: $event
            }).then(function (save) {
                if (true === save) {
                    Feed.rename($scope.feed);
                } else {
                    Feed.unsubscribe($scope.feed);
                }
            }, function () {
                Feed.rename($scope.feed);
            });
        };
    });

    qnFeed.controller('FeedEditionDialogController', function ($scope, $mdDialog, Feed) {
        $scope.unsubscribe = function () {
            $mdDialog.hide(false);
        };
        $scope.close = function () {
            $mdDialog.hide(true);
        };
    });

    qnFeed.factory('Feed', function ($http, Category) {
        var root;

        function findFeedInContainer(container, id) {
            var feeds = container.feeds;
            var categories = container.categories;
            for (var i = 0, length = feeds.length; i < length; ++i) {
                if (feeds[i].id === id) {
                    return feeds[i];
                }
            }
            for (var i = 0, length = categories.length; i < length; ++i) {
                var feed = findFeedInContainer(categories[i], id);
                if (undefined !== feed) {
                    return feed;
                }
            }
            return undefined;
        }

        function findFeed(id) {
            return findFeedInContainer(root, id);
        }

        function removeFeed(id) {
            removeFeedInContainer(root, id);
        }

        function removeFeedInContainer(container, id) {
            var feeds = container.feeds;
            var categories = container.categories;
            for (var i = 0, length = feeds.length; i < length; ++i) {
                if (feeds[i].id === id) {
                    feeds.splice(i, 1);
                    return true;
                }
            }
            for (var i = 0, length = categories.length; i < length; ++i) {
                if (removeFeedInContainer(categories[i], id)) {
                    return true;
                }
            }
            return false;
        }

        return {
            query: function (callback) {
                Category.query(function (data) {
                    root = data;
                    callback(data);
                });
            },
            get: function (id, callback) {
                if (root === undefined) {
                    Category.query(function (data) {
                        root = data;
                        callback(findFeed(id));
                    })
                } else {
                    callback(findFeed(id));
                }
            },
            subscribe: function (feed) {
                if (undefined === feed.id) {
                    if (feed.containerId === undefined) {
                        feed.containerId = root.id;
                    }
                    $http.post('/feeds', feed).success(function (data) {
                        console.log('Feed ' + data.uri + ' subscribed.');
                        $http.get(data.uri).success(function (feed) {
                            feed.url = '/feeds/' + feed.id;
                            root.feeds.push(feed);
                        });
                    });
                }
            },
            unsubscribe: function (feed) {
                if (undefined !== feed.id) {
                    $http.delete('/feeds/' + feed.id).success(function () {
                        removeFeed(feed.id);
                    });
                }
            },
            rename: function (feed) {
                if (undefined !== feed.id) {
                    $http.patch('/feeds/' + feed.id, {name: feed.name});
                }
            }
        };
    });
})();