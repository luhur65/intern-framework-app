# Sales Management Application

This is a web application built with the Laravel framework designed for managing sales transactions. It provides functionalities for creating, reading, updating, and deleting sales records, along with their corresponding details. The application features dynamic data tables with server-side processing for efficient data handling, as well as data export capabilities to Excel and PDF formats.

## About The Project

This project serves as a practical example of building a data-centric application using Laravel. It demonstrates the implementation of a clean architecture by separating concerns into Controllers, Services, Models, and Interfaces. The frontend is powered by jQuery and the jqGrid plugin for interactive data grids, showcasing how to integrate traditional JavaScript libraries with a modern backend framework.

### Key Features

*   **CRUD Operations**: Full capabilities to Create, Read, Update, and Delete sales transactions and their line items.
*   **Server-Side Data Processing**: Utilizes the jqGrid plugin with server-side processing to handle large datasets efficiently.
*   **Dynamic Filtering and Sorting**: Allows users to search and sort data dynamically in the grids.
*   **Data Export**: Functionality to export sales data to Excel (.xlsx) and PDF formats.
*   **Race-Condition Safe Operations**: Uses database transactions and row locking to ensure data integrity during concurrent operations.
*   **Structured Codebase**: Follows best practices for a structured and maintainable Laravel application.

## Getting Started

Follow these instructions to get a copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites

*   PHP >= 8.2
*   [Composer](https://getcomposer.org/)
*   [Node.js & npm](https://nodejs.org/)
*   A database server (e.g., MySQL, MariaDB).

### Installation

1.  **Clone the repository**
    ```sh
    git clone https://github.com/your-username/your-repository-name.git
    cd your-repository-name
    ```

2.  **Install PHP dependencies**
    ```sh
    composer install
    ```

3.  **Install JavaScript dependencies**
    ```sh
    npm install
    ```

4.  **Set up the environment file**
    *   Copy the `.env.example` file to `.env`:
        ```sh
        cp .env.example .env
        ```
    *   Generate an application key:
        ```sh
        php artisan key:generate
        ```

5.  **Configure your database**
    *   Open the `.env` file and update the `DB_*` variables with your database credentials:
        ```
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=your_database_name
        DB_USERNAME=your_database_user
        DB_PASSWORD=your_database_password
        ```

6.  **Run database migrations and seeders**
    *   The seeder will populate the `pelanggans` (customers) table with initial data.
    ```sh
    php artisan migrate --seed
    ```

7.  **Compile front-end assets**
    ```sh
    npm run dev
    ```

8.  **Serve the application**
    ```sh
    php artisan serve
    ```
    The application will be available at `http://127.0.0.1:8000`.

## Usage

Navigate to `http://127.0.0.1:8000/penjualan` in your browser to access the main sales management interface.

*   **View Sales**: The main grid displays a list of all sales transactions.
*   **View Details**: Click on a sale in the master grid to view its line items in the detail grid below.
*   **Add a Sale**: Click the "Add" button to open a modal form for creating a new sale. You can add multiple items before saving.
*   **Edit a Sale**: Select a sale and click the "Edit" button to modify its details.
*   **Delete a Sale**: Select a sale and click the "Delete" button to remove it.
*   **Export Data**: Use the "Export" button to generate an Excel or PDF report of the sales data. You can specify a range of records to export.

## Application Structure Overview

The application follows a standard Laravel project structure, with key logic organized as follows:

*   `app/Http/Controllers`: Controllers handle the HTTP requests. `PenjualanController` is the main controller for sales, while `PenjualanDetailController` provides data for the detail grid.
*   `app/Models`: Eloquent models (`Penjualan`, `PenjualanDetail`, `Pelanggan`) define the database schema and relationships. They also contain query scopes and static methods for complex data retrieval.
*   `app/Services`: The `PenjualanService` contains the core business logic for sales management, keeping the controllers lean.
*   `app/Interfaces`: The `PenjualanServiceInterface` defines the contract for the sales service, promoting a decoupled architecture.
*   `app/Http/Requests`: Form requests like `StorePenjualanRequest` handle validation of incoming data.
*   `resources/views/penjualan`: The Blade template for the main sales interface.
*   `public/js`: Contains the custom JavaScript logic for handling the jqGrid, modals, and AJAX requests.
*   `routes/web.php`: Defines the application's routes.