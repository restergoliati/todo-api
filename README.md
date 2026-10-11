# Todo API

A production-style Laravel REST API for managing users and
user-scoped todo items.

## Stack

- PHP 8.2
- Laravel 10
- MySQL 8
- Redis 7
- Docker Compose
- JWT Authentication
- PHPUnit

## Architecture

The application follows:

Controller
→ Form Request
→ Service
→ Repository Interface
→ Repository Implementation
→ Eloquent Model

Asynchronous operations are processed through
Redis-backed Laravel queues.

## Requirements

- Docker
- Docker Compose
- Git

## Setup

Clone the repository:

git clone <repository-url>

cd todo-api

Build containers:

docker compose build

Start:

docker compose up -d

Generate application key:

docker compose exec app php artisan key:generate

Generate JWT secret:

docker compose exec app php artisan jwt:secret

Run migrations:

docker compose exec app php artisan migrate

Seed database:

docker compose exec app php artisan db:seed

## Queue Worker

The queue worker runs through Docker Compose.

Check logs:

docker compose logs -f queue

## API

Base URL:

http://localhost:8000/api/v1

### Public

POST /auth/register

POST /auth/login

### Authenticated

GET /profile

GET /todos

POST /todos

GET /todos/{id}

PUT /todos/{id}

DELETE /todos/{id}

POST /todos/bulk-complete

GET /jobs/{id}

## Tests

Run:

docker compose exec app vendor/bin/phpunit

## Queue

Redis is configured as the Laravel queue driver.

Bulk todo completion is dispatched asynchronously.

The returned job ID can be used to query:

GET /jobs/{id}

## Error Handling

API errors use a consistent JSON response envelope.

Validation errors return HTTP 422.

Unauthenticated requests return HTTP 401.

Forbidden operations return HTTP 403.

Missing resources return HTTP 404.

## Architecture Decisions

Repositories isolate data access from controllers.

Services contain business logic.

Form Requests contain validation.

Redis handles asynchronous queue processing.

Job status is persisted in MySQL to provide
client-visible job progress.

## Tradeoffs

The implementation intentionally keeps the domain model
small while demonstrating the layered architecture required
for production use.

## Testing

Feature tests cover authentication and todo endpoints.

Unit tests cover service/repository behavior.

## Delivery

The project is maintained through incremental commits
and submitted through a pull/merge request into main.