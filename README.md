# Technical Task for ThinkHuge

Simple PHP 8.5 MVC application (custom backend, MySQL, Docker Compose).

## Requirements

- Docker
- Docker Compose v2
- Free ports:
  - `8080` (application)
  - `3306` (MySQL)

### Important note
The application was built and successfully ran using:
- Docker version 29.2.0
- Docker Compose version v5.0.2

## Run Locally

1. Clone the repository.
2. Create environment file:

```bash
cp .env.example .env
```

3. Set a secure setup token in `.env`:

```env
APP_SETUP_TOKEN=your-long-random-secret
```

4. Build and start containers:

```bash
docker compose up --build
```

5. Open the app:

```text
http://localhost:8080
```

6. Create first administrator account (initial setup):

```text
http://localhost:8080/setup?token=YOUR_APP_SETUP_TOKEN
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
