<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- filament/filament (FILAMENT) - v5
- laravel/ai (AI) - v0
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/socialite (SOCIALITE) - v5
- livewire/livewire (LIVEWIRE) - v4
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>

=== project rules ===

# Lead Intake Assistant — Project Guidelines

## Project Identity

This is **Lead Intake Assistant**, a multi-tenant SaaS lead qualification platform for trade businesses (roofing first). It uses AI-driven conversations to qualify leads before they enter the customer's CRM.

## Core Rule: Always Start Here

- Before ANY code change, read `developing.md` at the project root. It is the single source of truth synthesizing all 4 spec documents in `developing/`.
- If `developing.md` contradicts a spec document, `developing.md` wins — but flag the discrepancy.

## What We Build

- AI chat widget (separate frontend, embedded via script tag)
- AI conversation engine with structured extraction
- Multi-tenant admin panel (Filament)
- Lead qualification, scoring, summaries, notifications
- Missed call recovery
- Industry-configurable qualification templates

## What We NEVER Build

- CRM, pipelines, kanban boards, scheduling, calendars, invoicing, estimates, payments, job management, customer portals.
- The platform STOPS at lead qualification and delivery.

## Architecture Constraints

- **AI is first-class**: Use `laravel/ai` SDK. Support DeepSeek for MVP (swappable). AI drives conversation; platform owns business rules.
- **Multi-tenant by default**: Every query must be tenant-scoped. Every record belongs to a tenant.
- **Structured data is source of truth**: AI conversations always map to normalized fields. Never rely on raw conversation text alone.
- **Queue expensive operations**: AI calls, image processing, summaries, scoring, notifications — all queued. SQLite for queue DB to avoid overloading MySQL.
- **Widget is separate**: The customer-facing widget is its own frontend app, not part of the Laravel monolith. It talks to the backend via API.
- **New industries via config**: Adding roofing, HVAC, plumbing, etc. must NOT require code changes. Configuration-driven.
- **Database**: MySQL (main). No ENUM columns — store strings, map to PHP enums in `app/Enums/`. Use `json` (not jsonb).
- **File storage**: Local disk via Spatie MediaLibrary with `media` disk. Use Laravel `Storage` facade — easy migration to S3/R2 later.
- **API security**: Rate limit all public endpoints per IP + per tenant + per lead. Validate Twilio webhook signatures. Use signed URLs for lead intake.

## AI Implementation Rules

- AI extracts structured fields via tool/function calling — never stores data directly.
- AI must never invent customer info, fabricate phone numbers/addresses, provide legal/engineering advice, or estimate costs.
- AI never decides lead completion — the QualificationEngine does.
- Use smaller models for extraction/classification, larger models for summaries/complex conversations.
- Prompt structure: System Prompt → Industry Prompt → Qualification State → Conversation History → Latest User Message.

## Configuration Hierarchy

```
Global Defaults → Industry Config → Customer Config → Lead-Specific Runtime
```

Customer overrides industry. Industry overrides global.

## Testing

- Use Pest 4 for all tests.
- Feature tests over unit tests.
- Use model factories (never create models directly in tests).
- Run `php artisan test --compact` to verify.

## Code Quality

- Run `vendor/bin/pint --dirty --format agent` after any PHP changes.
- Use `php artisan make:` commands for new files with `--no-interaction`.
- PHP 8.5: constructor property promotion, typed properties, enums with TitleCase keys.
- PHPDoc blocks over inline comments.

## UI / Frontend

- Tailwind CSS v4 for all styling.
- Filament 5 for admin panel only — never for public lead qualification.
- Widget is mobile-first, lightweight, framework-agnostic.

## Session Workflow

1. Read `developing.md` first.
2. Plan changes against the architecture in `developing.md`.
3. When uncertain about Filament or Livewire APIs, consult the documentation summaries in:
   - `developing/filament/` — Filament 5 reference
   - `developing/livewire/` — Livewire 4 reference
   - Also use `search-docs` (Boost MCP) for live documentation queries.
4. Write tests alongside code.
5. Run Pint before finishing.
6. Never create documentation files unless explicitly asked.
7. All enum-like values go in `app/Enums/` as PHP 8.5 backed enums. Database columns store the string value — never use MySQL ENUM type.
8. All filesystem operations use Laravel's `Storage` facade with the `media` disk. No hardcoded paths.
