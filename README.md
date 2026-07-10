# 🔐 PHP Login System with MySQL

A complete PHP authentication system using **MySQL database** with PDO, featuring registration, login, logout, and a protected dashboard. Styled with **TwinCSS** — no frameworks needed.

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat&logo=mysql&logoColor=white)
![TwinCSS](https://img.shields.io/badge/TwinCSS-1.1.0-38bdf8?style=flat)
![License](https://img.shields.io/badge/license-MIT-green)

---

## ✨ Features

- 🔑 **User Registration** – username, email, and password
- 🔐 **Secure Login** – with username OR email support
- 🛡️ **Password Hashing** – using `password_hash()` / `password_verify()`
- 🗄️ **MySQL Database** – PDO with prepared statements (SQL injection protection)
- 📧 **Email Validation** – built-in email format verification
- 🚪 **Logout** – secure session destruction
- 🛡️ **Protected Dashboard** – shows user details
- 🎨 **TwinCSS Styling** – clean, responsive, utility-first CSS
- 📱 **Mobile-friendly** – responsive design
- 🔄 **Auto Table Creation** – users table created automatically

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7 or higher / MariaDB 10.2+
- Web server (Apache / Nginx / PHP built-in server)

### Database Setup

1. **Create database manually** (or let the system create tables automatically):
   ```sql
   CREATE DATABASE IF NOT EXISTS login_system;
