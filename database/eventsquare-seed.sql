--
-- EventSquare seed: schema + data
-- Uses app.schema if set, otherwise defaults to 'lbaw2536'.
--

DO $do$
DECLARE
  s text := COALESCE(current_setting('app.schema', true), 'lbaw2536');
BEGIN
  EXECUTE format('DROP SCHEMA IF EXISTS %I CASCADE', s);
  EXECUTE format('CREATE SCHEMA IF NOT EXISTS %I', s);
  PERFORM set_config('search_path', format('%I, public', s), false);
END
$do$ LANGUAGE plpgsql;


-----------------------------------------------------
--Types
-----------------------------------------------------

CREATE TYPE account_status AS ENUM ('active', 'blocked', 'deleted');
CREATE TYPE event_visibility AS ENUM ('public', 'private');
CREATE TYPE notification_message AS ENUM ('invited', 'event updated', 'event canceled', 'new application', 'application accepted', 'application rejected');
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
	id_application INTEGER REFERENCES application(id_application) ON UPDATE CASCADE ON DELETE SET NULL,
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

-- (FTS column, function, trigger, and business-rule triggers
-- should be appended here from the full A6 SQL if they are defined
-- after these indexes.)

--------------------------------------------------------------------
-- POPULATION (from lbaw2536_population.sql)
--------------------------------------------------------------------

--R01 ("user") 
INSERT INTO "user" (id_user, email, name, location, password_hash, status, created_at, updated_at)
OVERRIDING SYSTEM VALUE
VALUES
(1, 'joao.silva@email.com', 'João Silva', 'Porto', '$2y$12$b7HewdgVUFbMwKXW30p1tOMKZNzDxJGa3yy7F.EPh.OL0Gs5ZO0Sy', 'active', '2025-09-01', NULL),
(2, 'ana.santos@email.com', 'Ana Santos', 'Lisboa', '$2y$12$b7HewdgVUFbMwKXW30p1tOMKZNzDxJGa3yy7F.EPh.OL0Gs5ZO0Sy', 'active', '2025-09-02', '2025-09-15'),
(3, 'carlos.mendes@email.com', 'Carlos Mendes', 'Coimbra', '$2y$12$b7HewdgVUFbMwKXW30p1tOMKZNzDxJGa3yy7F.EPh.OL0Gs5ZO0Sy', 'active', '2025-09-03', NULL),
(4, 'maria.rocha@email.com', 'Maria Rocha', 'Braga', '$2y$12$b7HewdgVUFbMwKXW30p1tOMKZNzDxJGa3yy7F.EPh.OL0Gs5ZO0Sy', 'active', '2025-09-04', '2025-09-20'),
(5, 'pedro.costa@email.com', 'Pedro Costa', 'Aveiro', '$2y$12$b7HewdgVUFbMwKXW30p1tOMKZNzDxJGa3yy7F.EPh.OL0Gs5ZO0Sy', 'active', '2025-09-05', NULL),
(6, 'isabel.martins@email.com', 'Isabel Martins', 'Faro', '$2y$12$b7HewdgVUFbMwKXW30p1tOMKZNzDxJGa3yy7F.EPh.OL0Gs5ZO0Sy', 'active', '2025-09-06', '2025-09-25'),
(7, 'luis.gomes@email.com', 'Luís Gomes', 'Leiria', '$2y$12$b7HewdgVUFbMwKXW30p1tOMKZNzDxJGa3yy7F.EPh.OL0Gs5ZO0Sy', 'active', '2025-09-07', '2025-09-30'),
(8, 'sofia.almeida@email.com', 'Sofia Almeida', 'Setúbal', '$2y$12$b7HewdgVUFbMwKXW30p1tOMKZNzDxJGa3yy7F.EPh.OL0Gs5ZO0Sy', 'active', '2025-09-08', NULL),
(9, 'ricardo.pereira@email.com', 'Ricardo Pereira', 'Viseu', '$2y$12$b7HewdgVUFbMwKXW30p1tOMKZNzDxJGa3yy7F.EPh.OL0Gs5ZO0Sy', 'active', '2025-09-09', NULL),
(10, 'catarina.fernandes@email.com', 'Catarina Fernandes', 'Évora', '$2y$12$b7HewdgVUFbMwKXW30p1tOMKZNzDxJGa3yy7F.EPh.OL0Gs5ZO0Sy', 'active', '2025-09-10', '2025-09-28'),
(11, 'tiago.cruz@email.com', 'Tiago Cruz', 'Guarda', '$2y$12$b7HewdgVUFbMwKXW30p1tOMKZNzDxJGa3yy7F.EPh.OL0Gs5ZO0Sy', 'active', '2025-09-11', NULL),
(12, 'leonor.guerra@email.com', 'Leonor Guerra', 'Portimão', '$2y$12$b7HewdgVUFbMwKXW30p1tOMKZNzDxJGa3yy7F.EPh.OL0Gs5ZO0Sy', 'active', '2025-09-12', NULL),
(13, 'bruno.nogueira@email.com', 'Bruno Nogueira', 'Viana do Castelo', '$2y$12$b7HewdgVUFbMwKXW30p1tOMKZNzDxJGa3yy7F.EPh.OL0Gs5ZO0Sy', 'active', '2025-09-13', NULL),
(14, 'inês.barbosa@email.com', 'Inês Barbosa', 'Funchal', '$2y$12$b7HewdgVUFbMwKXW30p1tOMKZNzDxJGa3yy7F.EPh.OL0Gs5ZO0Sy', 'active', '2025-09-14', NULL),
(15, 'diogo.ramos@email.com', 'Diogo Ramos', 'Ponta Delgada', '$2y$12$b7HewdgVUFbMwKXW30p1tOMKZNzDxJGa3yy7F.EPh.OL0Gs5ZO0Sy', 'active', '2025-09-15', NULL),
(16, 'admin.sousa@eventsquare.pt', 'Ana Sousa', 'Porto', '$2y$12$fpmJCaXVOpZeN.C57rzLI.1Kb7DATSudXQlEa.FbuCNEPEqNAfEma', 'active', '2025-09-01', '2025-10-15');

