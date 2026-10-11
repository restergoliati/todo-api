# Todo API

A production-style Laravel REST API for managing users and user-scoped todo items. The application demonstrates layered architecture, JWT authentication, asynchronous job processing, consistent error handling, and automated testing.

## Technology Stack

- **PHP:** 8.2
- **Framework:** Laravel 10
- **Database:** MySQL 8
- **Queue:** Redis 7
- **Containerization:** Docker Compose
- **Authentication:** JWT
- **Testing:** PHPUnit
- **API Testing:** Postman

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

Asynchronous operations are processed through Redis-backed Laravel queues, while job status is persisted in MySQL for client-visible progress tracking.

## Prerequisites

Ensure the following tools are installed:

- Docker
- Docker Compose
- Git
- Postman (optional, for API testing)

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

### 8. Verify Container Status

```bash
docker compose ps
```

Ensure the application, database, and queue services are running before testing the API.

## API Reference

**Base URL:** `http://localhost:8000/api/v1`

### Public Endpoints

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/auth/register` | Register a new user |
| `POST` | `/auth/login` | Authenticate a user and obtain a token |

### Authenticated Endpoints

The following endpoints require valid JWT authentication.

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/profile` | Retrieve the authenticated user's profile |
| `GET` | `/todos` | List the authenticated user's todo items |
| `POST` | `/todos` | Create a todo item |
| `GET` | `/todos/{id}` | Retrieve a specific todo item |
| `PUT` | `/todos/{id}` | Update a todo item |
| `DELETE` | `/todos/{id}` | Delete a todo item |
| `POST` | `/todos/bulk-complete` | Submit an asynchronous bulk-completion job |
| `GET` | `/jobs/{id}` | Retrieve asynchronous job status |

### Authentication

Obtain a JWT by calling the login endpoint. Include the token in the `Authorization` header when accessing protected endpoints.

```http
Authorization: Bearer <your-jwt-token>
```

## Postman Collection

The Postman collection is intended to be stored in the repository at:

`postman/Todo-API.postman_collection.json`

Import the collection into Postman to explore and test the API endpoints.

### Import Instructions

1. Open Postman.
2. Select **Import**.
3. Select `docs/postman/Todo-API.postman_collection.json` from the cloned repository.
4. Set the base URL to `http://localhost:8000/api/v1`.
5. Register or log in to obtain a JWT.
6. Include the JWT when testing authenticated endpoints.

**Note:** The path above assumes the collection is saved in the `docs/postman/` directory. Adjust it if the collection is stored elsewhere in the repository.

## Asynchronous Job Processing

Redis is configured as the Laravel queue driver. Bulk todo completion is dispatched as an asynchronous operation, allowing the API to return a job identifier without waiting for the entire operation to finish.

### Track Job Status

Use the returned job ID to query the status of an asynchronous operation:

```http
GET /api/v1/jobs/{id}
```

The endpoint exposes the persisted job status according to the application's implementation.

### Monitor the Queue Worker

The queue worker runs through Docker Compose. Inspect its logs using:

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

Run the test suite before submitting changes to help detect regressions.

## Architecture Decisions

| Decision | Rationale |
|---|---|
| Repository pattern | Isolates data access from controllers and business logic. |
| Service layer | Encapsulates business rules and application workflows. |
| Form Requests | Centralizes request validation. |
| Redis-backed queues | Supports asynchronous processing without blocking the request lifecycle. |
| MySQL job persistence | Enables clients to query asynchronous job status. |
| Docker Compose | Provides a consistent development environment. |
| PHPUnit | Supports automated regression testing. |

## Design Trade-offs

The implementation intentionally keeps the domain model small while demonstrating the layered architecture and engineering practices expected in production-oriented API development.

Separating validation, business logic, and persistence improves maintainability and testability while allowing individual components to evolve independently.

## Troubleshooting

### Laravel Storage and Cache Permission Errors

#### Symptom

The API may return an error similar to:

```json
{
  "success": false,
  "message": "An unexpected error occurred."
}
```

Laravel may also report permission errors when writing to files under `storage/framework/cache/data`.

#### Cause

This is a filesystem permissions issue within the Docker environment, rather than an API endpoint problem. The PHP runtime must have write access to Laravel's `storage` and `bootstrap/cache` directories.

The issue can occur when files are created or modified by the host operating system or another container user, leaving the application runtime unable to write to them.

#### 1. Identify the PHP Runtime User

Inspect the container and PHP-FPM processes:

```bash
docker compose exec app sh -lc \
  'id; ps -eo user,group,comm,args | grep "[p]hp-fpm"'
```

Determine the user and group used by the PHP application before changing ownership.

If the container does not run PHP-FPM, identify the user running the PHP application process instead.

#### 2. Fix Directory Ownership and Permissions

The following example assumes the PHP runtime uses the `www-data` user and group:

```bash
docker compose exec -u root app sh -lc '
mkdir -p storage/logs \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache

find storage bootstrap/cache -type d -exec chmod 775 {} \;
find storage bootstrap/cache -type f -exec chmod 664 {} \;
'
```

If PHP runs under a different account, replace `www-data:www-data` with the appropriate user and group.

This approach grants the application the necessary write permissions without using the insecure `chmod -R 777` workaround.

> **Note:** If your application source code is bind-mounted from the host, changing ownership inside the container can also change ownership of corresponding host files. Verify your Docker volume configuration before applying this fix.

#### 3. Clear Laravel's Cached Configuration

Run:

```bash
docker compose exec app php artisan optimize:clear
```

This clears Laravel's cached optimization files and configuration-related caches. It does not replace the filesystem permission fix.

#### 4. Verify Write Access

Check whether the affected directories are writable:

```bash
docker compose exec app sh -lc '
test -w storage/logs \
  && echo "Log directory: writable" \
  || echo "Log directory: NOT writable"

test -w storage/framework/cache/data \
  && echo "Cache directory: writable" \
  || echo "Cache directory: NOT writable"
'
```

Both directories should report `writable` when checked under a user with the same permissions as the application runtime.

For an accurate check, run the verification as the actual PHP runtime user.

#### 5. Retry the API Request

Retry the previously failing API request using Postman or another HTTP client.

If the error persists, investigate:

- **Runtime identity:** PHP-FPM may run under a different user or group than expected.
- **Docker volume mounts:** Bind-mounted host directories may retain incompatible ownership or permissions.
- **Container configuration:** The application may run as a non-root user whose permissions differ from those assumed by the fix.
- **Filesystem restrictions:** Host filesystem or security policies may prevent writes despite apparently correct permissions.

Inspect the application and queue logs:

```bash
docker compose logs --tail=100 app
docker compose logs --tail=100 queue
```

#### Prevention

Ensure Laravel's writable directories have appropriate ownership and permissions as part of the Docker image or container initialization process. This helps prevent permission errors from recurring after container recreation or deployment.

Avoid running the application as root or granting unrestricted write permissions merely to bypass filesystem errors.

## Delivery Workflow

The project is developed through incremental commits and submitted to the `main` branch through a pull request or merge request, supporting reviewable changes and a structured delivery process.