# Folio CMS

**A block-based content management system for building and publishing portfolio pages and case studies.**

Folio CMS is a Laravel-based content management system focused on structured content creation, editorial review, and controlled publishing. It separates the public-facing website from the authenticated administration area, with role-based permissions governing content management and publication.

## Features

- **Block-based content:** Build pages using hero, text, gallery, and call-to-action blocks.
- **Editorial workflow:** Create drafts, submit them for review, request changes, and publish approved content.
- **Role-based access control:** Separate administrator and contributor responsibilities using Spatie Laravel Permission.
- **Authorization and ownership:** Enforce permissions for page creation, editing, review, publication, and deletion.
- **Public portfolio pages:** Render published pages using slug-based URLs without requiring visitors to sign in.
- **Navigation control:** Choose which published pages appear in the public navigation.
- **SEO metadata:** Configure page titles and meta descriptions, with a fallback to the page title.
- **Media uploads:** Upload and display gallery images with alternative text.
- **Authentication:** Provide registration, login, email verification, and password reset through Laravel Fortify.
- **Automated tests:** Test page validation, authorization, editorial workflows, authentication, and public rendering.

## Tech Stack

- **Backend:** Laravel 13, PHP 8.4
- **Interactive UI:** Livewire 4
- **Administration panel:** Filament 5
- **Authentication:** Laravel Fortify
- **Authorization:** Spatie Laravel Permission
- **Frontend styling:** Tailwind CSS 4
- **Database:** MySQL
- **Testing:** Pest
- **Asset bundling:** Vite

## Roles and Publishing Workflow

### Admin

Administrators can manage pages across contributors, review submitted content, publish approved pages, return pages for changes, and manage content according to their assigned permissions.

### Contributor

Contributors can create pages, edit their own eligible pages, and submit drafts for review. They cannot publish pages directly or edit content when their permissions and the page status prohibit it.

### Page lifecycle

1. A contributor creates a draft.
2. The contributor edits the draft and submits it for review.
3. An authorized reviewer approves the submission or returns it for changes.
4. An authorized administrator publishes the approved page.
5. Published pages become publicly accessible when their publication time has arrived.

## Development Setup

### Requirements

- PHP 8.4 or a compatible supported version
- Composer
- Node.js and npm
- MySQL
- A local PHP development environment such as Laravel Herd

### Installation

Clone the repository and install the dependencies:

```bash
git clone <repository-url>
cd folio-cms

composer install
npm install
```

Create the local environment file and generate an application key:

```bash
cp .env.example .env
php artisan key:generate
```

Configure the database connection in `.env`, ensuring that the MySQL database exists and the credentials are correct.

Run the database migrations and seed the required roles and permissions:

```bash
php artisan migrate --seed
```

Build the frontend assets:

```bash
npm run build
```

Configure the web server or local development environment to serve the application. For a standard Laravel development server, run:

```bash
php artisan serve
```

### Running Tests

Run the automated test suite with:

```bash
php artisan test
```

## Architecture

The project emphasizes:

- Separation of application actions, validation, authorization, and presentation
- Explicit page ownership and permission checks
- Structured content blocks with controlled rendering
- Separate public-facing pages and authenticated administration
- Testable editorial workflows and secure publishing rules
- Laravel conventions and maintainable Livewire and Filament components

## License

No license has been selected yet. Until a license is added to the repository, all rights remain reserved by the copyright holder.