--R02 (admin)
INSERT INTO admin (id_admin, email, name, password_hash, status, created_at, updated_at)
OVERRIDING SYSTEM VALUE
VALUES
(1, 'admin.sousa@eventsquare.pt', 'Ana Sousa', '$2y$12$fpmJCaXVOpZeN.C57rzLI.1Kb7DATSudXQlEa.FbuCNEPEqNAfEma', 'active', '2025-09-01', '2025-10-15'),
(2, 'admin.pires@eventsquare.pt', 'João Pires', '$2y$12$fpmJCaXVOpZeN.C57rzLI.1Kb7DATSudXQlEa.FbuCNEPEqNAfEma', 'active', '2025-09-02', NULL),
(3, 'admin.ferreira@eventsquare.pt', 'Manuel Ferreira', '$2y$12$fpmJCaXVOpZeN.C57rzLI.1Kb7DATSudXQlEa.FbuCNEPEqNAfEma', 'active', '2025-09-03', '2025-10-10');

--R03 (profile)
INSERT INTO profile (id_user, photo_url)
VALUES
(1, 'https://picsum.photos/id/101/200'),
(2, 'https://picsum.photos/id/102/200'),
(3, 'https://picsum.photos/id/103/200'),
(4, 'https://picsum.photos/id/104/200'),
(5, 'https://picsum.photos/id/105/200'),
(6, 'https://picsum.photos/id/106/200'),
(7, 'https://picsum.photos/id/107/200'),
(8, 'https://picsum.photos/id/108/200'),
(9, 'https://picsum.photos/id/109/200'),
(10, 'https://picsum.photos/id/110/200'),
(11, 'https://picsum.photos/id/111/200'),
(12, 'https://picsum.photos/id/112/200'),
(13, 'https://picsum.photos/id/113/200'),
(14, 'https://picsum.photos/id/114/200'),
(15, 'https://picsum.photos/id/115/200');

