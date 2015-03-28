(function () {
    var qnEntry = angular.module('qnEntry', []);

    qnEntry.directive('entryList', function () {
        return {
            restrict: 'E',
            templateUrl: 'partials/entry-list.html'
        };
    });

    qnEntry.directive('entryContent', function () {
        return {
            restrict: 'E',
            templateUrl: 'partials/entry-content.html'
        };
    });

    qnEntry.directive('entryFeed', function () {
        return {
            restrict: 'E',
            templateUrl: 'partials/entry-feed.html'
        };
    });

    qnEntry.directive('entryDetails', function () {
        return {
            restrict: 'E',
            templateUrl: 'partials/entry-details.html'
        };
    });

    qnEntry.controller('EntriesController', function ($scope, $routeParams, Entry) {
        $scope.entries = [];

        Entry.query(function (data) {
            $scope.entries = data;
        });

        $scope.selectedEntry = undefined;
        $scope.entryIndex = undefined;

        $scope.expand = function (entry) {
            $scope.selectedEntry = entry;
            if (!entry.read) {
                entry.read = true;
                Entry.markAsRead(entry.id, entry.read);
            }

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

    qnEntry.controller('EntryController', function ($scope, Entry) {
        $scope.toggleSaved = function (entry) {
            entry.saved = !entry.saved;
            Entry.markAsSaved(entry.id, entry.saved);
        };

        $scope.toggleRead = function (entry) {
            entry.read = !entry.read;
            Entry.markAsRead(entry.id, entry.read);
        };

    });

    qnEntry.factory('Entry', function ($http, Feed) {
        var entries = [];

        return {
            query: function(callback) {
                $http.get('/entries').success(function(data) {
                    entries = data;
                    angular.forEach(entries, function(entry) {
                        entry.publicationDate = new Date(entry.publicationDate);
                        Feed.get(entry.feedId, function(data) {
                             entry.feed = data;
                        });
                    });
                    callback(entries);
                });
            },
            markAsRead: function(id, read) {
                $http.patch('/entries/' + id, {
                    read: read
                });
            },
            markAsSaved: function(id, saved) {
                $http.patch('/entries/' + id, {
                    saved: saved
                });
            }
        }
    });
})();