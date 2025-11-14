# User Roles API

A RESTful API for managing users and roles with JWT authentication and authorization, built with Symfony 7.1.

## Features

- User management (CRUD operations)
- Role management (CRUD operations)
- JWT-based authentication
- Role-based access control (RBAC)
- Many-to-many relationship between users and roles
- Comprehensive validation
- RESTful API design
- JSON responses

## Technology Stack

- **PHP**: 8.4
- **Framework**: Symfony 7.1
- **Database**: MySQL/PostgreSQL (via Doctrine ORM)
- **Authentication**: JWT (Lexik JWT Authentication Bundle)
- **Testing**: PHPUnit

## Requirements

- PHP 8.1 or higher
- Composer
- MySQL 8.0+ or PostgreSQL 15+
- OpenSSL (for JWT key generation)

## Installation

#### 1. Clone the repository

```bash
git clone https://github.com/tundechris/user_roles_api.git
cd user_roles_api
```

#### 2. Install dependencies

```bash
composer install
```

#### 3. Configure environment

Copy the example environment file and update it with your settings:

```bash
cp .env.example .env
```

Edit `.env` and configure:

```env
# Application
APP_ENV=dev
APP_SECRET=your-secret-key-here

# Database (choose one)
# MySQL:
DATABASE_URL="mysql://user:password@127.0.0.1:3306/user_roles_api?serverVersion=8.0&charset=utf8mb4"

# PostgreSQL:
# DATABASE_URL="postgresql://user:password@127.0.0.1:5432/user_roles_api?serverVersion=15&charset=utf8"

# JWT settings (keys will be generated in next step)
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=
JWT_TOKEN_TTL=3600
```

#### 4. Generate JWT keys

**IMPORTANT:** This step is required for JWT authentication to work.

```bash
# Create JWT directory and generate keys
mkdir -p config/jwt
php bin/console lexik:jwt:generate-keypair
```

This will create:
- `config/jwt/private.pem` - Private key for signing tokens
- `config/jwt/public.pem` - Public key for verifying tokens

**Note:** The `config/jwt/` directory is gitignored for security. You must generate these keys on each environment (local, staging, production).

#### 5. Create the database

```bash
php bin/console doctrine:database:create
```

#### 6. Run migrations

```bash
php bin/console doctrine:migrations:migrate
```

#### 7. (Optional) Load sample data

```bash
php bin/console doctrine:fixtures:load
```

#### 8. Start the development server

```bash
symfony server:start
# OR
php -S localhost:8000 -t public/
```

The API will be available at `http://localhost:8000/api`

## Test Data Fixtures

The project includes comprehensive test fixtures for development and testing purposes. Load them using:

```bash
php bin/console doctrine:fixtures:load
```

### Available Test Users

After loading fixtures, the following test accounts are available:

| Username | Email | Password | Roles | Status |
|----------|-------|----------|-------|--------|
| superadmin | superadmin@example.com | SuperAdmin123! | ROLE_SUPER_ADMIN | Active |
| admin | admin@example.com | Admin123! | ROLE_ADMIN | Active |
| moderator | moderator@example.com | Moderator123! | ROLE_MODERATOR | Active |
| poweruser | power.user@example.com | Power123! | ROLE_ADMIN, ROLE_MODERATOR | Active |
| johndoe | john.doe@example.com | User123! | ROLE_USER | Active |
| janedoe | jane.doe@example.com | User123! | ROLE_USER | Active |
| alice | alice@example.com | Alice123! | ROLE_USER | Active |
| bob | bob@example.com | Bob123! | ROLE_USER | Active |
| charlie | charlie@example.com | Charlie123! | ROLE_USER | Active |
| inactiveuser | inactive@example.com | Inactive123! | ROLE_USER | Inactive |

### Available Roles