--R04 (event) git merge feature/us01/02/04-events-readonly
INSERT INTO event (id_event, id_organizer, title, description, visibility, status, capacity, start_at, end_at, venue, created_at, updated_at, canceled_at)
OVERRIDING SYSTEM VALUE
VALUES
(1, 1, 'Workshop de Programação Python', 'Introdução à programação com Python para iniciantes.', 'public', 'published', 30, '2025-11-15 14:00', '2025-11-15 17:00', 'FEUP - Sala B101', '2025-11-01', '2025-11-05', NULL),
(2, 2, 'Meetup de Empreendedorismo', 'Encontro para partilha de ideias e projetos de negócio.', 'private', 'published', 25, '2025-11-20 18:30', '2025-11-20 21:00', 'Lisboa - Startup Lisboa', '2025-11-10', '2025-11-12', NULL),
(3, 3, 'Conferência de Inteligência Artificial', 'Painel sobre o futuro da IA em Portugal.', 'public', 'published', 100, '2025-11-25 09:00', '2025-11-25 18:00', 'Porto - Centro de Congressos', '2025-11-15', '2025-11-18', NULL),
(4, 4, 'Feira de Emprego Tecnologia', 'Recrutamento para empresas de tecnologia.', 'public', 'completed', 200, '2025-10-10 10:00', '2025-10-10 18:00', 'Braga - Parque Tecnológico', '2025-09-20', '2025-10-10', NULL),
(5, 5, 'Seminário de Cibersegurança', 'Melhores práticas de segurança digital.', 'public', 'published', 50, '2025-12-01 10:00', '2025-12-01 13:00', 'Coimbra - DEI', '2025-11-20', '2025-11-22', NULL),
(6, 6, 'Workshop de Design UX/UI', 'Criação de interfaces intuitivas e experiências de utilizador.', 'private', 'draft', 20, '2025-12-05 15:00', '2025-12-05 18:00', 'Faro - Universidade do Algarve', '2025-11-25', NULL, NULL),
(7, 7, 'Hackathon Desafio 2025', '48 horas de programação intensiva.', 'public', 'published', 80, '2025-12-10 09:00', '2025-12-12 18:00', 'Lisboa - Web Summit', '2025-11-01', '2025-11-15', NULL),
(8, 8, 'Expo de Startups Portuguesas', 'Apresentação das melhores startups nacionais.', 'public', 'canceled', 150, '2025-10-15 14:00', '2025-10-15 20:00', 'Setúbal - Fórum Municipal', '2025-09-25', '2025-10-10', '2025-10-14 14:00');

--R05 (tag)
INSERT INTO tag (id_tag, name)
OVERRIDING SYSTEM VALUE
VALUES
(1, 'workshop'),
(2, 'networking'),
(3, 'seminar'),
(4, 'expo'),
(5, 'career fair'),
(6, 'art'),
(7, 'gala'),
(8, 'press meet');

--R06 (event_tag)
INSERT INTO event_tag (id_event, id_tag)
VALUES
(1, 1),  
(2, 2),  
(3, 3),  
(4, 5),  
(5, 3),  
(6, 1),  
(7, 1),  
(7, 2),  
(8, 4);  

--R07 (invitation) 
INSERT INTO invitation (id_invitation, id_event, id_invitee, status, sent_at, responded_at)
OVERRIDING SYSTEM VALUE
VALUES
(1, 2, 3, 'accepted', '2025-11-19 10:00', '2025-11-19 14:30'),  
(2, 2, 5, 'pending', '2025-11-18 16:00', NULL),                  
(3, 2, 7, 'declined', '2025-11-17 09:00', '2025-11-17 11:15'),   
(4, 6, 9, 'expired', '2025-11-24 12:00', NULL),                  
(5, 6, 11, 'accepted', '2025-11-23 15:30', '2025-11-23 16:45'),  
(6, 6, 13, 'canceled', '2025-11-22 08:00', NULL);                

--R08 (application) 
INSERT INTO application (id_application, id_event, id_user, status, created_at, decided_at)
OVERRIDING SYSTEM VALUE
VALUES
(1, 1, 2, 'approved', '2025-11-10 09:00', '2025-11-11 10:30'),   
(2, 1, 4, 'pending', '2025-11-12 14:00', NULL),                    
(3, 1, 6, 'rejected', '2025-11-09 11:00', '2025-11-10 09:15'),    
(4, 3, 5, 'approved', '2025-11-20 08:00', '2025-11-21 15:00'),   
(5, 5, 7, 'pending', '2025-11-25 16:00', NULL),                    
(6, 7, 9, 'approved', '2025-11-02 10:00', '2025-11-03 14:20'),   
(7, 7, 10, 'approved', '2025-11-03 13:00', '2025-11-04 09:45'),   
(8, 7, 12, 'rejected', '2025-11-04 16:00', '2025-11-05 11:30');   

