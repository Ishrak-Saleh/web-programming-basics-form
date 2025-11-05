## PHP Registration Form
A lightweight and responsive PHP-based Registration Form built with a clean, modular structure.  
This project demonstrates core concepts of web development using **PHP**, **HTML**, **CSS**, and **MySQL**, running on **XAMPP**.
---

## Features
- Register, view, edit, and delete users seamlessly  
- MySQL database integration with prepared statements  
- Modular architecture for better code organization  
- AJAX-based form handling (no full-page reloads)  
- Clean, responsive UI built with **Tailwind CSS**  
- Secure password hashing using `password_hash()`  
---

<h2>Screenshots</h2>
<h3 align="center">Registration Tab</h3>
<p align="center">
  <img src="screenshots/Registration Tab.png" width="600" alt="Registration Tab Screenshot">
</p>

<h3 align="center">User List Tab</h3>
<p align="center">
  <img src="screenshots/User List Tab.png" width="600" alt="User List Tab Screenshot">
</p>

<h3 align="center">Edit Tab</h3>
<p align="center">
  <img src="screenshots/Edit Tab.png" width="600" alt="Edit Tab Screenshot">
</p>

## Project Structure
- index.php # Main entry point (redirects to view.php)
- view.php # Handles UI rendering and form logic
- db.php # Database connection file
- script.js # JavaScript for AJAX and tab handling
- style.css # Styling for layout and design
- bg.jpg # Background image for the UI
- README.md # Project documentation
---

## Installation & Setup
1. **Place this project inside your XAMPP/htdocs/ directory.**  
   Example: C:\xampp\htdocs\Web-Programming

2. **Start Apache and MySQL** from the XAMPP Control Panel.

3. **Create a database** in phpMyAdmin:
- Database name: `webprogramming01`
- Table name: `tbl_regi_form`

4. **Create the table** with these columns:
```sql
   CREATE TABLE `tbl_regi_form` (
     `id` INT(11) NOT NULL AUTO_INCREMENT,
     `Full Name` VARCHAR(255) NOT NULL,
     `User name` VARCHAR(255) NOT NULL,
     `Date of Birth` DATE NOT NULL,
     `Email` VARCHAR(255) NOT NULL,
     `Password` VARCHAR(255) NOT NULL,
     PRIMARY KEY (`id`)
   );
```
5. **Open your browser** and navigate to:
   http://localhost/Web-Programming/
---

## Usage
- **index.php** → Redirects to `view.php` (keeps entrypoint clean)  
- **view.php** → Handles all backend logic and page rendering  
- **db.php** → Manages database connection (included in view.php)  
- **script.js** → Manages form submission, AJAX calls, and tab switching  
- **style.css** → Controls layout and UI appearance  
- **bg.jpg** → Optional background image for the UI  
---

## Technologies Used
- **Frontend:** HTML5, Tailwind CSS, JavaScript  
- **Backend:** PHP (running via XAMPP Apache Server)  
- **Database:** MySQL  
- **Local Server:** XAMPP (Apache + MySQL)  
- **Version Control:** Git + GitHub  
---

## Author
**Ishrak Saleh**  
📧 [ischy2003@gmail.com](mailto:ischy2003@gmail.com)  
🔗 [GitHub Profile](https://github.com/ishrak-saleh)
