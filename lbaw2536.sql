
DROP SCHEMA IF EXISTS lbaw2536 CASCADE;
CREATE SCHEMA lbaw2536;
SET search_path TO lbaw2536;



-----------------------------------------------------
--Types
-----------------------------------------------------

CREATE TYPE account_status AS ENUM ('active', 'blocked', 'deleted');
CREATE TYPE event_visibility AS ENUM ('public', 'private');
CREATE TYPE notification_message AS ENUM ('invited', 'event updated');
CREATE TYPE event_status AS ENUM ('published', 'completed', 'canceled', 'draft', 'deleted');
CREATE TYPE invitation_status AS ENUM ('pending', 'accepted', 'declined', 'canceled', 'expired');
CREATE TYPE application_status AS ENUM ('pending', 'approved', 'rejected', 'canceled');
CREATE TYPE report_status AS ENUM ('dismissed', 'open');
CREATE TYPE vote_value AS ENUM ('-1', '1');
CREATE TYPE admin_user_action_type AS ENUM ('block user', 'unblock user', 'create user account', 'delete user account', 'edit user account');
CREATE TYPE admin_event_action_type AS ENUM ('delete event');
CREATE TYPE admin_report_action_type AS ENUM ('resolve report', 'dismiss report');
CREATE TYPE tag_name AS ENUM ('workshop', 'press meet', 'career fair', 'seminar', 'art', 'gala', 'expo', 'networking');



-----------------------------------------------------
--Tables
-----------------------------------------------------

--For the user relation we used "user" (with double quotes) because user is a reserved word in PostgreSQL.
--R01 ("user")
CREATE TABLE "user" (
    id_user INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    email TEXT NOT NULL UNIQUE,
    name TEXT NOT NULL,
    location TEXT,
    password_hash TEXT NOT NULL,
    status account_status NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    CHECK (updated_at IS NULL OR updated_at >= created_at)
);

--R02 (admin)
CREATE TABLE admin (
    id_admin INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    email TEXT NOT NULL UNIQUE,
    name TEXT NOT NULL,
    password_hash TEXT NOT NULL,
    status account_status NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    CHECK (updated_at IS NULL OR updated_at >= created_at)
);

--R03 (profile)
CREATE TABLE profile (
    id_user INTEGER PRIMARY KEY REFERENCES "user"(id_user) ON UPDATE CASCADE ON DELETE CASCADE,
    photo_url TEXT
);

--R04 (event)
CREATE TABLE event (
    id_event INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_organizer INTEGER NOT NULL REFERENCES "user"(id_user) ON UPDATE CASCADE,
    title TEXT NOT NULL,
    description TEXT,
    visibility event_visibility NOT NULL DEFAULT 'public',
    status event_status NOT NULL DEFAULT 'draft',
    capacity INTEGER NOT NULL CHECK (capacity > 0),
    start_at TIMESTAMP NOT NULL,
    end_at TIMESTAMP NOT NULL,
    venue TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    canceled_at TIMESTAMP,
    CHECK (end_at > start_at),
    CHECK (updated_at IS NULL OR updated_at >= created_at),
    CHECK (canceled_at IS NULL OR canceled_at <= start_at - INTERVAL '24 hours'),
    CHECK(start_at >= created_at + INTERVAL '48 hours')
);

--R05 (tag)
CREATE TABLE tag (
    id_tag INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name tag_name NOT NULL UNIQUE
);

--R06 (event_tag)
CREATE TABLE event_tag (
    id_event INTEGER NOT NULL REFERENCES event(id_event) ON UPDATE CASCADE ON DELETE CASCADE,
    id_tag INTEGER NOT NULL REFERENCES tag(id_tag) ON UPDATE CASCADE ON DELETE CASCADE,
    PRIMARY KEY (id_event, id_tag)
);

--R07 (invitation)
CREATE TABLE invitation (
    id_invitation INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_event INTEGER NOT NULL REFERENCES event(id_event) ON UPDATE CASCADE ON DELETE CASCADE,
    id_invitee INTEGER NOT NULL REFERENCES "user"(id_user) ON UPDATE CASCADE ON DELETE CASCADE,
    status invitation_status NOT NULL DEFAULT 'pending',
    sent_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    responded_at TIMESTAMP,
    UNIQUE(id_event, id_invitee),
    CHECK (responded_at IS NULL OR responded_at > sent_at)
);