--R09 (participation) 
INSERT INTO participation (id_participation, id_event, id_user, joined_at, left_at)
OVERRIDING SYSTEM VALUE
VALUES
(1, 1, 1, '2025-11-14 10:00', NULL),     
(2, 1, 2, '2025-11-13 15:30', NULL),    
(3, 2, 2, '2025-11-19 18:00', '2025-11-20 21:15'),  
(4, 2, 3, '2025-11-19 17:45', '2025-11-20 20:30'),  
(5, 3, 3, '2025-11-24 08:30', NULL),    
(6, 3, 5, '2025-11-23 14:00', NULL),     
(7, 4, 4, '2025-10-09 09:00', '2025-10-10 18:30'),  
(8, 4, 8, '2025-10-08 11:15', '2025-10-10 17:45'),  
(9, 5, 5, '2025-11-30 09:00', NULL),     
(10, 7, 7, '2025-12-08 10:00', NULL),    
(11, 7, 9, '2025-12-07 14:30', NULL),    
(12, 7, 10, '2025-12-06 16:00', NULL);   

--R10 (comment) 
INSERT INTO comment (id_comment, id_event, id_author, body, created_at, edited_at, is_deleted)
OVERRIDING SYSTEM VALUE
VALUES
(1, 1, 1, 'Excelente workshop! O conteúdo foi muito prático e útil.', '2025-11-15 17:15', NULL, FALSE),
(2, 1, 2, 'Gostei muito dos exercícios práticos. O professor explicou muito bem.', '2025-11-15 17:30', NULL, FALSE),
(3, 2, 3, 'Ótimo ambiente para networking. Conheci pessoas muito interessantes.', '2025-11-20 21:30', NULL, FALSE),
(4, 2, 2, 'A organização foi impecável. Local excelente e bom acolhimento.', '2025-11-21 09:00', '2025-11-21 09:15', FALSE),
(5, 3, 5, 'Conferência de alto nível! Os oradores foram fantásticos.', '2025-11-25 18:30', NULL, FALSE),
(6, 4, 4, 'Foi muito útil para procurar emprego. Consegui algumas entrevistas!', '2025-10-11 10:00', NULL, FALSE),
(7, 4, 8, 'A diversidade de empresas presentes foi impressionante.', '2025-10-11 11:30', NULL, FALSE);

--R11 (comment_vote) 
INSERT INTO comment_vote (id_comment, id_participation, value, created_at)
VALUES
(1, 2, '1', '2025-11-15 18:00'),
(2, 1, '1', '2025-11-15 18:15'),
(3, 4, '1', '2025-11-21 09:30'),
(5, 6, '1', '2025-11-25 19:00'),
(6, 7, '1', '2025-10-11 10:30');

--R12 (upload) 
INSERT INTO upload (id_upload, id_event, id_uploader, file_url, file_name, created_at)
OVERRIDING SYSTEM VALUE
VALUES
(1, 1, 1, 'https://files.eventsquare.pt/python_slides.pdf', 'python_workshop_slides.pdf', '2025-11-15 09:00'),
(2, 1, 2, 'https://files.eventsquare.pt/python_exercises.zip', 'exercicios_python.zip', '2025-11-15 09:15'),
(3, 3, 3, 'https://files.eventsquare.pt/networking_contacts.pdf', 'contactos_networking.pdf', '2025-11-20 17:00'),
(4, 5, 5, 'https://files.eventsquare.pt/cybersecurity_guide.pdf', 'guia_ciberseguranca.pdf', '2025-11-30 08:30'),
(5, 7, 7, 'https://files.eventsquare.pt/hackathon_rules.pdf', 'regras_hackathon.pdf', '2025-12-07 15:00');

--R13 (poll) 
INSERT INTO poll (id_poll, id_event, question, created_at)
OVERRIDING SYSTEM VALUE
VALUES
(1, 1, 'Recomendaria este workshop a outros colegas?', '2025-11-15 17:00'),
(2, 3, 'Qual área da IA mais lhe interessa?', '2025-11-25 12:00'),
(3, 7, 'Prefere programar individualmente ou em equipa?', '2025-12-09 10:00');

