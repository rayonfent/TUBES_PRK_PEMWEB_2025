# Running DB migrations (local development)

This folder contains lightweight SQL migration helpers for development.

Files:

- `migrate_add_konselor_role.sql` — alters `users.role` enum to include `'konselor'`.
- `create_activity_log.sql` — creates `activity_log` table used by admin UI.

How to run locally (using mysql on Windows / Laragon):

1. Open a terminal (PowerShell) and change directory into your project database folder:

```powershell
cd "x:/System/laragon/www/TUBES_PRK_PEMWEB_2025/kelompok/kelompok_26/mental-health-platform/database"
```

2. Import a single migration (replace credentials/database name accordingly):

```powershell
mysql -u root -p mental_health_platform < migrate_add_konselor_role.sql
mysql -u root -p mental_health_platform < create_activity_log.sql
```

If you use a different database name, change `mental_health_platform` to match.

Tip: Back up your database before running migrations.
