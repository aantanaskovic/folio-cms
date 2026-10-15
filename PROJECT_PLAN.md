# Folio CMS — Project Plan

## 1. Project Overview

**Project name:** Folio CMS  
**Repository and directory:** `folio-cms`  
**Local development path:** `C:\herd\api\folio-cms`  
**Local URL:** `http://folio-cms.test`

Folio CMS is a block-based content management system for creating, reviewing, and publishing portfolio pages and case studies.

The project is intended to demonstrate a maintainable, secure, portfolio-quality Laravel application with structured content, role-based authorization, an editorial workflow, reusable content blocks, and controlled public publishing.

### Core goals

- Build pages from reusable, validated content blocks.
- Separate content creation from approval and publishing.
- Allow contributors to create and maintain their own content.
- Restrict publishing and administrative operations to authorized administrators.
- Render only published pages publicly.
- Provide a professional public landing page for Folio CMS.
- Support explicit control over which published pages appear in public navigation.
- Maintain clear application boundaries and testable business logic.
- Keep the codebase suitable for a public GitHub portfolio.

### Scope boundaries

- `/` is the marketing landing page for Folio CMS.
- `/pages/{slug}` displays an individual published page.
- A general public `/pages` index is not required.
- A dedicated `/work` page for case studies is optional future scope.
- Visitors can access public content without signing in.
- Contributors cannot publish content directly.
- New contributors' edits must never silently change already-published content.
- Implementation proceeds incrementally, with tests and verification at each step.

---

## 2. Technology Stack

| Area                   | Technology                |
| ---------------------- | ------------------------- |
| Backend                | Laravel 13                |
| Language               | PHP 8.4                   |
| Interactive components | Livewire 4                |
| Administration panel   | Filament 5                |
| Authentication backend | Laravel Fortify           |
| Roles and permissions  | Spatie Laravel Permission |
| CSS                    | Tailwind CSS 4            |
| Database               | MySQL                     |
| Testing                | Pest                      |
| Asset bundling         | Vite                      |
| Local environment      | Laravel Herd              |

### Architectural decisions

1. Use Laravel Fortify as a headless authentication backend.
2. Build custom authentication interfaces with Livewire.
3. Do not install Breeze or another full starter kit.
4. Use Filament for authenticated administration.
5. Use Laravel policies for record-level authorization.
6. Use Spatie Permission for roles and permissions.
7. Use dedicated application actions for meaningful business operations.
8. Store structured page blocks in a JSON column.
9. Render blocks through an explicit allowlist of Blade partials.
10. Use local filesystem storage initially.
11. Keep all user-facing application text in English.
12. Prefer explicit, maintainable code over unnecessary abstraction.

---

## 3. Current Project Status

The following foundation has been completed:

- Fresh Laravel application created without a starter kit.
- Laravel, Livewire, Filament, Fortify, Spatie Permission, Tailwind CSS, Vite, and Pest configured.
- MySQL configured for the local `folio_cms` database.
- Filament panel registered at `/admin`.
- Filament panel access restricted through `User::canAccessPanel()` to users with the `admin` role.
- Fortify configured with its default views disabled.
- Registration, password reset, and email verification features enabled in Fortify configuration.
- Two-factor authentication and passkeys intentionally excluded from the initial scope.
- Spatie permission tables migrated.
- Spatie teams functionality disabled.
- `.env` excluded from Git.
- `.env.example` aligned with the local project configuration.
- Local AI development files and directories excluded from Git.
- Duplicate `CLAUDE.md` removed; `AGENTS.md` retained.
- Initial README created.
- Vite production build completed successfully.
- Initial automated tests passed.
- Initial Git commit created.
- Git working tree confirmed clean.

### Remaining foundation considerations

- Custom Livewire authentication pages have not yet been implemented.
- The `contributor` registration role assignment still needs implementation and tests.
- Role creation/seeding needs to be made deterministic.
- Page model, migration, policies, resources, workflow, and public rendering remain planned work.
- The storage symlink has not yet been created; create it when media uploads require it.
- The optional `fontaine` optimization warning during asset building is not currently a blocker.

### Local environment example

```dotenv
APP_NAME="Folio CMS"
APP_URL=http://folio-cms.test
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=folio_cms
DB_USERNAME=root
DB_PASSWORD=
```

These are local development settings, not production credentials. Never commit `.env` or real secrets.

---

## 4. Roles and Authorization

### Administrator: `admin`

Administrators will be able to:

