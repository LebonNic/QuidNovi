(function () {
    var qnCategory = angular.module('qnCategory', []);

    qnCategory.controller('CategoryController', function ($scope, $routeParams, $location, $mdDialog, Entry, Feed, Category) {
        $scope.category = undefined;
        Category.get($routeParams.category, function (data) {
            $scope.category = data;
        });

        $scope.showEditDialog = function ($event) {
            $mdDialog.show({
                controller: 'CategoryEditionDialogController',
                templateUrl: 'partials/category-dialog.html',
                targetEvent: $event
            }).then(function (answer) {
                $scope.alert = 'You said the information was "' + answer + '".';
            }, function () {
                $scope.alert = 'You cancelled the dialog.';
            });
        };
    });

    qnCategory.controller('CategoryEditionDialogController', function ($scope, $mdDialog) {
        $scope.close = function () {
            $mdDialog.hide();
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
            query: function(callback) {
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
                    pendingQuery.then(function() {
                        callback(categories);
                    });
                }
            },
            get: function(id, callback) {
                // All categories are loaded on application startup. We don't make any other network calls.
                var category = findCategory(id);
                callback(category);
            }
        };
    });
})();