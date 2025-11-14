# CLAUDE.md - AI Assistant Guide for user_roles_api

## Project Overview

**Project Name:** user_roles_api
**Purpose:** RESTful API for managing users and roles with authentication and authorization
**Framework:** Symfony PHP (version 2.x-4.x compatible based on .gitignore)
**Current State:** Initial repository setup - no application code implemented yet
**Repository:** tundechris/user_roles_api
**Development Branch:** claude/claude-md-mhzau1rgxksbhx2x-01YN9683Hv87KUpEpraLAbFa

---

## Current Repository State

### Existing Files (as of Nov 14, 2025)
```
/home/user/user_roles_api/
├── .git/              # Git repository
├── .gitignore         # Symfony project gitignore
└── README.md          # Minimal project description
```

**Status:** This is a greenfield project ready for initial development. Only basic repository structure exists.

---

## Technology Stack

### Core Technologies
- **Language:** PHP (version TBD - recommend PHP 8.1+)
- **Framework:** Symfony (recommend 6.x or 7.x for new projects)
- **API Type:** RESTful JSON API
- **Database:** Not yet configured (recommend PostgreSQL or MySQL)
- **ORM:** Doctrine (Symfony's database layer)
- **Package Manager:** Composer

### Expected Development Tools
- **Testing:** PHPUnit
- **Code Quality:** PHP-CS-Fixer, PHPStan (recommended)
- **API Documentation:** API Platform or Swagger/OpenAPI (recommended)
- **Authentication:** Symfony Security Component + JWT tokens (recommended)

---

## Expected Project Structure

Based on Symfony best practices, the project should follow this structure:

```
user_roles_api/
├── bin/
│   ├── console              # Symfony CLI commands
│   └── phpunit              # Test runner
├── config/
│   ├── packages/            # Bundle configurations
│   ├── routes/              # Route definitions
│   ├── routes.yaml          # Main routing file
│   ├── services.yaml        # Service container configuration
│   └── bundles.php          # Registered bundles
├── migrations/              # Doctrine database migrations
├── public/
│   └── index.php            # Application entry point
├── src/
│   ├── Controller/          # API endpoints
│   │   ├── UserController.php
│   │   └── RoleController.php
│   ├── Entity/              # Domain models
│   │   ├── User.php
│   │   └── Role.php
│   ├── Repository/          # Database queries
│   │   ├── UserRepository.php
│   │   └── RoleRepository.php
│   ├── Service/             # Business logic
│   │   ├── UserService.php
│   │   └── RoleService.php
│   ├── Security/            # Auth & authorization
│   │   └── Voter/           # Access control voters
│   ├── Exception/           # Custom exceptions
│   ├── DTO/                 # Data transfer objects (optional)
│   └── Kernel.php           # Application kernel
├── tests/
│   ├── Unit/                # Unit tests
│   ├── Integration/         # Integration tests
│   └── Functional/          # API endpoint tests
├── var/
│   ├── cache/               # Application cache (gitignored)
│   ├── log/                 # Application logs (gitignored)
│   └── sessions/            # Session storage (gitignored)
├── vendor/                  # Composer dependencies (gitignored)
├── .env                     # Environment config (gitignored)
├── .env.dist/.env.example   # Environment template
├── .gitignore               # Git ignore patterns
├── composer.json            # PHP dependencies
├── composer.lock            # Locked dependency versions
├── phpunit.xml              # PHPUnit configuration
├── symfony.lock             # Symfony Flex recipes
├── CLAUDE.md                # This file
└── README.md                # Project documentation
```

---

## Domain Model

### Core Entities

#### User Entity
```php
// src/Entity/User.php
- id: int (primary key, auto-increment)
- username: string (unique, not null)
- email: string (unique, not null)
- password: string (hashed, not null)
- roles: Collection<Role> (many-to-many)
- isActive: boolean (default true)
- createdAt: DateTime
- updatedAt: DateTime
```

#### Role Entity
```php
// src/Entity/Role.php
- id: int (primary key, auto-increment)
- name: string (unique, not null, e.g., "ROLE_ADMIN", "ROLE_USER")
- description: string (nullable)
- permissions: array/json (optional, for fine-grained permissions)
- users: Collection<User> (many-to-many)
- createdAt: DateTime
- updatedAt: DateTime
```

**Relationship:** Many-to-Many between User and Role
- A user can have multiple roles
- A role can be assigned to multiple users
- Join table: `user_role` (managed by Doctrine)

---

## API Endpoints (Expected)

### User Management
```
POST   /api/users                      # Create new user
GET    /api/users                      # List all users (paginated)
GET    /api/users/{id}                 # Get user by ID
PUT    /api/users/{id}                 # Update user
PATCH  /api/users/{id}                 # Partial update user
DELETE /api/users/{id}                 # Delete user
GET    /api/users/{id}/roles           # Get user's roles
POST   /api/users/{id}/roles           # Assign roles to user
DELETE /api/users/{id}/roles/{roleId}  # Remove role from user
```

### Role Management
```
POST   /api/roles                      # Create new role
GET    /api/roles                      # List all roles
GET    /api/roles/{id}                 # Get role by ID
PUT    /api/roles/{id}                 # Update role
PATCH  /api/roles/{id}                 # Partial update role
DELETE /api/roles/{id}                 # Delete role
GET    /api/roles/{id}/users           # Get users with this role
```

### Authentication (Recommended)
```
POST   /api/auth/login                 # User login (returns JWT token)
POST   /api/auth/register              # User registration
POST   /api/auth/refresh               # Refresh JWT token
POST   /api/auth/logout                # Logout (invalidate token)
```

---

## Development Workflow

### Initial Setup (Not Yet Done)

When starting development, follow these steps:

1. **Create composer.json**
   ```bash
   composer init
   # OR use Symfony skeleton
   composer create-project symfony/skeleton user_roles_api
   ```

2. **Install core dependencies**
   ```bash
   composer require symfony/orm-pack
   composer require symfony/maker-bundle --dev
   composer require symfony/security-bundle
   composer require symfony/serializer-pack
   composer require symfony/validator
   composer require lexik/jwt-authentication-bundle  # for JWT auth
   ```

3. **Configure database**
   - Edit `.env` with database credentials
   - Create database: `php bin/console doctrine:database:create`

4. **Generate entities**
   ```bash
   php bin/console make:entity User
   php bin/console make:entity Role
   ```

5. **Create migration and update schema**
   ```bash
   php bin/console make:migration
   php bin/console doctrine:migrations:migrate
   ```

6. **Generate controllers**
   ```bash
   php bin/console make:controller UserController
   php bin/console make:controller RoleController
   ```

### Daily Development Workflow

1. **Create feature branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make changes** following Symfony best practices

3. **Run tests**
   ```bash
   php bin/phpunit
   # or specific test
   php bin/phpunit tests/Unit/Service/UserServiceTest.php
   ```

4. **Check code quality** (when tools are installed)
   ```bash
   vendor/bin/php-cs-fixer fix --dry-run
   vendor/bin/phpstan analyse src
   ```

5. **Commit changes**
   ```bash
   git add .
   git commit -m "feat: add user creation endpoint"
   ```

6. **Push to remote**
   ```bash
   git push -u origin feature/your-feature-name
   ```

---

## Coding Conventions

### Symfony Best Practices

1. **Controllers**
   - Keep controllers thin - delegate to services
   - Return JSON responses using `JsonResponse` or serializer
   - Use dependency injection, not service locator
   - Handle HTTP concerns only (request/response)

2. **Services**
   - Encapsulate business logic in service classes
   - Make services stateless
   - Use constructor injection for dependencies
   - Prefer interfaces for type hints

3. **Entities**
   - Use Doctrine annotations or attributes
   - Add validation constraints
   - Include timestamps (createdAt, updatedAt)
   - Use lifecycle callbacks for auto-timestamps

4. **Repositories**
   - Custom queries go in repository classes
   - Use QueryBuilder for complex queries
   - Return entities or arrays, not raw data

5. **Security**
   - Never store plain-text passwords - use password hasher
   - Implement RBAC (Role-Based Access Control)
   - Use Voters for complex authorization logic
   - Validate and sanitize all inputs

### PHP Code Style

- **PSR-12** coding standard
- **Type declarations** for all parameters and return types
- **Strict types**: `declare(strict_types=1);` at top of each file
- **Namespaces** following PSR-4 autoloading
- **PHPDoc blocks** for all public methods
- **Meaningful variable names** (no single letters except iterators)

### Example Controller
```php
<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/users', name: 'api_users_')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $users = $this->userService->findAll();
        return $this->json($users);
    }
}
```

### Testing Standards

- **Unit tests** for services and models
- **Integration tests** for repositories
- **Functional tests** for API endpoints
- **Test coverage** aim for 80%+ on critical paths
- Use **fixtures** for test data
- Use **database transactions** in tests (rollback after each test)

---

## Git Workflow

### Branch Naming Conventions
```
feature/description       # New features
bugfix/description        # Bug fixes
hotfix/description        # Urgent production fixes
refactor/description      # Code refactoring
docs/description          # Documentation updates
test/description          # Test additions/fixes
```

### Commit Message Format
```
<type>(<scope>): <subject>

<body>

<footer>
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation only
- `style`: Code style changes (formatting)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

**Examples:**
```
feat(user): add user registration endpoint
fix(auth): resolve JWT token expiration issue
docs(readme): update installation instructions
test(user): add unit tests for UserService
```

### Current Development Branch
All development should occur on:
```
claude/claude-md-mhzau1rgxksbhx2x-01YN9683Hv87KUpEpraLAbFa
```

### Git Operations Best Practices

1. **Always specify branch when pushing:**
   ```bash
   git push -u origin claude/claude-md-mhzau1rgxksbhx2x-01YN9683Hv87KUpEpraLAbFa
   ```

2. **Network retry logic:** If push/fetch fails due to network, retry up to 4 times with exponential backoff (2s, 4s, 8s, 16s)

3. **Fetch specific branches:**
   ```bash
   git fetch origin claude/claude-md-mhzau1rgxksbhx2x-01YN9683Hv87KUpEpraLAbFa
   ```

4. **Never push to main/master** without explicit permission

---

## Environment Configuration

### Environment Variables (.env)

```env
# Application
APP_ENV=dev
APP_SECRET=<generate-secret-key>

# Database
DATABASE_URL="mysql://user:password@127.0.0.1:3306/user_roles_api?serverVersion=8.0"
# OR for PostgreSQL:
# DATABASE_URL="postgresql://user:password@127.0.0.1:5432/user_roles_api?serverVersion=15"

# JWT Authentication (if using lexik/jwt-authentication-bundle)
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your-passphrase
JWT_TOKEN_TTL=3600

# Mailer (if email notifications needed)
MAILER_DSN=null://null
```

### Environment Files Structure
- `.env` - Default configuration (gitignored)
- `.env.local` - Local overrides (gitignored)
- `.env.test` - Test environment configuration
- `.env.dist` or `.env.example` - Template (committed to git)

---

## Security Considerations

### Authentication & Authorization

1. **Password Hashing**
   - Use Symfony's PasswordHasher service
   - Never use md5 or sha1 for passwords
   - Use bcrypt or argon2i algorithm

2. **JWT Tokens**
   - Set appropriate expiration (e.g., 1 hour)
   - Implement refresh token mechanism
   - Store tokens securely on client side

3. **Role-Based Access Control (RBAC)**
   - Use Symfony Security Voters for complex permissions
   - Implement role hierarchy in security.yaml
   - Example roles: ROLE_USER, ROLE_ADMIN, ROLE_SUPER_ADMIN

4. **Input Validation**
   - Use Symfony Validator component
   - Validate all request data
   - Sanitize outputs to prevent XSS

5. **CORS Configuration**
   - Configure CORS properly for API access
   - Whitelist allowed origins
   - Use nelmio/cors-bundle

### Common Security Pitfalls to Avoid

- SQL Injection: Use Doctrine ORM, never raw SQL with user input
- XSS: Always escape output, validate inputs
- CSRF: API doesn't need CSRF for stateless JWT auth
- Sensitive data in logs: Never log passwords or tokens
- Hardcoded secrets: Use environment variables
- Missing authentication: Protect all endpoints except public ones

---

## Database Management

### Migrations

```bash
# Create migration after entity changes
php bin/console make:migration

# Review generated migration in migrations/
# Then apply migration
php bin/console doctrine:migrations:migrate

# Rollback last migration
php bin/console doctrine:migrations:migrate prev

# Check migration status
php bin/console doctrine:migrations:status
```

### Doctrine Commands

```bash
# Validate schema
php bin/console doctrine:schema:validate

# Generate getters/setters for entity
php bin/console make:entity --regenerate

# Load fixtures (if using DoctrineFixturesBundle)
php bin/console doctrine:fixtures:load

# Clear Doctrine cache
php bin/console doctrine:cache:clear-metadata
php bin/console doctrine:cache:clear-query
php bin/console doctrine:cache:clear-result
```

---

## Testing

### PHPUnit Configuration

Create `phpunit.xml.dist`:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="tests/bootstrap.php"
         colors="true">
    <testsuites>
        <testsuite name="Project Test Suite">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

### Running Tests

```bash
# All tests
php bin/phpunit

# Specific test file
php bin/phpunit tests/Unit/Service/UserServiceTest.php

# Tests in directory
php bin/phpunit tests/Unit

# With coverage report (requires xdebug)
php bin/phpunit --coverage-html coverage/
```

### Test Structure Example

```php
<?php
namespace App\Tests\Unit\Service;

use App\Service\UserService;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    private UserService $userService;

    protected function setUp(): void
    {
        // Setup before each test
        $this->userService = new UserService(/* dependencies */);
    }

    public function testCreateUser(): void
    {
        // Arrange
        $userData = ['username' => 'test', 'email' => 'test@example.com'];

        // Act
        $user = $this->userService->create($userData);

        // Assert
        $this->assertNotNull($user);
        $this->assertEquals('test', $user->getUsername());
    }
}
```

---

## Debugging

### Symfony Debug Tools

```bash
# Debug routes
php bin/console debug:router

