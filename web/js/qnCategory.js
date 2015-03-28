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

    qnCategory.controller('CategoryEditionDialogController', function ($scope, $mdDialog, Category) {
        $scope.remove = function () {
            $mdDialog.hide(false);
        };
        $scope.close = function () {
            $mdDialog.hide(true);
        };
    });

    qnCategory.factory('Category', function ($http) {
        var categories;
        var pendingQuery;

        function findCategory(id) {
            for (var i = 0, length = categories.length; i < length; ++i) {
                if (id === categories[i].id) {
                    return categories[i];
                }
            }
        }

        return {
            query: function (callback) {
                if (undefined !== categories) {
                    return callback(categories);
                }
                // Get all query or subscribe to pending query
                if (undefined === pendingQuery) {
                    console.log('Querying /categories');
                    pendingQuery = $http.get('/categories');
                    pendingQuery.success(function (data) {
                        categories = data;
                        angular.forEach(data, function (category) {
                            category.url = '/categories/' + category.id;
                        });
                        pendingQuery = undefined;
                        callback(categories);
                    });
                } else {
                    pendingQuery.then(function () {
                        callback(categories);
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
                    $http.post('/categories', category).success(function (data) {
                        console.log('Category ' + data.uri + ' created.');
                        $http.get(data.uri).success(function(category) {
                            category.url = '/categories/' + category.id;
                            categories.push(category);
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
                    $http.delete('/categories/' + category.id).success(function() {
                        for (var i = 0, length = categories.length; i < length; ++i) {
                            if (categories[i].id ===  category.id) {
                                return categories.splice(i, 1);
                            }
                        }
                    });
                }
            }
        };
    });
})();