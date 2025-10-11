# ☕ IKOMERS KOPI

**IKOMERS KOPI** is a fun and simple coffee shop website project built with HTML, CSS, JavaScript, PHP, and MySQL. It includes basic features like user authentication, product listings, and a cart system—ideal for beginner-level e-commerce development and educational purposes.

---

## 📸 Screenshots

> Add images of each page of the application in the sections below. Upload your screenshots to a `screenshots/` directory in your repo and replace the placeholder links.

### 🏠 Home Page
![Home Page](screenshots/home.png)

### 📋 Product Page / Menu
![Product Page](screenshots/menu.png)

### 🛒 Cart Page
![Cart Page](screenshots/cart.png)

### 🔐 Login Page
![Login Page](screenshots/login.png)

### 📝 Sign Up Page
![Sign Up Page](screenshots/signup.png)

---

## 🧑‍💻 Authors

- [Lauren Andre David](https://github.com/Laurennn123)
- [Juanito M. Ramos II](https://github.com/Juaaanits)

---

## 🛠️ Tech Stack

| Layer      | Technology         |
|------------|--------------------|
| Frontend   | HTML, CSS, JavaScript |
| Backend    | PHP                |
| Database   | MySQL              |

---

## 📁 Project Structure

```
ikomers-kopi/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── includes/
│   ├── db.php
│   ├── header.php
│   └── footer.php
├── pages/
│   ├── home.php
│   ├── menu.php
│   ├── cart.php
│   ├── login.php
│   └── signup.php
├── screenshots/
│   └── (screenshots)
├── index.php
├── .env (optional for DB config)
└── README.md
```

---

## ⚙️ Installation

> To run this project locally, make sure you have **XAMPP**, **MAMP**, or another LAMP stack installed.

1. Clone the repository:
   ```bash
   git clone https://github.com/Juaaanits/ikomers-kopi.git
   ```

2. Move the project to your web root:
   - For XAMPP: move it to `htdocs/`
   - For MAMP: move it to `htdocs/`

3. Start Apache and MySQL via your local server manager.

4. Import the database:
   - Open **phpMyAdmin**
   - Create a database: `ikomers_kopi`
   - Import the `.sql` file if available.

5. Configure the DB connection:
   - Edit `/includes/db.php` with your local DB credentials.

---

## 🚀 Usage

- Navigate to `http://localhost/ikomers-kopi` in your browser.
- Use the navigation to browse the homepage, login, signup, and access the product/cart pages.

---

## 🧩 Features

- Simple homepage and menu layout
- User login and registration
- Add to cart functionality
- Responsive design (if implemented)
- MySQL-based backend for product and user data

---

## 🔧 Configuration

Edit the file at:

```
includes/db.php
```

Example:
```php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'ikomers_kopi';
```

---

## ❓ Troubleshooting

- Make sure your local server (XAMPP/MAMP) is running.
- Check that `db.php` contains correct database credentials.
- Ensure the database has been properly imported via phpMyAdmin.
- Use browser developer tools (F12) to debug front-end issues.

---

## 📜 License

This project is licensed under the **MIT License**.  
Feel free to use, modify, and share with credit.

---
