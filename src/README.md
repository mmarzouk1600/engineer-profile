<p align="center">
    <img src="https://img.shields.io/badge/PHP-8.2-blue.svg" alt="PHP Version">
    <img src="https://img.shields.io/badge/Laravel-10.x-red.svg" alt="Laravel Version">
    <img src="https://img.shields.io/badge/JWT-Authentication-green.svg" alt="JWT Auth">
    <img src="https://img.shields.io/badge/Swagger-Documentation-brightgreen.svg" alt="Swagger">
    <img src="https://img.shields.io/badge/Docker-Ready-blue.svg" alt="Docker">
    <img src="https://img.shields.io/badge/License-MIT-yellow.svg" alt="License">
</p>

# 🚀 Customer & Service Management API

A robust, modular backend application built with **Laravel 10**, **JWT Authentication**, and structured using `nwidart/laravel-modules`. This system provides RESTful API capabilities for managing **Customers** and their associated **Services**, fully documented via **Swagger (OpenAPI)** and protected with **JWT (JSON Web Token)** authentication.

---

## 📋 Features & Technical Checklist

- [x] **Customer Module**: Complete CRUD endpoints (Create, View, View All, Update, Delete).
- [x] **Service Module**: Complete CRUD endpoints (Create for Customer, View Customer Services, View All, Update, Delete).
- [x] **Modular Architecture**: Built with `nwidart/laravel-modules`.
- [x] **Security**: JWT (JSON Web Token) Authentication on all API endpoints.
- [x] **Interactive Documentation**: Integrated Swagger/OpenAPI UI via `l5-swagger`.
- [x] **Caching**: Optimized service retrieval using Laravel Cache (Redis/File).
- [x] **Automated Testing**: Unit & Feature test coverage using PHPUnit.
- [x] **Containerization**: Pre-configured `Dockerfile` and `docker-compose.yml`.
- [x] **API Versioning**: Structured API routes with versioning support.
- [x] **Error Handling**: Comprehensive exception handling with meaningful responses.

---

## 📊 Database Schema

### Users Table
```sql
- id (bigint, primary key)
- name (string)
- email (string, unique)
- password (string, hashed)
- remember_token (string, nullable)
- created_at (timestamp)
- updated_at (timestamp)


Customers Table
- id (bigint, primary key)
- name (string)
- email (string, unique)
- phone (string, nullable)
- created_at (timestamp)
- updated_at (timestamp)

Services Table
- id (bigint, primary key)
- customer_id (bigint, foreign key → customers.id)
- title (string)
- description (text, nullable)
- price (decimal, 10,2)
- status (string, default: 'active')
- created_at (timestamp)
- updated_at (timestamp)

🚀 Quick Start & Installation
Prerequisites
PHP: >= 8.2
Composer: >= 2.x
Database: MySQL
Redis (Optional, for caching)
Docker & Docker Compose (Optional, for containerized environment)

Clone the Repository
git clone <repository-url>
cd project-name

Install PHP Dependencies
composer install

Environment Configuration
cp .env.example .env
php artisan key:generate

Configure Database (Edit .env file)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# For Redis Cache (Optional)
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379


Generate Swagger Documentation
php artisan l5-swagger:generate

Method 2: Docker Setup (Recommended)
Build and Start Containers
docker-compose up -d --build

Run Migrations & Seeders
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan db:seed --class=UserSeeder

Generate JWT Secret
docker-compose exec app php artisan jwt:secret

Generate Swagger Documentation
docker-compose exec app php artisan l5-swagger:generate

Access the Application

API Base URL: http://localhost:8080/api
Swagger Documentation: http://localhost:8080/api/documentation


Run Tests
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/CustomerTest.php


🐳 Docker Commands
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# Rebuild containers
docker-compose up -d --build

# View logs
docker-compose logs -f

# Execute commands inside container
docker-compose exec app bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan l5-swagger:generate

# Run tests in container
docker-compose exec app php artisan test

🔗 Important Links
Resource	URL
API Base URL	http://localhost:8080/api
Swagger Documentation	http://localhost:8080/api/documentation
Swagger JSON	http://localhost:8080/docs?api-docs.json
Health Check	http://localhost:8080/api/health