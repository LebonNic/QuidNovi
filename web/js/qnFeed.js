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

        $scope.containerId = 0;

        $scope.categoryList = function() {
            return Feed.listWithExclude($scope.feed);
        };

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
        $scope.moveTo = function(feed, containerId) {
            Feed.moveToCategory(feed, containerId);
        }
    });

    qnFeed.factory('Feed', function ($http, Category, $location) {
        var root;

        function findFeed(id) {
            return findFeedInContainer(root, id);
        }

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

        function findFeedContainer(id) {
            return findFeedContainerInContainer(root, id);
        }

        function findFeedContainerInContainer(container, id) {
            var feeds = container.feeds;
            var categories = container.categories;
            for (var i = 0, length = feeds.length; i < length; ++i) {
                if (feeds[i].id == id) {
                    return container;
                }
            }
            for (var i = 0, length = categories.length; i < length; ++i) {
                var result = findFeedContainerInContainer(categories[i], id);
                if (undefined !== result) {
                    return result;
                }
            }
            return undefined;
        }

        function findCategory(id) {
            return findCategoryInContainer(root, id);
        }

        function findCategoryInContainer(container, id) {
            if (container.id === id) {
                return container;
            }
            var categories = container.categories;
            for (var i = 0, length = categories.length; i < length; ++i) {
                if (categories[i].id === id) {
                    return categories[i];
                }
                var category = findCategoryInContainer(categories[i], id);
                if (undefined !== category) {
                    return category;
                }
            }
            return undefined;
        }

        function appendCategories(container, exclude, list) {
            list.push(container);
            angular.forEach(container.categories, function(category) {
                if (category.id !== exclude.id) {
                    appendCategories(category, exclude, list);
                }
            });
            return list;
        }

        function removeFeedFromContainer(feed, container) {
            var feeds = container.feeds;
            for (var i = 0, length = feeds.length; i < length; ++i) {
                if (feeds[i].id === feed.id) {
                    container.feeds.splice(i, 1);
                    return;
                }
            }
        }

        function addFeedToContainer(feed, container) {
            container.feeds.push(feed);
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
                        var container = findFeedContainer(feed.id);
                        removeFeedFromContainer(feed, container);
                        $location.url('/');
                    });
                }
            },
            rename: function (feed) {
                if (undefined !== feed.id) {
                    $http.patch('/feeds/' + feed.id, {name: feed.name});
                }
            },
            listWithExclude: function(exclude) {
                if (undefined !== root) {
                    var list = [];
                    appendCategories(root, exclude, list);
                    return list;
                }
                return undefined;
            },
            moveToCategory: function(feed, containerId) {
                console.log(containerId);
                var container = findCategory(parseInt(containerId));
                var oldContainer = findFeedContainer(feed.id);

                if (oldContainer.id !== container.id) {
                    removeFeedFromContainer(feed, oldContainer);
                    addFeedToContainer(feed, container);
                    $http.patch('/feeds/' + feed.id, {containerId: container.id});
                }
            }
        };
    });
})();