- Access `/admin`.
- View and manage pages across contributors.
- Review pending submissions.
- Publish approved content.
- Return submissions for changes where supported.
- Manage users and roles if included in the final scope.
- Manage other explicitly approved administrative settings.

### Contributor: `contributor`

Contributors will be able to:

- Sign in through the shared login interface.
- Create pages.
- Edit their own drafts.
- Submit their own pages for review.
- View their own content statuses.
- Update and resubmit content when changes are requested.

Contributors must not be able to:

- Publish pages directly.
- Access administrator-only functions.
- Assign themselves the `admin` role.
- Manage roles or permissions.
- Modify another contributor's pages without an explicit future requirement.

### Security rules

- Public registration always creates a `contributor`.
- Never accept a role or privilege field from registration input.
- Assign the author using the authenticated user, not a submitted `author_id`.
- Enforce panel access server-side.
- Use policies for page ownership and operations.
- Enforce permissions in actions and backend requests, not only in the UI.
- Test direct URLs, manipulated IDs, and forged Livewire requests.
- Do not rely on hidden buttons or navigation items for security.
- Decide which protected operations require verified email.
- Never use fixed demo credentials as production secrets.

---

## 5. Authentication Strategy

Fortify handles authentication logic. Custom Livewire pages provide the interface.

### Planned interfaces

- Login page.
- Registration page.
- Forgot-password page.
- Reset-password page.
- Email verification notice.
- Email verification and resend flow.
- Logout action.

Fortify's views are disabled, so custom GET routes and Livewire interfaces must be implemented. Fortify's POST routes should be reused where appropriate rather than reimplementing authentication internals.

### Registration

Use `app/Actions/Fortify/CreateNewUser.php` as the registration entry point.

Registration must:

1. Validate the submitted data.
2. Create the user.
3. Hash the password using Laravel's supported hashing behavior.
4. Assign the `contributor` role server-side.
5. Never assign a role based on submitted request data.
6. Follow the project's email verification policy.

Ensure roles exist before registration attempts to assign them.

### Login and redirects

- Keep public content available to unauthenticated visitors.
- Use a shared login page.
- Define redirect behavior explicitly.
- Do not assume every authenticated user is allowed into `/admin`.
- Preserve Filament's independent panel-access check.

### Authentication tests

- Successful login.
- Invalid credentials.
- Registration validation.
- New user receives only the `contributor` role.
- Role injection cannot grant administrator access.
- Logout behavior.
- Password reset request and completion.
- Email verification and resend behavior.
- Protected route behavior for guests.
- Contributor denial from the admin panel.
- Administrator access to the admin panel.

---

## 6. Content Model

The primary content entity is `Page`.

Portfolio pages and case studies can initially share this model. A separate case-study model should only be introduced if the domain requires meaningfully different data or behavior.

### Planned fields

| Field                | Purpose                     |
| -------------------- | --------------------------- |
| `id`                 | Primary key                 |
| `title`              | Page title                  |
| `slug`               | Unique public URL segment   |
| `excerpt`            | Short summary               |
| `blocks`             | Ordered JSON content blocks |
| `author_id`          | Page owner/author           |
| `status`             | Editorial workflow state    |
| `published_at`       | Publication timestamp       |
| `cover_image`        | Optional cover image path   |
| `meta_title`         | Optional SEO title          |
| `meta_description`   | Optional SEO description    |
| `show_in_navigation` | Explicit navigation opt-in  |
| `created_at`         | Creation timestamp          |
| `updated_at`         | Last update timestamp       |

The exact migration should define suitable data types, foreign keys, indexes, nullability, and deletion behavior.

Use a unique index for `slug`. Add indexes for frequently queried fields such as `status` and `published_at` when justified by the queries.

### Model

Planned location: `app/Models/Page.php`.

Responsibilities:

- Define explicit mass-assignable attributes.
- Cast `blocks` to an array.
- Cast `published_at` to a datetime.
- Define the author relationship.
- Represent workflow states using an enum or another constrained type.
- Avoid accepting sensitive fields directly from untrusted input.
- Delegate multi-step workflow operations to application actions.

### Block data structure

Conceptual example:

```json
[
    {
        "type": "hero",
        "data": {
            "heading": "A project headline",
            "subheading": "A supporting statement"
        }
    },
    {
        "type": "text",
        "data": {
            "heading": "Overview",
            "body": "Project details."
        }
    }
]
```

Each block type must have a defined schema and server-side validation rules.

### Initial block types

