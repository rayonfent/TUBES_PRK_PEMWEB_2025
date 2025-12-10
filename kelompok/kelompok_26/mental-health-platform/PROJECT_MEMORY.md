# Mental Health Platform — Project Snapshot

This file is an automatically generated snapshot and documentation index for the `mental-health-platform` project (persisted in repo by assistant). It summarizes the project structure, main components, and quick instructions for running or viewing the generated documentation site.

## Project summary
- Project: Mental Health Platform (PHP)
- Location: `kelompok/kelompok_26/mental-health-platform`
- Type: PHP web application (server-side, designed to run under a LAMP/AMP stack like XAMPP, Laragon)

## Main functionality
- User authentication (login / register) — `src/controllers/AuthController.php`, `src/views/auth`
- Real-time-ish chat / sessions — `src/controllers/ChatController.php`, `src/models/ChatMessage.php`, `src/views/chat`
- Matching algorithm for users ↔ konselor — `src/controllers/MatchingController.php` and `handle_matching.php`
- Survey flows — `src/controllers/SurveyController.php` and `handle_survey.php`
- Database schema / seed: `database/mental_health_platform.sql`

## Simplified file map (high-level)

src/
 - index.php — application entry point
 - config/database.php — DB connection
 - controllers/ — route handlers and controllers
 - helpers/ — small helper utilities (auth, upload)
 - models/ — data model classes (User, Konselor, Chat, etc.)
 - views/ — HTML/PHP templates split by feature

database/mental_health_platform.sql — database schema + sample data

## How to run locally
1. Install a PHP server environment (Laragon, XAMPP, or local PHP + MySQL).
2. Import `database/mental_health_platform.sql` into a MySQL database.
3. Update `src/config/database.php` with DB credentials.
4. Point your web server document root to `src/` and open `index.php`.

## Generated docs site
- A simple docs website was created at `src/docs/` (or `docs/` at project root) to view this snapshot in a browser.
- Open `kelompok/kelompok_26/mental-health-platform/src/docs/index.html` in your browser to see the project summary and file index.

---
If you'd like more details included (file content snippets, README extraction per file, or a different layout for the web pages), tell me what to include next and I will update the docs site accordingly.