--R08 (application)
CREATE TABLE application (
    id_application INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_event INTEGER NOT NULL REFERENCES event(id_event) ON UPDATE CASCADE ON DELETE CASCADE,
    id_user INTEGER NOT NULL REFERENCES "user"(id_user) ON UPDATE CASCADE ON DELETE CASCADE,
    status application_status NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    decided_at TIMESTAMP,
    UNIQUE(id_event, id_user),
    CHECK (decided_at IS NULL OR decided_at >= created_at)
);

--R09 (participation)
CREATE TABLE participation (
    id_participation INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_event INTEGER NOT NULL REFERENCES event(id_event) ON UPDATE CASCADE ON DELETE CASCADE,
    id_user INTEGER NOT NULL REFERENCES "user"(id_user) ON UPDATE CASCADE ON DELETE CASCADE,
    joined_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    left_at TIMESTAMP,
    UNIQUE(id_event, id_user),
    CHECK (left_at IS NULL OR left_at > joined_at)
);

--R10 (comment)
CREATE TABLE comment (
    id_comment INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_event INTEGER NOT NULL,
    id_author INTEGER NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    edited_at TIMESTAMP,
    is_deleted BOOLEAN NOT NULL DEFAULT FALSE,
    CHECK (edited_at IS NULL OR edited_at >= created_at),
    FOREIGN KEY (id_event, id_author) REFERENCES participation(id_event, id_user) ON UPDATE CASCADE ON DELETE CASCADE
);

--R11 (comment_vote)
CREATE TABLE comment_vote (
    id_comment INTEGER NOT NULL REFERENCES comment(id_comment) ON UPDATE CASCADE ON DELETE CASCADE,
    id_participation INTEGER NOT NULL REFERENCES participation(id_participation) ON UPDATE CASCADE ON DELETE CASCADE,
    value vote_value NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_comment, id_participation)
);

--R12 (upload)
CREATE TABLE upload (
    id_upload INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_event INTEGER NOT NULL,
    id_uploader INTEGER NOT NULL,
    file_url TEXT NOT NULL,
    file_name TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_event, id_uploader) REFERENCES participation(id_event, id_user) ON UPDATE CASCADE ON DELETE CASCADE
);

--R13 (poll)
CREATE TABLE poll (
    id_poll INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_event INTEGER NOT NULL REFERENCES event(id_event)  ON UPDATE CASCADE ON DELETE CASCADE,
    question TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

--R14 (poll_option)
CREATE TABLE poll_option (
    id_option INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_poll INTEGER NOT NULL REFERENCES poll(id_poll) ON UPDATE CASCADE ON DELETE CASCADE,
    label TEXT NOT NULL,
    position INTEGER NOT NULL CHECK (position >= 1),
    UNIQUE (id_poll, id_option),
    UNIQUE(id_poll, label),
    UNIQUE(id_poll, position)
);
--R15 (poll_vote)
CREATE TABLE poll_vote (
    id_poll INTEGER NOT NULL,
    id_option INTEGER NOT NULL,
    id_participation INTEGER NOT NULL REFERENCES participation(id_participation) ON UPDATE CASCADE ON DELETE CASCADE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_poll, id_participation),
    FOREIGN KEY (id_poll, id_option) REFERENCES poll_option(id_poll, id_option) ON UPDATE CASCADE ON DELETE CASCADE
);

--R16 (notification)
CREATE TABLE notification (
    id_notification INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_user INTEGER NOT NULL REFERENCES "user"(id_user) ON UPDATE CASCADE ON DELETE CASCADE,
    message notification_message,
    id_event INTEGER REFERENCES event(id_event) ON UPDATE CASCADE ON DELETE SET NULL,
    id_invitation INTEGER REFERENCES invitation(id_invitation) ON UPDATE CASCADE ON DELETE SET NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP,
    CHECK (read_at IS NULL OR read_at >= created_at)
);

