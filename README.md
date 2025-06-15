# Scandiweb Full-Stack Developer Test Task

This repository contains the solution for the Scandiweb Junior Full-Stack Developer test task. It is a simple eCommerce website with a PHP/GraphQL backend and a React/TypeScript frontend.

---

## Live Demo

The application is deployed and available at:
**[https://scandiweb-fs-test.infinityfreeapp.com/](https://scandiweb-fs-test.infinityfreeapp.com/)**

---

## Features

*   Product Listing Page (PLP) with category filtering.
*   Product Details Page (PDP) with image gallery and attribute selection.
*   "Quick Shop" functionality from the PLP.
*   Client-side cart with a persistent state using LocalStorage.
*   Cart overlay for viewing and managing cart items.
*   Order placement via a GraphQL mutation.

---

## Tech Stack

*   **Backend:** PHP 8.1+, MySQL, GraphQL (webonyx/graphql-php)
*   **Frontend:** React, TypeScript, Vite, Apollo Client, TailwindCSS

---

## Local Setup and Installation

To run this project on your local machine, follow these steps:

### Prerequisites

*   PHP 8.1 or higher
*   Composer
*   Node.js and npm
*   A MySQL database server (like XAMPP, MAMP, or Docker)

### 1. Backend Setup

1.  **Clone the repository:**
    ```bash
    git clone [your-repo-url]
    cd fullstack-test-starter-main
    ```

2.  **Create the database:** Create a new MySQL database named `scandiweb_test`.

3.  **Configure environment variables:**
    *   Navigate to the `backend` directory.
    *   Copy the `.env.example` file to `.env` (or create `.env` from scratch).
    *   Update the `.env` file with your local database credentials (DB_USERNAME, DB_PASSWORD, etc.).

4.  **Install PHP dependencies:**
    ```bash
    cd backend
    composer install
    ```

5.  **Create and populate the database tables:**
    *   From the `backend` directory, import the schema: `mysql -u your_user -p scandiweb_test < scripts/create_tables.sql`
    *   Then, populate the data: `php scripts/populate_db.php`

6.  **Start the PHP server:**
    *   From the **root project directory**, run:
    ```bash
    php -S localhost:8000 -t backend/public
    ```
    The backend API will be available at `http://localhost:8000`.

### 2. Frontend Setup

1.  **Install Node.js dependencies:**
    *   In a new terminal, navigate to the `frontend` directory:
    ```bash
    cd frontend
    npm install
    ```

2.  **Start the development server:**
    ```bash
    npm run dev
    ```
    The frontend application will be available at `http://localhost:5173` (or another port if 5173 is busy).
