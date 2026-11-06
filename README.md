# Counterpoint

A small Laravel POS account manager for customers and user profiles. It includes create/edit forms, field validation, avatar processing, fictional seed data and a responsive Blade interface. It deliberately has **no authentication, passwords, roles, sales, inventory or payments**.

**Public demo warning:** there is no access control. Anyone who can reach the app can view, create and edit records. Use fictional information only until authentication is implemented.

## Delivery status

Built and locally verified on **6 November 2026 (Asia/Manila)**. The instructor-approved image package is **Intervention Image v3 with GD**. See the verification table below and [TASKS.md](TASKS.md).

- GitHub repository: [counterpoint-tfa7](https://github.com/howardcallanta00-ship-it/counterpoint-tfa7), branch `main`.
- Live Render URL: **not deployed**. Account access, free-plan eligibility and external credentials are pending.
- Proposed production architecture: Render Free web service + one Supabase Free project containing PostgreSQL and S3-compatible Storage. No paid resource, billing activation or card submission has been performed.

## Local installation

Requires PHP **8.2+**, Composer 2, and extensions GD (JPEG/PNG), EXIF, fileinfo, mbstring, PDO, pdo_sqlite, OpenSSL, ctype, DOM, XML and tokenizer. Use PHP 8.3 in the production container. Node, npm, a CDN and a frontend build are not required.

From the app directory:

```sh
composer install
```

Copy `.env.example` to `.env` (`Copy-Item .env.example .env` in PowerShell, or `cp .env.example .env` on macOS/Linux). Then:

```sh
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan serve
```

Open `http://localhost:8000`. Keep `APP_URL` in `.env` consistent with your actual local address. `PUBLIC_DISK_DRIVER=local` stores photos in `storage/app/public`; the public symlink serves them at `/storage/avatars/...`. On Windows, creating symlinks may need Developer Mode or an elevated terminal.

Run `php --ini` to locate the active configuration. Enable `extension=gd`, `extension=exif` and `extension=fileinfo` if absent, and confirm with `php -m`. If using a separate PHP configuration, set `PHPRC` to its absolute path **before** `php artisan serve`, so the child web server uses the same extensions as the CLI. No machine-wide PHP settings were changed while building this project.

The image package and S3 adapter are already in `composer.json` and the lockfile; `composer install` installs both. No Laravel service-provider package or published Intervention configuration is needed. `AvatarService` directly creates Intervention's GD `ImageManager`.

### Optional MySQL locally

Create a UTF-8 database and set `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT=3306`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` in `.env`. Enable `pdo_mysql`. Run `php artisan config:clear`, then migrations and seeders. Do not use `migrate:fresh` on a database containing work to preserve.

SQLite was exercised locally; external PostgreSQL and MySQL connections have not yet been tested in this delivery.

## How to explain the app

| File or directory | Responsibility |
| --- | --- |
| `routes/web.php` | Named routes for home, about and resource-style account actions. |
| `app/Http/Controllers` | Read records, display views, save validated data and redirect with flash messages. |
| `app/Http/Requests` | Validation and friendly messages; the update uniqueness rule ignores the bound user's ID. |
| `app/Models` | Eloquent records with explicit `$fillable` fields. These users are ordinary models, not login identities. |
| `database/migrations` | Create the two account tables, then add avatar in a separate reversible migration. |
| `database/seeders` | Five Filipino-friendly customers and five users. `firstOrCreate` makes reruns safe and preserves edited names/photos. |
| `app/Services/AvatarService.php` | Correct orientation, center-crop, encode JPEG, write/read/delete using the disk named `public`. |
| `app/Providers/AppServiceProvider.php` | HTTPS in production and Supabase's S3 compatibility adjustment. |
| `resources/views` | Escaped Blade views and reusable UI components. Old form input takes priority over model values. |
| `public/css/app.css` | All interface styling, shared tokens and responsive breakpoints. |
| `public/js/app.js` | Mobile menu, error-summary focus and image-load fallback. |
| `tests/Feature` | Account, avatar, schema and S3 request verification. |
| `deploy`, `Dockerfile`, `render.yaml` | Production container, safe startup and free Render configuration. |

Follow a request: `GET /customers/create` → controller → Blade form → `POST /customers` → `CustomerRequest` validation → `Customer::create(validated data)` → redirect → listing and flash. Editing uses implicit route model binding; nonexistent IDs produce the styled 404 page. Forms include CSRF protection and PUT method spoofing.

### Schema choices

`customers`: ID, full_name (100), email (100), nullable phone (20), created_at. `users`: ID, unique username (50), full_name (100), created_at; nullable avatar is added later. **Both models set `UPDATED_AT = null`**, keeping Laravel's automatic `created_at` behavior without adding `updated_at`. There are no password, email, remember_token or role columns on users. Customer email is not unique because the assignment does not require that constraint; seeders use it as their lookup key.

Demo emails use the reserved `example.test` domain. Phone strings visibly begin with `DEMO` to avoid assigning real phone numbers.

## Avatar behavior and persistence

Accepted inputs are real JPEG/PNG files, maximum 2048 KB. Both MIME-based extension detection and the actual MIME type are checked. A 4096×4096 dimension limit also avoids excessive image allocations. The form documents this limit.

Intervention v3 automatically applies EXIF orientation, then `cover(256, 256, 'center')` creates a square JPEG at quality 85. A UUID filename prevents collisions. The database stores **only** `avatars/<uuid>.jpg`. Every file operation uses `Storage::disk('public')`, and display URLs come from that disk's `url()` method. Null paths, missing files and failed browser loads use a bundled SVG placeholder.

On replacement:

1. Process and store the new photo.
2. In a database transaction, lock the current user row and save validated values and the new relative path.
3. After commit, delete the old photo.

Processing/write failures return an avatar field error. Database failures roll back the row and remove the newly stored file. An empty upload leaves the current avatar unchanged. Cleanup failures are logged; they do not erase a successfully saved record or show a 500. If the storage service itself refuses deletion, cleanup cannot be guaranteed: inspect stderr and retry removal after recovery. A process kill between file write and database commit can also leave an unreferenced object; there is no distributed transaction between SQL and S3.

### Why redefine `public` instead of changing upload code?

Render's local filesystem is temporary. Production therefore switches **the same disk name** to Laravel's S3 adapter via `PUBLIC_DISK_DRIVER=s3`; local development leaves it `local`. This is the first storage approach requested in the assignment. No Cloudinary SDK, stored URL, stored binary or paid disk is used.

Supabase provides both the database and S3-compatible object storage, reducing the proposal to two providers. Its public bucket makes photos readable; only server-held S3 credentials permit writes. Never expose the S3 secret in Blade, JavaScript, GitHub or a screenshot.

Supabase does not implement object ACLs. The AWS SDK adds an ACL by default even without a visibility option, so `AppServiceProvider` removes that command field before requests are built. The SDK is configured to calculate optional checksums only when required. An offline test drives the actual S3 adapter/SDK and verifies PUT, HEAD, DELETE, URL construction and the absence of ACL and optional CRC32 headers. **This test does not prove a live Supabase connection.**

## Free-service research gate

Official documentation checked on 6 November 2026. This is a researched deployment proposal; card requirements for the user's actual accounts remain unverified. If either dashboard requests a card, stop and ask before entering it. Never enable billing, paid add-ons, paid disks or an auto-converting trial.

| Service | Free plan exists now | Relevant limits | Card required | Expires or trial-to-paid | Persistence across app restarts/redeploys | Fit |
| --- | --- | --- | --- | --- | --- | --- |
| Render Free web | Yes | 750 instance-hours/workspace/month; sleeps after 15 idle minutes; cold start about a minute; usage/build limits apply. | Docs explicitly describe operation without a payment method; account-specific verification not observed. | No timed web-service trial stated. Limits can suspend service. | Local files are lost. External DB/S3 are independent of app lifecycle. | Required app host; use Docker for PHP. |
| Render Free PostgreSQL | Yes | 1 GB, one free database/workspace; no backups or managed pooling. Exact connection ceiling not verified. | Actual account requirement not observed. | Expires after 30 days; deleted after a further 14-day grace period unless upgraded. | Survives app lifecycle only while database remains active. | Rejected: requires paid upgrade to keep using it beyond expiry. |
| Supabase Free PostgreSQL + Storage | Yes | Two active projects; 500 MB DB; 1 GB file storage; 5 GB egress + 5 GB cached egress; Nano: 60 DB connections, 200 pooler clients. S3 API available. | A no-card guarantee was not located in the consulted official pages; dashboard confirmation pending. | Free plan is distinct from paid plans; inactivity can pause a project. Restoration documented for up to one year after pause. | External data and objects survive Render restarts; paused provider must be resumed. No indefinite retention guarantee. | Proposed: one project supplies both SQL and S3. Requires setup and live verification. |

Sources: [Render free instances](https://render.com/docs/free), [Render Docker](https://render.com/docs/docker), [Render web services and PORT](https://render.com/docs/web-services), [Render pricing](https://render.com/pricing), [Supabase pricing](https://supabase.com/pricing), [Supabase billing](https://supabase.com/docs/guides/platform/billing-on-supabase), [Supabase connection limits](https://supabase.com/docs/guides/platform/compute-and-disk), [Supabase pausing and restoration](https://supabase.com/docs/guides/platform/free-project-pausing), [S3 compatibility](https://supabase.com/docs/guides/storage/s3/compatibility), [S3 credentials and endpoint](https://supabase.com/docs/guides/storage/s3/authentication), [database connection methods](https://supabase.com/docs/guides/database/connecting-to-postgres).

Render can restrict heavy outbound service traffic. Keep all accounts on Free and without a payment method; exceeding available quotas can interrupt service. Supabase can pause after a week of low activity; resume it in its dashboard before an instructor demo if needed. Keep your own SQL/object exports. **No uptime promise or automated anti-sleep traffic is included.** Clever Cloud and Cloudinary were not selected; neither is needed by this design.

## Render deployment

Publishing resumes once the repository URL is supplied. Inspect its branch and contents first, preserve existing work, commit the app with a clear message and push without force-pushing. Do not initialize against or overwrite an assumed remote.

### 1. Prepare the external data project

In an authorized Supabase account, select an organization on **Free** and create one Free project. Do not add a payment method or purchase an IPv4 add-on. If creating the project requires a card, request approval before continuing.

Use the **shared session pooler**, port **5432**, for IPv4 connectivity and normal prepared-statement behavior. Copy its exact host and username from the Connect panel. This app uses a small Apache worker pool; provider connection limits still apply. Use the database password and `DB_SSLMODE=require`.

Create a **public bucket named `avatars`** in Storage. Generate S3 access credentials in Storage settings (not the anon key and not a browser-exposed key). Keep writes server-side; do not create anonymous insert/update/delete policies. Obtain the region and S3 endpoint from those settings. See the official S3 authentication page above.

Set `AWS_URL` to the public object base URL ending in `/storage/v1/object/public/avatars`, with no trailing slash. `AWS_ENDPOINT` is the S3 API endpoint, typically `https://<project-ref>.storage.supabase.co/storage/v1/s3`. These serve different purposes. Because the bucket is `avatars` and the saved relative path starts with `avatars/`, public image URLs intentionally contain `/avatars/avatars/<uuid>.jpg`.

### 2. Create the Render Free web service

Connect the supplied GitHub repository and choose **Docker** and **Free**. The app must be the repository root, or Render's root directory must point to its subdirectory. `render.yaml` defines only a free web service, with no database add-on or disk. Manual dashboard setup is also valid if Blueprint creation asks for billing; do not submit a card or select a paid plan.

Generate a Laravel key locally with `php artisan key:generate --show`; save its entire `base64:...` value as the Render **APP_KEY secret**. Generate once and preserve across deployments. Do not generate a new key on every boot or commit it. Set `APP_URL` to the assigned HTTPS Render address before the final deployment.

### 3. Environment variables

Set these in Render, using its secret fields for sensitive values. `.env.example` contains no credentials.

| Names | Values / purpose |
| --- | --- |
| `APP_ENV`, `APP_DEBUG` | `production`, `false` |
| `APP_KEY`, `APP_URL` | Persistent Laravel secret; actual HTTPS Render URL |
| `LOG_CHANNEL` | `stderr` |
| `SESSION_DRIVER`, `SESSION_ENCRYPT`, `SESSION_SECURE_COOKIE` | `cookie`, `true`, `true` |
| `CACHE_STORE`, `QUEUE_CONNECTION` | `file`, `sync`; no database cache/session/job tables needed |
| `DB_CONNECTION`, `DB_HOST`, `DB_PORT` | `pgsql`, exact Supabase session-pooler host, `5432` |
| `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_SSLMODE` | `postgres`, exact pooler username, secret password, `require` |
| `PUBLIC_DISK_DRIVER` | `s3` |
| `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY` | Supabase S3 credentials; server secrets |
| `AWS_DEFAULT_REGION`, `AWS_BUCKET` | Exact project region; `avatars` |
| `AWS_ENDPOINT`, `AWS_URL` | S3 API endpoint; public object base URL described above |
| `PORT` | Injected by Render; defaults to `10000` in the image |

`DB_URL` should remain unset, so it cannot override the discrete database settings. There is no need for a Supabase client SDK, auth provider or service-role key in this app.

### 4. What startup does

The Docker image installs PHP extensions and locked production dependencies, uses Apache with `public/` as the document root, and runs as `www-data` on an unprivileged `$PORT`. Startup validates required environment variables, clears configuration, runs **`migrate --force` and repeat-safe `db:seed --force`**, then caches configuration, routes and views. It never resets the database. A failed migration stops startup.

Render health checks `/up`. Laravel trusts the proxy and forces generated URLs to HTTPS in production. Application output goes to stderr. Sessions use encrypted cookies so they do not depend on temporary local files. A stable APP_KEY is essential.

The Docker build has not been executed locally because Docker is unavailable. The repository's GitHub Actions workflow runs the suite and builds the image when publishing makes it available; workflow runs have not yet been observed. Keep deployment status pending until an actual build and health check succeed.

## Verification

Run with an active PHP configuration containing GD and EXIF:

```sh
php artisan config:clear
php artisan test
vendor/bin/pint --test
composer validate --strict
```

On Windows the equivalent PHPUnit command is `php vendor/bin/phpunit`. Tests use in-memory SQLite, `Storage::fake('public')` and fake uploaded images, and do not contact a live cloud account. The S3 compatibility test uses an AWS mock transport with the real adapter and SDK.

Observed results: **23 tests, 160 assertions, all passing**. Two generated example tests were removed; the suite contains project-specific checks. Laravel config, route and Blade view caching also succeeded and caches were cleared afterward.

The source ZIP was extracted into a separate clean directory. Locked dependencies installed successfully; fresh SQLite migrations and seeding, the full 23-test suite, formatting verification and Composer validation all passed there too. The ZIP excludes `.env`, dependencies, local database files, uploaded photos, caches and logs.

| Required check | Result | Evidence / limitation |
| --- | --- | --- |
| 1. Required pages and navigation | Passed locally | Page tests; browser checks of home, about, listings, both create/edit forms, 404, `/up`; mobile navigation operated. |
| 2. Customer create/edit and flashes | Passed locally | Automated tests and browser form submissions. |
| 3. Required fields and email format | Passed locally | Tests and browser invalid-email submission. |
| 4. User create/edit | Passed locally | Tests and browser submissions. |
| 5. Username uniqueness and unchanged own name | Passed locally | Tests cover duplicate create/update and unchanged own username. |
| 6. Field errors and old input | Passed locally | Tests; browser confirmed error-summary focus and preserved invalid input. |
| 7. Missing customer/user 404 | Passed locally | GET and update tests; styled missing-page browser check. |
| 8. Real JPEG/PNG processing, storage, display | Passed locally | Exact 256×256 JPEG output, center crop and synthetic EXIF orientation; browser image dimensions. |
| 9. Invalid type and over-2MB upload | Passed locally | PDF, GIF, spoofed JPEG, oversized upload and dimension-limit tests. |
| 10. No-upload preserves photo | Passed locally | Automated and browser checks. |
| 11. Safe replacement and forced failures | Passed locally | Old file exists at save time; replacement cleanup; DB create/update rollback; processor failure and partial write cleanup. |
| 12. Missing/null avatar fallback | Passed locally | Placeholder tests and observed seeded placeholders. |
| 13. Seeding twice without duplicates | Passed locally | Tests also verify edited names survive reseeding. |
| 14. Live use and restart persistence | **Not verified** | No repository URL, Render service or Supabase credentials yet. |
| Responsive 375 / 768 / 1280 | Passed locally | Browser page checks; no page-wide overflow after fixing mobile table caption positioning; tables scroll inside cards. Screenshots reviewed. |
| Basic accessibility | Passed local checks | Labels, descriptive avatar alt, focus, summary links, semantic regions, keyboard menu, 44px controls and text contrast checked. Not a full screen-reader audit. |
| S3 request compatibility | Passed offline | Actual adapter/SDK: relative key, public URL, no ACL, PUT/HEAD/DELETE. Live credentials/network unverified. |
| Docker / production PostgreSQL | **Not verified** | Docker unavailable; no external database provisioned. |
| GitHub push / Render deploy | **Not performed** | Waiting for repository URL and authorized account access. |
| Clean source archive installation | Passed locally | Separate extraction, dependency installation, migrations, repeat seeding, tests, formatting and Composer validation. |

### Live acceptance procedure still required

After a successful free deployment, open home, about, both listings and create/edit pages. Create and edit fictional customer and user records. Upload a JPG/PNG, record the user's ID, avatar URL and relative database path, and confirm the image decodes to 256×256. Test a rejected upload and an unchanged username. **Trigger an actual Render restart or redeploy**, wait for `/up`, then confirm the same record IDs, edited values and avatar URL still work. Record the deployment ID, timestamps and observed results here. A passing local test or a normal page refresh is not proof of cloud persistence.

## Remaining actions

The supplied repository is `https://github.com/howardcallanta00-ship-it/counterpoint-tfa7.git`; it was inspected and contained no refs before the initial publication. Deployment requires authorized Render access and a Supabase Free project. Confirm the accounts permit the proposed free setup without a card. Credentials should be entered directly in service secret settings, not pasted into public project files. If the services cannot satisfy the free/card constraints, stop and agree on a specific alternative before deployment.
