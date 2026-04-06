# Specialization Tracker System (Core PHP + MySQL)

Beginner-friendly project for tracking student specialization paths (Honours, Minor, Honours with Research).

## Tech Stack
- Frontend: HTML, CSS, JavaScript
- Backend: Core PHP (procedural)
- Database: MySQL (phpMyAdmin)

## Folder Structure
```text
specialization-tracker/
|-- config/
|-- includes/
|-- admin/
|-- coordinator/
|-- mentor/
|-- student/
|-- assets/
|   |-- css/
|   |-- js/
|-- uploads/
|-- index.php
|-- login.php
|-- register.php
|-- logout.php
|-- database.sql
```

## Setup on XAMPP/WAMP
1. Copy folder `specialization-tracker` to your web root:
   - XAMPP: `htdocs/`
   - WAMP: `www/`
2. Start Apache and MySQL.
3. Open phpMyAdmin and import `database.sql`.
4. Confirm DB name is `specialization_tracker`.
5. Open `http://localhost/specialization-tracker/`.

## Default Login Users
All seeded users use password: `123456`
- superadmin@example.com (super_admin)
- admin@example.com (admin)
- coordinator@example.com (coordinator)
- mentor@example.com (mentor)
- student@example.com (student)

## Core Features Included
- Role-based login with session management
- Student specialization apply flow with eligibility checks
- Certificate upload to `uploads/`
- Mentor certificate review (approve/reject)
- Coordinator mentor assignment and reports
- Admin CRUD for departments/users + overall data view
- Super admin dynamic specialization rule management UI

## Role Hierarchy (Implemented)
- super_admin (Developer) -> can create admin (Dean)
- admin (Dean) -> can create coordinator (HOD)
- coordinator (HOD) -> can create mentor
- mentor -> can create student

Hierarchy pages:
- `admin/add_admin.php`
- `admin/add_coordinator.php`
- `coordinator/add_mentor.php`
- `mentor/add_student.php`

## Extra Important Features Added
- Active sidebar navigation (shows current page)
- Mobile menu toggle for smaller screens
- Table search on every table page
- CSV export button on every table page
- Theme switch button (normal/mint theme, saved in browser)
- Coordinator dashboard live counters (assignments + pending applications)

## Notes
- Use only PDF/JPG/JPEG/PNG for certificate uploads (max 5MB).
- For production use, add stronger validation/security (CSRF, strict prepared statements, etc.).

## UI Customization for Beginners
- Main design file: assets/css/style.css
- Change colors quickly from the variable block at the top of style.css (values under :root).
- Secondary theme palette: body[data-theme="mint"] section in style.css.
- Sidebar/menu layout is in includes/header.php.
- Mobile menu and table search behavior are in assets/js/script.js.
- Theme switch and CSV export behavior are in assets/js/script.js.
- If you want to change only spacing and card look, edit classes:
   - .card
   - .form-card
   - .table-wrap
   - .btn
