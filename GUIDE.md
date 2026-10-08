# Uptime Monitor: Architecture, Standards and Deployment Guide

A beginner-friendly plan for building the Uptime Monitor portfolio project in Laravel 13, testing it automatically on GitHub, packaging it with Docker, and deploying it to a free Google Cloud server.

Follow the stages in order. Each stage ends with a "Done when" checklist. Do not start the next stage until the current one is done and pushed to GitHub.

Ready-to-copy config files are in the `templates/` folder next to this guide. Section 9 explains each one.

---

## 1. What you are building

**The product:** users add website URLs. The app checks each URL on a schedule (every 1, 5 or 15 minutes), records whether it was up and how fast it responded, opens an incident and alerts the owner when a site goes down, and shows a public status page.

**First version (stages 1 to 4):**
- Login, registration and password reset, using Laravel Fortify with your own Blade pages
- Add, edit, pause and delete monitors
- Scheduled checks that run in the background through a queue
- Incidents: opened after a few failed checks in a row, closed when the site recovers
- Email alerts on down and recovery
- A dashboard with status, uptime percentage and a response-time chart
- A public status page per user

**Later additions:** Telegram alerts, Redis and Horizon, checking many sites in parallel, SSL certificate expiry warnings.

---

## 2. Tech stack and why

| Part | Choice | Why |
|---|---|---|
| Framework | Laravel 13 (needs PHP 8.3+), plain skeleton with no starter kit | Current version. Writing every screen yourself shows more of your own work on GitHub |
| Templates | Blade with components (`<x-layouts.app>`, `<x-form.input>`) | Laravel's own engine, which you already know. Building reusable components and layouts yourself, instead of copying a starter kit, shows modern Blade skills |
| Authentication | Laravel Fortify | Official Laravel package that handles login, registration and password reset logic with no screens of its own, so you write the pages in Blade |
| Styling | Tailwind CSS 4 | Already included in the plain Laravel skeleton through Vite |
| Interactivity | Alpine.js | Small, Laravel-friendly library for dropdowns, toggles and delete confirmations |
| Charts | Chart.js | Simple, well-documented response-time and uptime charts |
| Database | MySQL 8 | Same as your work projects |
| Queue | Database driver first, Redis later | Database needs no extra setup; Redis comes in stage 6 |
| Tests | Pest | Laravel's default test framework option, easy to read |
| Code style | Laravel Pint | Auto-formats code to the Laravel standard |
| Static analysis | Larastan | Finds bugs without running the code |
| Packaging | Docker | One image runs the same way on your laptop and the server |
| Process manager | Supervisor (inside the container) | Keeps PHP-FPM, Nginx, the queue worker and the scheduler running |
| HTTPS | Caddy | Gets and renews free Let's Encrypt certificates automatically |
| CI/CD | GitHub Actions | Free for public repositories |
| Image registry | GitHub Container Registry (ghcr.io) | Free, built into GitHub |
| Server | Google Cloud e2-micro (Always Free) | Real Linux server with admin access, no time limit |

---

## 3. Architecture

### How a check works

```
Every minute
  Laravel scheduler
    -> runs command: monitors:dispatch-checks
         -> finds monitors that are due (next_check_at <= now, not paused)
         -> puts one RunMonitorCheck job per monitor on the queue

Queue worker (always running)
  RunMonitorCheck job
    -> sends an HTTP request to the URL with a timeout
    -> saves a Check row (status code, response time, up or down, error)
    -> updates the monitor (last_checked_at, next_check_at, consecutive_failures)
    -> if failures reach the threshold and the monitor was up:
         mark it down, open an Incident, fire the MonitorWentDown event
    -> if the check succeeded and the monitor was down:
         mark it up, resolve the Incident, fire the MonitorRecovered event

Event listeners (queued)
  MonitorWentDown  -> send "site is down" notification (email, later Telegram)
  MonitorRecovered -> send "site is back up" notification
```

**Why this design:** the scheduler only decides what is due and stays fast. The slow part, waiting for websites to respond, happens in queue jobs, so one slow site never delays the others. Events keep the alert logic separate from the check logic, so adding Telegram later means adding one listener, not editing the check job.

### How it runs in production