- `hero` — heading, supporting text, optional image or action.
- `text` — heading and body.
- `gallery` — ordered images with appropriate alternative text.
- `cta` — call-to-action copy and a validated destination.

Never store arbitrary Blade view paths, PHP class names, or executable scripts in block data. Validate each block type against an explicit allowlist.

---

## 7. Editorial Workflow

Initial state transitions:

```text
Draft → Pending Review → Published
```

A later revision workflow may include a returned-for-changes state or action.

### Draft

- Contributors can create drafts.
- Contributors can edit their own drafts.
- Drafts are not public.
- Access is restricted to authorized users.

### Pending review

- Contributors submit their own content for review.
- The server validates the submission.
- Contributors cannot publish their own submissions.
- Administrators can locate pending pages in a review queue.
- Editing and resubmission rules must be explicit.

### Published

- Only administrators can publish.
- Publication sets `published_at` consistently.
- Only published pages are available through public routes.
- Draft and pending pages return 404 to anonymous visitors.
- Contributor edits must not silently change the live version.

### Published-content edit strategy

The initial safe behavior may move a page back to pending review and temporarily remove it from public display when a contributor edits published content.

If the requirement is to keep the previous published version visible while new edits await approval, implement explicit revisions or versioning. A single mutable page row cannot preserve the old live version automatically.

### Application actions

Planned actions include:

- `CreatePage`
- `UpdatePage`
- `SubmitPageForReview`
- `PublishPage`
- `ReturnPageForChanges`, if required

Actions must enforce authorization, valid state transitions, and relevant validation. Invalid transitions should fail predictably and be tested.

---

## 8. Public Pages and Rendering

### Routes

- `/` — Folio CMS marketing landing page.
- `/pages/{slug}` — individual published page.
- `/login` — login interface.
- `/register` — registration interface.
- Password reset and verification interfaces.
- `/admin` — Filament administration.

A public `/pages` index is not required. `/work` is optional future scope.

### Public visibility rules

- Query by slug and require the published state.
- Do not expose draft or pending pages.
- Return 404 for missing or unpublished pages.
- Do not leak internal notes or administrative metadata.
- Generate SEO metadata from configured fields with sensible fallbacks.

### Block rendering

Use dedicated Blade partials, for example:

```text
resources/views/components/blocks/
├── hero.blade.php
├── text.blade.php
├── gallery.blade.php
└── cta.blade.php
```

Use an explicit mapping from supported block types to their renderers. Never construct arbitrary view names from untrusted database values.

Escape output by default. If rich HTML is introduced later, use a deliberate sanitization policy and tests.

### Public navigation

- Only published pages with `show_in_navigation = true` appear.
- Draft and pending pages never appear.
- Navigation ordering must be predictable.
- Add manual ordering only if needed.
- Keep the marketing landing page separate from CMS content navigation.

---

## 9. Filament Administration

The Filament panel is registered in:

`app/Providers/Filament/AdminPanelProvider.php`

### Planned functionality

- Page resource for creating, viewing, editing, and listing permitted pages.
- Review queue for pending submissions.
- Administrator-only publishing and return-for-changes actions.
- Optional user and role management.
- Clear validation feedback and status indicators.

### Authorization requirements

- Scope contributor queries to their own records.
- Use Laravel policies for record-level permissions.
- Set `author_id` from the authenticated user.
- Do not trust submitted `status`, `published_at`, `author_id`, or role fields.
- Restrict publishing to authorized administrators.
- Test direct URLs and backend actions even when the UI hides them.

Contributors may use a restricted area within the same panel or a separate contributor experience. The implementation can be decided later; server-side authorization is mandatory either way.

---

## 10. File and Code Organization

The following structure is the intended organization. Not every file exists yet.

```text
app/
├── Actions/
│   ├── Fortify/
│   │   ├── CreateNewUser.php
│   │   ├── ResetUserPassword.php
│   │   └── ...
│   └── Pages/
│       ├── CreatePage.php
│       ├── UpdatePage.php
│       ├── SubmitPageForReview.php
│       ├── PublishPage.php
│       └── ReturnPageForChanges.php
├── Enums/
│   └── PageStatus.php
├── Filament/
│   ├── Resources/
│   │   └── Pages/
│   │       ├── PageResource.php
│   │       └── ...
│   └── ...
├── Livewire/
│   └── Pages/
│       └── Auth/
│           ├── Login.php
│           ├── Register.php
│           ├── ForgotPassword.php
│           ├── ResetPassword.php
│           └── VerifyEmail.php
├── Models/
│   ├── Page.php
│   └── User.php
├── Policies/
│   └── PagePolicy.php
└── Providers/
    ├── FortifyServiceProvider.php
    └── Filament/
        └── AdminPanelProvider.php

database/
├── factories/
│   ├── UserFactory.php
│   └── PageFactory.php
├── migrations/
└── seeders/
    └── DatabaseSeeder.php

resources/
├── css/
│   └── app.css
├── js/
│   └── app.js
└── views/
    ├── components/
    │   ├── layouts/
    │   └── blocks/
    └── pages/
        └── auth/

routes/
├── web.php
└── console.php

tests/
├── Feature/
│   ├── Actions/
│   │   └── Pages/
│   ├── Livewire/
│   │   └── Pages/
│   │       └── Auth/
│   ├── Filament/
│   └── ...
├── Unit/
└── Pest.php
```

