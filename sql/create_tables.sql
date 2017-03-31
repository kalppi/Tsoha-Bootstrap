CREATE TABLE forum_user (
	id SERIAL PRIMARY KEY,
	name VARCHAR(50) UNIQUE,
	hash VARCHAR(255),
	email VARCHAR(100),
	accepted BOOLEAN DEFAULT FALSE,
	admin BOOLEAN DEFAULT FALSE,
	registered TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE forum_category (
	id SERIAL PRIMARY KEY,
	name VARCHAR(100) UNIQUE
);

CREATE TABLE forum_thread (
	id SERIAL PRIMARY KEY,
	category_id INTEGER REFERENCES forum_category (id),
	title VARCHAR(200)
);

CREATE TABLE forum_message (
	id SERIAL PRIMARY KEY,
	thread_id INTEGER REFERENCES forum_thread (id),
	parent_id INTEGER REFERENCES forum_message (id) DEFAULT NULL,
	user_id INTEGER REFERENCES forum_user (id),
	sent TIMESTAMPTZ DEFAULT NOW(),
	message TEXT
);

CREATE TABLE forum_thread_read (
	id SERIAL PRIMARY KEY,
	thread_id INTEGER REFERENCES forum_thread (id),
	user_id INTEGER REFERENCES forum_user (id),
	last_message_id INTEGER REFERENCES forum_message (id),
	UNIQUE (thread_id, user_id)
);

CREATE TABLE forum_login_token (
	id SERIAL PRIMARY KEY,
	token VARCHAR(64),
	user_id INTEGER REFERENCES forum_user (id),
	last_active TIMESTAMPTZ DEFAULT NOW()
);