--R17 (event_report)
CREATE TABLE event_report (
    id_report INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_event INTEGER NOT NULL REFERENCES event(id_event) ON UPDATE CASCADE ON DELETE CASCADE,
    id_user INTEGER NOT NULL REFERENCES "user"(id_user) ON UPDATE CASCADE ON DELETE CASCADE,
    status report_status NOT NULL DEFAULT 'open',
    reason TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP,
    CHECK (resolved_at IS NULL OR resolved_at > created_at)
);

--R18 (admin_action)
CREATE TABLE admin_action (
    id_action INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_admin INTEGER NOT NULL REFERENCES admin(id_admin) ON UPDATE CASCADE ON DELETE CASCADE,
    details TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

--R19 (admin_user_action)
CREATE TABLE admin_user_action (
    id_action INTEGER PRIMARY KEY REFERENCES admin_action(id_action) ON UPDATE CASCADE ON DELETE CASCADE,
    action admin_user_action_type NOT NULL,
    target_user INTEGER NOT NULL REFERENCES "user"(id_user) ON UPDATE CASCADE ON DELETE CASCADE
);

--R20 (admin_event_action)
CREATE TABLE admin_event_action (
    id_action INTEGER PRIMARY KEY REFERENCES admin_action(id_action) ON UPDATE CASCADE ON DELETE CASCADE,
    action admin_event_action_type NOT NULL,
    target_event INTEGER NOT NULL REFERENCES event(id_event) ON UPDATE CASCADE ON DELETE CASCADE
);

--R21 (admin_report_action)
CREATE TABLE admin_report_action (
    id_action INTEGER PRIMARY KEY REFERENCES admin_action(id_action) ON UPDATE CASCADE ON DELETE CASCADE,
    action admin_report_action_type NOT NULL,
    id_report INTEGER NOT NULL REFERENCES event_report(id_report) ON UPDATE CASCADE ON DELETE CASCADE
);

-- R22 (security_question)
CREATE TABLE security_question (
    id_security_question INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    prompt TEXT NOT NULL UNIQUE
);

-- R23 (security_answer)
CREATE TABLE security_answer (
    id_security_answer INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_user INTEGER NOT NULL REFERENCES "user"(id_user) ON UPDATE CASCADE ON DELETE CASCADE,
    question TEXT NOT NULL,
    answer_hash TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);



--------------------------------------------------------------------

--INDEXES
--------------------------------------------------------------------

CREATE INDEX idx_event_public_active_date 
ON event 
USING btree(visibility, status, start_at);
CLUSTER event 
USING idx_event_public_active_date;

CREATE INDEX idx_participation_event_user 
ON participation 
USING btree (id_event, id_user);

CREATE INDEX idx_invitation_event_status_user 
ON invitation 
USING btree (id_event, status, id_invitee);

-- FTS INDEXES

-- 1) Add a column to store the computed tsvector
ALTER TABLE event
ADD COLUMN IF NOT EXISTS search_fts tsvector;

-- 2) Function to keep the tsvector up to date
CREATE OR REPLACE FUNCTION event_search_update()
RETURNS trigger AS $$
BEGIN
  NEW.search_fts :=
      setweight(to_tsvector('english', COALESCE(NEW.title, '')), 'A') ||
      setweight(to_tsvector('english', COALESCE(NEW.description, '')), 'B') ||
      setweight(to_tsvector('english', COALESCE(NEW.venue, '')), 'B');
  RETURN NEW;
END
$$ LANGUAGE plpgsql;

-- 3) Trigger to run the function on INSERT/UPDATE
DROP TRIGGER IF EXISTS trg_event_search_update ON event;
CREATE TRIGGER trg_event_search_update
BEFORE INSERT OR UPDATE ON event
FOR EACH ROW
EXECUTE FUNCTION event_search_update();

-- 5) GIN index over the tsvector column
CREATE INDEX IF NOT EXISTS idx_event_fts
ON event
USING GIN (search_fts);

--------------------------------------------------------------------

--TRIGGERS
--------------------------------------------------------------------
-- 1) Event capacity limit
-- Function to check event capacity before inserting participation
CREATE OR REPLACE FUNCTION check_event_capacity()
RETURNS trigger AS $$
DECLARE
    current_count INTEGER;
    max_capacity INTEGER;