```
Visitor's browser
   | HTTPS (443)
   v
Caddy container  -- gets the SSL certificate automatically
   | HTTP
   v
App container (one Docker image, managed by Supervisor)
   - Nginx        serves pages and passes PHP requests to PHP-FPM
   - PHP-FPM      runs Laravel for each web request
   - Queue worker runs RunMonitorCheck jobs and queued listeners
   - Scheduler    runs the Laravel scheduler every minute
   |
   +--> MySQL container  (data saved in a Docker volume)
   +--> Redis container  (queue, cache, sessions; from stage 6)
```

### How code gets to the server

```
You push to a feature branch -> open a pull request
  GitHub Actions "tests" job: Pint, Larastan, Pest
You merge into main
  "tests" job runs again
  "deploy" job:
     builds the Docker image
     pushes it to ghcr.io
     connects to your server over SSH
     server pulls the new image, restarts containers, runs migrations
```

---

## 4. Database design

| Table | Key columns | Notes |
|---|---|---|
| users | from Laravel's default migration (Fortify adds two-factor columns), plus `telegram_chat_id` later | |
| monitors | `user_id`, `name`, `url`, `method` (GET/HEAD), `expected_status` (default 200), `interval_minutes`, `timeout_seconds`, `failure_threshold` (default 3), `status`, `consecutive_failures`, `is_paused`, `last_checked_at`, `next_check_at` | Index on `next_check_at` so finding due monitors stays fast |
| checks | `monitor_id`, `status_code` (nullable), `response_time_ms` (nullable), `is_up`, `error` (nullable), `checked_at` | Index on (`monitor_id`, `checked_at`). Delete rows older than 30 days automatically |
| incidents | `monitor_id`, `started_at`, `resolved_at` (nullable), `cause` | Open incident = `resolved_at` is null |
| status_pages | `user_id`, `title`, `slug` (unique), `is_public` | |
| monitor_status_page | `monitor_id`, `status_page_id` | Pivot table: which monitors appear on which page |

Rules:
- Every change to the database goes through a migration. Never edit tables by hand.
- Use foreign keys with `->constrained()->cascadeOnDelete()` so deleting a monitor deletes its checks and incidents.
- Write a factory for every model and a seeder that creates demo data. You will need it for tests and for the live demo.

---

## 5. Code structure and standards

### Where code goes

```
app/
  Actions/            One class per business action, with a handle() method
                      e.g. CreateMonitor, PerformCheck, OpenIncident, ResolveIncident
  Console/Commands/   DispatchDueChecks (the command the scheduler runs)
  Enums/              MonitorStatus (Pending, Up, Down, Paused)
  Events/             MonitorWentDown, MonitorRecovered
  Http/Controllers/   Thin controllers
  Http/Requests/      Form Requests: StoreMonitorRequest, UpdateMonitorRequest
  Jobs/               RunMonitorCheck
  Listeners/          SendDownAlert, SendRecoveryAlert (queued)
  Models/             Monitor, Check, Incident, StatusPage
  Notifications/      MonitorDownNotification, MonitorRecoveredNotification
  Policies/           MonitorPolicy, StatusPagePolicy
  Providers/FortifyServiceProvider.php   Tells Fortify which Blade page to show for login, register and so on
config/uptime.php     Default timeout, failure threshold, retention days
routes/console.php    Schedule definitions
resources/views/      Blade templates, all ending in .blade.php
  components/
    layouts/app.blade.php    Main layout, used as <x-layouts.app>: navigation, flash message, page content
    layouts/guest.blade.php  Layout for login and register pages, used as <x-layouts.guest>
    form/input.blade.php     Text field with label and validation error, used as <x-form.input>
    form/select.blade.php    Dropdown with label and validation error, used as <x-form.select>
    status-badge.blade.php   Coloured Up / Down / Paused label, used as <x-status-badge>
  auth/               login, register, forgot-password, reset-password
  dashboard.blade.php Overview of all monitors
  monitors/           index, create, edit, show
  status/show.blade.php  Public status page
resources/js/app.js   Starts Alpine.js and draws Chart.js charts
resources/css/app.css Tailwind CSS
tests/Feature/        Tests that hit routes, jobs and commands
tests/Unit/           Tests for small pieces of logic
```

### Rules to follow

