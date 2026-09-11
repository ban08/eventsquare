# EventSquare

An event-management web application: people create events, invite others, apply to attend, and get notified as things change.

## What it does

- **Accounts and roles** — visitors, authenticated users, event organizers and administrators, each with their own permissions.
- **Events** — full create/read/update/delete, with tags, capacity, dates and states (upcoming, published, completed, cancelled).
- **Participation** — apply to or get invited to events, answer organizer polls, see the attendee list, leave an event.
- **Notifications** — users are notified when an event they joined is updated or cancelled, and organizers when someone applies.
- **Full-text search** and tag exploration across events.
- **Admin panel** — browse, create, edit, block and soft-delete users, and handle event reports.

## Stack

Laravel 12 (PHP), PostgreSQL, Blade templates, Vite, Docker Compose for the database.

## How to run

```bash
docker compose up -d                         # start PostgreSQL
psql ... < database/eventsquare-seed.sql     # seed schema and demo data
composer install && npm install
php artisan serve                            # http://127.0.0.1:8000
```

Demo logins are listed in the seed file (an admin and a regular user).

## What I built

Group project of four for the Web Applications Lab course (2025/26). I did the largest share of the application:

- The read-only event views and routing, then the full event lifecycle: creation, editing, deletion, cancellation and state transitions.
- **Invitations and applications**, including the rules that stop overlapping invites and applications.
- The **notification system** and the notifications page.
- **Admin** user management: browse, create, edit, block, soft-delete.
- **Full-text search** and tag exploration, profile editing with picture upload, and event reports.

Teammates set up the initial database schema and the registration/login flow.

## What I would do differently

Add automated tests — the project leaned on manual checking, and a Laravel feature-test suite would have caught regressions in the permission rules. I would also move the heavier queries behind form-request validation and policies more consistently instead of checking permissions inline.
