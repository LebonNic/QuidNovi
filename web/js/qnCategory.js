(function () {
    var qnCategory = angular.module('qnCategory', []);

    qnCategory.controller('CategoryController', function ($scope, $routeParams, $location, $mdDialog, Entry, Feed, Category) {
        $scope.category = Category.find($routeParams.category);

        if ($scope.category === undefined) {
            $location.url('/error');
        }

        $scope.showEditDialog = function ($event) {
            $mdDialog.show({
                controller: 'DialogController',
                templateUrl: 'partials/category-dialog.html',
                targetEvent: $event
            }).then(function (answer) {
                $scope.alert = 'You said the information was "' + answer + '".';
            }, function () {
                $scope.alert = 'You cancelled the dialog.';
            });
        };
    });

    qnCategory.factory('Category', function (Feed) {
        var categories = [{
            id: 4,
            name: 'Awesome Category',
            url: '/categories/4',
            categories: [{
                id: 7,
                name: 'Nested Category',
                url: '/categories/7',
                categories: [{
                    id: 8,
                    name: 'Inner Nested Category',
                    url: '/categories/8',
                    categories: [],
                    feeds: []
                }],
                feeds: []
            }],
            feeds: [Feed.find(1), Feed.find(2)]
        }, {
            id: 5,
            name: 'Random Category',
            url: '/categories/5',
            categories: [],
            feeds: [Feed.find(3)]
        }, {
            id: 6,
            name: 'Empty Category',
            url: '/categories/6',
            categories: [],
            feeds: []
        }];

        function searchCategoryWithId(searchCategories, id) {
            for (var i = 0, length = searchCategories.length; i < length; ++i) {
                var category = searchCategories[i];
                if (id === category.id) {
                    return category;
                }
                var depthSearch = searchCategoryWithId(category.categories, id);
                if (depthSearch !== undefined) {
                    return depthSearch;
                }
            }
            return undefined;
        }

        function searchFeedInCategory(category, id) {
            for (var i = 0, length = category.feeds.length; i < length; ++i) {
                if (category.feeds[i].id === id) {
                    return category.feeds[i];
                }
            }
            return undefined;
        }

        function searchRecursivelyFeedInCategory(category, id) {
            if (searchFeedInCategory(category, id) !== undefined) {
                return true;
            }
            for (var i = 0, length = category.categories.length; i < length; ++i) {
                var innerCategory = category.categories[i];
                if (searchFeedInCategory(innerCategory, id) !== undefined) {
                    return true;
                }
                if (searchRecursivelyFeedInCategory(innerCategory, id)) {
                    return true;
                }
            }
            return false;
        }

        return {
            findAll: function () {
                return categories;
            },
            find: function (id) {
                id = parseInt(id);
                return searchCategoryWithId(categories, id);
            },
            contains: function (categoryId, id) {
                var category = searchCategoryWithId(categories, categoryId);
                return searchRecursivelyFeedInCategory(category, id);
            }
        }
    });
})();