BEGIN
    SELECT COUNT(*) INTO current_count
    FROM participation
    WHERE id_event = NEW.id_event AND left_at IS NULL;

    SELECT capacity INTO max_capacity
    FROM event
    WHERE id_event = NEW.id_event;

    IF current_count >= max_capacity THEN
        RAISE EXCEPTION 'Maximum capacity reached for event %', NEW.id_event;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger to apply capacity limit on participation
DROP TRIGGER IF EXISTS trg_check_event_capacity ON participation;
CREATE TRIGGER trg_check_event_capacity
BEFORE INSERT ON participation
FOR EACH ROW
EXECUTE FUNCTION check_event_capacity();

-- 2) Registration deadline
-- Function to apply registration deadline: 24h before event
CREATE OR REPLACE FUNCTION registration_deadline()
RETURNS trigger AS $$
DECLARE
    start_time TIMESTAMP;
BEGIN
    SELECT start_at INTO start_time
    FROM event
    WHERE id_event = NEW.id_event;

    IF CURRENT_TIMESTAMP >= (start_time - INTERVAL '24 hours') THEN
        RAISE EXCEPTION 'Registration closed for event %', NEW.id_event;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger for application 
DROP TRIGGER IF EXISTS trg_deadline_application ON application;
CREATE TRIGGER trg_deadline_application
BEFORE INSERT ON application
FOR EACH ROW
EXECUTE FUNCTION registration_deadline();

-- Trigger for participation
DROP TRIGGER IF EXISTS trg_deadline_participation ON participation;
CREATE TRIGGER trg_deadline_participation
BEFORE INSERT ON participation
FOR EACH ROW
EXECUTE FUNCTION registration_deadline();


-- 3) Participant activity window
-- Function for overall activity window validation: 24h before event
CREATE OR REPLACE FUNCTION check_event_activity_window(p_event_id INTEGER)
RETURNS VOID AS $$
DECLARE
    start_time TIMESTAMP;
BEGIN
    SELECT start_at INTO start_time
    FROM event
    WHERE id_event = p_event_id;

    IF CURRENT_TIMESTAMP >= (start_time - INTERVAL '24 hours') THEN
        RAISE EXCEPTION 'Activity closed for event %', p_event_id;
    END IF;
END;
$$ LANGUAGE plpgsql;

-- Function for comment activity window
CREATE OR REPLACE FUNCTION trg_comment_activity_window()
RETURNS trigger AS $$
BEGIN
    PERFORM check_event_activity_window(NEW.id_event);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger for comment activity window
DROP TRIGGER IF EXISTS trg_comment_activity_window ON comment;
CREATE TRIGGER trg_comment_activity_window
BEFORE INSERT ON comment
FOR EACH ROW
EXECUTE FUNCTION trg_comment_activity_window();

-- Function for upload activity window
CREATE OR REPLACE FUNCTION trg_upload_activity_window()
RETURNS trigger AS $$
BEGIN
    PERFORM check_event_activity_window(NEW.id_event);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger for upload activity window
DROP TRIGGER IF EXISTS trg_upload_activity_window ON upload;
CREATE TRIGGER trg_upload_activity_window
BEFORE INSERT ON upload
FOR EACH ROW
EXECUTE FUNCTION trg_upload_activity_window();

-- Function for comment vote activity window
CREATE OR REPLACE FUNCTION trg_comment_vote_activity_window()
RETURNS trigger AS $$
DECLARE
    event_id INTEGER;
BEGIN
    SELECT id_event INTO event_id FROM comment WHERE id_comment = NEW.id_comment;
    PERFORM check_event_activity_window(event_id);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger for comment vote activity window
DROP TRIGGER IF EXISTS trg_comment_vote_activity_window ON comment_vote;
CREATE TRIGGER trg_comment_vote_activity_window
BEFORE INSERT ON comment_vote
FOR EACH ROW
EXECUTE FUNCTION trg_comment_vote_activity_window();

-- Function for poll vote activity window
CREATE OR REPLACE FUNCTION trg_poll_vote_activity_window()
RETURNS trigger AS $$
DECLARE
    event_id INTEGER;
BEGIN
    SELECT id_event INTO event_id FROM poll WHERE id_poll = NEW.id_poll;
    PERFORM check_event_activity_window(event_id);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger for poll vote activity window
