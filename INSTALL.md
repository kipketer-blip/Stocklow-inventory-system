# OSMS – Installation and Running Guide

Online Supermarket Management System · PHP 8 · MySQL/MariaDB · Bootstrap 5 · XAMPP

## 1. What you need

| Item | Notes |
|---|---|
| XAMPP (PHP 8.1 or newer) | https://www.apachefriends.org – install to `C:\xampp` |
| A web browser | Chrome or Edge |
| Optional hardware | USB barcode scanner (works like a keyboard), 80 mm receipt printer |
| Node.js (optional) | Only to regenerate the documentation and screenshots |

## 2. Put the project in the right place

Copy the whole **osms** folder to `C:\xampp\htdocs\osms`.

> Developer tip: on this computer the folder is linked instead of copied:
> `mklink /J C:\xampp\htdocs\osms "C:\Users\<you>\Desktop\FAI\osms"`

## 3. Start Apache and MySQL

Open the **XAMPP Control Panel** and click **Start** for **Apache** and **MySQL**.

If MySQL will not start because port 3306 is used by another MySQL service, either stop that
service (`services.msc` → MySQL80 → Stop) or start XAMPP's MariaDB on port 3307 with
`start_database_3307.bat`. `config\config.php` is set to the standard `'db_port' => 3306`;
change it to `3307` only if you start MariaDB with that batch file.

## 4. Create the database

```
C:\xampp\php\php.exe C:\xampp\htdocs\osms\database\setup.php --demo
```

- `--demo` loads fictitious demonstration data (45 products, 3 cashiers, 12 customers, 30 days of sales).
  Leave it out for a clean installation – the script then prints a random administrator password.
- WARNING: running setup again deletes and recreates the `osms` database.

## 5. Open the system

Go to **http://localhost/osms** (from other tills: `http://<server-IP>/osms`).

Demo accounts (only with `--demo`):

| Role | Username | Password |
|---|---|---|
| Administrator | `admin` | `Admin@1234` |
| Cashier | `CSH001`, `CSH002`, `CSH003` | `Cashier@123` |
| Customer | `0712000001` (also …02, …03) | `Customer@123` |

## 6. Automated tests

```
C:\xampp\php\php.exe C:\xampp\htdocs\osms\tests\run_tests.php
```

The tests **reset the database with demo data** and write `tests\results\test_results.json` and `.txt`.

## 7. Backups

**Settings & Backup → Create backup now** saves a `.sql` file in `storage\backups\`. Restore with
phpMyAdmin (Import) or `C:\xampp\mysql\bin\mysql.exe -u root -P 3307 < backup.sql`.

## 8. Before real use

- [ ] Install without `--demo`, then change the administrator password.
- [ ] Set `'debug' => false` in `config\config.php`.
- [ ] Set a MySQL password (or a dedicated user) and put it in `config.php`.
- [ ] Use HTTPS if the system is reachable from the internet.
- [ ] Enter the real store name, KRA PIN and VAT rate in **Settings**.
- [ ] Take a backup every day and keep a copy off the computer.

## 9. Troubleshooting

| Problem | Solution |
|---|---|
| "OSMS cannot connect to the database" | MySQL not running, or wrong port/password in `config\config.php` |
| "Not Found" | Folder is not at `C:\xampp\htdocs\osms`, or `base_url` in config is wrong |
| "Your form expired for security reasons" | Reload the page and submit again |
| Account locked | Wait 15 minutes, or the administrator clicks **Unlock** in Employees & Users |
