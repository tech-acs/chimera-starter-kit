# Demo Mode

Enable a dashboard-starter-kit site as a **public demo**: visitors explore the full app (including the manage section) with one click, but **cannot modify any data**. The read-only guarantee comes from a PostgreSQL account with only `SELECT` privileges.

---

## Architecture

| Layer | Enforcement |
|---|---|
| **Authentication** | `POST /login-demo` route bypasses Fortify entirely. Calls `Auth::login($user)` directly — no password required. |
| **Application** | `DemoServiceProvider` swaps `log_page_views`, `enforce_2fa`, and `password.confirm` middleware to no-ops. Registers a renderable `QueryException` handler that flashes a read-only banner. |
| **Database** | A dedicated PostgreSQL role with `SELECT ONLY`. Every `INSERT`/`UPDATE`/`DELETE` fails at the DB layer. |
| **UX** | Failed writes redirect back with the Jetstream banner: *"This is a demo site. Visitors cannot make changes."* |

---

## Setup Steps

### 1. Switch the consumer app to the `demo-mode` branch

If the host app already has the package installed from Packagist, you don't need to remove it first. Override the source in `composer.json` and run `composer update` — Composer will switch to the branch automatically.

Edit `composer.json` and add a `repositories` entry pointing to the GitHub repo, then change the `require` constraint to `dev-demo-mode`:

```json
// composer.json — add this block at the top level (alongside "require", "autoload", etc.)
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/tech-acs/dashboard-starter-kit"
    }
],
"require": {
    // ... other dependencies ...
    "uneca/dashboard-starter-kit": "dev-demo-mode"
}
```

Then run (target the package specifically to avoid updating everything):

```bash
composer update uneca/dashboard-starter-kit
```

Composer will clone the `demo-mode` branch from GitHub (instead of pulling from Packagist) and update the lock file.

**To switch back to the stable release later:**
- Remove the `repositories` entry
- Change the constraint back (e.g. `"^7.2"`)
- Run `composer update uneca/dashboard-starter-kit`

### 2. Create a read-only PostgreSQL role

Connect as a superuser and run:

```sql
CREATE ROLE demo_reader WITH LOGIN PASSWORD 'a-strong-password';
GRANT CONNECT ON DATABASE your_database TO demo_reader;

-- Grant SELECT on all existing tables
GRANT SELECT ON ALL TABLES IN SCHEMA public TO demo_reader;

-- Ensure future tables also get SELECT
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT ON TABLES TO demo_reader;
```

### 3. Set environment variables

Add to `.env`:

```env
CHIMERA_DEMO=true
DEMO_ACCOUNT=demo@example.com

DB_USERNAME=demo_reader
DB_PASSWORD=a-strong-password
```

If your app uses database-backed `SESSION_DRIVER`, `CACHE_STORE`, or `QUEUE_CONNECTION`, switch them to file-based or disabled:

```env
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

### 4. Seed the demo user

Run **before** switching to the read-only DB credentials:

```bash
php artisan chimera:demo-setup
```

This creates a user `demo@example.com` with the **Super Admin** role and a random password (not needed for login).

**What the command does:**
- Creates or finds a user by `DEMO_ACCOUNT` email
- Creates or finds the `Super Admin` role
- Assigns that role to the user
- Warns if session/cache/queue use database drivers

### 5. Clear caches

```bash
php artisan config:clear
php artisan optimize:clear
```

### 6. Verify

| Test | Expected result |
|---|---|
| Visit `/login` | See demo login page with disabled email field |
| Click "Enter Demo Site" | Logged in as Super Admin, redirected to `/home` |
| Navigate to manage section | All menus visible (users, pages, indicators, etc.) |
| Create a page | Redirected back with banner: *"This is a demo site. Visitors cannot make changes."* |
| Edit a user | Same banner |
| Modify settings | Same banner |
| Log out | Returned to login page |

---

## Demo login page

The demo login (`/login`) shows a disabled email field with the configured `DEMO_ACCOUNT` address and a single "Enter Demo Site" button. The `POST /login-demo` route finds the user by email and calls `Auth::login()` directly — no Fortify credential validation, no password confirmation.

---

## Write paths that are skipped in demo mode

These actions return early to avoid hitting the read-only DB, providing smoother UX than catching the exception after the fact:

| Component / Action | What is skipped |
|---|---|
| `CreateArtefactAction` | Artefact creation (also flashes banner in catch block) |
| `FetchCacheAndRecord` | Analytics `INSERT` for slow queries |
| `InvitationManager` | Create, renew, delete, and resend invitations |
| `AreaRestrictionManager` | Save area restrictions |
| `ColumnMapper` | Save column mappings |

All other write paths (controllers, model boot events, Livewire components, MCP tools) are caught by the global renderable exception handler and show the banner.

---

## Scope / Limitations

| Concern | Status |
|---|---|
| **Queued jobs** (`ImportAreaSpreadsheetJob`, `BulkInvitationJob`, etc.) | Fail at the DB layer. No banner possible (no session in queue worker). |
| **MCP tools** (`CreateIndicator`, `EditGauge`, etc.) | Return API error responses. Not applicable to page visitors. |
| **Artisan commands** | Run with super-admin DB credentials, not the read-only role. |
| **Report generation** | The `last_generated_at` update fails. Reports should not be generated on the demo site. |

---

## Maintaining the branch

The `demo-mode` branch is based on `master` (stable releases). After each `master` tag:

```bash
git checkout demo-mode
git rebase master
git push --force-with-lease origin demo-mode
```

`ChimeraServiceProvider.php` and `routes/web.php` are never touched on this branch, so rebase conflicts are rare and limited to `composer.json`.

---

## Reverting to normal

To disable demo mode and restore the host app to normal operation:

### 1. Revert composer.json

- Remove the `repositories` block (lines pointing to the GitHub repo)
- Change the `require` constraint back to a stable version, e.g. `"^7.2"`

### 2. Revert .env

- Restore the super-admin `DB_USERNAME` and `DB_PASSWORD`
- Remove `CHIMERA_DEMO=true`
- Remove `DEMO_ACCOUNT=...`
- Restore any session/cache/queue drivers you changed

### 3. Run

```bash
composer update uneca/dashboard-starter-kit
php artisan optimize:clear
```

The demo user (`demo@example.com`) remains in the database but is harmless. Delete it via the manage users section or SQL if you want it gone.

---

## File inventory

| File | Purpose |
|---|---|
| `src/DemoServiceProvider.php` | All demo boot logic: login view, exception handler, middleware no-ops |
| `routes/demo.php` | `/login-demo` route |
| `resources/views/demo/login.blade.php` | Demo login page |
| `src/Commands/DemoSetup.php` | `chimera:demo-setup` command |
| `config/chimera.php` | `demo_mode`, `demo_email` config keys |

**Guard clauses (skip writes in demo mode):**

| File | Methods guarded |
|---|---|
| `src/Actions/Maker/CreateArtefactAction.php` | `execute()` — flashes banner in catch |
| `src/Services/FetchCacheAndRecord.php` | `__invoke()` — skips analytics INSERT |
| `src/Livewire/InvitationManager.php` | `submit()`, `renew()`, `delete()`, `resendEmail()` |
| `src/Livewire/AreaRestrictionManager.php` | `filter()` |
| `src/Livewire/ColumnMapper.php` | `save()` |
