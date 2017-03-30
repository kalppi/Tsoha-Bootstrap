INSERT INTO forum_user (name, hash, email, accepted) VALUES ('pera', '$2a$12$b95d8162803b88d3a857cenSizJpkTiLoYYPXrYlcs/CbC2LHGqZS', 'pera@luukku.com', TRUE);
INSERT INTO forum_user (name, hash, email, accepted, admin) VALUES ('pirjo', '$2a$12$d2a5585d1aa87b20ced77OX2ICyK14WgEPJYJfl3lVD8IqWuk9yjO', 'pirjo@gmail.com', TRUE, TRUE);

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