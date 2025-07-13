# full-stuck-Github

# PHP Product Management System
A lightweight, user-authenticated product management dashboard built with PHP, MySQL, and Bootstrap. It allows you to manage products, categories, and users with secure CRUD operations.

## Project Overview

This system provides:

- User login and session management
- Add, edit, delete products
- Add, edit, delete categories
- Assign products to categories
- Filter and search products by name, SKU, or category
- Responsive UI using Bootstrap 5

---

##  Setup Instructions

### Requirements

- PHP 8+
- MySQL 5.7+ or MariaDB
- Apache (XAMPP/LAMP recommended)
- Composer (optional, for dotenv support)

---

### ⚙️ Backend Setup (XAMPP)

1. **Clone the repo** or copy project files into your `htdocs` folder:

   ```
   C:\xampp\htdocs\php_project
   ```

2. **Create the MySQL database**

   Open `phpMyAdmin` and run:

   ```sql
   CREATE DATABASE ptour;

   USE ptour;

   CREATE TABLE category (
     id INT PRIMARY KEY,
     name VARCHAR(100) NOT NULL
   );

   CREATE TABLE products (
     id INT PRIMARY KEY,
     name VARCHAR(100) NOT NULL,
     sku VARCHAR(100) NOT NULL,
     pax INT NOT NULL,
     price DECIMAL(10,2) NOT NULL,
     category INT,
     FOREIGN KEY (category) REFERENCES category(id)
   );

   CREATE TABLE users (
     id INT AUTO_INCREMENT PRIMARY KEY,
     username VARCHAR(50) NOT NULL UNIQUE,
     password VARCHAR(255) NOT NULL
   );
   ```

3. **Create `.env` from example**

   Copy the example file and set your credentials:

   ```
   cp .env.example .env
   ```

---

## Run the Project

Start Apache and MySQL from XAMPP, then visit:

```
http://localhost/php_project/login.php
```

Log in with your user (add manually to DB if needed), and access the dashboard.

---

## Environment Variables

### `.env.example`

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=ptour
DB_USER=root
DB_PASS=
```

> Copy this to `.env` and configure before running the app.

---

## Folder Structure
```
php_project/
├── auth/
│   ├── add_category.php
│   ├── add_product.php
│   ├── dashboard.php
│   ├── update_product.php
│   ├── delete_category.php
│   └── ...
├── db.php
├── login.php
├── logout.php
├── index.html
└── .env
```