DROP TRIGGER IF EXISTS trg_poll_vote_activity_window ON poll_vote;
CREATE TRIGGER trg_poll_vote_activity_window
BEFORE INSERT ON poll_vote
FOR EACH ROW
EXECUTE FUNCTION trg_poll_vote_activity_window();


-- 4) Event Edit Deadline
-- Function to apply event edit deadline
CREATE OR REPLACE FUNCTION event_edit_deadline()
RETURNS trigger AS $$
DECLARE
    hours_left INTERVAL;
    critical_change BOOLEAN := FALSE;
BEGIN
    hours_left := OLD.start_at - CURRENT_TIMESTAMP;

    IF NEW.title        IS DISTINCT FROM OLD.title        OR
       NEW.description  IS DISTINCT FROM OLD.description  OR
       NEW.visibility   IS DISTINCT FROM OLD.visibility   OR
       NEW.status       IS DISTINCT FROM OLD.status       OR
       NEW.capacity     IS DISTINCT FROM OLD.capacity     OR
       NEW.start_at     IS DISTINCT FROM OLD.start_at     OR
       NEW.end_at       IS DISTINCT FROM OLD.end_at       OR
       NEW.venue        IS DISTINCT FROM OLD.venue THEN
        critical_change := TRUE;
    END IF;

    IF critical_change AND hours_left < INTERVAL '24 hours' THEN
        RAISE EXCEPTION 'Event % cannot be edited less than 24h before start', OLD.id_event;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger to apply event edit restrictions
DROP TRIGGER IF EXISTS trg_event_edit_deadline ON event;
CREATE TRIGGER trg_event_edit_deadline
BEFORE UPDATE ON event
FOR EACH ROW
EXECUTE FUNCTION event_edit_deadline();

--------------------------------------------------------------------

--TRANSACTIONS
--------------------------------------------------------------------

-- 1) List upcoming public events (count + next 10)
BEGIN TRANSACTION;

SET TRANSACTION ISOLATION LEVEL SERIALIZABLE READ ONLY;

-- Count upcoming public, published events
SELECT COUNT(*)
FROM event e
WHERE e.visibility = 'public'
  AND e.status = 'published'
  AND now() < e.end_at;

-- Get next 10 upcoming events (with organizer)
SELECT e.id_event, e.title, e.start_at, e.end_at, e.venue,
       u.id_user, u.name
FROM event e
JOIN "user" u ON u.id_user = e.id_organizer
WHERE e.visibility = 'public'
  AND e.status = 'published'
  AND now() < e.end_at
ORDER BY e.start_at ASC
LIMIT 10;

END TRANSACTION;


-- 2) Create event and attach tags
BEGIN TRANSACTION;

SET TRANSACTION ISOLATION LEVEL REPEATABLE READ;

-- Insert event
INSERT INTO event
  (id_organizer, title, description, visibility, status, capacity,
   start_at, end_at, venue, created_at)
VALUES
  ($id_organizer, $title, $description, $visibility, 'published', $capacity,
   $start_at, $end_at, $venue, now());

-- Attach tags (example with two tags; extend as needed)
INSERT INTO event_tag (id_event, id_tag)
VALUES
  (currval('event_id_event_seq'), $id_tag_1),
  (currval('event_id_event_seq'), $id_tag_2);

END TRANSACTION;


-- 3) Approve application and create participation
BEGIN TRANSACTION;

SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;

-- Approve application if still pending
UPDATE application
   SET status = 'approved', decided_at = now()
 WHERE id_application = $id_application
   AND id_event = $id_event
   AND id_user = $id_user
   AND status = 'pending';

-- Create participation (fails if already exists)
INSERT INTO participation (id_event, id_user, joined_at)
VALUES ($id_event, $id_user, now());

END TRANSACTION;


-- 4) Invite user to event and notify 
BEGIN TRANSACTION;

SET TRANSACTION ISOLATION LEVEL REPEATABLE READ;

-- Create invitation (UK(id_event, id_invitee) avoids duplicates)
INSERT INTO invitation (id_event, id_invitee, status, sent_at)
VALUES ($id_event, $id_invitee, 'pending', now());

