# Offix

Offix is a CRM built on a hand-rolled PHP MVC framework — no Laravel, no Symfony, no Composer. The router, autoloader, database layer, base controller and model, translation system and module loader are all written for this project and live in `app/core/`, totalling a few hundred lines.

It covers contacts, tasks, projects, users and documents as core features, with invoicing and IT asset planning as modules that can be switched on and off from the database. The UI is server-rendered PHP with vanilla JavaScript.

## Project Status

Phase 1 has been delivered against the agreed client scope. Development is currently paused pending the client's direction on the next phase.

Everything described under [Features](#features) is implemented and working in the code. The [Known gaps](#known-gaps) section lists what is unfinished, partially wired, or deliberately deferred — that list is accurate and worth reading before picking the project back up.

## Tech Stack

| Layer | Choice |
|---|---|
| Language | PHP 8.0+ (developed and tested against 8.2) |
| Database | MySQL 5.7+ / MariaDB 10.3+ |
| Frontend | Server-rendered PHP templates, vanilla JS (no build step) |
| Rich text | Quill 1.3.7 (CDN) |
| Icons | Font Awesome 6.5.1 (CDN) |
| Web server | PHP built-in server, or Apache with `mod_rewrite` |

Required PHP extensions: `pdo`, `pdo_mysql`, `mbstring`, `fileinfo`, `session`.

There is no package manager and no build pipeline. Clone it, point it at a database, and it runs.

## Features

### The framework

- **Router** (`app/core/Router.php`) — registers GET and POST routes, matches `{param}` placeholders by converting them to named regex groups, and dispatches to either a controller/action pair or a closure. Unmatched routes render a 404.
- **Autoloader** (`public/index.php`) — PSR-4-style prefix map covering core, controllers, models and each module namespace.
- **Database layer** (`app/core/Database.php`) — singleton PDO connection configured with `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, emulated prepares turned **off**, and utf8mb4.
- **Base model** (`app/core/Model.php`) — thin `query` / `queryOne` / `execute` / `lastInsertId` helpers over PDO that every model inherits.
- **Base controller** (`app/core/Controller.php`) — view rendering with layout wrapping via output buffering, plus redirect, JSON, abort and authorization helpers.
- **Translations** (`app/core/Lang.php`) — flat key-value files per locale, session-backed locale switching, English and Swiss German present.

### CRUD

Full create / read / update / delete across **contacts**, **tasks**, **projects**, **users**, **invoices** and **IT assets**. Every controller follows the same shape: `sanitize()` the input, `validate()` it, re-render the form with errors and prior input on failure, otherwise hand off to the model and redirect with a flash message.

### Recurring tasks

Tasks can repeat daily, weekly, monthly or yearly with a configurable interval and an optional end date. Completing a recurring task automatically spawns the next occurrence at the computed due date and links it back to the parent task, stopping once the end date is passed.

### Contact import

Imports contacts from **CSV** or **vCard (.vcf)**. The CSV parser strips the UTF-8 BOM that Google Contacts exports include and maps both Google's column names (`Given Name`, `E-mail 1 - Value`, `Organization 1 - Name`, and so on) and plain `Name` / `Email` / `Phone` headers. The vCard parser unfolds wrapped lines per RFC 6350, prefers `FN` over `N` for the display name, parses the positional `ADR` field into address components, and decodes quoted-printable where declared. Rows whose email already exists are counted as duplicates and skipped; the result is reported back as imported / duplicate / skipped counts.

### Avatar upload

Profile images are validated server-side: the MIME type is sniffed from the file contents with `mime_content_type()` rather than trusted from the upload, capped at 2 MB, restricted to JPEG/PNG/GIF/WebP, and stored under a randomly generated filename with the extension derived from the detected type.

### Module system

Optional features are rows in a `modules` table. Enabling one from Settings flips a flag, which causes its route file in `routes/modules/` to be loaded on the next request and its entry to appear in the sidebar. Two modules ship: **Invoicing** and **IT Planning**. Module state is cached per request and the cache is busted on toggle.

### Invoicing

Invoices carry any number of line items, with quantity and unit price multiplied into a per-line amount and totalled with a configurable tax rate. Line totals update live in the browser as you type. Invoice numbers are generated as `INV-YYYY-NNNN`, and there is a dedicated print layout.

### Inline editing

Fields on the contact, task and project detail pages can be edited by double-clicking them; the change is saved over a JSON endpoint without a page reload. The endpoint resolves the target table and column through a hardcoded whitelist rather than accepting them from the request.

### Document editor

A rich-text editor backed by Quill, with documents identified by random 32-character hex IDs. Content is autosaved as a Quill delta, every save is written to a version history, and any previous version can be restored. A heartbeat mechanism shows which other users currently have the document open.

## Security

The following are enforced in code:

- **Parameterized queries throughout.** Every query goes through PDO prepared statements with bound parameters, and emulated prepares are disabled. The one endpoint that needs a dynamic table and column name — the inline-edit API — resolves both against a hardcoded whitelist before they reach the SQL string.
- **Role-based authorization on admin controllers.** `UserController`, `SettingsController` and `ModuleController` call an admin check in their constructors, so every action on those routes is covered rather than relying on each method to remember. Non-admins receive a 403 (JSON for API callers, an HTML page otherwise). Hiding the nav links is treated as presentation, not access control.
- **CSRF protection on all state-changing requests.** Enforced once in the front controller for every POST rather than per controller. The token is accepted from a `_token` form field, an `X-CSRF-Token` header, or a `_token` key in a JSON body — the last so `navigator.sendBeacon()` in the document editor can authenticate, since it cannot set headers. Comparison is constant-time. All forms and JSON endpoints carry it, and the token is rotated on login.
- **Session hardening.** The session ID is regenerated on login, closing session fixation. Cookie flags are set explicitly rather than inherited from `php.ini`: `HttpOnly` and `SameSite=Lax` always, and `Secure` whenever the request is actually served over HTTPS — directly, on port 443, or behind a proxy setting `X-Forwarded-Proto`. It is scheme-aware rather than hardcoded so that plain-HTTP local installs remain usable. Logout clears the session array, expires the cookie with the flags it was set with, destroys the session, and starts a clean one.
- **Uploads stored outside the webroot.** Files are written to `uploads/` at the project root, which sits above the `public/` document root, and are served through a controller that strips path traversal with `basename()` and re-checks the MIME type against an allowlist before streaming.
- **Errors do not leak internals.** Uncaught exceptions, missing views and routing failures are logged with full detail and rendered to the user as a generic error page. Stack traces, file paths and SQL error text never reach the browser. `display_errors` is off unless `APP_DEBUG=1` is set in the environment.

Passwords are hashed with bcrypt via `password_hash()`.

## Known gaps

Stated plainly, so nobody rediscovers these the hard way:

- **Currency selection is not wired into invoices.** The settings page offers a full ISO 4217 currency list and persists the active currencies and a default, and `InvoiceController` reads a `currency` field from the submitted form — but there is no `currency` column on the `invoices` table, the model does not store it, and no invoicing view renders a currency selector or symbol. The configuration exists and currently affects nothing.
- **Internationalization is roughly a third done.** 220 translation keys exist in both English and Swiss German, but only 16 of 42 view files reference them. The sidebar, dashboard, list pages, user management, documents and settings are translated; the login page, every create/edit form, the contact and project detail pages, and the entire Invoicing and IT Planning modules are hardcoded English. Switching to Swiss German gives a partly translated interface.
- **The document editor is last-write-wins, not concurrent editing.** Each client posts the whole document on a debounce and polls for a newer server copy, replacing its contents when it finds one. There is no operational transform or CRDT, so two people typing in the same document at the same time will overwrite each other. Presence indicators show who else is open, but they do not prevent this.
- **Duplicate emails are still reachable through the inline-edit API.** Contact create and update validate email uniqueness and show a proper field error. The inline-edit endpoint performs a direct `UPDATE` with no validation, so setting a duplicate email that way hits the unique index, returns a generic 500, and is silently swallowed by the client-side error handler — the field appears to save and reverts on reload.
- **A WebRTC signaling layer exists but is unused.** The `signaling_messages` table, the send/poll endpoints and the offer/answer/candidate message types were built for peer-to-peer editing that was never wired up. The table is currently only used to record editor heartbeats for presence.
- **Every autosave writes a full version row.** `document_versions` stores an entire copy of the document on each save with no pruning, so the table grows without bound during sustained editing. The history view caps its display at 100 entries; the table itself does not.
- **No automated tests.** No test suite, no CI. Changes are verified by hand.
- **No dependency manager.** There is no `composer.json`. Quill and Font Awesome are loaded from CDNs without subresource integrity hashes.
- **Multiple overlapping SQL files with no migration runner.** Six `.sql` files with no ordering metadata or tracking table, plus `thomas_crm_schema.sql`, which is a full `mysqldump` snapshot that begins with `DROP TABLE` on every table — do not run that one against a database you care about. The correct order is documented below. The bundled setup manual omits the two `documents` migrations entirely, so following it leaves the document editor without its tables.
- **Quill is pinned to 1.3.7**, a 2019 release. Worth reviewing against current advisories before this goes anywhere public.
- **Roles are declared but not fully differentiated.** The `users.role` column allows `admin`, `manager` and `user`, and authorization enforces admin versus non-admin. `manager` currently has the same effective access as `user`.

## Project structure

```
app/
  controllers/        Request handling, one per resource
  core/               The framework: Router, Controller, Model, Database,
                      Lang, ModuleManager, helpers
  models/             Data access, one per table
  modules/            Optional features (invoicing, it_planning), each with
                      its own controller and model
  views/
    shared/           Layouts (main, auth, iframe, print), error pages
    <resource>/       index / create / edit / show templates
config/
  database.php        Connection settings
lang/
  en/app.php          English strings
  de_CH/app.php       Swiss German strings
public/               Document root — index.php front controller, CSS, JS,
                      .htaccess
routes/
  web.php             Core routes
  modules/            Per-module route files, loaded only when enabled
uploads/              Outside the webroot; avatars/ is used, logos/ is not
                      currently referenced anywhere in the code
docs/setup_manual.html
*.sql                 Schema and migrations
```

## Getting started

### 1. Requirements

PHP 8.0 or newer with `pdo_mysql`, `mbstring` and `fileinfo` enabled, and MySQL 5.7+ or MariaDB 10.3+. XAMPP or Laragon both provide all of this.

### 2. Database

Create the database and run the SQL files **in this order** — later files depend on tables created by earlier ones, and the `documents` migration has a foreign key onto `users`:

```sql
1. schema.sql                          -- creates the database, contacts, projects, tasks (+ sample rows)
2. schema_migration_phase2.sql         -- modules, invoices, invoice_items, it_assets, recurring task columns
3. schema_migration_users.sql          -- users table and the default admin account
4. schema_migration_settings.sql       -- settings key-value table
5. schema_migration_documents.sql      -- documents, document_versions, signaling_messages
6. schema_migration_documents_v2.sql   -- adds documents.last_editor_id
```

From the command line:

```bash
mysql -u root -p < schema.sql
mysql -u root -p thomas_crm < schema_migration_phase2.sql
mysql -u root -p thomas_crm < schema_migration_users.sql
mysql -u root -p thomas_crm < schema_migration_settings.sql
mysql -u root -p thomas_crm < schema_migration_documents.sql
mysql -u root -p thomas_crm < schema_migration_documents_v2.sql
```

Ignore `thomas_crm_schema.sql` unless you specifically want to restore a dump — it drops every table first.

> The database is named `thomas_crm` and the default account uses a `@thomascrm.com` address, carried over from the project's original working name.

### 3. Configuration

Edit `config/database.php`:

```php
return [
    'host'     => 'localhost',
    'dbname'   => 'thomas_crm',
    'username' => 'root',
    'password' => '',
    'charset'  => 'utf8mb4',
];
```

Make sure `uploads/avatars/` is writable by the web server.

### 4. Run it

With the built-in server:

```bash
php -S localhost:8080 -t public
```

Then open <http://localhost:8080>.

Under Apache, point the virtual host's document root at `public/` and make sure `mod_rewrite` is enabled — `public/.htaccess` routes everything through the front controller.

To see errors on screen while developing, set `APP_DEBUG=1` in the environment. Leave it unset everywhere else.

### 5. First login

```
Email:    admin@thomascrm.com
Password: admin1234
```

Change this immediately — the hash ships in `schema_migration_users.sql` and is public to anyone with the repository. Then visit Settings → Modules to enable Invoicing or IT Planning if you want them.