| Role Name | Description | Permissions |
|-----------|-------------|-------------|
| ROLE_USER | Standard user with basic access | read:own-profile, update:own-profile |
| ROLE_MODERATOR | Moderator with extended permissions | read:own-profile, update:own-profile, read:users, moderate:content |
| ROLE_ADMIN | Administrator with full user management | read:own-profile, update:own-profile, read:users, create:users, update:users, delete:users, read:roles, assign:roles |
| ROLE_SUPER_ADMIN | Super administrator with complete system access | read:*, create:*, update:*, delete:*, manage:system |

You can use these accounts to test authentication, authorization, and different permission levels.

## API Documentation

Interactive API documentation is available via Swagger UI:

```
http://localhost:8000/api/doc
```

You can explore all endpoints, view request/response schemas, and test API calls directly from the browser. The OpenAPI specification JSON is available at:

```
http://localhost:8000/api/doc.json
```

## API Endpoints

### Authentication

#### Register a new user
```http
POST /api/auth/register
Content-Type: application/json

{
    "username": "johndoe",
    "email": "john@example.com",
    "password": "securepassword"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "User registered successfully",
    "data": {
        "id": 1,
        "username": "johndoe",
        "email": "john@example.com",
        "isActive": true,
        "createdAt": "2025-11-14 20:30:00"
    }
}
```

#### Login
```http
POST /api/auth/login
Content-Type: application/json

{
    "username": "johndoe",
    "password": "securepassword"
}
```

**Response:**
```json
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
}
```

Use the token in subsequent requests:
```http
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...
```

### User Management

All user endpoints require authentication (except registration).

#### List all users
```http
GET /api/users
Authorization: Bearer {token}
```

#### Get user by ID
```http
GET /api/users/{id}
Authorization: Bearer {token}
```

#### Create user
```http
POST /api/users
Authorization: Bearer {token}
Content-Type: application/json

{
    "username": "janedoe",
    "email": "jane@example.com",
    "password": "password123",
    "isActive": true
}
```

#### Update user
```http
PUT /api/users/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "username": "janedoe_updated",
    "email": "jane.updated@example.com"
}
```

#### Delete user
```http
DELETE /api/users/{id}
Authorization: Bearer {token}
```

#### Get user's roles
```http
GET /api/users/{id}/roles
Authorization: Bearer {token}
```

#### Assign roles to user
```http
POST /api/users/{id}/roles
Authorization: Bearer {token}
Content-Type: application/json

{
    "roleIds": [1, 2, 3]
}
```

#### Remove role from user
```http
DELETE /api/users/{id}/roles/{roleId}
Authorization: Bearer {token}
```

#### Search users
```http
GET /api/users/search?q=john
Authorization: Bearer {token}
```

#### Get user statistics
```http
GET /api/users/stats
Authorization: Bearer {token}
```

### Role Management

All role endpoints require authentication.

#### List all roles
```http
GET /api/roles
Authorization: Bearer {token}
```

#### Get role by ID
```http
GET /api/roles/{id}
Authorization: Bearer {token}
```

#### Create role
```http
POST /api/roles
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "ROLE_ADMIN",
    "description": "Administrator role",
    "permissions": ["user.create", "user.edit", "user.delete"]
}
```

**Note:** Role names must follow the pattern `ROLE_*` with uppercase letters and underscores only.

#### Update role
```http
PUT /api/roles/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "ROLE_ADMIN",
    "description": "Updated administrator role"
}
```

#### Delete role
```http
DELETE /api/roles/{id}
Authorization: Bearer {token}
```

#### Get users with this role
```http
GET /api/roles/{id}/users
Authorization: Bearer {token}
```

#### Search roles
```http
GET /api/roles/search?q=ADMIN
Authorization: Bearer {token}
```

#### Get role statistics
```http
GET /api/roles/stats
Authorization: Bearer {token}
```

## Response Format

### Success Response
```json
{
    "status": "success",
    "data": { ... }
}
```

### Error Response
```json
{
    "status": "error",
    "message": "Error description"
}
```

