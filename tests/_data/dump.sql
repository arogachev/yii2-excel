BEGIN TRANSACTION;

CREATE TABLE "authors" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "name" text NOT NULL
);

INSERT INTO "authors" ("id", "name") VALUES (1, 'Ivan Ivanov');
INSERT INTO "authors" ("id", "name") VALUES (2, 'Petr Petrov');

CREATE TABLE "tests" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "name" text NOT NULL,
  "type" integer NOT NULL,
  "description" text NOT NULL,
  "author_id" integer NOT NULL
);

INSERT INTO "tests" ("id", "name", "type", "description", "author_id") VALUES (1, 'Common test', 1, '<p>This is the common test</p>', 1);

CREATE TABLE "questions" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "test_id" integer NOT NULL,
  "content" text NOT NULL,
  "sort" integer NOT NULL,
  FOREIGN KEY ("test_id") REFERENCES "tests" ("id") ON DELETE CASCADE ON UPDATE CASCADE
);

COMMIT;
