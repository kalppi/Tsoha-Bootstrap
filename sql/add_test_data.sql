INSERT INTO forum_user (name, hash, email, accepted) VALUES ('pera', '$2y$12$ef390ec5dc68fcb861e00ODXmJK1nSWBa7BqRux926wDgbLtb6usK', 'pera@luukku.com', TRUE);
INSERT INTO forum_user (name, hash, email, accepted, admin) VALUES ('pirjo', '$2y$12$a275be13642f1bee4d2fcuvkVVRhKTbjgga6dqPIrGPHnwLF8AAC6', 'pirjo@gmail.com', TRUE, TRUE);

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