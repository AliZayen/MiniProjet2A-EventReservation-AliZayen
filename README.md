# MyEvent

MyEvent is a Symfony 7 web application for event publishing, discovery, and registration.  
It supports role-based workflows for participants, organizers, and administrators, with a modern Bootstrap-based UI.

## Features

- Authentication (sign up, sign in, sign out) with role-aware access control.
- Event management (create, edit, delete, approve, and pin events).
- Event discovery with status filters (today, upcoming, passed) and search.
- Registration flow for participants with ticket type and payment status tracking.
- Organizer/admin registration dashboards with CSV export.
- Admin user management and organizer approval workflow.
- Toast notifications for successful and failed actions.
- Portfolio and landing pages integrated into the same app shell.

## Tech Stack

- PHP 8.2+
- Symfony 7.4
- Doctrine ORM + Doctrine Migrations
- Twig templates
- MySQL / MariaDB
- Bootswatch (Cyborg) + Bootstrap Icons

## Project Structure

- `src/Controller/` application HTTP controllers
- `src/Entity/` Doctrine entities (e.g. `User`, `Event`, `EventRegistration`)
- `src/Form/` Symfony form types
- `src/Repository/` query logic
- `src/Security/` auth-related classes
- `src/EventSubscriber/` auth flash notifications
- `templates/` Twig views
- `migrations/` database migrations
- `config/` framework, security, routing, and package configuration

## Roles and Permissions

- `ROLE_PARTICIPANT`
  - Browse accepted events
  - Register for events
  - View personal registrations
- `ROLE_ORGANIZER`
  - Create and manage own events
  - View registrations of own events
  - Export event registrations CSV
  - Requires admin approval to be fully active
- `ROLE_ADMIN`
  - Full access to users, events, and registrations
  - Approves organizer accounts
  - Approves/pins events

## Prerequisites

Before running locally, ensure:

- PHP 8.2+ is installed
- Composer is installed
- MySQL/MariaDB is running
- Symfony CLI is installed (recommended)

## Local Setup

1. Clone the repository and enter the project:

```bash
git clone <your-repo-url>
cd my_event
```

1. Install dependencies:

```bash
composer install
```

1. Configure environment variables:

- Update `.env` (or `.env.local`) with your database connection:

```dotenv
DATABASE_URL="mysql://root@127.0.0.1:3306/my_event?serverVersion=10.4.32-MariaDB&charset=utf8mb4"
```

1. Create database and run migrations:

```bash
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate
```

1. Start the server:

```bash
symfony server:start
```

Then open `http://127.0.0.1:8000`.

## Useful Commands

- Clear cache:

```bash
php bin/console cache:clear
```

- Lint Twig templates:

```bash
php bin/console lint:twig templates
```

- Lint service container:

```bash
php bin/console lint:container
```

- Run tests:

```bash
php bin/phpunit](docs/screenshots/admin-users.png)
```

## Authentication Notes

- Login is form-based (`/login`) with CSRF protection enabled.
- Logout route is `/logout` (POST via form button).
- Organizer accounts are checked by `UserChecker` and can be blocked from login until approved.
- Authentication events are handled in `AuthenticationFlashSubscriber` to display login/logout status messages.

## Notifications

The base layout renders flash messages as Bootstrap toasts for:

- successful actions (`success`)
- warnings (`warning`)
- failed/security actions (`danger` / `error`)
- informational messages (`info`)

## Troubleshooting

- **Cannot login as organizer**
  - Verify organizer user is approved by an admin.
- **Access denied on routes**
  - Check `config/packages/security.yaml` role rules and your current account role.
- **Database errors on startup**
  - Verify `DATABASE_URL`, DB server status, and migration state.
- **Styles/scripts not loading**
  - Confirm internet access to CDN assets (Bootswatch/Bootstrap Icons/Bootstrap JS).

## Roadmap Ideas

- Add pagination for event and registration lists.
- Add email notifications for organizer approvals and event updates.
- Add richer profile/portfolio editing from admin panel.
- Add API endpoints for mobile clients.

## License

This project is currently configured as `proprietary` in `composer.json`.