1. **Thin controllers.** A controller method should only: validate with a Form Request, authorize with a Policy, call an Action, and return a response. Business logic lives in Actions.
2. **Never call `env()` outside the `config/` folder.** Put settings in `config/uptime.php` and read them with `config('uptime.failure_threshold')`. Config caching in production breaks `env()` calls elsewhere.
3. **Authorize everything.** Users must only see and change their own monitors. Write a test that proves user A cannot open user B's monitor.
4. **Use enums for statuses** and cast them on the model, instead of plain strings like `'down'`.
5. **Make jobs safe to repeat.** Give `RunMonitorCheck` a timeout, a retry limit, and `ShouldBeUnique` keyed by the monitor id, so two checks for the same monitor never run at once.
6. **Keep old data under control.** Make the `Check` model `Prunable` (older than 30 days) and schedule `model:prune` daily.
7. **Strict models in development.** In `AppServiceProvider::boot()` add `Model::shouldBeStrict(! app()->isProduction());`. It warns you about N+1 queries and typos in attribute names.
8. **Eager load relationships** on list pages (`Monitor::with('latestCheck')`) to avoid N+1 queries.
9. **Every feature comes with tests.** Use `Http::fake()` to simulate websites being up or down, `Event::fake()` and `Notification::fake()` to check alerts, and `Queue::fake()` to check jobs are dispatched.
10. **Run Pint before every commit** (`vendor/bin/pint`). CI fails if code is not formatted.
11. **Keep logic out of templates.** Templates only display data. Calculate uptime percentages, counts and chart data in the controller or an Action, and pass plain values to `view('monitors.show', [...])`. No database queries inside Blade files.
12. **Build with components, not copy-paste.** Pages wrap their content in a layout component (`<x-layouts.app title="Monitors">...</x-layouts.app>`). Anything repeated, such as form fields and status badges, becomes a component in `resources/views/components/`.
13. **Print with `{{ }}`, never `{!! !!}`, for user data.** `{{ }}` escapes HTML, which stops script injection. Never use `{!! !!}` on monitor names, URLs or anything a user typed.
14. **Protect every form.** Add `@csrf` to every form. Edit and delete forms also need `@method('PUT')` or `@method('DELETE')`. Show validation errors with `@error('url') ... @enderror` and keep what the user typed with `old('url')`. The form components in the templates folder do both for you.
15. **Check permissions with `@can`.** Use `@can('update', $monitor)` to show or hide buttons. The Policy still checks every request on the server, so hiding a button is only for looks.
16. **Test pages.** In Pest, check the response with `->assertOk()->assertViewIs('monitors.index')->assertSee($monitor->name)`.

### Naming conventions

- Models singular (`Monitor`), tables plural (`monitors`), columns `snake_case`.
- Resource routes: `Route::resource('monitors', MonitorController::class)`.
- Boolean columns read like questions: `is_paused`, `is_up`, `is_public`.
- Events in past tense (`MonitorWentDown`), listeners as actions (`SendDownAlert`).

---

## 6. Git and GitHub workflow

- **Repository:** public, named `uptime-monitor`, on your personal GitHub account.
- **Branches:** `main` is always deployable. Do each piece of work on a branch such as `feature/monitor-crud` or `fix/timeout-handling`.
- **Pull requests:** open a PR for every branch, even though you work alone. Read your own diff before merging. Recruiters can see this history.
- **Protect main** (Settings > Branches): require a pull request and require the "tests" check to pass before merging.
- **Commit messages:** short and specific, in the style `type: what changed`.
  - `feat: add monitor create and edit screens`
  - `fix: treat connection timeouts as failed checks`
  - `test: cover incident opening after three failures`
  - `chore: add Pint and Larastan to CI`
- **Never commit `.env`.** Keep `.env.example` up to date with every new setting, using fake values.
- **Tag milestones:** after each stage, create a release such as `v0.1.0` with a short note of what was added.

---

## 7. Local setup on this computer

### 7.1 Use PHP 8.4 from Herd, not XAMPP's PHP 8.2

Laravel 13 needs PHP 8.3 or newer. This computer has Laravel Herd installed with PHP 8.4, but XAMPP's PHP 8.2 comes first in the Windows PATH.

1. Open Windows Settings, search for "Edit the system environment variables", then click Environment Variables.
2. In both your user Path and the system Path, move `C:\Users\ic-de\.config\herd\bin` above `C:\xampp\php` and `D:\xampp\php`.
3. Open a new terminal and run `php -v`. It should say PHP 8.4.
4. Run `composer --version` to confirm Composer still works.

