# Task Manager

This is a Task Manager application with Laravel 12 and VUE 3.

## Features
- User Authentication (Sanctum)
- Tasks CRUD with Image Upload
- Full API with JSON Responses

## Requirements
- PHP 8.2+
- Composer
- MySQL 8+

## Backend Setup
1. Clone the repository:
    ```bash
    git clone https://github.com/dev-ranad/task-manager.git
    cd task-manager
    ```

2. Install dependencies:
    ```bash
    composer install
    ```

3. Set up environment variables:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. Run database migrations and seeders:
    ```bash
    php artisan migrate --seed
    ```

5. Set up storage link for images:
    ```bash
    php artisan storage:link
    ```

6. Run the application:
    ```bash
    php artisan serve
    ```

7. Run test (For Task CRUD Test):
    ```bash
    php artisan test
    ```

## Demo Users
- **Admin**: `admin@example.com`, Password: `password`
- **User**: `user@example.com`, Password: `password`

## Requirements
  - Node.js 18+
  - npm 9+

## Frontend Setup

1. Go into frontend directory:
    ```bash
    cd task-manager-frontend
    ```

2. Install dependencies:
    ```bash
    npm install
    ```

3. Install dependencies:
    ```bash
    cp .env.example .env
    ```

4. Install dependencies:
    ```bash
    npm run dev
    ```
