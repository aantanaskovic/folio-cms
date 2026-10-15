# Folio CMS

**A block-based content management system for building and publishing portfolio pages and case studies.**

Folio CMS is a Laravel-based CMS project focused on structured content creation, editorial review, and controlled publishing. It is being built with a clear separation between public-facing content and an authenticated administration area.

The goal is to provide a maintainable foundation for creating rich portfolio pages while keeping content workflows, permissions, and presentation organized.

## Project Status

**In active development.** The initial application foundation and core dependencies are configured. Content management, editorial workflows, and public page rendering are planned for subsequent development.

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

## Planned Features

- **Block-based content:** Compose pages from reusable content blocks, including hero sections, text, galleries, and calls to action.
- **Editorial workflow:** Support draft, pending review, and published content states.
- **Role-based access control:** Separate administrator responsibilities from contributor permissions.
- **Review and publishing:** Allow contributors to submit content for review while reserving publishing permissions for authorized administrators.
- **Public pages:** Render published content through clean, slug-based URLs.
- **Navigation control:** Include only explicitly selected pages in public navigation.
- **Metadata management:** Support page titles, descriptions, and search-engine metadata.
- **Media management:** Support cover images and uploaded content assets.

These features describe the intended direction of the project and are not all implemented yet.

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

Configure the database connection in `.env`, then run the migrations:

```bash
php artisan migrate
```

Build the frontend assets:

```bash
npm run build
```

### Running Tests

Run the automated test suite with:

```bash
php artisan test
```

## Architecture Goals

Folio CMS is being developed with an emphasis on:

- Clear separation of responsibilities
- Explicit authorization and ownership checks
- Reusable content blocks with controlled rendering
- Testable application behavior
- Secure defaults for authentication and publishing
- A maintainable Laravel and Livewire codebase

## License

No license has been selected yet. Until a license is added to the repository, all rights remain reserved by the copyright holder.