# Debug services
php bin/console debug:container

# Debug configuration
php bin/console debug:config framework

# Debug event listeners
php bin/console debug:event-dispatcher

# Clear cache
php bin/console cache:clear
```

### Logging

- **Location:** `var/log/dev.log` (development), `var/log/prod.log` (production)
- **Log levels:** DEBUG, INFO, NOTICE, WARNING, ERROR, CRITICAL, ALERT, EMERGENCY
- **Usage in code:**
  ```php
  $this->logger->error('User creation failed', ['user_id' => $id]);
  ```

---

## Performance Optimization

### Doctrine Query Optimization

```php
// Bad: N+1 query problem
foreach ($users as $user) {
    echo $user->getRoles(); // Triggers additional query per user
}

// Good: Use joins to eager load
$users = $userRepository->createQueryBuilder('u')
    ->leftJoin('u.roles', 'r')
    ->addSelect('r')
    ->getQuery()
    ->getResult();
```

### Caching Strategies

1. **HTTP Cache:** Use Symfony HTTP Cache or Varnish
2. **Application Cache:** Use Symfony Cache component
3. **Doctrine Cache:** Cache queries and metadata
4. **Redis/Memcached:** For session and data caching

---

## API Response Format

### Success Response
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "username": "johndoe",
        "email": "john@example.com",
        "roles": [
            {"id": 1, "name": "ROLE_USER"}
        ]
    }
}
```

