// Definition of QuidNovi module.
(function () {
    var app = angular.module('QuidNovi', ['ngRoute', 'ngMaterial'])

        .factory('Entry', function (Category) {
            var entries = [{
                id: 1,
                title: "Some interesting news",
                description: "Interesting news about an incredibly important subject. This may change your life forever.",
                publicationDate: new Date(2015, 1, 31, 11, 45),
                feedId: 1,
                url: "http://www.google.com",
                read: false,
                saved: false
            }, {
                id: 2,
                title: "An unknown fact that will change you life",
                description: "Did you known that narwhals were swimming in the ocean, causing a commotion 'cause they are so awesome. " +
                "We previously saw that narwhals were swimming in the ocean but they are also pretty big and pretty white and they beat a polar bear in a fight." +
                "They are like an underwater unicorn, they've got a kick-ass facial horn, they are the Jedis of the seas and stop Cthulhu eating 'ye." +
                "Narwhals. They are narwhals. Just don't let them touch your balls." +
                "Narwhals. They are narwhals. Inventors of the shich kebab.",
                publicationDate: new Date(2015, 1, 15, 13, 50),
                feedId: 1,
                url: "http://www.google.com",
                read: true,
                saved: false
            }, {
                id: 3,
                title: "Honey Badger",
                description: "This is the honey badger. Watch it run in slow motion. It's pretty badass. " +
                "Look. It runs all over the place. \"Whoa! Watch out!\" says that bird. Eew, it's got a snake! " +
                "Oh! It's chasing a jackal! Oh my gosh! Oh, the honey badger is just crazy! " +
                "The honey badger has been referred to by the Guiness Book of World Records as the most fearless animal in the animal kingdom. " +
                "It really doesn't give a shit. If it's hungry, it's hungry. Eew! What's that in its mouth? Oh, it's got a cobra? " +
                "Oh, it runs backwards? Now watch this: look a snake's up in the tree. Honey badger don't care. It just takes what it wants. " +
                "Whenever it's hungry it just -- Eew, and it eats, snakes... Watch it dig! Look at that digging. " +
                "The honey badger is really pretty badass. It has no regard for any other animal whatsoever. " +
                "Look at him, he's just grunting, and eating snakes. Eew! What's that? A mouse? Oh that's nasty. They're so nasty. " +
                "Oh look it's chasing things and eating them. The honey badgers have a fairly long body, but a distinctly thickset broad shoulders," +
                " and, you know, their skin is loose, allowing them to move about freely, and they twist around. " +
                "Now look: Here's a house full of bees. Do you think the honey badger cares? " +
                "It doesn't give a shit, it goes right into the house of bees to get some larvae. How disgusting is that? It eats larvae. " +
                "Eew, that's so nasty. But look! The honey badger doesn't care! It's getting stung like a thousand times. " +
                "It doesn't give a shit. It's just hungry. It doesn't care about being stung by bees. " +
                "Nothing can stop the honey badger when it's hungry. What a crazy fuck! Look, it's eating larvae, that's disgusting. " +
                "It's running in slow-motion again. See?",
                publicationDate: new Date(2015, 1, 7, 9, 40),
                feedId: 2,
                url: "http://www.google.com",
                read: false,
                saved: true
            }, {
                id: 4,
                title: "Nyan",
                description: "Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan",
                publicationDate: new Date(2015, 0, 15, 6, 20),
                feedId: 3,
                url: "http://www.google.com",
                read: true,
                saved: true
            }];

            return {
                findAll: function (args) {
                    var result = [];
                    var read = (args.read === undefined) ? undefined : (args.read === 'true');
                    var saved = (args.saved === undefined) ? undefined : (args.saved === 'true');
                    var feed = (args.feed === undefined) ? undefined : parseInt(args.feed);
                    var category = (args.category === undefined) ? undefined : parseInt(args.category);
                    var offset = parseInt(args.offset) || 0;
                    var max = parseInt(args.max) || 20;

                    angular.forEach(entries, function (entry) {
                        var valid = (read === undefined || entry.read === read) &&
                            (saved === undefined || entry.saved === saved) &&
                            (feed === undefined || entry.feedId === feed) &&
                            (category === undefined || Category.contains(category, entry.feedId));
                        if (valid) {
                            result.push(entry);
                        }
                    });

                    return result.slice(offset, offset + max);
                },
                find: function (id) {
                    id = parseInt(id);
                    var searchedEntry = undefined;
                    angular.forEach(entries, function (entry) {
                        if (entry.id === id) {
                            searchedEntry = entry;
                        }
                    });
                    return searchedEntry;
                }
            };
        })
        .factory('Feed', function () {
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
        })
        .factory('Category', function (Feed) {
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
        })
        .factory('RecursionHelper', ['$compile', function ($compile) {
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
        }])

        .controller('AppController', function ($scope, $location, $mdSidenav, Category) {
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
        })

        .directive('menuLink', function () {
            return {
                restrict: 'E',
                scope: {
                    section: '='
                },
                templateUrl: 'partials/menu-link.html',
                controller: 'AppController'
            };
        })
        .directive('menuToggle', function (RecursionHelper) {
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
        })
        .directive('entryList', function () {
            return {
                restrict: 'E',
                templateUrl: 'partials/entry-list.html'
            };
        })

        .controller('EntriesController', function ($scope, $routeParams, $document, Entry, Feed) {
            $scope.entries = Entry.findAll({
                offset: $routeParams.offset,
                max: $routeParams.max,
                read: $routeParams.read,
                saved: $routeParams.saved,
                feed: $routeParams.feed,
                category: $routeParams.category
            });

            angular.forEach($scope.entries, function (entry) {
                if (!entry.feed) {
                    entry.feed = Feed.find(entry.feedId);
                }
            });

            $scope.selectedEntry = undefined;
            $scope.entryIndex = undefined;

            $scope.expand = function (entry) {
                $scope.selectedEntry = entry;
                entry.read = true;

                // Search corresponding index in loaded entries
                for (var entryIndex = 0, entryCount = $scope.entries.length; entryIndex < entryCount; ++entryIndex) {
                    if ($scope.entries[entryIndex] === entry) {
                        $scope.entryIndex = entryIndex;
                        return;
                    }
                }
            };

            $scope.collapse = function (entry, $event) {
                $scope.selectedEntry = undefined;
                $event.stopPropagation();
            };

            $scope.isExpanded = function (entry) {
                return entry === $scope.selectedEntry;
            };
        })
        .controller('EntryController', function ($scope) {
            $scope.toggleSaved = function (entry) {
                entry.saved = !entry.saved;
            };

            $scope.toggleRead = function (entry) {
                entry.read = !entry.read;
            };

        })
        .controller('FeedController', function ($scope, $routeParams, $location, $mdDialog, Entry, Feed) {
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
        })
        .controller('CategoryController', function ($scope, $routeParams, $location, $mdDialog, Entry, Feed, Category) {
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
        })

        .controller('DialogController', function ($scope, $mdDialog) {
            $scope.hide = function () {
                $mdDialog.hide();
            };
            $scope.cancel = function () {
                $mdDialog.cancel();
            };
            $scope.answer = function (answer) {
                $mdDialog.hide(answer);
            };
        })

        .config(function ($routeProvider) {
            //$location.html5Mode(true);
            // Route configuration
            $routeProvider
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

                // By default, go to list of last entries
                .otherwise({redirectTo: '/error'})
        })
})();