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
  "author_id" integer NOT NULL
);

INSERT INTO "tests" ("id", "name", "type", "author_id") VALUES (1, 'Common test', 1, 1);

COMMIT;
