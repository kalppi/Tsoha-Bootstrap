INSERT INTO forum_user (name, hash, email, accepted) VALUES ('pera', '$2a$12$b95d8162803b88d3a857cenSizJpkTiLoYYPXrYlcs/CbC2LHGqZS', 'pera@luukku.com', TRUE);
INSERT INTO forum_user (name, hash, email, accepted, admin) VALUES ('pirjo', '$2a$12$d2a5585d1aa87b20ced77OX2ICyK14WgEPJYJfl3lVD8IqWuk9yjO', 'pirjo@gmail.com', TRUE, TRUE);
INSERT INTO forum_user (name, hash, email, accepted) VALUES ('merja', '$2a$12$b95d8162803b88d3a857cenSizJpkTiLoYYPXrYlcs/CbC2LHGqZS', 'merja@gmail.com', TRUE);
INSERT INTO forum_user (name, hash, email, accepted) VALUES ('ilpo', '$2a$12$b95d8162803b88d3a857cenSizJpkTiLoYYPXrYlcs/CbC2LHGqZS', 'ilpo@luukku.com', FALSE);
INSERT INTO forum_user (name, hash, email, accepted) VALUES ('irma', '$2a$12$b95d8162803b88d3a857cenSizJpkTiLoYYPXrYlcs/CbC2LHGqZS', 'irma@gmail.com', TRUE);

INSERT INTO forum_category (name) VALUES ('Yleinen'), ('Vapaa-aika');

INSERT INTO forum_thread (category_id, title) VALUES (1, 'Aaaaaaaa'), (2, 'Bbbbbbbb'), (1, 'Ccccccccc');

INSERT INTO forum_message (thread_id, user_id, message, sent) VALUES
	(1, 1, 'Moro', now() - INTERVAL '4 hours'),
	(1, 2, 'Terse', now() - INTERVAL '3 hours'),
	(2, 2, 'Jahas', now() - INTERVAL '2 hours'),
	(2, 1, 'Joo...', now() - INTERVAL '1 hours'),
	(3, 3, 'Jepa', now() - INTERVAL '1 hours 30 minutes');

INSERT INTO forum_thread_read (thread_id, user_id, last_message_id) VALUES
	(1, 1, 2),
	(1, 2, 1),
	(2, 1, 2),
	(2, 2, 2),
	(1, 3, 2),
	(3, 1, 5);