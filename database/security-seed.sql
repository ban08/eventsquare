-- Security tables
-- Creates tables only if they don't exist

CREATE TABLE IF NOT EXISTS security_question (
    id_security_question INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    prompt TEXT NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS security_answer (
    id_security_answer INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_user INTEGER NOT NULL REFERENCES "user"(id_user) ON UPDATE CASCADE ON DELETE CASCADE,
    question TEXT NOT NULL,
    answer_hash TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    CHECK (updated_at IS NULL OR updated_at >= created_at)
);

-- Suggested questions 
INSERT INTO security_question (prompt)
SELECT 'What is the name of a teacher you will never forget?'
WHERE NOT EXISTS (SELECT 1 FROM security_question WHERE prompt = 'What is the name of a teacher you will never forget?');

INSERT INTO security_question (prompt)
SELECT 'What city did you visit on your first trip?'
WHERE NOT EXISTS (SELECT 1 FROM security_question WHERE prompt = 'What city did you visit on your first trip?');

INSERT INTO security_question (prompt)
SELECT 'What was your first pet''s name?'
WHERE NOT EXISTS (SELECT 1 FROM security_question WHERE prompt = 'What was your first pet''s name?');

INSERT INTO security_question (prompt)
SELECT 'What is your favorite childhood nickname?'
WHERE NOT EXISTS (SELECT 1 FROM security_question WHERE prompt = 'What is your favorite childhood nickname?');

INSERT INTO security_question (prompt)
SELECT 'What is the title of a book you loved as a kid?'
WHERE NOT EXISTS (SELECT 1 FROM security_question WHERE prompt = 'What is the title of a book you loved as a kid?');