### Error Response
```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "email": ["This email is already in use"],
        "password": ["Password must be at least 8 characters"]
    }
}
```

### Pagination Response
```json
{
    "status": "success",
    "data": [...],
    "pagination": {
        "page": 1,
        "per_page": 20,
        "total": 150,
        "total_pages": 8
    }
}
```

---

## Common Symfony Commands

### Development
```bash
# Start development server
symfony server:start
# OR
php -S localhost:8000 -t public/

# Run console commands
php bin/console <command>

# List all available commands
php bin/console list

# Get help on specific command
php bin/console help doctrine:migrations:migrate
```

### Code Generation
```bash
# Create entity
php bin/console make:entity

# Create controller
php bin/console make:controller

# Create form
php bin/console make:form

# Create voter (for authorization)
php bin/console make:voter

# Create subscriber (event listener)
php bin/console make:subscriber
```

---

## Troubleshooting

### Common Issues

1. **Composer install fails**
   - Check PHP version compatibility
   - Ensure required PHP extensions are installed (pdo, xml, mbstring, etc.)
   - Clear composer cache: `composer clear-cache`

2. **Database connection errors**
   - Verify DATABASE_URL in .env
   - Check database server is running
   - Ensure database user has proper permissions

3. **Cache issues**
   - Clear cache: `php bin/console cache:clear`
   - Delete var/cache/* manually if needed
   - Check var/ directory permissions (must be writable)

4. **Doctrine schema issues**
   - Validate schema: `php bin/console doctrine:schema:validate`
   - Update schema: `php bin/console doctrine:schema:update --force`
   - Or use migrations for production

5. **Autoloading errors**
   - Regenerate autoload: `composer dump-autoload`
   - Check namespace matches directory structure

---

## AI Assistant Guidelines

### When Working on This Project

1. **Always check current state** before making changes
   - Read relevant files first
   - Understand existing patterns and conventions
   - Don't assume structure exists - verify with Glob/Read

2. **Follow Symfony best practices**
   - Use dependency injection
   - Keep controllers thin
   - Use services for business logic
   - Follow PSR-12 coding standards

3. **Security first**
   - Never commit sensitive data (.env, credentials)
   - Validate all inputs
   - Use parameterized queries (Doctrine does this)
   - Hash passwords properly

4. **Test your changes**
   - Write tests for new features
   - Run existing tests before committing
   - Update tests when modifying functionality

5. **Documentation**
   - Update this CLAUDE.md when structure changes
   - Add PHPDoc comments to all public methods
   - Update README.md with user-facing changes

6. **Git practices**
   - Commit frequently with clear messages
   - Push to designated branch only
   - Use feature branches for major changes
   - Never force push without permission

7. **Communication**
   - Explain what you're doing and why
   - Ask for clarification when requirements are unclear
   - Highlight breaking changes or major decisions
   - Provide alternatives when multiple approaches exist

### Code Review Checklist

Before marking task complete, verify:
- [ ] Code follows Symfony conventions
- [ ] All inputs are validated
- [ ] Passwords are hashed (never plain text)
- [ ] Tests are written and passing
- [ ] No sensitive data in code
- [ ] Error handling is implemented
- [ ] Code is properly documented
- [ ] Database migrations are created if schema changed
- [ ] Changes are committed with clear message
- [ ] Branch is pushed to remote

---

## Resources

### Official Documentation
- Symfony Docs: https://symfony.com/doc/current/index.html
- Doctrine ORM: https://www.doctrine-project.org/projects/doctrine-orm/en/latest/
- PHPUnit: https://phpunit.de/documentation.html
- PHP The Right Way: https://phptherightway.com/

### Useful Bundles
- API Platform: https://api-platform.com/ (advanced API framework)
- LexikJWTAuthenticationBundle: https://github.com/lexik/LexikJWTAuthenticationBundle
- NelmioCorsBundle: https://github.com/nelmio/NelmioCorsBundle
- DoctrineFixturesBundle: https://symfony.com/bundles/DoctrineFixturesBundle/current/index.html

### Code Quality Tools
- PHP-CS-Fixer: https://github.com/FriendsOfPHP/PHP-CS-Fixer
- PHPStan: https://phpstan.org/
- Psalm: https://psalm.dev/

---

## Project Status & Next Steps

### Current Status (as of Nov 14, 2025)
- ✅ Repository initialized
- ✅ .gitignore configured for Symfony
- ✅ README.md created
- ✅ CLAUDE.md documentation created
- ❌ Composer dependencies not installed
- ❌ Symfony application not initialized
- ❌ Database not configured
- ❌ Entities not created
- ❌ Controllers not created
- ❌ Tests not created

### Immediate Next Steps

1. **Initialize Symfony application**
   ```bash
   composer create-project symfony/skeleton .
   ```

2. **Install required dependencies**
   ```bash
   composer require orm security maker serializer validator
   ```

3. **Configure database** (.env file)

4. **Create entities** (User and Role)

5. **Generate migrations and update schema**

6. **Create controllers** and implement API endpoints

7. **Set up authentication** (JWT tokens)

8. **Write tests** for critical functionality

9. **Update README.md** with setup instructions

---

## Changelog

### 2025-11-14
- Initial CLAUDE.md creation
- Documented project structure and conventions
- Added development workflow guidelines
- Established coding standards

---

*Last updated: 2025-11-14*
*Document version: 1.0.0*
*Maintained by: AI Assistants working on user_roles_api*
