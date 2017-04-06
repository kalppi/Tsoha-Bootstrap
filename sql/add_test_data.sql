INSERT INTO forum_user (name, hash, email, accepted)
	VALUES ('pera', '$2a$12$b95d8162803b88d3a857cenSizJpkTiLoYYPXrYlcs/CbC2LHGqZS', 'pera@luukku.com', TRUE);
INSERT INTO forum_user (name, hash, email, accepted, admin)
	VALUES ('pirjo', '$2a$12$d2a5585d1aa87b20ced77OX2ICyK14WgEPJYJfl3lVD8IqWuk9yjO', 'pirjo@luukku.com', TRUE, TRUE);

INSERT INTO forum_category (name, "order") VALUES ('Aaaa aaa aa', 1), ('Bbb bbb', 2), ('Ccc cccc', 3);

CREATE OR REPLACE FUNCTION pick_random(data TEXT[]) RETURNS TEXT AS $$
BEGIN
	RETURN data[random_int(1, array_length(data, 1))];
END;
$$ LANGUAGE 'plpgsql';


CREATE OR REPLACE FUNCTION random_int(min INTEGER, max INTEGER) RETURNS INTEGER AS $$
BEGIN
	RETURN  min + (random() * (max - min))::int;
END;
$$ LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION generate_text() RETURNS TEXT AS $$
DECLARE
	lines text[];
	text text[];
BEGIN
	lines := '{"Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
		"Suspendisse mollis auctor tellus, a pretium lorem vulputate vitae.",
		"Nulla nibh urna, congue posuere egestas ut, iaculis id erat.",
		"Proin laoreet sem vitae bibendum suscipit.", 
		"Nullam congue lacus blandit, scelerisque ipsum ut, cursus est.",
		"Sed nec massa ut dolor sagittis tempus vel id mauris.",
		"Sed nec purus lacus. Quisque vulputate venenatis sapien vel scelerisque.",
		"Nam suscipit viverra ante, a vehicula tortor fermentum eu.",
		"Nunc viverra sapien sed nunc scelerisque, et porttitor nunc interdum.",
		"Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
		"Cras malesuada quam nec commodo egestas.",
		"Maecenas molestie mauris iaculis risus vehicula, sit amet efficitur tellus tristique.",
		"Cras sapien orci, gravida nec nisl sed, lobortis facilisis dolor.",
		"Aliquam fermentum euismod nulla sit amet interdum."}'::text[];

	FOR paragraph IN 1 .. (SELECT 1 + (random() * 2)::int)
	LOOP
		FOR sentence IN 1 .. (SELECT 2 + (random() * 5)::int)
		LOOP
			text := text || lines[(random() * array_length(lines, 1))::int];
		END LOOP;

		text := text || E'\n'::text;
	END LOOP;

	RETURN array_to_string(text, ' ');
END;
$$ LANGUAGE 'plpgsql';



CREATE OR REPLACE FUNCTION generate_users(count INTEGER) RETURNS VOID AS $$
DECLARE
	fnames text[];
	lnames text[];
BEGIN
	fnames := '{"Pera", "Irmeli", "Matti", "Tuomas", "Pentti", "Marja", "Heikki", "Iina", "Teemu", "Masa"}'::text[];
	lnames := '{"Kauppinen", "Laukkanen", "Kullervoinen", "Nikula", "Taalasmaa", "Koski", "Liukkonen", "Latu"}'::text[];

	WITH names AS 
		(SELECT
			concat(pick_random(fnames), ' ' , pick_random(lnames)) AS name
		FROM generate_series(1, count))

	 INSERT INTO forum_user (name, hash, email, registered, accepted)
		SELECT DISTINCT ON (name)
			name,
			'$2a$12$b95d8162803b88d3a857cenSizJpkTiLoYYPXrYlcs/CbC2LHGqZS' AS hash,
			REPLACE(LOWER(name), ' ', '.') || '@' || pick_random('{"luukku.com", "gmail.com", "hotmail.com"}'::text[]) AS email,
			NOW() - '1 year'::interval * random(),
			TRUE
		FROM names;
END;
$$ LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION generate_messages(thread_id INTEGER, parent_id INTEGER, count INTEGER, t FLOAT) RETURNS VOID AS $$
DECLARE
	message_id INTEGER;
	msg_user_id INTEGER;
BEGIN
	IF parent_id IS NULL THEN
		INSERT INTO forum_message (thread_id, parent_id, user_id, sent, message)
			VALUES (
				thread_id,
				parent_id,
				random_int(1, (SELECT MAX(id) FROM forum_user)),
				NOW() - ('1 month'::interval * t) - '3 days'::interval * random(),
				generate_text())
			RETURNING id, user_id INTO parent_id, msg_user_id;

		INSERT INTO
			forum_thread_read (thread_id, user_id, last_message_id)
		VALUES (thread_id, msg_user_id, 1);

		INSERT INTO
			forum_thread_read (thread_id, user_id, last_message_id)
		SELECT thread_id, u.id, 1
			FROM
				(SELECT
					id
				FROM
					forum_user
				ORDER BY
					random()
				LIMIT
					random_int(2, (SELECT MAX(id) FROM forum_user))) AS u
		ON CONFLICT DO NOTHING;
	END IF;

	FOR i IN 1 .. (SELECT count)
	LOOP
		INSERT INTO forum_message (thread_id, parent_id, user_id, sent, message)
			VALUES (
				thread_id,
				parent_id,
				random_int(1, (SELECT MAX(id) FROM forum_user)),
				NOW() - ('1 month'::interval * t),
				generate_text())
			RETURNING id INTO message_id;

		IF random() > 0.7 THEN
			PERFORM generate_messages(thread_id, message_id, count - 1, t * 0.9);
		END IF;
	END LOOP;
END;
$$ LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION generate_threads(count INTEGER, message_min INTEGER, message_max INTEGER) RETURNS VOID AS $$
DECLARE
	ids INTEGER[];
BEGIN
	WITH inserts AS (INSERT INTO forum_thread (title, category_id)
		SELECT 'ketju-' || i, 1 + (random() * ((SELECT MAX(id) FROM forum_category) - 1))::int
		FROM generate_series(1, count) series(i) RETURNING id)
	SELECT array_agg(id) FROM inserts INTO ids;

	FOR thread_id IN 1 .. array_upper(ids, 1)
	LOOP
		PERFORM generate_messages(thread_id, NULL, random_int(message_min, message_max), 1);
	END LOOP;
END;
$$ LANGUAGE 'plpgsql';

SELECT generate_users(50);
SELECT generate_threads(200, 3, 7);