--R14 (poll_option) 
INSERT INTO poll_option (id_option, id_poll, label, position)
OVERRIDING SYSTEM VALUE
VALUES
(1, 1, 'Sim, definitivamente', 1),
(2, 1, 'Sim, com algumas reservas', 2),
(3, 1, 'Não', 3),
(4, 2, 'Machine Learning', 1),
(5, 2, 'Processamento de Linguagem Natural', 2),
(6, 2, 'Visão Computacional', 3),
(7, 2, 'Robótica', 4),
(8, 3, 'Individualmente', 1),
(9, 3, 'Em equipa', 2);

--R15 (poll_vote)
INSERT INTO poll_vote (id_poll, id_option, id_participation, created_at)
VALUES
(1, 1, 2, '2025-11-15 17:30'),
(1, 2, 1, '2025-11-15 17:35'),
(2, 4, 5, '2025-11-25 13:00'),
(2, 6, 6, '2025-11-25 13:05'),
(3, 8, 10, '2025-12-09 11:00');

--R16 (notification) 
INSERT INTO notification (id_notification, id_user, message, id_event, id_invitation, created_at, read_at)
OVERRIDING SYSTEM VALUE
VALUES
(1, 2, 'invited', 2, 1, '2025-11-18 10:05', '2025-11-18 16:00'),
(2, 3, 'invited', 2, 1, '2025-11-17 09:05', '2025-11-17 11:20'),
(3, 5, 'event updated', 3, NULL, '2025-11-18 08:00', '2025-11-18 14:30'),
(4, 7, 'invited', 6, 4, '2025-11-23 15:35', '2025-11-23 16:50'),
(5, 9, 'invited', 6, 5, '2025-11-24 12:05', NULL),
(6, 11, 'event updated', 7, NULL, '2025-11-03 14:25', '2025-11-03 20:00');

--R17 (event_report) 
INSERT INTO event_report (id_report, id_event, id_user, status, reason, created_at, resolved_at)
OVERRIDING SYSTEM VALUE
VALUES
(1, 1, 6, 'dismissed', 'Informação incorreta sobre pré-requisitos', '2025-11-16 10:00', '2025-11-17 09:00'),
(2, 3, 7, 'open', 'Evento sobrelotado, capacidade insuficiente', '2025-11-25 19:00', NULL),
(3, 5, 8, 'dismissed', 'Local alterado sem aviso prévio', '2025-12-01 14:00', '2025-12-02 10:00'),
(4, 7, 11, 'open', 'Equipas desequilibradas no hackathon', '2025-12-10 19:00', NULL);

--R18 (admin_action) 
INSERT INTO admin_action (id_action, id_admin, details, created_at)
OVERRIDING SYSTEM VALUE
VALUES
(1, 1, 'Revisão de eventos reportados', '2025-11-17 10:30'),
(2, 2, 'Verificação de conteúdo inadequado', '2025-11-18 14:00'),
(3, 3, 'Análise de queixas de utilizadores', '2025-12-01 11:00');

--R19 (admin_user_action)
INSERT INTO admin_user_action (id_action, action, target_user)
VALUES
(1, 'block user', 7),  
(3, 'unblock user', 7); 

--R20 (admin_event_action) 
INSERT INTO admin_event_action (id_action, action, target_event)
VALUES
(2, 'delete event', 8);

--R21 (admin_report_action) 
INSERT INTO admin_report_action (id_action, action, id_report)
VALUES
(1, 'dismiss report', 1),  
(3, 'resolve report', 3);

-- Update sequences to match the highest IDs inserted
SELECT setval('lbaw2536.user_id_user_seq', 16, true);
SELECT setval('lbaw2536.admin_id_admin_seq', 3, true);
SELECT setval('lbaw2536.tag_id_tag_seq', 8, true);
SELECT setval('lbaw2536.event_id_event_seq', 8, true);
SELECT setval('lbaw2536.invitation_id_invitation_seq', 6, true);
SELECT setval('lbaw2536.application_id_application_seq', 8, true);
SELECT setval('lbaw2536.participation_id_participation_seq', 12, true);
SELECT setval('lbaw2536.comment_id_comment_seq', 7, true);
SELECT setval('lbaw2536.notification_id_notification_seq', 6, true);
SELECT setval('lbaw2536.poll_id_poll_seq', 3, true);
SELECT setval('lbaw2536.poll_option_id_option_seq', 9, true);
SELECT setval('lbaw2536.event_report_id_report_seq', 4, true);
SELECT setval('lbaw2536.admin_action_id_action_seq', 3, true);
SELECT setval('lbaw2536.upload_id_upload_seq', 5, true);