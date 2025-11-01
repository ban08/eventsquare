
DROP SCHEMA IF EXISTS lbaw2536 CASCADE;
CREATE SCHEMA lbaw2536;
SET search_path TO lbaw2536;


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


--------------------------------------------------------------------

--TRANSACTIONS
--------------------------------------------------------------------