# Canoe Fund Management Platform

A fund management application built with Laravel 12 and Vue 3 that supports CRUD operations for funds, fund managers, and companies, with event-driven duplicate fund detection via RabbitMQ.

## Tech Stack

| Service    | Version    | Host Port |
|------------|------------|-----------|
| Nginx      | latest     | 8080      |
| PHP (FPM)  | 8.4        | -         |
| MySQL      | latest     | 8306      |
| Redis      | latest     | 6379      |
| RabbitMQ   | latest     | 5672 / 15672 |

- **Backend:** Laravel 12
- **Frontend:** Vue 3, Vue Router 4, Tailwind CSS 4
- **Build:** Vite 7
- **Testing:** PHPUnit 11 with SQLite in-memory

## Getting Started

```bash
./init.sh
```

This script handles everything: Docker services, dependencies, migrations, seeding, frontend build, API documentation generation, tests, and starts the queue worker.

The application is available at **http://localhost:8080**.

## Credentials (Local Development Only)

### MySQL

- Host: `mysql` (internal) / `localhost:8306` (external)
- Database: `silitech`
- User: `silitech`
- Password: `silitech`

### RabbitMQ

- Host: `rabbitmq` (internal) / `localhost:5672` (external)
- Management UI: http://localhost:15672
- User: `silitech`
- Password: `silitech`

## Running Tests

```bash
docker compose exec php php artisan test
```

Tests use SQLite in-memory with `QUEUE_CONNECTION=sync` so no external services are needed.

## ER Diagram

```
┌──────────────────────┐       ┌──────────────────────────┐       ┌──────────────────────┐
│    FUND_MANAGERS     │       │          FUNDS           │       │     FUND_ALIASES     │
├──────────────────────┤       ├──────────────────────────┤       ├──────────────────────┤
│ id          PK       │       │ id              PK       │       │ id          PK       │
│ name        string   │──1:N─▶│ fund_manager_id FK       │◀─N:1──│ fund_id     FK       │
│ deleted_at  timestamp│       │ name            string   │       │ name        string UK│
│ created_at  timestamp│       │ start_year      smallint │       │ created_at  timestamp│
│ updated_at  timestamp│       │ deleted_at      timestamp│       │ updated_at  timestamp│
└──────────────────────┘       │ created_at      timestamp│       └──────────────────────┘
                               │ updated_at      timestamp│
                               └─────────┬────────────────┘
                                         │
                                         │ N:N
                                         ▼
                               ┌──────────────────────────┐       ┌──────────────────────┐
                               │     COMPANY_FUND         │       │      COMPANIES       │
                               │       (pivot)            │       ├──────────────────────┤
                               ├──────────────────────────┤       │ id          PK       │
                               │ fund_id    FK  ├─────────│──N:1─▶│ name        string   │
                               │ company_id FK  ├─────────│       │ deleted_at  timestamp│
                               │ created_at     timestamp │       │ created_at  timestamp│
                               │ updated_at     timestamp │       │ updated_at  timestamp│
                               └──────────────────────────┘       └──────────────────────┘

┌──────────────────────────────────────┐
│       DUPLICATE_FUND_WARNINGS        │
├──────────────────────────────────────┤
│ id                PK                 │
│ fund_id           FK ──▶ funds.id    │
│ duplicate_fund_id FK ──▶ funds.id    │
│ fund_manager_id   FK ──▶ fund_mgrs  │
│ matched_name      string            │
│ is_resolved       boolean           │
│ created_at        timestamp         │
│ updated_at        timestamp         │
└──────────────────────────────────────┘
```

## API Documentation

Interactive API documentation is available via Swagger UI at **http://localhost:8080/api/documentation** (non-production environments only).

All API responses follow the [JSON:API](https://jsonapi.org/) specification, with resources wrapped in `{ "data": { "type", "id", "attributes", "relationships" } }` envelopes and related resources included via the `included` top-level member.

## Architecture

### Event-Driven Duplicate Detection

1. When a fund is created, `FundService` calls `DuplicateDetectionService` to check for name/alias matches (case-insensitive) within the same fund manager.
2. For each match found, a `DuplicateFundWarningEvent` is dispatched.
3. The `PersistDuplicateFundWarning` listener (implements `ShouldQueue`) processes events via RabbitMQ and persists warnings to the `duplicate_fund_warnings` table.
4. The warnings API endpoint exposes unresolved duplicates for review.

### Key Design Decisions

- **Fund Manager deletion protection:** Returns 409 Conflict instead of cascading deletes, since losing fund data is riskier than requiring explicit cleanup.
- **Alias uniqueness:** Enforced at both the database level (unique index on `fund_aliases.name`) and application level (cross-table validation in form requests) to prevent aliases from conflicting with fund names.
- **Soft deletes:** Applied to funds, fund managers, and companies. Soft-deleted records are automatically excluded from queries via Laravel's `SoftDeletes` trait.
- **Queue driver flexibility:** The listener uses the default queue connection (`rabbitmq` in production, `sync` in tests).

### Tradeoffs & Areas for Improvement

- **Async duplicate detection is overkill for simple matching:** The current case-insensitive name/alias comparison is fast enough to run synchronously. The async queue architecture becomes valuable when duplicate detection evolves to use AI-powered validation (NLP, fuzzy matching, machine learning), where processing time would be unpredictable and better handled outside the request lifecycle.
- **Dedicated worker container:** A separate app container should be added for the queue worker instead of running it in the same container as PHP-FPM, allowing independent scaling and preventing worker crashes from affecting the web server.
- **Fund name search:** The `LIKE '%...%'` filter on fund names cannot leverage a standard B-tree index. A full-text index on `funds.name` would improve search performance at scale.
- **Authentication/Authorization** is not implemented. In production, API endpoints would be protected by middleware and policies.
- **Pagination** is fixed at 15 items per page. This could be made configurable via query parameters.
- **Database transactions** wrap fund creation and updates to ensure atomic operations with aliases and company associations.

### Scalability Considerations

- **Database indexes** on `fund_manager_id`, `fund_aliases.name` (unique), and the `company_fund` composite key support efficient queries as data grows.
- **RabbitMQ** decouples duplicate detection from the request lifecycle, keeping API responses fast even if detection logic becomes more complex.
- **Pagination** on all list endpoints prevents loading entire datasets.
- **Soft deletes** preserve audit history without impacting query performance (filtered by index on `deleted_at`).
