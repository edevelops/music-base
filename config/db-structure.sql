CREATE TABLE sqlite_sequence(name,seq);
CREATE TABLE albums (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "title" TEXT,
    "year" TEXT
);
CREATE TABLE artists (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "title" TEXT NOT NULL
);
CREATE TABLE track_artists (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "track_id" INTEGER NOT NULL,
    "artist_id" INTEGER NOT NULL
);
CREATE TABLE tracks (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "title" TEXT NOT NULL,
    "album_id" INTEGER,
    "version" TEXT,
    "has_file" INTEGER,
    "duration" REAL,
    "bitrate" REAL
);

CREATE TABLE tags (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "title" TEXT NOT NULL,
    "color" TEXT NOT NULL
);

CREATE TABLE track_tags (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "track_id" INTEGER NOT NULL,
    "tag_id" INTEGER NOT NULL
);
