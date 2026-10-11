# Todo API

A production-style Laravel REST API for managing users and user-scoped todo items. The application demonstrates layered architecture, JWT authentication, asynchronous job processing, and automated testing.

## Technology Stack

- **PHP:** 8.2
- **Framework:** Laravel 10
- **Database:** MySQL 8
- **Queue / Cache Infrastructure:** Redis 7
- **Containerization:** Docker Compose
- **Authentication:** JWT
- **Testing:** PHPUnit

## Architecture

The application follows a layered architecture that separates HTTP handling, validation, business logic, and data access.

```text
Controller
    ↓
Form Request
    ↓
Service
    ↓
Repository Interface
    ↓
Repository Implementation
    ↓
Eloquent Model
```

Asynchronous operations are processed through Redis-backed Laravel queues, while job status is persisted in MySQL for client-side tracking.

## Prerequisites

Ensure the following tools are installed:

- Docker
- Docker Compose
- Git

## Getting Started

### 1. Clone the Repository

```bash
git clone <repository-url>
cd todo-api
```

### 2. Build the Containers

```bash
docker compose build
```

### 3. Start the Application

```bash
docker compose up -d
```

### 4. Generate the Application Key

```bash
docker compose exec app php artisan key:generate
```

### 5. Generate the JWT Secret

```bash
docker compose exec app php artisan jwt:secret
```

### 6. Run Database Migrations

```bash
docker compose exec app php artisan migrate
```

### 7. Seed the Database

```bash
docker compose exec app php artisan db:seed
```

## API Reference

**Base URL:** `http://localhost:8000/api/v1`

### Public Endpoints

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/auth/register` | Register a new user |
| `POST` | `/auth/login` | Authenticate a user |

### Authenticated Endpoints

The following endpoints require valid JWT authentication.

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/profile` | Retrieve the authenticated user's profile |
| `GET` | `/todos` | List the user's todo items |
| `POST` | `/todos` | Create a todo item |
| `GET` | `/todos/{id}` | Retrieve a specific todo item |
| `PUT` | `/todos/{id}` | Update a todo item |
| `DELETE` | `/todos/{id}` | Delete a todo item |
| `POST` | `/todos/bulk-complete` | Submit a bulk-completion job |
| `GET` | `/jobs/{id}` | Retrieve asynchronous job status |

## Asynchronous Job Processing

Redis is configured as the Laravel queue driver. Bulk todo completion is dispatched as an asynchronous operation, allowing the API to return without waiting for the entire operation to finish.

The returned job ID can be used to track progress through:

```http
GET /api/v1/jobs/{id}
```

### Monitoring the Queue Worker

The queue worker runs through Docker Compose. To inspect its logs:

```bash
docker compose logs -f queue
```

## Error Handling

The API uses a consistent JSON response envelope for errors and failures.

| HTTP Status | Meaning |
|---|---|
| `401 Unauthorized` | Authentication is missing or invalid |
| `403 Forbidden` | The requested operation is not permitted |
| `404 Not Found` | The requested resource does not exist |
| `422 Unprocessable Entity` | Request validation failed |

## Testing

The test suite uses PHPUnit to verify API behavior and application logic.

### Run the Test Suite

```bash
docker compose exec app vendor/bin/phpunit
```

### Test Coverage

- **Feature tests:** Authentication and todo API endpoints.
- **Unit tests:** Service and repository behavior.

## Architecture Decisions

| Decision | Rationale |
|---|---|
| Repository pattern | Isolates data access from controllers and business logic. |
| Service layer | Encapsulates business rules and application workflows. |
| Form Requests | Centralizes request validation. |
| Redis-backed queues | Supports asynchronous processing without blocking the request lifecycle. |
| MySQL job persistence | Enables clients to query job status and progress. |
| Docker Compose | Provides a consistent, reproducible development environment. |

## Design Trade-offs

The implementation intentionally keeps the domain model small while demonstrating the layered architecture and engineering practices expected in production-oriented API development.

The separation of concerns improves maintainability and testability while allowing individual components to evolve independently.

## Delivery Workflow

The project is developed through incremental commits and submitted to the `main` branch through a pull request or merge request, supporting reviewable changes and a structured delivery process.