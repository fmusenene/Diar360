Diar 360 Website
================

This project is the custom website for **Diar 360**, a construction and facility management company operating in Saudi Arabia and the Gulf region.

Key features
-----------
- Multi‑language support (English / Arabic) with full RTL layout for Arabic
- Dynamic services, projects, and project details driven by PHP configuration
- Arabic numeral conversion for all numbers when Arabic is selected
- Custom quote form with email notification and auto‑reply (EN/AR)
- Google Maps integration for the contact page

Technology stack
----------------
- PHP 8+
- Bootstrap 5.3
- Vanilla JavaScript
- HTML5 / CSS3

Project structure (high level)
------------------------------
- `index.php` – Homepage
- `about.php` – About Diar 360
- `services.php` / `service-details.php` – Services and detailed service view
- `projects.php` / `project-details.php` – Projects listing and detailed project pages
- `quote.php` – Get a quote form
- `contact.php` – Contact information and map
- `include/` – Shared header and footer
- `config/` – Site configuration and projects data
- `functions/` – Helper and language functions
- `assets/` – CSS, JS, images, and vendor libraries

Notes
-----
- The initial layout was based on the **Constructo** Bootstrap template from BootstrapMade, and has been heavily customized for Diar 360’s branding, content, and bilingual requirements.
- All template credits and licensing obligations are handled via the purchased Pro license from BootstrapMade.

To run locally
--------------
1. Place the project under your local web root (e.g. `C:\xampp\htdocs\diar360`).
2. Start Apache (and MySQL if needed) in XAMPP.
3. Visit `http://localhost/diar360/` in your browser.

