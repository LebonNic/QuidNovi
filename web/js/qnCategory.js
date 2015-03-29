(function () {
    var qnCategory = angular.module('qnCategory', []);

    qnCategory.controller('CategoryController', function ($scope, $routeParams, $location, $mdDialog, Entry, Feed, Category) {
        $scope.category = undefined;
        Category.get($routeParams.category, function (data) {
            if (undefined === data) {
                $location.url('/error');
            }
            $scope.category = data;
        });

        $scope.showEditDialog = function ($event) {
            $mdDialog.show({
                controller: 'CategoryEditionDialogController',
                templateUrl: 'partials/category-dialog.html',
                targetEvent: $event
            }).then(function (save) {
                if (true === save) {
                    Category.rename($scope.category);
                } else {
                    Category.remove($scope.category);
                    $location.url('/');
                }
            }, function () {
                Category.rename($scope.category);
            });
        };
    });

    qnCategory.controller('CategoryEditionDialogController', function ($scope, $mdDialog) {
        $scope.remove = function () {
            $mdDialog.hide(false);
        };
        $scope.close = function () {
            $mdDialog.hide(true);
        };
    });

    qnCategory.factory('Category', function ($http) {
        var pendingQuery;
        var root;

        function findCategory(id) {
            return findCategoryInContainer(root, id);
        }

        function findCategoryInContainer(container, id) {
            if (container.id === id) {
                return container;
            }
            var categories = container.categories;
            for (var i = 0, length = categories.length; i < length; ++i) {
                var result = findCategoryInContainer(categories[i], id);
                if (undefined !== result) {
                    return result;
                }
            }
            return undefined;
        }

        function removeCategory(id) {
            removeCategoryInContainer(root, id);
        }

        function removeCategoryInContainer(container, id) {
            var categories = container.categories;
            for (var i = 0, length = categories.length; i < length; ++i) {
                if (categories[i].id === id) {
                    console.log(categories[i]);
                    categories.splice(i, 1);
                    return true;
                }
                if (removeCategoryInContainer(categories[i], id)) {
                    return true;
                }
            }
            return false;
        }

        function assignUrl(container) {
            container.url = '/categories/' + container.id;
            angular.forEach(container.categories, function (category) {
                assignUrl(category);
            });
            angular.forEach(container.feeds, function (feed) {
                feed.url = '/feeds/' + feed.id;
            });
        }

        return {
            query: function (callback) {
                if (undefined !== root) {
                    return callback(root);
                }
                // Get all query or subscribe to pending query
                if (undefined === pendingQuery) {
                    console.log('Querying /categories');
                    pendingQuery = $http.get('/categories');
                    pendingQuery.success(function (data) {
                        root = data;
                        assignUrl(root);
                        pendingQuery = undefined;
                        callback(data);
                    });
                } else {
                    pendingQuery.then(function () {
                        callback(root);
                    });
                }
            },
            get: function (id, callback) {
                // All categories are loaded on application startup. We don't make any other network calls.
                var category = findCategory(id);
                callback(category);
            },
            create: function (category) {
                if (undefined === category.id) {
                    if (category.containerId === undefined) {
                        category.containerId = root.id;
                    }
                    $http.post('/categories', category).success(function (data) {
                        console.log('Category ' + data.uri + ' created.');
                        $http.get(data.uri).success(function (category) {
                            category.url = '/categories/' + category.id;
                            root.categories.push(category);
                        });
                    });
                }
            },
            rename: function (category) {
                if (undefined !== category.id) {
                    $http.patch('/categories/' + category.id, {name: category.name});
                }
            },
            remove: function (category) {
                if (undefined !== category.id) {
                    $http.delete('/categories/' + category.id).success(function () {
                        removeCategory(category.id);
                    });
                }
            }
        };
    });
})();