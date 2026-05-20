# Titip.in Backend API

A robust, headless API architecture built for the Titip.in platform, serving as a marketplace for personal shoppers (Jastip) and preloved items. The system leverages modern AI-driven search, scalable asynchronous processing, and containerized infrastructure.

## Live Environment
- **Official Website / Frontend:** [https://titipin.me](https://titipin.me)
- **API Base URL:** `https://titipin-api.bccdev.id/api/v1`
- **API Documentation (Swagger):** `https://titipin-api.bccdev.id/docs/api`

## System Architecture & Tech Stack

- **Framework:** Laravel 13 (PHP 8.3)
- **Database:** PostgreSQL 16 with `pgvector` extension
- **Cache & Queue:** Redis 7
- **Object Storage:** MinIO (Amazon S3 Compatible API)
- **Authentication:** Laravel Sanctum (Token-based) & Laravel Socialite (Google OAuth)
- **Messaging Service:** Evolution API (WhatsApp Gateway)
- **AI Integration:** Laravel AI (Semantic & Vector Search capabilities)
- **API Documentation:** Dedoc Scramble (OpenAPI)
- **Deployment:** Docker, Docker Compose, and GitHub Actions (CI/CD)

## Core Features

### 1. Robust Authentication & Security
- Secure registration and login using JWT (JSON Web Tokens).
- OAuth 2.0 integration for seamless Google Login.
- Multi-factor verification (Email verification and WhatsApp OTP).
- Strict Redis-based rate limiting to prevent brute-force attacks and API abuse.
- Edge-case handled account takeovers for unverified credentials.

### 2. Intelligent Search (Vector Search)
- Implements `pgvector` for advanced semantic search functionality.
- Integrates with AI models to convert search queries into embeddings, allowing users to find items based on contextual meaning rather than exact keyword matches.

### 3. Marketplace Core Modules
- **Jastip (Personal Shopper):** Endpoints to manage and view shopper listings and specific item requests.
- **Preloved:** Endpoints to manage second-hand item listings and requests.
- Distinctly separated profiles and user activity tracking.

### 4. Scalable File Management
- All user-uploaded media (avatars, item images) are directly streamed and stored securely in MinIO via Flysystem S3 integration.
- Public endpoint available to securely serve and distribute the latest compiled Android application (APK) using read-only Docker volume binds.

### 5. Asynchronous Processing
- Dedicated Queue Workers handle resource-heavy operations such as sending verification emails, generating AI embeddings, and external API communications.
- Cron-based task scheduling managed entirely within isolated Docker containers.

## High-Level API Endpoints Overview

All routes are prefixed with `/api/v1`.

### Public Endpoints
- `GET /download/android` - Download the latest Android APK.
- `GET /auth/google` & `/auth/google/callback` - Google OAuth 2.0 integration.
- `POST /register`, `/login`, `/forgot-password`, `/reset-password` - Authentication actions.
- `POST /email/verify` - Email verification verification callback.
- `GET /categories`, `/categories/{id}` - Retrieve marketplace categories.
- `GET /preloved/listings`, `/preloved/requests` - Browse public preloved items.
- `GET /jastip/listings`, `/jastip/requests` - Browse public jastip services.
- `GET /search` - AI-driven semantic and vector search functionality.

### Protected Endpoints (Requires Bearer Token)
- `POST /logout` - Invalidate current access token.
- `GET /me` - Retrieve authenticated user profile.
- `PUT/PATCH /me` - Update user profile.
- `PUT /me/password` - Change account password.
- `POST /email/resend` - Resend verification email (Strict Rate Limit).
- `POST /me/whatsapp/request-otp` - Request WhatsApp verification OTP (Strict Rate Limit).
- `POST /me/whatsapp/verify-otp` - Verify WhatsApp OTP.

### Restricted Endpoints (Requires Completed & Verified Profile)
- `POST /upload` - Upload images to S3/MinIO.
- `POST / PUT / DELETE /preloved/listings` - Manage personal preloved listings.
- `POST / PUT / DELETE /preloved/requests` - Manage personal preloved requests.
- `POST / PUT / DELETE /jastip/listings` - Manage personal jastip listings.
- `POST / PUT / DELETE /jastip/requests` - Manage personal jastip requests.
- `GET /me/preloved/*` & `/me/jastip/*` - Retrieve user's specific activity and items.

## Infrastructure Setup

The project utilizes a multi-container Docker environment. The `docker-compose.prod.yaml` defines the following services:

- `web`: Nginx web server handling HTTP requests and reverse proxying.
- `php`: The core Laravel application container.
- `worker`: Dedicated container processing Redis queues.
- `scheduler`: Dedicated container handling scheduled tasks.
- `db`: PostgreSQL instance optimized for vector operations.
- `redis`: In-memory data store for caching and rate limiting.
- `minio` & `minio-setup`: S3-compatible local storage and automated bucket provisioning.
- `evolution-api` & `evolution-manager`: Isolated microservices for WhatsApp integrations.