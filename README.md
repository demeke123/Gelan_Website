# Gelan Boys Boarding School — Website

Bilingual (English / Amharic) public website with a staff administration area.
Built with plain PHP 8 and MySQL/MariaDB, so it runs on ordinary shared hosting (cPanel) with no extra software.

## What's included

**Public site** (`index.php`)
- Home, About, Academics (with academic calendar), Boarding & Facilities, News & Announcements, Gallery, Contact
- English / Amharic switch, remembered for each visitor
- Dates shown in the Ethiopian calendar on the Amharic site (e.g. ኅዳር 4፣ 2019 ዓ.ም.)
- Contact form that saves messages for staff (spam trap + limit of 5 messages per hour per visitor)
- Responsive layout for phones, tablets and computers; dark mode; printable pages

**Staff administration** (`/admin/`)
- Secure sign-in (hashed passwords, form tokens, sign-in lock after 5 failed attempts)
- Two roles: **Administrator** (everything) and **Content editor** (content only)
- News, notices and events in both languages, with drafts; events appear in the academic calendar
- Gallery uploads: photos are checked, resized, given thumbnails, and stripped of hidden data such as GPS location
- Page text editor: welcome message, profile, history, vision, mission, programs, grade levels, boarding text, contact details, leadership names
- Contact-form inbox
- Staff accounts (administrators only)
- Backup download and restore (administrators only)

## Folder structure

```
index.php            Public site entry point
config.php           Database and site settings  ← edit this
.htaccess            Security and caching rules (Apache)
robots.txt
admin/               Staff administration area
  setup.php          First-time installer (locks itself after use)
includes/            Shared PHP code and the English/Amharic text (lang.php)
pages/               Public page templates
assets/              CSS, JavaScript, logo
uploads/             Gallery photos (must be writable)
database/schema.sql  Optional manual table import
data/                Only used for the SQLite preview mode
```

## Installing on cPanel hosting

1. **Create the database.** In cPanel → *MySQL Databases*, create a database and a user, give the user *All privileges* on the database, and note the names and password.
2. **Edit `config.php`.** Set `DB_NAME`, `DB_USER` and `DB_PASS` (on cPanel these usually start with your account name, e.g. `gelan_school`).
3. **Upload the files.** Zip the project folder, upload it to `public_html` with the File Manager and extract it.
4. **Make uploads writable.** Set permissions on `uploads/` and `uploads/thumbs/` to `755` (or `775` if uploads fail).
5. **Run the installer.** Visit `https://your-domain/admin/setup.php`, create the first administrator account. The installer creates all tables and then locks itself.
6. **Turn on HTTPS.** Activate the free SSL certificate (cPanel → *SSL/TLS Status* → *Run AutoSSL*), then remove the `#` from the three HTTPS lines in `.htaccess`.
7. **Add content.** Sign in at `/admin/`, fill in *Page text* (phone, email, leaders, history), post the first announcement and upload approved photos.

Requirements: PHP 8.0+ with the `pdo_mysql` and `gd` extensions (standard on almost all hosts), MySQL 5.7+ or MariaDB 10.3+.

## Previewing on your own computer (no MySQL needed)

With PHP installed, create a file `config.local.php` next to `config.php`:

```php
<?php
define('DB_DRIVER', 'sqlite');
define('DEBUG', true);
```

Then run `php -S localhost:8000` inside the project folder and open http://localhost:8000.
Delete `config.local.php` before uploading to the live server.

## Changing the wording

- Fixed text on the public pages (menus, headings, values, subjects, facilities, daily routine) is in `includes/lang.php`, in English and Amharic side by side.
- Text the school updates regularly is edited in the admin area under *Page text*; nothing needs to be changed in the code.
- The logo is `assets/img/crest.svg`. Replace it with the school's approved logo (same file name).

## Maintenance checklist

- Download a backup from *Admin → Backup* at least monthly, and copy the `uploads/` folder with the File Manager.
- Also use the hosting control panel's own backup tool for full-server backups.
- Renew the domain and hosting every year before they expire.
- Remove staff accounts (Admin → Staff accounts → Disable) when staff leave.
- Keep PHP up to date in cPanel → *Select PHP Version*.

## Privacy

Don't publish student names, photos or records without school authorization and appropriate consent. The contact form asks visitors not to send private student information.

## Nginx hosting

`.htaccess` files are ignored by Nginx. Add equivalent rules to block `/includes/`, `/pages/`, `/data/`, `/database/`, `config.php`, and any script execution inside `/uploads/`.