Your old XAMPP projects use PHP 8.2. If one breaks, you can switch that project's PHP version in the Herd app.

### 7.2 Create the project

```bash
cd D:/Projects            # or any folder outside XAMPP's htdocs
laravel new uptime-monitor
```

Choose when asked (the exact wording of the questions may differ slightly):
- Starter kit: **None**
- Testing framework: **Pest**
- Database: **MySQL**
- Run npm install and build: **Yes**

Then add Fortify and the front-end libraries:

```bash
cd uptime-monitor
composer require laravel/fortify
php artisan fortify:install
npm install alpinejs chart.js
```

- `fortify:install` creates `config/fortify.php`, `app/Providers/FortifyServiceProvider.php`, the classes in `app/Actions/Fortify/` and a migration. In `config/fortify.php`, keep `registration` and `resetPasswords` in the `features` list and comment out `twoFactorAuthentication` for now. Two-factor login is a good feature to add later.
- Copy `templates/resources/js/app.js` over `resources/js/app.js`.
- Copy the Blade files from `templates/resources/views/` into `resources/views/`, keeping the same folders. Delete `welcome.blade.php` once you have your own home page.
- Tell Fortify which pages to show. In `app/Providers/FortifyServiceProvider.php`, inside `boot()`, add:
  ```php
  Fortify::loginView(fn () => view('auth.login'));
  Fortify::registerView(fn () => view('auth.register'));
  Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
  Fortify::resetPasswordView(fn ($request) => view('auth.reset-password', ['request' => $request]));
  ```
  The template folder has `auth/login.blade.php` as a full example. Write the other three the same way.

### 7.3 Database

Start MySQL in the XAMPP control panel, open phpMyAdmin, and create a database named `uptime_monitor`. Then edit `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uptime_monitor
DB_USERNAME=root
DB_PASSWORD=
QUEUE_CONNECTION=database
```

Run `php artisan migrate`.

### 7.4 Run the app

```bash
composer run dev          # starts the web server, a queue listener, logs and Vite together
php artisan schedule:work # in a second terminal, runs the scheduler every minute
```

Open the URL shown in the terminal, register a user, and you are ready for stage 1.

### 7.5 Install Docker (needed from stage 6)

1. Open PowerShell as Administrator and run `wsl --install`, then restart the computer.
2. Install Docker Desktop from docker.com. It is free for personal use.
3. Run `docker --version` to confirm it works.

---

## 8. Build plan

### Stage 0: Repository and quality tools (1 day)
- Create the GitHub repository and push the fresh Laravel project.
- `composer require --dev larastan/larastan` and copy `templates/phpstan.neon` to the project root.
- Run `vendor/bin/pint`, `vendor/bin/phpstan analyse` and `php artisan test`. All three should pass.
- Copy `templates/.github/workflows/ci-deploy.yml` into `.github/workflows/`, but delete the `deploy` job for now. Push and watch the Actions tab.

**Done when:** the Actions tab shows a green tests run, and main is protected.

### Stage 1: Login pages and monitors (5 to 7 days)
- Layout components (`<x-layouts.app>`, `<x-layouts.guest>`) and the form components.
- Login, register, forgot-password and reset-password pages for Fortify. Protect app routes with the `auth` middleware.
- Tests: a guest is redirected to login, a user can register and log in, wrong passwords are rejected.
- Migration, model, factory and enum for monitors.
- Create, list, edit, pause and delete screens with Form Requests and a Policy.
- Tests: create a monitor, validation errors, user A cannot see user B's monitor.

**Done when:** you can register, log in and manage monitors in the browser, and all tests pass in CI.

### Stage 2: Checks (3 to 5 days)
- Checks table and model.
- `PerformCheck` action, `RunMonitorCheck` job and `monitors:dispatch-checks` command, scheduled every minute in `routes/console.php`.
- Tests with `Http::fake()`: a 200 response is up, a 500 is down, a timeout is down, the job saves a check and moves `next_check_at`.

**Done when:** with `composer run dev` and `schedule:work` running, new check rows appear every minute.