### Organization rules

- Application actions belong in `app/Actions/<Domain>/`.
- Action tests mirror the same domain structure under `tests/Feature/Actions/<Domain>/`.
- Livewire page tests belong under `tests/Feature/Livewire/Pages/...`.
- Policies define record-level authorization.
- Livewire components handle UI state, user input, and calls to application behavior.
- Avoid putting every query, workflow, and business rule into one oversized Livewire component.
- Introduce abstractions only when they make responsibilities clearer.

---

## 11. Coding Conventions

- Prefer `use` imports and short class names instead of inline fully qualified class names.
- Use `app(SomeAction::class)` where appropriate.
- Keep user-facing text in English.
- Validate all untrusted input server-side.
- Do not mass-assign sensitive fields such as roles, `author_id`, `status`, or `published_at`.
- Prefer explicit relationship assignment for related models.
- Use enums or constrained values for workflow states.
- Keep authorization in the backend, not only in UI conditions.
- Keep changes small and focused.
- Avoid unrelated test edits and broad refactors during focused tasks.

---

## 12. Testing Strategy

Use Pest and a test-first workflow.

### Development loop

1. Agree on the behavior and edge cases.
2. Write or adjust one targeted test.
3. Run that test.
4. Implement the smallest change that satisfies it.
5. Run the targeted test again.
6. Run related tests.
7. Run the full suite at suitable checkpoints.
8. Review the diff before committing.

Avoid unnecessary tests whose only purpose is to demonstrate that an obviously missing class or method fails.

### Essential coverage

- Registration and role assignment.
- Privilege-injection attempts.
- Admin panel access.
- Page ownership policies.
- Page creation and validation.
- Slug uniqueness.
- Block payload validation.
- Valid and invalid workflow transitions.
- Contributor attempts to publish.
- Attempts to edit another user's page.
- Contributor edits to published content.
- Public visibility of published vs. unpublished pages.
- Navigation visibility.
- Invalid block types and unsafe output.
- Media validation and storage when implemented.

---

## 13. Media Storage

Initial approach: Laravel local filesystem storage.

Planned behavior:

- Validate file type and size.
- Use server-generated storage paths.
- Use Laravel's filesystem abstraction.
- Keep private files private.
- Use the appropriate public disk for publicly accessible images.
- Create the storage symlink when uploads are implemented.
- Validate gallery image references.
- Avoid orphaned files after replacement or deletion.
- Provide appropriate alternative text for meaningful images.

Reassess storage if deployment requires object storage or multiple application servers.

---

## 14. SEO, Validation, and Error Handling

### SEO

- Support `meta_title` and `meta_description`.
- Fall back to title and excerpt.
- Do not expose unpublished pages in public output or navigation.
- Consider canonical URLs and sitemap generation after public routing is stable.

### Validation

- Validate all form data server-side.
- Validate each block's type and data schema.
- Enforce slug uniqueness.
- Validate uploads and CTA destinations.
- Prevent arbitrary view selection.
- Avoid rendering untrusted HTML.
- Check persisted workflow state before transitions.
- Handle stale forms and concurrent changes predictably where practical.

### Errors

- Return 404 for missing or unpublished public pages.
- Reject unauthorized operations.
- Show understandable validation feedback.
- Never expose stack traces or internal exceptions in production.
- Keep debug mode enabled only locally.

---

## 15. Implementation Roadmap

### Phase 1 — Authentication and roles

1. Review Fortify configuration and the User model.
2. Decide how `admin` and `contributor` roles are created reliably.
3. Test that new registrations receive only `contributor`.
4. Implement server-side role assignment in `CreateNewUser`.
5. Build custom Livewire login and registration screens.
6. Implement password reset and email verification screens.
7. Define login/logout redirects.
8. Test authentication and privilege-injection behavior.

