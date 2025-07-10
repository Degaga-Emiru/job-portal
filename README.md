# 💼 Job Portal

This repository contains a **Job Portal Web Application** developed using **PHP**, **JavaScript**, and **Bootstrap CSS**. It allows **job seekers** to explore and apply for jobs, and **employers** to post and manage job listings in a user-friendly interface.

---

## 🚀 Features

- User registration and login (for job seekers and employers)
- Post, view, and apply for jobs
- Filter jobs by category, title, and location
- Responsive user interface using **Bootstrap**
- Dynamic client-side functionality using **JavaScript**
- Backend logic and database management using **PHP & MySQL**

---

## 🛠️ Tech Stack

- **Frontend**: HTML, CSS (Bootstrap), JavaScript  
- **Backend**: PHP  
- **Database**: MySQL  
- **Web Server**: Apache (via XAMPP)

---

## 💻 Running the Project Locally (Using XAMPP)

Follow these steps to run the project on your local machine:

### 1. Install XAMPP
- Download and install XAMPP from [https://www.apachefriends.org/index.html](https://www.apachefriends.org/index.html)
- Open XAMPP Control Panel
- Start **Apache** and **MySQL**

### 2. Clone or Download the Project
- Place the project folder `job-portal` into the `htdocs` directory:

### 3. Import the Database
- Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
- Create a new database named:
- Import the provided SQL file:

### 4. Configure Database Connection
- Open `config.php` or your connection file
- Set the following values:
```php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'job_portal';
http://localhost/job-portal


📁 Project Structure

/job-portal
├── index.php
├── login.php
├── register.php
├── dashboard/
├── jobs/
├── config.php
├── assets/
│   ├── css/
│   └── js/
└── sql/
    └── job_portal.sql
