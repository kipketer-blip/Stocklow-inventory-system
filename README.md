# OSMS – Online Supermarket Management System

Diploma project (Kabarak University) by Kibet Faith Jerop – "Supermarket Management System".

Web-based system for a supermarket in Turkana County: point of sale with automatic totals, VAT and
change; inventory with goods received, adjustments, movement ledger and low-stock alerts; suppliers;
employees with roles; customers with loyalty points; online shop with click-and-collect orders;
customer feedback; reports; append-only audit log; backups.

- Install and run: see [INSTALL.md](INSTALL.md)
- Quick start: see START_HERE_How_to_run_OSMS.txt

## Folder layout

| Folder | Contents |
|---|---|
| `admin/` | Administrator pages (dashboard, products, stock, suppliers, sales, reports, users, audit, settings) |
| `cashier/` | Point of sale, my sales, online orders, customers, price lookup |
| `customer/` | Online shop, cart, orders, receipts & points, feedback |
| `api/` | JSON endpoints used by the till |
| `app/` | Database, Auth, helpers, StockService, SalesService, OrderService, ReportService, layout |
| `database/` | `schema.sql`, `setup.php`, `demo_data.php` |
| `tests/` | `run_tests.php` and results |
