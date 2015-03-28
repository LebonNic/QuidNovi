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
            }).then(function(save) {
                if (true === save) {
                    Feed.rename($scope.feed);
                } else {
                    Feed.unsubscribe($scope.feed);
                }
            }, function() {
                Feed.rename($scope.feed);
            });
        };
    });

    qnFeed.controller('FeedEditionDialogController', function ($scope, $mdDialog, Feed) {
        $scope.unsubscribe = function() {
            $mdDialog.hide(false);
        };
        $scope.close = function () {
            $mdDialog.hide(true);
        };
    });

    qnFeed.factory('Feed', function ($http) {
        var feeds = [];
        var feedRequests = [];
        var pendingQuery;

        function findFeed(id) {
            for (var i = 0, length = feeds.length; i < length; ++i) {
                if (feeds[i].id === id) {
                    return feeds[i];
                }
            }
        }

        function findRequestForFeed(id) {
            for (var i = 0, length = feedRequests.length; i < length; ++i) {
                if (feedRequests[i].id === id) {
                    return feedRequests[i].request;
                }
            }
        }

        return {
            query: function (callback) {
                if (pendingQuery === undefined) {
                    console.log('Querying /feeds');
                    pendingQuery = $http.get('/feeds', {cache: true});
                    pendingQuery.success(function (data) {
                        feeds = data;
                        angular.forEach(data, function (feed) {
                            feed.url = '/feeds/' + feed.id;
                        });
                        pendingQuery = undefined;
                        callback(feeds);
                    });
                } else {
                    pendingQuery.then(function () {
                        callback(feeds);
                    });
                }
            },
            get: function (id, callback) {
                var feed = findFeed(id);
                if (undefined !== feed) {
                    return callback(feed);
                }

                var request = findRequestForFeed(id);
                if (undefined !== request) {
                    request.then(function () {
                        var feed = findFeed(id);
                        callback(feed);
                    });
                    return;
                }

                console.log('Querying /feeds/' + id);
                request = $http.get('/feeds/' + id, {cache: true});
                feedRequests.push({id: id, request: request});
                request.success(function (data) {
                    feeds.push(data);
                    data.url = '/feeds/' + data.id;
                    callback(data);
                }).error(function() {
                    callback(undefined);
                });
            },
            subscribe: function (feed) {
                if (undefined === feed.id) {
                    $http.post('/feeds', feed).success(function (data) {
                        console.log(data);
                    });
                }
            },
            unsubscribe: function(feed) {
                if (undefined !== feed.id) {
                    $http.delete('/feeds/' + feed.id);
                }
            },
            rename: function(feed) {
                if (undefined !== feed.id) {
                    $http.patch('/feeds/' + feed.id, {name: feed.name});
                }
            }
        };
    });
})();