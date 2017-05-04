CREATE TABLE forum_user (
	id SERIAL PRIMARY KEY,
	name CITEXT UNIQUE NOT NULL,
	hash VARCHAR(255) NOT NULL,
	email VARCHAR(100) UNIQUE NOT NULL,
	admin BOOLEAN DEFAULT FALSE NOT NULL,
	registered TIMESTAMPTZ DEFAULT now() NOT NULL,
	deleted BOOLEAN DEFAULT FALSE NOT NULL
);

CREATE TABLE forum_category (
	id SERIAL PRIMARY KEY,
	name VARCHAR(100) UNIQUE NOT NULL,
	simplename VARCHAR(100) UNIQUE NOT NULL,
	"order" INTEGER NOT NULL
);

CREATE TABLE forum_thread (
	id SERIAL PRIMARY KEY,
	category_id INTEGER REFERENCES forum_category (id) ON UPDATE CASCADE ON DELETE CASCADE,
	title VARCHAR(200) NOT NULL,
	message_count INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE forum_message (
	id SERIAL PRIMARY KEY,
	thread_id INTEGER REFERENCES forum_thread (id) ON UPDATE CASCADE ON DELETE CASCADE,
	parent_id INTEGER REFERENCES forum_message (id) ON UPDATE CASCADE ON DELETE CASCADE DEFAULT NULL,
	user_id INTEGER REFERENCES forum_user (id) ON UPDATE CASCADE ON DELETE CASCADE,
	sent TIMESTAMPTZ DEFAULT NOW() NOT NULL,
	message TEXT NOT NULL,
	deleted BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE forum_thread_read (
	id SERIAL PRIMARY KEY,
	thread_id INTEGER REFERENCES forum_thread (id) ON UPDATE CASCADE ON DELETE CASCADE,
	user_id INTEGER REFERENCES forum_user (id) ON UPDATE CASCADE ON DELETE CASCADE,
	last_message_id INTEGER,
	UNIQUE (thread_id, user_id)
);

CREATE TABLE forum_login_token (
	id SERIAL PRIMARY KEY,
	token VARCHAR(64) NOT NULL,
	user_id INTEGER REFERENCES forum_user (id) ON UPDATE CASCADE ON DELETE CASCADE,
	last_active TIMESTAMPTZ DEFAULT NOW() NOT NULL
);

CREATE OR REPLACE RULE set_user_deleted_flag AS
	ON DELETE TO forum_user
	DO INSTEAD
		UPDATE forum_user SET deleted = TRUE WHERE id = OLD.id;

CREATE OR REPLACE RULE set_message_deleted_flag AS
	ON DELETE TO forum_message
	DO INSTEAD
		UPDATE forum_message SET deleted = TRUE WHERE id = OLD.id;

CREATE OR REPLACE FUNCTION generate_simplename() RETURNS TRIGGER AS $$
BEGIN
	NEW.simplename = REPLACE(LOWER(NEW.name), ' ', '-');
	RETURN NEW;
END
$$ LANGUAGE plpgsql;


CREATE TRIGGER generate_simplename_trigger
	BEFORE INSERT ON forum_category
	FOR EACH ROW
		EXECUTE PROCEDURE generate_simplename();

CREATE OR REPLACE FUNCTION increase_message_count() RETURNS TRIGGER AS $$
BEGIN
	UPDATE forum_thread SET message_count = message_count + 1
		WHERE id = NEW.thread_id;

	RETURN NEW;
END
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION decrease_message_count() RETURNS TRIGGER AS $$
BEGIN
	UPDATE forum_thread SET message_count = message_count - 1
		WHERE id = OLD.thread_id;

	RETURN OLD;
END
$$ LANGUAGE plpgsql;

CREATE TRIGGER increase_message_count_trigger
	AFTER INSERT ON forum_message
	FOR EACH ROW
		EXECUTE PROCEDURE increase_message_count();

CREATE TRIGGER decrease_message_count_trigger
	AFTER DELETE ON forum_message
	FOR EACH ROW
		EXECUTE PROCEDURE decrease_message_count();

CREATE OR REPLACE FUNCTION move_up(tablename TEXT, moveid INTEGER) RETURNS VOID AS $$
BEGIN
	EXECUTE 'UPDATE ' || quote_ident(tablename) || ' c
	SET "order" =
		CASE c."order"
			WHEN x."order" THEN x."order" - 1
			WHEN x."order" - 1 THEN x."order"
		END
	FROM (SELECT id, "order"
		FROM ' || quote_ident(tablename) || '
		WHERE id = $1
		AND "order" > 1) x
	WHERE c."order" = x."order" OR c."order" = x."order" - 1' USING moveid;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION move_down(tablename TEXT, moveid INTEGER) RETURNS VOID AS $$
BEGIN
	EXECUTE 'UPDATE ' || quote_ident(tablename) || ' c
	SET "order" =
		CASE c."order"
			WHEN x."order" THEN x."order" + 1
			WHEN x."order" + 1 THEN x."order"
		END
	FROM (SELECT id, "order"
		FROM ' || quote_ident(tablename) || '
		WHERE id = $1
		AND "order" < (SELECT MAX("order") FROM forum_category)) x
	WHERE c."order" = x."order" OR c."order" = x."order" + 1' USING moveid;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION move_to(tablename TEXT, moveid INTEGER, toposition INTEGER) RETURNS VOID AS $$
DECLARE
	max INTEGER;
BEGIN
	EXECUTE 'SELECT MAX("order") FROM ' || quote_ident(tablename) INTO max;

	IF toposition >= 1 AND toposition <= max THEN
		EXECUTE 'UPDATE ' || quote_ident(tablename) || ' c
		SET "order" = CASE
			WHEN c."order" = x."order" THEN $2
			WHEN x."order" < $2 THEN c."order" - 1
			ELSE c."order" + 1
		END
		FROM (
			SELECT "order"
			FROM ' || quote_ident(tablename) || '
			WHERE id = $1
		) x
		WHERE
			CASE WHEN x."order" < 2 THEN
				c."order" >= x."order" AND c."order" <= $2
			ELSE
				c."order" <= x."order" AND c."order" >= $2
			END' USING moveid, toposition;
	END IF;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION set_order() RETURNS TRIGGER AS $$
DECLARE
	val INTEGER;
BEGIN
	IF NEW."order" IS NULL THEN
		EXECUTE 'SELECT COALESCE(MAX("order") + 1, 1) AS "order" FROM ' || quote_ident(TG_ARGV[0]) INTO val;
		NEW."order" := val;
	END IF;
	
	RETURN NEW;
END
$$ LANGUAGE plpgsql;

CREATE TRIGGER set_order_trigger
	BEFORE INSERT ON forum_category
	FOR EACH ROW
		EXECUTE PROCEDURE set_order('forum_category');