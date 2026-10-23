# Folio CMS

Folio CMS is a portfolio-focused content management system built with Laravel. It demonstrates structured page content, role-based access control, an editorial review workflow, and a public-facing website backed by a Filament administration panel.

This is a portfolio and learning project, not a claim of production readiness.

## Features

- **Page management** — create and manage portfolio pages and case studies.
- **Structured content blocks** — compose pages from supported blocks such as hero, text, gallery, and call to action.
- **Editorial workflow** — contributors submit draft pages for review; authorized administrators review and publish them.
- **Public website** — visitors can view published pages without signing in.
- **Public navigation** — published pages can be included in the website navigation.
- **User management** — administrators manage user accounts through the admin panel.
- **Role and permission management** — roles and permissions control access to administration and content operations.
- **Authentication** — custom authentication views backed by Laravel Fortify, including email verification and password reset flows.
- **Authorization** — Laravel policies and application actions enforce access rules beyond simply hiding interface controls.
- **Automated tests** — Pest tests cover key application behavior, content workflows, validation, and authorization.

## Roles and workflow

The default roles are `admin` and `contributor`.

- **Admin** — manages users and roles, reviews submitted pages, and publishes content.
- **Contributor** — creates and manages their own eligible pages and submits drafts for review. Contributors cannot publish pages directly.

The typical content workflow is:

1. A contributor creates a draft page.
2. The contributor submits the draft for review.
3. An authorized administrator reviews and publishes the page.
4. Published content is available on the public website.

The application intentionally keeps this workflow straightforward; it does not attempt to provide a full content-versioning or enterprise editorial system.

## Technology stack

- PHP 8.4
- Laravel 13
- Livewire 4
- Filament 5
- Laravel Fortify
- Spatie Laravel Permission
- Tailwind CSS 4
- Vite
- MySQL
- Pest

## Requirements

Install a compatible PHP version and Composer, Node.js and npm, and a MySQL server. The exact PHP extensions required are determined by the Composer dependencies.

## Installation

1. Clone the repository and enter the project directory:

    ```bash
    git clone https://github.com/aantanaskovic/folio-cms.git
    cd folio-cms
    ```

2. Install PHP dependencies:

    ```bash
    composer install
    ```

3. Create your local environment file:

    ```bash
    cp .env.example .env
    ```

    On Windows PowerShell:

    ```powershell
    Copy-Item .env.example .env
    ```

4. Generate the application key:

    ```bash
    php artisan key:generate
    ```

5. Create a MySQL database and update the `DB_*` settings in `.env` to match your local database. Review the other environment settings as needed.

6. Install JavaScript dependencies:

    ```bash
    npm install
    ```

7. Run database migrations and seed the initial roles, permissions, and demo data:

    ```bash
    php artisan migrate --seed
    ```

8. Build the frontend assets:

    ```bash
    npm run build
    ```

9. Start the Laravel development server:

    ```bash
    php artisan serve
    ```

Open the local URL shown by Artisan. The Filament administration panel is available at `/admin`.

For frontend development with Vite's development server, run `npm run dev` in a separate terminal instead of relying only on the production build.

### Demo data

The database seeder creates the initial role and permission setup and the project's demo records. Check the seeders under `database/seeders/` for the demo accounts and their local credentials. Use demo credentials only in a local development environment.

> **Note:** Run seeders against a development database. Do not use demo accounts or development configuration for a public deployment.

## Running tests

Run the complete Pest test suite:

```bash
php artisan test
```

## Project structure

The application separates its main responsibilities into Laravel models, enums, policies, validators, and application actions, with Filament resources for administration and Blade views for public rendering.

- `app/Actions/` — application operations and workflow actions
- `app/Enums/` — domain enums, including page status
- `app/Filament/` — administration resources and form/table schemas
- `app/Models/` — Eloquent models
- `app/Policies/` — record-level authorization
- `app/Validators/` — structured page data validation
- `database/seeders/` — roles, permissions, and demo data
- `resources/views/` — public pages, layouts, navigation, and content-block rendering
- `tests/` — Pest feature and unit tests

## Project status

The core portfolio demonstration is implemented: page management, public rendering, user management, role/permission management, authentication, editorial workflow, and automated tests.

The project is kept intentionally focused. Additional production-specific work—such as deployment hardening, operational monitoring, and a more elaborate editorial/versioning system—is outside the current scope.

## Security notes

- Keep `.env` out of version control.
- Never commit real passwords, API keys, tokens, or other secrets.
- Use HTTPS and review environment, email, storage, and database configuration before deploying publicly.
- Review all demo credentials and seeded records before exposing an instance to the internet.

## License

No license has been specified. Add a license file if you intend to permit others to use, modify, or distribute this project.
