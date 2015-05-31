BEGIN TRANSACTION;

CREATE TABLE "authors" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "name" text NOT NULL,
  "rating" integer NOT NULL
);

INSERT INTO "authors" ("id", "name", "rating") VALUES (1, 'Ivan Ivanov', 7);
INSERT INTO "authors" ("id", "name", "rating") VALUES (2, 'Petr Petrov', 9);

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

CREATE TABLE "answers" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "question_id" integer NOT NULL,
  "content" text NOT NULL,
  "sort" integer NOT NULL,
  FOREIGN KEY ("question_id") REFERENCES "questions" ("id") ON DELETE CASCADE ON UPDATE CASCADE
);

COMMIT;
