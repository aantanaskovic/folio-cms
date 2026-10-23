# Folio CMS --- Project Plan

**Last updated:** October 24, 2026\
**Status:** Core portfolio demonstration complete; preparing for GitHub
publication.

## 1. Project overview

Folio CMS is a block-based content management system for creating,
reviewing, and publishing portfolio pages and case studies.

The project demonstrates a coherent Laravel application with structured
content, authentication, role-based authorization, an editorial
workflow, a Filament administration panel, a public website, and
automated tests. It is a portfolio project, not an attempt to deliver
every feature of a production CMS.

### Core goals

- Demonstrate practical Laravel, Livewire, and Filament development.
- Keep business operations and authorization rules explicit and
  testable.
- Support a contributor-to-admin publishing workflow.
- Render published content publicly without requiring visitors to sign
  in.
- Use automated tests to protect important behavior and prevent
  regressions.
- Keep the scope small enough to finish and present clearly.

### Scope boundaries

- `/` is the public Folio CMS landing page.
- `/pages/{slug}` displays an individual published page.
- A general public `/pages` index is not required.
- Public visitors do not need to sign in to read published pages.
- Contributors cannot publish content directly.
- Contributors cannot edit published pages; authorized administrators
  can manage published content.
- Page versioning, complex approval chains, and enterprise editorial
  features are out of scope.
- New features should be added only when they materially improve the
  portfolio demonstration.

## 2. Technology stack

- PHP 8.4
- Laravel 13
- Livewire 4
- Filament 5
- Laravel Fortify for authentication flows
- Spatie Laravel Permission for roles and permissions
- Tailwind CSS 4
- Vite
- MySQL
- Pest for automated testing

Refer to `composer.json` and `package.json` for exact dependency
constraints and installed versions.

## 3. Architectural decisions

1.  Use Laravel Fortify for authentication with custom application
    views.
2.  Use Filament for authenticated administration.
3.  Use Laravel policies for record-level authorization.
4.  Use Spatie Permission for roles and permissions.
5.  Put meaningful business operations in dedicated application actions.
6.  Store structured page blocks as JSON and validate their shape before
    saving.
7.  Render supported block types through explicit Blade partials.
8.  Use Laravel's filesystem abstraction for file storage where
    applicable.
9.  Keep application-facing text in English.
10. Prefer explicit, maintainable code over unnecessary abstraction.
11. Use focused tests for behavior changes and avoid unrelated
    refactors.

## 4. Implemented functionality

### Authentication and access

- [x] Custom authentication interface backed by Fortify.
- [x] Login, registration, password reset, and email verification
      flows.
- [x] Contributor role assignment for registered users.
- [x] Filament panel access restricted by administration permissions.
- [x] Authorization rules tested independently of interface
      visibility.

### Roles and permissions

- [x] Spatie Permission configured with teams disabled.
- [x] Roles and permissions seeded through `RolePermissionSeeder`.
- [x] `DatabaseSeeder` invokes the role/permission and demo-data
      seeders.
- [x] Role management through Filament.
- [x] User management through Filament.
- [x] Tests for role/permission assignment and access restrictions.

### Page management and editorial workflow

- [x] Page model, migration, factory, and page status enum.
- [x] Structured content blocks stored in JSON.
- [x] Page data validation through `PageDataValidator`.
- [x] Filament page listing, creation, editing, and details view.
- [x] Contributor ownership and page-status restrictions.
- [x] Draft submission for review.
- [x] Administrative review and publishing actions.
- [x] Status-transition and authorization behavior covered by tests.
- [x] Tests for direct Filament URL access and access to pages owned
      by other contributors.

### Public website

- [x] Public landing page.
- [x] Public rendering of published pages and supported content
      blocks.
- [x] Draft and pending pages excluded from public access.
- [x] SEO title and description rendering.
- [x] Public navigation for pages enabled for navigation.
- [x] Navigation dropdown and related tests.

### Demo data and quality checks

