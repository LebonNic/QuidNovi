(function () {
    var qnEntry = angular.module('qnEntry', []);

    qnEntry.directive('entryList', function () {
        return {
            restrict: 'E',
            templateUrl: 'partials/entry-list.html'
        };
    });

    qnEntry.directive('entryContent', function() {
        return {
            restrict: 'E',
            templateUrl: 'partials/entry-content.html'
        };
    });

    qnEntry.directive('entryFeed', function() {
        return {
            restrict: 'E',
            templateUrl: 'partials/entry-feed.html'
        };
    });

    qnEntry.directive('entryDetails', function() {
        return {
            restrict: 'E',
            templateUrl: 'partials/entry-details.html'
        };
    });

    qnEntry.controller('EntriesController', function ($scope, $routeParams, $document, Entry, Feed) {
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
    });

    qnEntry.controller('EntryController', function ($scope) {
        $scope.toggleSaved = function (entry) {
            entry.saved = !entry.saved;
        };

        $scope.toggleRead = function (entry) {
            entry.read = !entry.read;
        };

    });

    qnEntry.factory('Entry', function (Category) {
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
})();