### Validation Error Response
```json
{
    "status": "error",
    "message": "Validation failed: {...}",
    "errors": {
        "field": ["Error message"]
    }
}
```

## Database Schema

### Users Table
- `id`: int (PK)
- `username`: string (unique)
- `email`: string (unique)
- `password`: string (hashed)
- `is_active`: boolean
- `created_at`: datetime
- `updated_at`: datetime

### Roles Table
- `id`: int (PK)
- `name`: string (unique)
- `description`: string (nullable)
- `permissions`: json (nullable)
- `created_at`: datetime
- `updated_at`: datetime

### User_Role Table (Junction)
- `user_id`: int (FK)
- `role_id`: int (FK)

## Testing

Run the test suite:

```bash
php bin/phpunit
```

Run specific test:

```bash
php bin/phpunit tests/Unit/Entity/UserTest.php
```

## Development Commands

### Doctrine Commands

```bash
# Validate schema
php bin/console doctrine:schema:validate

# Create migration
php bin/console make:migration

# Run migrations
php bin/console doctrine:migrations:migrate

# Rollback migration
php bin/console doctrine:migrations:migrate prev
```

### Debug Commands

```bash
# List all routes
php bin/console debug:router

# List all services
php bin/console debug:container

# Clear cache
php bin/console cache:clear
```

### Code Generation

```bash
# Generate entity
php bin/console make:entity

# Generate controller
php bin/console make:controller

# Generate service
php bin/console make:service
```

## Security Considerations

1. **Password Hashing**: Passwords are automatically hashed using Symfony's PasswordHasher (bcrypt/argon2i)
2. **JWT Tokens**: Tokens expire after 1 hour (configurable in `.env`)
3. **Input Validation**: All inputs are validated using Symfony Validator
4. **SQL Injection**: Protected by Doctrine ORM parameterized queries
5. **CORS**: Configured via NelmioCorsBundle (modify `config/packages/nelmio_cors.yaml` as needed)

## Project Structure

```
user_roles_api/
├── bin/                      # Console and executable files
├── config/                   # Configuration files
│   ├── packages/            # Bundle configurations
│   ├── jwt/                 # JWT keys
│   └── routes.yaml          # Route definitions
├── migrations/              # Database migrations
├── public/                  # Public web directory
│   └── index.php           # Application entry point
├── src/
│   ├── Controller/         # API controllers
│   ├── Entity/             # Doctrine entities
│   ├── Repository/         # Database repositories
│   ├── Service/            # Business logic services
│   └── Kernel.php          # Application kernel
├── tests/                  # Test files
│   ├── Unit/              # Unit tests
│   ├── Integration/       # Integration tests
│   └── Functional/        # Functional tests
├── var/                    # Cache and logs
├── vendor/                 # Composer dependencies
├── .env                    # Environment configuration
├── composer.json           # PHP dependencies
└── README.md              # This file
```

## Contributing

1. Create a feature branch
2. Make your changes
3. Write/update tests
4. Ensure tests pass
5. Submit a pull request

## Coding Standards

- Follow PSR-12 coding standard
- Use type declarations for all parameters and return types
- Add PHPDoc blocks for all public methods
- Write tests for new features

## License

This project is proprietary.

## Support

For issues and questions, please create an issue in the GitHub repository.

## Changelog

### Version 1.0.0 (2025-11-14)
- Initial release
- User management CRUD
- Role management CRUD
- JWT authentication
- Role-based access control
- Database migrations
- Unit tests

## TODO / Future Enhancements

- [ ] Implement token refresh mechanism
- [ ] Add pagination for list endpoints
- [ ] Implement password reset functionality
- [ ] Add email verification for new users
- [ ] Implement rate limiting
- [x] Add API documentation (Swagger/OpenAPI) - Completed
- [ ] Add more comprehensive test coverage
- [ ] Implement user activity logging
- [ ] Add role permissions enforcement via Voters
- [ ] Docker containerization

---

Built with Symfony 7.1 | PHP 8.4
