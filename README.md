# lbaw2536

EventSquare Laravel app.

## Quick start (local)

1) Start the database services:
```
docker compose up -d
```

2) Seed the database (SQL):
```
docker exec -i lbaw-postgres psql -U lbaw2536 -d lbaw2536 < database/eventsquare-seed.sql
```

3) Install dependencies and run the app:
```
composer install
npm install
php artisan serve
```

Then open `http://127.0.0.1:8000`.

## Test credentials

Admin
- Email: admin.*@eventsquare.pt
- Password: adminhash123

User
- Email: *@email.com
- Password: EventSquare123!