- [x] Demo page and demo user seeders.
- [x] Seeders designed to be repeatable by updating records identified
      by stable email addresses or page slugs.
- [x] Seeder behavior covered by tests.
- [x] Full Pest test suite passed in the latest reported verification.
- [x] `npm run build` completed successfully in the latest reported
      verification.
- [x] `git diff --check` completed without whitespace errors; Git
      reported line-ending normalization warnings.

The project owner reported that the complete test suite and production
asset build both passed on October 24, 2026. Re-run these checks after
any subsequent code changes.

## 5. Roles and expected behavior

---

Capability Admin Contributor

---

Access administration Yes, when authorized Yes, when authorized

Manage users Yes, with permission No

Manage roles and Yes, with permission No
permissions

Create pages Yes, according to Yes
permissions

Edit own draft or Yes, according to Yes
pending page permissions

Submit own draft for When authorized Yes
review

Publish pages With publishing No
permission

View published pages in With permission With permission
administration

Edit published pages With the relevant No
permission

Read public published Yes; login is not Yes; login is not
pages required required

---

Exact capabilities are enforced by permissions, policies, application
actions, and Filament resource behavior rather than by role names alone.

## 6. Content blocks

The page content is stored as structured JSON. The current supported
block types include:

- **Hero** --- heading and supporting text.
- **Text** --- text content.
- **Gallery** --- image URLs and alternative text.
- **CTA** --- heading, label, and destination URL.

Block data is validated before saving and rendered through explicit view
partials. Adding a new block type should include validation, rendering,
and relevant tests.

## 7. Demo seed data

The seeders create example admin and contributor accounts, along with
sample pages in published, draft, pending, and scheduled-publication
states.

The demo accounts use predictable local-development credentials. They
are intended only for local demonstration and must not be used as real
credentials or deployed to a public environment. Do not place real user
information, secrets, production credentials, or private content in demo
seeders.

The demo pages use sample copy and externally hosted Unsplash image
URLs. An internet connection may be required for those remote images to
load.

## 8. Testing and local verification

Run the automated suite with:

```bash
php artisan test
```

Build the frontend assets with:

```bash
npm run build
```

Useful repository checks before committing:

```bash
git diff --check
git status --short
git diff --cached --check
git diff --cached --stat
```

The automated tests cover key behavior across application actions,
authorization policies, Filament pages and actions, public rendering,
and seeders. A passing suite is useful evidence of correctness, but does
not guarantee the absence of defects. Manual UI checks complement
automated testing.

## 9. GitHub publication checklist

- [ ] Confirm `.env` and other secret-bearing files are not tracked by
      Git.
- [ ] Check `.env.example` contains placeholders rather than real
      credentials.
- [ ] Confirm local databases, logs, cache files, and generated
      dependencies are not staged.
- [ ] Review demo seeders to ensure they contain only synthetic data
      and clearly demo-only credentials.
- [ ] Verify setup instructions in `README.md` against the current
      project.
- [ ] Confirm migrations and seeders work against a fresh development
      database.
- [x] Run `php artisan test`.
- [x] Run `npm run build`.
- [x] Run `git diff --check`.
- [ ] Review staged changes with `git diff --cached --stat` and
      `git diff --cached`.
- [ ] Commit the final implementation and documentation changes.
- [ ] Push the repository to GitHub.

## 10. Deliberately out of scope

The project does not aim to provide:

- Full content versioning or restoration.
- Multi-stage editorial approval chains.
- Enterprise audit logging or advanced monitoring.
- A complete media asset management platform.
- Production deployment infrastructure or a guarantee of production
  readiness.
- Every possible CMS feature.

These are intentional scope decisions, not unfinished requirements for
the portfolio demonstration.

## 11. Definition of done

Folio CMS is considered complete for its portfolio purpose when the core
features work in the UI, the automated suite passes, frontend assets
build successfully, setup documentation is clear, and the final Git
changes have been reviewed.

Further changes should be driven by a reproducible defect or a clear
improvement to the presentation---not by adding features indefinitely.
