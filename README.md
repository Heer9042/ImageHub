# 📷 ImageHub - Photo Delivery Platform

ImageHub is a mini photo delivery platform built using **PHP**, **MySQL**, and **Tailwind CSS**.  
It supports user authentication, image upload, selective download, and an admin panel for management.

---

## 🚀 Features

### 👤 Users
- Login / Registration
- Upload and view their photos
- Paginated gallery with lightbox
- Dark/Light mode toggle
- Select multiple images and download them as a ZIP archive
- View/Edit profile and change password

### 🛠️ Admin
- Secure login
- Upload entire folders of images to user directories
- View all uploaded images with:
  - Preview
  - Download
  - Delete
- Manage users and photos

---

## 🗂 Folder Structure

```
/imagehub
  ├── /admin
  │     ├── dashboard.php
  │     ├── photos.php
  │     └── sidebar.php
  ├── /auth
  │     ├── login.php
  │     ├── register.php
  │     ├── logout.php
  ├── /config
  │     └── db.php
  ├── /uploads
  │     └── {username}_id{user_id}/  ← Each user's folder
  ├── /user
  │     ├── userdashboard.php
  │     ├── profile.php
  │     ├── download_selected.php
  ├── index.php
  └── README.md
```

---

## ⚙️ Setup Instructions

1. 📦 Clone the repo or download the source
2. 🛠 Configure the database in `config/db.php`:
   ```php
   $pdo = new PDO("mysql:host=localhost;dbname=imagehub", "root", "");
   ```
3. 🧰 Import the `imagehub.sql` database (provided separately)
4. ✅ Ensure **PHP ZipArchive** extension is enabled  
   In XAMPP, uncomment `extension=zip` in your `php.ini` file.
5. ▶ Start Apache and MySQL from XAMPP
6. 🔐 Register users via `auth/register.php`  
   Or login via `auth/login.php` for user and `auth/admin_login.php` for admin


---

## 📌 Requirements

- PHP 7.4+
- MySQL
- Apache/Nginx (XAMPP or similar)
- Zip extension (`ZipArchive`)
- Tailwind CDN (included in `<head>`)

---

## 📃 License

This project is for educational/demo purposes.  
Feel free to adapt or reuse the code for personal/commercial use.

---

## 👨‍💻 Author

**Heer Jitesh Patel**  
Developed with ❤️ using PHP + Tailwind CSS