### Stage 3: Incidents and alerts (3 to 4 days)
- Incidents table, `OpenIncident` and `ResolveIncident` actions, the two events and queued listeners, mail notifications.
- Use Mailpit or Laravel's `log` mail driver locally to see the emails.
- Tests: three failures open one incident and send one alert; recovery closes it and sends one recovery alert; a fourth failure does not send a second alert.

**Done when:** pausing a real site in the test (or pointing a monitor at a bad URL) triggers one down email and one recovery email.

### Stage 4: Dashboard and status page (4 to 6 days)
- Dashboard: status of each monitor, uptime percentage for 24 hours and 7 days, and a response-time chart with Chart.js.
- Calculate uptime percentages and chart points in the controller or an Action. Do not send thousands of raw check rows to the page; group them, for example one average per hour.
- Pass chart points to the page through a data attribute, so Blade's escaping keeps it safe: `<canvas data-points="{{ json_encode($points) }}"></canvas>`. The `app.js` template reads every canvas with `data-points` and draws the chart.
- Public status page at `/status/{slug}`.
- Seeder that creates a demo user with sample monitors and a few days of checks.

**Done when:** a stranger could understand the dashboard and status page without explanation.

### Stage 5: Quality pass (2 to 3 days)
- Raise Larastan to level 6 and fix what it finds.
- Add tests for anything not yet covered, especially the status page and authorization.
- Write the README: what it does, screenshots, tech stack, how to run it, how to run tests.

**Done when:** CI is green, and the README makes sense to someone who has never seen the project.

### Stage 6: Docker and Redis (3 to 4 days)
- `composer require predis/predis` (a Redis client written in PHP, so the Docker image needs no extra extension).
- Copy `Dockerfile`, `.dockerignore` and the `docker/` folder from `templates/` into the project.
- Build the image locally: `docker build -t uptime-monitor .`
- Test the full production setup on your laptop: copy `docker-compose.prod.yml`, `Caddyfile` (use the `:80` version) and a filled-in `.env` into a separate folder, change the image to `uptime-monitor`, then run `docker compose -f docker-compose.prod.yml up -d`.
- Optionally add Laravel Horizon for a queue dashboard, and replace the `queue-worker` program in `supervisord.conf` with `php artisan horizon`.

**Done when:** the whole app, including checks and alerts, runs from Docker on your laptop at http://localhost.

### Stage 7: Deploy to Google Cloud (2 to 3 days)
Follow section 10, then add the `deploy` job back into the workflow.

**Done when:** merging a pull request into main updates the live site automatically.

### Stage 8: Domain, HTTPS and demo (1 to 2 days)
- Point a subdomain such as `uptime.yourdomain.com` at the server and update the `Caddyfile`.
- Create a demo account with sample data and put the login on the README.
- Record a short GIF of adding a monitor and seeing it go down and recover.
- Pin the repository on your GitHub profile.

---

## 9. The template files

| File | Copy to | What it does |
|---|---|---|
| `templates/Dockerfile` | project root | Builds one image: installs PHP dependencies, builds CSS and JS, sets up PHP-FPM, Nginx and Supervisor |
| `templates/.dockerignore` | project root | Keeps `.env`, `vendor`, `node_modules` and logs out of the image |
| `templates/docker/nginx.conf` | `docker/nginx.conf` | Web server settings for Laravel |
| `templates/docker/supervisord.conf` | `docker/supervisord.conf` | Starts and restarts PHP-FPM, Nginx, the queue worker and the scheduler |
| `templates/docker/php.ini` | `docker/php.ini` | Production PHP settings, including OPcache |
| `templates/docker-compose.prod.yml` | server: `~/uptime-monitor/docker-compose.yml` | Runs the app, MySQL, Redis and Caddy together on the server |
| `templates/Caddyfile` | server: `~/uptime-monitor/Caddyfile` | HTTPS and forwarding to the app |
| `templates/.env.production.example` | server: `~/uptime-monitor/.env` | Production settings; fill in real values on the server only |
| `templates/.github/workflows/ci-deploy.yml` | `.github/workflows/ci-deploy.yml` | Tests on every push and pull request; builds and deploys on main |
| `templates/phpstan.neon` | project root | Larastan settings |
| `templates/resources/views/components/layouts/app.blade.php` | `resources/views/components/layouts/` | Layout for logged-in pages: navigation, log out, flash message |
| `templates/resources/views/components/layouts/guest.blade.php` | `resources/views/components/layouts/` | Layout for login and register pages |
| `templates/resources/views/components/form/input.blade.php` | `resources/views/components/form/` | Text field that keeps old input and shows its validation error |
| `templates/resources/views/components/form/select.blade.php` | `resources/views/components/form/` | Dropdown that keeps the old choice and shows its validation error |
| `templates/resources/views/auth/login.blade.php` | `resources/views/auth/` | Full login page for Fortify, the pattern for the other auth pages |
| `templates/resources/views/monitors/edit.blade.php` | `resources/views/monitors/` | Example form with `@csrf`, `@method`, components, an `@can` check and an Alpine.js delete confirmation |
| `templates/resources/js/app.js` | `resources/js/app.js` | Starts Alpine.js and turns `<canvas data-points>` elements into Chart.js charts |