### Phase 2 — Authorization

1. Verify `canAccessPanel()` for administrators and contributors.
2. Add `PagePolicy` when the Page model exists.
3. Define ownership rules for every page operation.
4. Test direct URL and backend action access.
5. Ensure contributors cannot manage roles or publish pages.

### Phase 3 — Page model and schema

1. Add `PageStatus` or equivalent.
2. Create the `pages` migration and `Page` model.
3. Add relationships, casts, indexes, and factory.
4. Define block schemas and validation.
5. Test the model and relevant persistence behavior.

### Phase 4 — Page creation and editing

1. Create `CreatePage` and `UpdatePage` actions.
2. Build the basic Filament page resource.
3. Scope contributor records to their owners.
4. Validate title, slug, excerpt, blocks, and metadata.
5. Assign authors server-side.
6. Test ownership and validation edge cases.

### Phase 5 — Editorial workflow

1. Implement submission for review.
2. Implement admin review and publishing.
3. Add return-for-changes behavior if required.
4. Reject invalid transitions.
5. Prevent contributor edits from silently changing published content.
6. Test role/state combinations.

### Phase 6 — Public rendering

1. Build the marketing landing page.
2. Implement `/pages/{slug}`.
3. Query only published pages.
4. Add the hero, text, gallery, and CTA renderers.
5. Add SEO metadata fallbacks.
6. Test visibility, unknown slugs, invalid blocks, and output escaping.

### Phase 7 — Navigation

1. Implement navigation opt-in.
2. Include only published pages marked for navigation.
3. Define ordering.
4. Test that drafts and pending pages never appear.

### Phase 8 — Media management

1. Implement upload validation and storage.
2. Create the storage symlink as needed.
3. Add cover image and gallery support.
4. Test invalid uploads, permissions, replacements, and cleanup.

### Phase 9 — Hardening and documentation

1. Review authentication and authorization boundaries.
2. Run the full test suite.
3. Run `npm run build`.
4. Verify migrations from a clean test database.
5. Review environment and Git ignore rules.
6. Update README to distinguish implemented features from planned features.
7. Document installation, configuration, and testing.
8. Review production email, storage, HTTPS, debug mode, and secrets management.

### Optional future improvements

Only add these when justified:

- Content revisions that preserve the currently published version during review.
- A public `/work` case-study listing.
- Manual navigation ordering.
- Rich text with a tested sanitization policy.
- Additional block types.
- Search, tags, and categories.
- Editorial audit trail.
- Object storage.
- Sitemap and extended SEO tools.

---

## 16. Git Workflow

- Never commit `.env` or real secrets.
- Keep `.env.example` safe and aligned with setup documentation.
- Do not commit generated build output unless required by a deliberate deployment strategy.
- Ignore local-only AI tooling files where appropriate.
- Commit small, coherent changes after tests pass.
- Review changes before committing:

```powershell
git status
git diff
git diff --cached
git diff --cached --check
```

### Commit message convention

Use Conventional Commit-style prefixes consistently:

- `chore:` — setup and maintenance.
- `feat:` — new functionality.
- `fix:` — bug fixes.
- `test:` — tests.
- `refactor:` — structural changes without intended behavior changes.
- `docs:` — documentation.

Examples:

```text
chore: initialize Folio CMS project
feat: add contributor registration
test: cover page ownership policies
feat: add page review workflow
fix: prevent contributors from publishing pages
docs: update setup instructions
```

---

## 17. Definition of Done

A feature is complete when:

- Expected behavior and edge cases are defined.
- Server-side validation is implemented.
- Authorization is enforced at the application boundary.
- Targeted Pest tests pass.
- Related tests pass.
- The full suite passes at appropriate checkpoints.
- Frontend assets build when relevant.
- Unpublished and private content cannot leak publicly.
- Code follows the project's action, policy, and test conventions.
- The diff contains no unrelated changes or secrets.
- Documentation reflects actual behavior.
- The work is committed in a coherent Git commit.

---

## 18. Immediate Next Step

Start with authentication and role setup:

1. Decide how the `admin` and `contributor` roles will be created reliably.
2. Write a targeted test that a newly registered user receives only the `contributor` role.
3. Run that test.
4. Implement server-side role assignment in the Fortify registration action.
5. Re-run the targeted test.
6. Run the related test suite.

Continue one small step at a time. Do not start the Page model or editorial workflow until registration and authorization foundations have been verified.
