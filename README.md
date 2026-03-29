# BrewBean

![BrewBean](https://img.shields.io/badge/BrewBean-Admin%20Portal-8B5A2B.png?style=for-the-badge&logo=coffeescript&logoColor=white)
![Version](https://img.shields.io/badge/version-1.0.0-97ca00.png?style=for-the-badge)
![License](https://img.shields.io/badge/license-MIT-orange.png?style=for-the-badge)
![Status](https://img.shields.io/badge/status-Active-success.png?style=for-the-badge)

**BrewBean** is a modern, secure, and user-friendly **Cafe Administration Portal** designed to simplify menu, customer, order, and user management. It supports full CRUD operations, role-based access checks, and dashboard analytics for daily operations.

---

## 🏫 Project Details

- **Project Name:** BrewBean Admin Portal
- **Type:** Full-Stack Web Application
- **Focus Area:** Admin Operations & Management System
- **Architecture:** PHP + MariaDB + Server-Rendered UI + JavaScript Enhancements
- **Current Mode:** Admin Portal Only

---

## 👥 Project Member

| Name | Role |
| :--- | :--- |
| **Juanito Ramos** | Developer and DevOps |
| **Lauren Andre David** | QA and Developer |

---

## 🛠 Tech Stack

BrewBean uses a practical and scalable stack to support performance, maintainability, and secure authentication.

### **Frontend**
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-323330?style=for-the-badge&logo=javascript&logoColor=F7DF1E)
![Chart.js](https://img.shields.io/badge/CHART.JS-FF6384.png?style=for-the-badge)

### **Backend**
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MariaDB](https://img.shields.io/badge/MariaDB-003545?style=for-the-badge&logo=mariadb&logoColor=white)

### **Tools & DevOps**
![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![Git](https://img.shields.io/badge/Git-F05032?style=for-the-badge&logo=git&logoColor=white)
![GitHub](https://img.shields.io/badge/GitHub-181717?style=for-the-badge&logo=github&logoColor=white)
![VS Code](https://img.shields.io/badge/VS_Code-0078D4?style=for-the-badge&logo=visual-studio-code&logoColor=white)

## 🚀 Key Features & CRUD Functionalities

BrewBean implements complete **CRUD (Create, Read, Update, Delete)** workflows across core admin modules.

### **Create**
- Add menu items with image upload, category, price, and description
- Add customer records with contact details
- Add orders with dynamic item rows and auto-computed totals
- Add users with role assignment (`Admin`, `User`)

### **Read**
- Dashboard with live analytics (sales, orders, customers, order status)
- Paginated list views for Menu, Customers, Orders, and Users
- Order details modal with itemized breakdown and subtotal/total summary

### **Update**
- Edit menu/customer/order/user records through modal forms
- Update profile information for signed-in admin
- Update order statuses (`Pending`, `Processing`, `Completed`, `Cancelled`)

### **Delete**
- Delete menu/customer/order/user records with confirmation dialogs
- Protected delete endpoints (admin session required)

---

## 🔐 Authentication & Access Control

- Root route (`/`) automatically redirects to **Admin Login**
- Unauthenticated sessions are restricted to the login page
- Authenticated admins land directly on **Dashboard**
- Protected pages use server-side guards (`require_admin.php`)
- Protected API endpoints use admin API guards (`require_admin_api.php`)
- Passwords are stored as bcrypt hashes (`password_hash`, `password_verify`)

---

## ☕ Business Rules Implemented

- Completed orders can be locked from further edits (UI + backend enforcement)
- Customer order count is dynamically derived from actual orders table data
- Customer spending distribution is computed from real order totals

---

## 🏁 Quick Start (Docker)

1. **Clone the repository:**
   ```bash
   git clone https://github.com/Juaaanits/BrewBean.git
   cd BrewBean
   ```

2. **Start services:**
   ```bash
   docker compose up -d
   ```

3. **Access the app:**
   - App: [http://localhost:8080](http://localhost:8080)
   - phpMyAdmin: [http://localhost:8081](http://localhost:8081)

4. **Stop services:**
   ```bash
   docker compose down
   ```

5. **Full reset (optional):**
   ```bash
   docker compose down -v
   docker compose up -d
   ```

---

## 🌱 Seeding Scripts

Run seed scripts from project root:

```bash
docker compose exec -T db mariadb -uroot -prootpass < scripts/seed_customers_25.sql
docker compose exec -T db mariadb -uroot -prootpass < scripts/seed_orders_aligned.sql
docker compose exec -T db mariadb -uroot -prootpass < scripts/seed_users_5.sql
```

---

## 🔐 Default Accounts

> Only `Admin` role accounts can log into the Admin Portal.

| Role | Email / Username | Password | Status |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@mail.com` | `Admin@12345` | Active |
| **Admin** | `ops.admin@mail.com` | `Admin@67890` | Active |
| **User** | `juan.user@mail.com` | `User@12345` | Not allowed in admin login |
| **User** | `maria.user@mail.com` | `User@67890` | Not allowed in admin login |
| **User** | `test.user@mail.com` | `User@24680` | Not allowed in admin login |

---

## 📁 Project Structure

```text
BrewBean/
|-- assets/
|   |-- css/
|   |-- js/
|   |-- uploads/
|   `-- demo-uploads/
|-- docker/
|   `-- init.sql
|-- includes/
|   |-- db.php
|   |-- require_admin.php
|   `-- require_admin_api.php
|-- pages/
|   |-- admin-login.php
|   |-- dashboard.php
|   |-- menu.php
|   |-- customers.php
|   |-- orders.php
|   |-- users.php
|   |-- *_update.php / *_delete.php
|   `-- logout.php
|-- scripts/
|   `-- seed_*.sql
`-- index.php
```

---

## 🚀 Future Enhancements (DevOps + Full-Stack Growth)

1. **Laravel Migration Path**
   - Refactor the current PHP codebase into Laravel for cleaner MVC architecture, routing, validation, authentication, and storage handling.
2. **Database Modernization**
   - Replace runtime schema updates with Laravel migrations and seeders, while keeping MariaDB/MySQL in the short term and evaluating PostgreSQL for long-term production use.
3. **Frontend Framework Adoption**
   - Introduce Blade + Alpine.js for lightweight interactivity, with Livewire as an option for reactive CRUD screens and React/Vue only if the system grows into a full SPA.
4. **Environment Management**
   - Add `.env`-based configuration for `dev`, `staging`, and `production`.
5. **Production Deployment**
   - Move toward cloud-ready deployment with managed database hosting, object storage for uploads, HTTPS, and improved session/auth handling.
6. **CI/CD Pipeline**
   - Automate lint checks, PHP syntax checks, test runs, and deploy workflows using GitHub Actions.
7. **Container Security Hardening**
   - Run non-root containers, perform image vulnerability scanning, and add dependency audits.
8. **Observability Stack**
   - Add monitoring and logging with Prometheus, Grafana, and Loki.
9. **Backup Automation**
   - Schedule database backups with restore validation.
10. **Role-Based Expansion**
   - Add `Manager` and `Staff` roles with scoped permissions.
11. **REST API Layer**
   - Expose endpoints for future mobile, customer-facing, or third-party integrations.
12. **Quality Engineering**
   - Add automated integration and UI tests for critical CRUD flows.

---

## 📜 License

MIT License. You are free to use, adapt, and extend this project for academic and portfolio purposes.

---

Made with dedication by **Juanito Ramos and Lauren Andre David** ☕