**Why Supervisor inside the container:** Supervisor is a small program that starts other programs and restarts them if they crash. Here it runs four things in one container: PHP-FPM, Nginx, the queue worker and the scheduler. Bigger setups often run each in its own container instead, but one container is simpler to understand and uses less memory on a 1 GB server.

**Why `schedule:work` instead of cron:** on a normal server you add one cron line that runs `php artisan schedule:run` every minute. Inside the container, `schedule:work` does the same job and Supervisor keeps it running, so the server needs no cron setup.

**One required code change for HTTPS:** Caddy sits in front of the app, so Laravel must trust it or links will be generated as `http://`. In `bootstrap/app.php`, inside `->withMiddleware(...)`, add:

```php
$middleware->trustProxies(at: '*');
```

---

## 10. Google Cloud server setup (stage 7)

### 10.1 Create the server
1. Create a Google Cloud account and a project. Set a budget alert (Billing > Budgets & alerts) for a small amount so you hear about any charge immediately.
2. Compute Engine > VM instances > Create instance:
   - Region: **us-central1**, **us-east1** or **us-west1** (other regions are not free)
   - Machine type: **e2-micro**
   - Boot disk: **Ubuntu 24.04 LTS**, **Standard persistent disk**, 30 GB. The default "Balanced" disk is not free.
   - Firewall: tick **Allow HTTP traffic** and **Allow HTTPS traffic**
3. VPC network > IP addresses: find the server's external IP and click **Promote to static**, so it never changes.

### 10.2 Prepare the server
Click **SSH** next to the server in the console. A terminal opens in your browser. Run:

```bash
# Add 2 GB of swap, because the server only has 1 GB of RAM
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab

# Install Docker
curl -fsSL https://get.docker.com | sudo sh
sudo usermod -aG docker $USER
```

Close the SSH window and open it again so the Docker permission takes effect. Check with `docker ps`.

### 10.3 Put the app files on the server
```bash
mkdir -p ~/uptime-monitor && cd ~/uptime-monitor
nano docker-compose.yml   # paste templates/docker-compose.prod.yml, change the image name
nano Caddyfile            # paste templates/Caddyfile (use the :80 version until you have a domain)
nano .env                 # paste templates/.env.production.example and fill in real values
```

For `APP_KEY`, run `php artisan key:generate --show` on your laptop and paste the result.

### 10.4 Let GitHub Actions log in to the server
1. On your laptop, create a key pair just for deployments:
   ```bash
   ssh-keygen -t ed25519 -f gha_deploy -C github-actions
   ```
   This makes two files: `gha_deploy` (private) and `gha_deploy.pub` (public).
2. In Google Cloud: open the server > **Edit** > **SSH Keys** > **Add item**, paste the contents of `gha_deploy.pub`, and save. The username shown at the end of the key line (`github-actions`) is the user GitHub will log in as. Use your own username instead if you prefer, so the files from 10.3 are in that user's home folder.
3. In your GitHub repository: Settings > Secrets and variables > Actions > New repository secret. Add:
   - `SERVER_HOST`: the static IP
   - `SERVER_USER`: the username from the key
   - `SERVER_SSH_KEY`: the full contents of the private `gha_deploy` file
4. Delete the private key file from your laptop once it is saved in GitHub, or keep it somewhere safe.