-- Notify invited user
INSERT INTO notification (id_user, message, id_event, id_invitation, created_at)
VALUES ($id_invitee, 'invited', $id_event, currval('invitation_id_invitation_seq'), now());

END TRANSACTION;


--5) Accept invitation and add participation
BEGIN TRANSACTION;

SET TRANSACTION ISOLATION LEVEL REPEATABLE READ;

-- Accept the invitation if still pending
UPDATE invitation
   SET status = 'accepted', responded_at = now()
 WHERE id_invitation = $id_invitation
   AND id_invitee = $id_user
   AND status = 'pending';

-- Create participation (id_event taken from the invitation)
INSERT INTO participation (id_event, id_user, joined_at)
SELECT i.id_event, i.id_invitee, now()
  FROM invitation i
 WHERE i.id_invitation = $id_invitation;

END TRANSACTION;


-- 6) Post comment and notify organizer
BEGIN TRANSACTION;

SET TRANSACTION ISOLATION LEVEL REPEATABLE READ;

-- Insert comment (FK ensures (id_event, id_author) exists in participation)
INSERT INTO comment (id_event, id_author, body, created_at, is_deleted)
VALUES ($id_event, $id_user, $body, now(), false);

-- Notify organizer that there is a new comment
INSERT INTO notification (id_user, message, id_event, created_at)
SELECT e.id_organizer, 'event updated', $id_event, now()
FROM event e
WHERE e.id_event = $id_event;

END TRANSACTION;


-- 7) Cast or replace a poll vote
BEGIN TRANSACTION;

SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;

-- Remove any previous vote by this participant in this poll
DELETE FROM poll_vote pv
USING poll_option po
WHERE pv.id_participation = $id_participation
  AND pv.id_poll = po.id_poll
  AND po.id_poll = $id_poll;

-- Insert the new vote
INSERT INTO poll_vote (id_poll, id_option, id_participation, created_at)
VALUES ($id_poll, $id_option, $id_participation, now());

END TRANSACTION;


-- 8) Cancel event and notify stakeholders
BEGIN TRANSACTION;

SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;

-- Cancel event
UPDATE event
   SET status = 'canceled', canceled_at = now(), updated_at = now()
 WHERE id_event = $id_event;

-- Cancel pending/accepted invitations
UPDATE invitation
   SET status = 'canceled', responded_at = COALESCE(responded_at, now())
 WHERE id_event = $id_event
   AND status IN ('pending','accepted');

-- Cancel pending/approved applications
UPDATE application
   SET status = 'canceled', decided_at = COALESCE(decided_at, now())
 WHERE id_event = $id_event
   AND status IN ('pending','approved');

-- Notify participants and invitees
INSERT INTO notification (id_user, message, id_event, created_at)
SELECT p.id_user, 'event updated', $id_event, now()
FROM participation p
WHERE p.id_event = $id_event
UNION
SELECT i.id_invitee, 'event updated', $id_event, now()
FROM invitation i
WHERE i.id_event = $id_event;

END TRANSACTION;


-- 9) Delete user account (anonymize and cleanup)
BEGIN TRANSACTION;

SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;

-- Anonymize user
UPDATE "user"
   SET status = 'deleted',
       name   = 'Deleted User',
       location = NULL,
       updated_at = now()
 WHERE id_user = $id_user;

-- Remove profile picture
UPDATE profile
   SET photo_url = NULL
 WHERE id_user = $id_user;

-- Soft-delete user's comments
UPDATE comment
   SET is_deleted = TRUE, edited_at = now()
 WHERE (id_event, id_author) IN (
   SELECT id_event, id_user
   FROM participation
   WHERE id_user = $id_user
);

-- Cancel pending invitations and applications
UPDATE invitation
   SET status = 'canceled', responded_at = COALESCE(responded_at, now())
 WHERE id_invitee = $id_user
   AND status IN ('pending','accepted');

UPDATE application
   SET status = 'canceled', decided_at = COALESCE(decided_at, now())
 WHERE id_user = $id_user
   AND status IN ('pending','approved');

-- Close participations
UPDATE participation
   SET left_at = COALESCE(left_at, now())
 WHERE id_user = $id_user;

END TRANSACTION;