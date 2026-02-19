# Technical assignment for ThinkHuge

Simple PHP 8.5 MVC application (custom backend, MySQL, Docker Compose).
For purpose of this technical assignment I implemented basic security measures like for example:
- Password hashing
- Protecting routes by authentication gate
- Secure session cookie settings (HttpOnly, SameSite=Lax, Secure when HTTPS is available)
- CSRF protection with per-session token generation + verification
- SQL injection protection using prepared PDO statements across repositories
- Output escaping (XSS protection)
- Persistent login throttling/lockout

In production environement the application should also have the following features:
- Should be served via HTTPS
- Multi-Factor Authentication
- Password reset flow
- Secure and centralized secrets management

## Requirements

- Docker
- Docker Compose
- Free ports:
  - `8080` (application)
  - `3306` (MySQL)

### Important:

The application was built and successfully ran using:
- Docker version 29.2.0
- Docker Compose version v5.0.2

## Run Locally

1. Clone the repository.

```bash
git clone https://github.com/litwinski-p/th.git
```

2. Create environment file:

```bash
cp .env.example .env
```

3. Build and start containers:

```bash
docker compose up --build
```

4. Open the app:

```text
http://localhost:8080
```

5. Create first administrator account (initial setup) using APP_SETUP_TOKEN from the .env file:

```text
http://localhost:8080/setup?token=APP_SETUP_TOKEN
```

## Useful Commands

- Start in background:

```bash
docker compose up -d --build
```

- Stop containers:

```bash
docker compose down
```

- Reset database volume (fresh DB):

```bash
docker compose down -v
docker compose up --build
```
