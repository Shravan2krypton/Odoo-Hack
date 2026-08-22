<div align="center">

# 🌍 GlobalTraveler

### Plan. Track. Explore. — A full-stack trip planning platform built for a hackathon sprint.

[![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![jQuery](https://img.shields.io/badge/jQuery-0769AD?style=for-the-badge&logo=jquery&logoColor=white)](https://jquery.com/)
[![XAMPP](https://img.shields.io/badge/XAMPP-FB7A24?style=for-the-badge&logo=xampp&logoColor=white)](https://www.apachefriends.org/)

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](LICENSE)
[![Status](https://img.shields.io/badge/status-hackathon--build-orange?style=flat-square)]()
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat-square)]()

</div>

---

GlobalTraveler is a full-stack **PHP + MySQL** web application for planning, managing, and tracking trips end-to-end — from signup, to building a day-by-day itinerary, to keeping every trip on budget.

<div align="center">

*Register → Plan a trip → Build your itinerary → Track your budget → Get inspired by the community*

</div>

---

## 📑 Table of Contents

- [✨ Features](#-features)
- [🛠️ Tech Stack](#️-tech-stack)
- [📂 Project Structure](#-project-structure)
- [⚙️ Setup](#️-setup-xampp--phpmyadmin)
- [👤 Demo Flow](#-demo-flow)
- [🗺️ Roadmap](#️-roadmap)
- [🤝 Contributing](#-contributing)

---

## ✨ Features

<table>
<tr>
<td width="50%" valign="top">

### 🔐 User Authentication
- Register with validation (email, phone, password rules)
- Secure login with hashed passwords
- Session-based dashboard access
- Forgot password flow

### 🏠 Dashboard
- Profile card with user details (city, country)
- Trips categorized by status: **Planned · Ongoing · Completed**
- Quick access to itineraries
- One-click **Create Trip** button

### ✈️ Trip Management
- Create trips with destination, dates, notes, cover photo
- View trips by status
- Full itinerary builder with stops and activities

</td>
<td width="50%" valign="top">

### 💰 Budget Tracking
- Planned vs. actual expenses per category
  *(transport, food, accommodation, etc.)*
- Summarized budget view per trip

### 🌎 Regional Recommendations
- Homepage showcases top regions with images & descriptions
- Built-in trip inspiration on first login

### 🎁 Extras
- Reviews & ratings for trips
- Community feed of shared experiences
- Responsive, modern UI

</td>
</tr>
</table>

---

## 🛠️ Tech Stack

| Layer          | Technology                                  |
|----------------|----------------------------------------------|
| **Frontend**   | HTML5, CSS3, JavaScript, jQuery (dynamic dropdowns) |
| **Backend**    | PHP (procedural + MySQLi prepared statements) |
| **Database**   | MySQL                                        |
| **Server**     | XAMPP / Apache                               |
| **Admin Tool** | phpMyAdmin                                   |

---

## 📂 Project Structure

<details>
<summary><b>Click to expand full file tree</b></summary>

```
Odoo-Hack/
│
├── api/                        # Lightweight JSON/HTML endpoints used by JS (fetch/AJAX)
│   ├── config.php              # DB connection shared by all API endpoints
│   ├── get_cities_html.php     # Returns pre-rendered <option>/list HTML for city dropdowns
│   └── get_cities.php          # Returns city data as JSON (search/autocomplete)
│
├── assets/
│   ├── css/                    # Stylesheets (layout, components, theme)
│   └── js/                     # Client-side scripts (dropdowns, validation, dynamic UI)
│
├── includes/                   # Shared PHP partials (header, footer, navbar, auth guard, db connection)
│
├── activity_search.php         # Browse & filter activities to add to a trip
├── admin_dashboard.php         # Admin analytics view (users, popular cities/activities, trends)
├── admin.php                   # Admin panel entry / user management
├── budget.php                  # Trip budget & cost breakdown (planned vs. actual)
├── city_search.php             # Browse & filter cities to add to a trip
├── community.php               # Shared trip experiences / community feed
├── create_trip.php             # New trip form (destination, dates, notes, cover photo)
├── dashboard.php                # Logged-in home: profile card, trips by status, quick actions
├── index.php                   # Public landing page / entry point
├── itinerary_builder.php       # Add/edit stops, dates, and activities for a trip
├── itinerary_view.php          # Day-wise view of a trip's full itinerary
├── login.php                   # Login form + session start
├── logout.php                  # Destroys session, redirects to login
├── register.php                # Signup form with validation
├── my_trips.php                # List of the user's trips grouped by status
├── profile.php                 # Edit user profile / account settings
├── forgot_password.php         # Forgot-password request flow
└── schema.sql                  # MySQL schema — import via phpMyAdmin to create the database
```

> `assets/css`, `assets/js`, and `includes/` are shown collapsed above — this tree reflects their expected contents based on the app's features. Adjust file names to match your actual repo.

</details>

---

## ⚙️ Setup (XAMPP + phpMyAdmin)

```bash
# 1. Clone into your XAMPP htdocs folder
git clone https://github.com/<your-username>/Odoo-Hack.git C:/xampp/htdocs/Odoo-Hack
```

1. Start **Apache** and **MySQL** from the XAMPP control panel.
2. Open `http://localhost/phpmyadmin`, create a database (e.g. `globaltraveler`), and import `schema.sql`.
3. Set your DB credentials in `includes/db.php` (or `api/config.php`):
```php
   $host = "localhost";
   $user = "root";
   $pass = "";
   $dbname = "globaltraveler";
```
4. Visit **`http://localhost/Odoo-Hack/index.php`** and you're in. 🎉

---

## 👤 Demo Flow

| Step | Page | Action |
|------|------|--------|
| 1 | `register.php` | Create a new account |
| 2 | `login.php`