### 10.5 First deployment
1. Add the `deploy` job back to the workflow and merge to main. The job builds the image and pushes it to ghcr.io.
2. The first time, the image is private. Either make it public (GitHub profile > Packages > the package > Package settings > Change visibility), or log in on the server once with a GitHub token that has the `read:packages` permission: `docker login ghcr.io -u YOUR_GITHUB_USERNAME`.
3. Re-run the deploy job, then open `http://YOUR_STATIC_IP` in a browser.

### 10.6 Useful server commands
```bash
cd ~/uptime-monitor
docker compose ps                           # what is running
docker compose logs -f app                  # Laravel, Nginx and worker logs
docker compose exec --user www-data app php artisan migrate:status
docker compose restart app                  # restart the app container
```

**Rolling back a bad deploy:** each build is also tagged with its commit id. In `docker-compose.yml`, change `:latest` to `:<previous commit id>`, then run `docker compose up -d`.

---

## 11. Security and production checklist

- [ ] `APP_DEBUG=false` and `APP_ENV=production` on the server
- [ ] Strong, different passwords for `DB_PASSWORD` and `DB_ROOT_PASSWORD`
- [ ] MySQL and Redis have no `ports:` in the compose file, so they are not reachable from the internet
- [ ] Only ports 22, 80 and 443 open in the Google Cloud firewall
- [ ] `.env` exists only on the server and never in Git or the Docker image
- [ ] Budget alert set in Google Cloud billing
- [ ] Old checks pruned daily so the 30 GB disk does not fill up
- [ ] Rate limiting on login and on the public status page
- [ ] A weekly database backup: `docker compose exec mysql mysqldump -u root -p uptime > backup.sql`, copied off the server

---

## 12. What you will be able to say in interviews

- "Checks run as queued jobs so one slow website never delays the others, and `ShouldBeUnique` stops two checks for the same site running at once."
- "An incident opens only after three failures in a row, so a single network blip does not spam the user."
- "Alerts are event listeners, so adding Telegram meant adding one listener without touching the check logic."
- "I built it without a starter kit: Laravel Fortify handles the authentication logic, and I wrote the layouts and form fields as reusable Blade components with props, slots and built-in validation errors."
- "Old check data is pruned automatically with Laravel's `Prunable` trait."
- "Every pull request runs Pint, Larastan and Pest in GitHub Actions; merging to main builds a Docker image, pushes it to GitHub Container Registry and deploys it to a Google Cloud server over SSH."
- "In the container, Supervisor manages PHP-FPM, Nginx, the queue worker and the scheduler, and Caddy handles HTTPS."
- "The trade-off: migrations run right after the new container starts, so there is a few seconds' gap. For a bigger app I would look at zero-downtime deploys."

---

## 13. Words you will meet

| Word | Meaning |
|---|---|
| Queue | A list of jobs waiting to be done in the background |
| Queue worker | A program that keeps taking jobs from the queue and running them |
| Scheduler | Laravel's way of running tasks at set times, such as every minute |
| Cron | Linux's built-in timer that runs commands on a schedule |
| Supervisor | A program that starts other programs and restarts them if they stop |
| Docker image | A packaged copy of your app with PHP, extensions and settings included |
| Container | A running copy of an image |
| Docker Compose | A file that describes several containers that run together |
| Volume | Storage that survives when a container is replaced, used for the database |
| Registry | A place to store images, here GitHub Container Registry |
| Reverse proxy | A server in front of your app that handles HTTPS and forwards requests, here Caddy |
| CI/CD | Automatically testing (continuous integration) and releasing (continuous deployment) code |
| SSH key | A pair of files used to log in to a server without a password |
| Swap | Disk space used as extra memory when RAM runs out |
| N+1 query | Running one database query per row in a list instead of one query for the whole list |
| Blade component | A reusable piece of a page, written once in `resources/views/components/` and used like an HTML tag, such as `<x-form.input>` |
| Props and slots | Props are values passed into a component (`name="email"`); the slot is the content you place between its opening and closing tags |
| Escaping | `{{ }}` converts characters like `<` into safe text when printing, which blocks script injection (XSS) |
| Fortify | Laravel's back-end-only authentication package: it handles login and registration logic, and you supply the pages |

---

**Versions to double-check when you start:** GitHub Actions and Docker images get new major versions over time. The workflow template uses the latest versions available on 5 October 2026. If a step fails months from now, check that action's GitHub page for a newer version.
