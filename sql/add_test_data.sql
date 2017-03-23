INSERT INTO forum_user (name, hash, email) VALUES ('pera', '', 'pera@luukku.com');
INSERT INTO forum_user (name, hash, email, admin) VALUES ('pirjo', '', 'pirjo@gmail.com', TRUE);

INSERT INTO forum_category (name) VALUES ('Yleinen'), ('Vapaa-aika');

INSERT INTO forum_thread (category_id) VALUES (1), (2);

INSERT INTO forum_message (thread_id, user_id, message) VALUES
	(1, 1, 'Moro'),
	(1, 2, 'Terse'),
	(2, 2, 'Jahas'),
	(2, 1, 'Joo...');

INSERT INTO forum_thread_read (thread_id, user_id, last_message_id) VALUES
	(1, 1, 2),
	(1, 2, 1),
	(2, 1, 2),
	(2, 2, 2);