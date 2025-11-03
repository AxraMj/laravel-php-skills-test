# Laravel Product Management System

A Laravel application for managing products with form submission, AJAX functionality, and JSON/XML file storage.

## Features

- Add products with name, quantity, and price
- View all products in a table ordered by submission datetime
- Edit existing products
- Automatic calculation of total value (quantity × price)
- Sum total of all product values
- Data persisted in both JSON and XML formats
- AJAX form submission (no page reload)
- Bootstrap-styled UI

## Requirements

- PHP >= 8.2
- Composer
- Node.js and npm (for asset compilation, though Bootstrap is loaded via CDN)

## Installation

1. Extract the project files
2. Install dependencies:
   ```bash
   composer install
   npm install
   ```

3. Copy the environment file:
   ```bash
   copy .env.example .env
   ```

4. Generate application key:
   ```bash
   php artisan key:generate
   ```

5. Ensure storage directory is writable:
   ```bash
   php artisan storage:link
   ```
   
   On Windows, you may need to set permissions on the `storage/app` directory.

6. Start the development server:
   ```bash
   php artisan serve
   ```

7. Open your browser and navigate to `http://localhost:8000`

## Usage

1. Fill in the form with:
   - Product Name
   - Quantity in Stock
   - Price per Item

2. Click "Submit" to add the product

3. The product will appear in the table below, ordered by submission datetime (newest first)

4. Click "Edit" on any row to modify the product details

5. The "Total Value" is automatically calculated as Quantity × Price

6. The sum of all total values is displayed at the bottom of the table

## Data Storage

Product data is saved in:
- `storage/app/products.json` - JSON format
- `storage/app/products.xml` - XML format

Both files are updated automatically when products are added or edited.

## Technologies Used

- Laravel 12.x
- PHP 8.2+
- Bootstrap 5.3
- jQuery (for AJAX)
- JSON/XML file storage
