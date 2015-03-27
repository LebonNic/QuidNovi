PRAGMA FOREIGN_KEYS = ON;

CREATE TABLE Component
(
  id          INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  containerId INTEGER, -- Component container id
  name        TEXT                              NOT NULL, -- Component name
  FOREIGN KEY (containerId) REFERENCES Component (id)
);

CREATE TABLE Category
(
  id INTEGER PRIMARY KEY NOT NULL,
  FOREIGN KEY (id) REFERENCES Component (id)
);

CREATE TABLE Feed
(
  id         INTEGER PRIMARY KEY NOT NULL,
  source     TEXT                NOT NULL, -- Feed source url
  lastUpdate DATETIME            NOT NULL, -- Feed last update date
  FOREIGN KEY (id) REFERENCES Component (id)
);

CREATE TABLE Entry
(
  id              INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  feedId          INTEGER                           NOT NULL, -- Entry feed id
  title           TEXT                              NOT NULL, -- Entry title
  summary         TEXT                              NOT NULL, -- Entry summary
  location        TEXT                              NOT NULL, -- Entry url
  publicationDate DATETIME                          NOT NULL, -- Entry publication date
  read            BOOLEAN                           NOT NULL, -- Indicates if entry is read
  saved           BOOLEAN                           NOT NULL, -- Indicates if entry is mark as saved
  FOREIGN KEY (feedId) REFERENCES Feed (id)
);

INSERT INTO Component (containerId, name) VALUES (NULL, 'Awesome Category');
INSERT INTO Component (containerId, name) VALUES (NULL, 'Random Category');
INSERT INTO Component (containerId, name) VALUES (NULL, 'Empty Category');
INSERT INTO Component (containerId, name) VALUES (1, 'Nested Category');
INSERT INTO Component (containerId, name) VALUES (1, 'Inner Nested Category');
INSERT INTO Component (containerId, name) VALUES (1, 'Awesome Feed');
INSERT INTO Component (containerId, name) VALUES (1, '"IT" Feed');
INSERT INTO Component (containerId, name) VALUES (2, 'Random Feed');

INSERT INTO Category (id) VALUES (1);
INSERT INTO Category (id) VALUES (2);
INSERT INTO Category (id) VALUES (3);
INSERT INTO Category (id) VALUES (4);
INSERT INTO Category (id) VALUES (5);

INSERT INTO Feed (id, source, lastUpdate) VALUES (6, 'http://www.awesome-feed.com', datetime('2015-04-21 12:00:00'));
INSERT INTO Feed (id, source, lastUpdate) VALUES (7, 'http://www.it-feed.org', datetime('2015-04-21 12:00:00'));
INSERT INTO Feed (id, source, lastUpdate) VALUES (8, 'http://www.random-feed.fr', datetime('2015-04-21 12:00:00'));

INSERT INTO Entry (feedId, title, summary, location, publicationDate, read, saved) VALUES
  (6, 'Some interesting news',
   'Interesting news about an incredibly important subject. This may change your life forever.',
   'http://www.awesome-feed.com/news/1234',
   datetime('2015-01-12 14:25:16'), 0, 0);
INSERT INTO Entry (feedId, title, summary, location, publicationDate, read, saved) VALUES
  (6, 'An unknown fact that will change you life',
   'Did you known that narwhals were swimming in the ocean, causing a commotion ''cause they are so awesome. We previously saw that narwhals were swimming in the ocean but they are also pretty big and pretty white and they beat a polar bear in a fight. They are like an underwater unicorn, they''ve got a kick-ass facial horn, they are the Jedis of the seas and stop Cthulhu eating ''ye. Narwhals. They are narwhals. Just don''t let them touch your balls. Narwhals. They are narwhals. Inventors of the shich kebab.',
   'http://www.awesome-feed.com/news/42',
   datetime('2015-02-09 12:02:15'), 1, 0);
INSERT INTO Entry (feedId, title, summary, location, publicationDate, read, saved) VALUES
  (7, 'Honey Badger',
   'This is the honey badger. Watch it run in slow motion. It''s pretty badass. Look. It runs all over the place. \"Whoa! Watch out!\" says that bird. Eew, it''s got a snake! Oh! It''s chasing a jackal! Oh my gosh! Oh, the honey badger is just crazy! The honey badger has been referred to by the Guiness Book of World Records as the most fearless animal in the animal kingdom. It really doesn''t give a shit. If it''s hungry, it''s hungry. Eew! What''s that in its mouth? Oh, it''s got a cobra? Oh, it runs backwards? Now watch this: look a snake''s up in the tree. Honey badger don''t care. It just takes what it wants. Whenever it''s hungry it just -- Eew, and it eats, snakes... Watch it dig! Look at that digging. The honey badger is really pretty badass. It has no regard for any other animal whatsoever. Look at him, he''s just grunting, and eating snakes. Eew! What''s that? A mouse? Oh that''s nasty. They''re so nasty. Oh look it''s chasing things and eating them. The honey badgers have a fairly long body, but a distinctly thickset broad shoulders, and, you know, their skin is loose, allowing them to move about freely, and they twist around. Now look: Here''s a house full of bees. Do you think the honey badger cares? It doesn''t give a shit, it goes right into the house of bees to get some larvae. How disgusting is that? It eats larvae. Eew, that''s so nasty. But look! The honey badger doesn''t care! It''s getting stung like a thousand times. It doesn''t give a shit. It''s just hungry. It doesn''t care about being stung by bees. Nothing can stop the honey badger when it''s hungry. What a crazy fuck! Look, it''s eating larvae, that''s disgusting. It''s running in slow-motion again. See?',
   'http://www.it-feed.org/entry/1337',
   datetime('2015-03-15 13:37:42'), 0, 1);
INSERT INTO Entry (feedId, title, summary, location, publicationDate, read, saved) VALUES
  (8, 'Nyan',
   'Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan Nyan.',
   'http://www.random-feed.fr/news/1234',
   datetime('2015-04-11 03:14:15'), 1, 1);

