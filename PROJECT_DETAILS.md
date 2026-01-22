# 🌾 AgriSense - Project Details Report

## Agricultural Market Intelligence System

**Generated:** January 22, 2026  
**Platform:** PHP/MySQL Web Application  
**Server:** XAMPP (Apache + MySQL)

---

## 📋 Table of Contents

1. [Project Overview](#1-project-overview)
2. [Technology Stack](#2-technology-stack)
3. [Project Structure](#3-project-structure)
4. [Database Schema](#4-database-schema)
5. [Features & Modules](#5-features--modules)
6. [Authentication System](#6-authentication-system)
7. [API Endpoints & Controllers](#7-api-endpoints--controllers)
8. [SQL Queries & Analytics](#8-sql-queries--analytics)
9. [UI/UX Design](#9-uiux-design)
10. [Security Features](#10-security-features)
11. [Data Flow Diagrams](#11-data-flow-diagrams)

---

## 1. Project Overview

**AgriSense** is a comprehensive Agricultural Market Intelligence System designed for the Bangladesh agricultural sector. The system provides real-time market analytics, price tracking, supply management, and farmer empowerment tools.

### Key Objectives:

- Provide market price intelligence to farmers and stakeholders
- Track crop supply across different markets and regions
- Detect price anomalies and market saturation
- Enable farmers to submit supply data through a secure portal
- Generate analytical insights for better decision-making

### Target Users:

1. **Administrators/Analysts** - View market analytics, trends, and reports
2. **Farmers** - Submit crop supply data via secure 6-digit code verification

---

## 2. Technology Stack

| Layer                  | Technology                              |
| ---------------------- | --------------------------------------- |
| **Backend**            | PHP 7.4+                                |
| **Database**           | MySQL (MariaDB)                         |
| **Frontend**           | HTML5, TailwindCSS (CDN)                |
| **Server**             | Apache (XAMPP)                          |
| **Database Driver**    | PDO (PHP Data Objects)                  |
| **Session Management** | PHP Native Sessions                     |
| **Password Hashing**   | `password_hash()` with PASSWORD_DEFAULT |

---

## 3. Project Structure

```
agrisense/
├── index.php                      # Main dashboard (protected)
├── PROJECT_DETAILS.md             # This documentation
│
├── auth/                          # Authentication module
│   ├── login.php                  # User login page
│   ├── logout.php                 # Session termination
│   └── signup.php                 # User registration
│
├── controllers/                   # Business logic layer
│   ├── AuthController.php         # User authentication controller
│   └── FarmerUpdateController.php # Farmer portal controller
│
├── db/                            # Database layer
│   └── connection.php             # PDO connection & helper functions
│
├── farmer/                        # Farmer portal module
│   ├── verify_code.php            # 6-digit code verification
│   └── update_crop.php            # Crop supply submission form
│
├── pages/                         # Analytics pages
│   ├── market_gap.php             # Inter-market price gap analysis
│   ├── market_price_gap.php       # Market price comparison
│   ├── market_saturation.php      # Supply saturation analysis
│   ├── price_anomaly.php          # Price anomaly detection
│   ├── price_trend.php            # Historical price trends
│   ├── top_crop_region.php        # Top revenue crop by region
│   └── top_farmer_region.php      # Top revenue farmer by region
│
└── sql/                           # SQL scripts
    ├── schema.sql                 # Main database schema + sample data
    ├── categoryA_queries.sql      # Category A SQL queries
    ├── farmer_code_schema.sql     # Farmer code structure
    ├── historical_price_trend.sql # Price trend queries
    ├── inter_market_price_gap.sql # Market gap queries
    ├── top_farmer_by_region.sql   # Top farmer queries
    └── update_units_to_kg.sql     # Unit conversion script
```

---

## 4. Database Schema

### Entity Relationship Diagram

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   regions   │     │   markets   │     │    crops    │
├─────────────┤     ├─────────────┤     ├─────────────┤
│ region_id PK│◄────│ region_id FK│     │ crop_id  PK │
│ region_name │     │ market_id PK│     │ crop_name   │
│ state       │     │ market_name │     │ category    │
│ created_at  │     │ location    │     │ unit        │
└─────────────┘     │ market_type │     │ created_at  │
       │            │ created_at  │     └─────────────┘
       │            └─────────────┘            │
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐     ┌───────────────────┐
│   farmers   │     │   market_prices   │
├─────────────┤     ├───────────────────┤
│ farmer_id PK│     │ price_id      PK  │
│ farmer_name │     │ crop_id       FK  │
│ region_id FK│     │ market_id     FK  │
│ contact     │     │ current_price     │
│ farmer_code │     │ min_price         │
│ farm_size   │     │ max_price         │
│ created_at  │     │ price_date        │
└─────────────┘     │ updated_at        │
       │            └───────────────────┘
       │
       ▼
┌───────────────────┐     ┌───────────────────┐
│  market_supply    │     │   price_history   │
├───────────────────┤     ├───────────────────┤
│ supply_id     PK  │     │ history_id    PK  │
│ farmer_id     FK  │     │ crop_id       FK  │
│ market_id     FK  │     │ market_id     FK  │
│ crop_id       FK  │     │ price             │
│ quantity          │     │ quantity_sold     │
│ price_per_unit    │     │ record_date       │
│ supply_date       │     │ created_at        │
│ created_at        │     └───────────────────┘
└───────────────────┘

┌─────────────┐
│    users    │
├─────────────┤
│ id       PK │
│ name        │
│ email    UQ │
│ password    │
│ created_at  │
└─────────────┘
```

### Table Descriptions

| Table           | Description                               | Records |
| --------------- | ----------------------------------------- | ------- |
| `regions`       | Geographic regions (Bangladesh Divisions) | 10      |
| `markets`       | Agricultural markets/haats                | 12      |
| `crops`         | Crop/commodity information                | 15      |
| `farmers`       | Registered farmer data                    | 20      |
| `market_prices` | Current market prices                     | Dynamic |
| `price_history` | Historical price data                     | Dynamic |
| `market_supply` | Farmer supply records                     | Dynamic |
| `users`         | System users for authentication           | Dynamic |

### Sample Data (Bangladesh Context)

**Regions:** Dhaka North, Dhaka South, Chittagong, Rajshahi, Khulna, Sylhet, Rangpur, Barishal, Mymensingh, Comilla

**Markets:** Kawran Bazar, Khatunganj Market, Rajshahi Krishi Market, etc.

**Crops:** Rice (Aman, Boro, Aus), Wheat, Potato, Onion, Tomato, Jute, Mustard, Lentil, Chickpea, Sugarcane, Brinjal, Chili, Garlic

---

## 5. Features & Modules

### 5.1 Dashboard (index.php)

**Route:** `/agrisense/index.php`  
**Access:** Protected (requires authentication)

**Features:**

- Welcome message with user's name
- Real-time statistics display:
  - Total Crops
  - Total Markets
  - Total Regions
  - Total Farmers
  - Price Records
  - Supply Records
- Quick access to Farmer Portal
- Navigation to all analytics modules

### 5.2 Price Anomaly Detection

**Route:** `/agrisense/pages/price_anomaly.php`  
**Access:** Protected

**Features:**

- Configurable deviation threshold (5-50%)
- Detects crops with prices deviating from market average
- Shows current price vs. average price
- Displays deviation percentage with color coding
- Uses subquery for average price calculation

### 5.3 Inter-Market Price Gap Analysis

**Route:** `/agrisense/pages/market_gap.php` or `/agrisense/pages/market_price_gap.php`  
**Access:** Protected

**Features:**

- Compare prices between two markets
- Self-JOIN SQL query for price comparison
- Shows price gap in BDT (৳) and percentage
- Identifies arbitrage opportunities
- Color-coded comparison indicators

### 5.4 Historical Price Trends

**Route:** `/agrisense/pages/price_trend.php`  
**Access:** Protected

**Features:**

- Monthly price aggregation
- Min, Max, and Average price per month
- Total quantity sold tracking
- Price change calculation over time
- Trend direction indicators (up/down)

### 5.5 Market Saturation Analysis

**Route:** `/agrisense/pages/market_saturation.php`  
**Access:** Protected

**Features:**

- Saturation Index calculation: `Total Supply / Farmer Count`
- Three-level saturation classification:
  - **HIGH (>150):** Risk of price crash
  - **MEDIUM (100-150):** Monitor closely
  - **LOW (<100):** Healthy market
- Filter by specific crop
- Grouped by market and crop

### 5.6 Top Crop by Region

**Route:** `/agrisense/pages/top_crop_region.php`  
**Access:** Protected

**Features:**

- Identifies highest revenue-generating crop per region
- Correlated subquery for maximum revenue calculation
- Shows total revenue, quantity, and farmer count
- Summary statistics with totals

### 5.7 Top Farmer by Region

**Route:** `/agrisense/pages/top_farmer_region.php`  
**Access:** Protected

**Features:**

- Identifies top-performing farmer in each region
- Revenue-based ranking
- Shows total supply quantity and transaction count
- Regional leaderboard display

### 5.8 Farmer Portal

**Routes:**

- Verification: `/agrisense/farmer/verify_code.php`
- Supply Form: `/agrisense/farmer/update_crop.php`

**Access:** 6-digit code verification required

**Features:**

- Secure 6-digit farmer code authentication
- Crop selection dropdown
- Market selection dropdown
- Quantity and price input
- Supply data submission to `market_supply` table
- Session-based farmer tracking

---

## 6. Authentication System

### 6.1 User Authentication (AuthController.php)

**Registration Validation:**

- Email format validation
- Password requirements:
  - Minimum 6 characters
  - At least 1 uppercase letter
  - At least 1 lowercase letter
  - At least 1 number
  - At least 1 special character (!@#$%^&\*(),.?":{}|<>)
- Duplicate email check
- Password hashing with `password_hash()`

**Login Flow:**

```
User Input → Email/Password Validation → Database Check →
Password Verify → Session Creation → Dashboard Redirect
```

**Session Variables:**

```php
$_SESSION['user_id']    // User's database ID
$_SESSION['user_email'] // User's email
$_SESSION['user_name']  // User's display name
$_SESSION['logged_in']  // Boolean authentication flag
```

### 6.2 Farmer Authentication (FarmerUpdateController.php)

**Verification Flow:**

```
6-digit Code Input → Format Validation → Database Lookup →
Session Creation → Supply Form Access
```

**Session Variables:**

```php
$_SESSION['farmer_id']       // Farmer's database ID
$_SESSION['farmer_name']     // Farmer's display name
$_SESSION['farmer_verified'] // Boolean verification flag
```

---

## 7. API Endpoints & Controllers

### 7.1 AuthController Methods

| Method                               | Type     | Description                     |
| ------------------------------------ | -------- | ------------------------------- |
| `validateEmail($email)`              | Instance | Validates email format          |
| `validatePassword($password)`        | Instance | Validates password strength     |
| `validateName($name)`                | Instance | Validates name (min 2 chars)    |
| `emailExists($email)`                | Instance | Checks for duplicate email      |
| `register($name, $email, $password)` | Instance | Creates new user account        |
| `login($email, $password)`           | Instance | Authenticates user              |
| `isLoggedIn()`                       | Static   | Checks authentication status    |
| `getCurrentUser()`                   | Static   | Returns current user data       |
| `logout()`                           | Static   | Destroys session                |
| `requireAuth()`                      | Static   | Middleware for protected routes |

### 7.2 FarmerUpdateController Methods

| Method                        | Type     | Description                      |
| ----------------------------- | -------- | -------------------------------- |
| `validateCodeFormat($code)`   | Instance | Validates 6-digit format         |
| `verifyCode($code)`           | Instance | Verifies farmer code in DB       |
| `getFarmerById($farmerId)`    | Instance | Retrieves farmer by ID           |
| `getCrops()`                  | Instance | Gets all crops for dropdown      |
| `getMarkets()`                | Instance | Gets all markets for dropdown    |
| `submitSupply(...)`           | Instance | Inserts supply record            |
| `startFarmerSession($farmer)` | Static   | Creates farmer session           |
| `isFarmerVerified()`          | Static   | Checks farmer verification       |
| `getVerifiedFarmerId()`       | Static   | Returns farmer ID from session   |
| `getVerifiedFarmerName()`     | Static   | Returns farmer name from session |
| `clearFarmerSession()`        | Static   | Clears farmer session            |
| `requireVerification()`       | Static   | Middleware for farmer portal     |

### 7.3 Database Helper Functions (connection.php)

| Function                      | Description                      |
| ----------------------------- | -------------------------------- |
| `getConnection()`             | Returns PDO connection           |
| `executeQuery($sql, $params)` | Executes prepared statement      |
| `getAllCrops()`               | Returns all crops                |
| `getAllMarkets()`             | Returns all markets with regions |
| `getAllRegions()`             | Returns all regions              |

---

## 8. SQL Queries & Analytics

### 8.1 Price Anomaly Detection Query

```sql
SELECT
    c.crop_name,
    m.market_name,
    mp.current_price,
    ROUND(avg_prices.avg_price, 2) AS avg_price,
    ROUND(
        ((mp.current_price - avg_prices.avg_price) / avg_prices.avg_price) * 100,
        2
    ) AS deviation_percentage
FROM
    market_prices mp
    JOIN crops c ON mp.crop_id = c.crop_id
    JOIN markets m ON mp.market_id = m.market_id
    JOIN (
        SELECT crop_id, AVG(current_price) AS avg_price
        FROM market_prices
        GROUP BY crop_id
    ) avg_prices ON mp.crop_id = avg_prices.crop_id
WHERE
    ABS((mp.current_price - avg_prices.avg_price) / avg_prices.avg_price) > :threshold
ORDER BY
    ABS((mp.current_price - avg_prices.avg_price) / avg_prices.avg_price) DESC
```

**SQL Features:** Subquery, JOIN, Aggregate Functions (AVG), Mathematical Calculations

### 8.2 Inter-Market Price Gap Query (Self-JOIN)

```sql
SELECT
    c.crop_name,
    ma.market_name AS market_a_name,
    mp_a.current_price AS market_a_price,
    mb.market_name AS market_b_name,
    mp_b.current_price AS market_b_price,
    ABS(mp_a.current_price - mp_b.current_price) AS price_gap,
    ROUND(
        (ABS(mp_a.current_price - mp_b.current_price) /
         LEAST(mp_a.current_price, mp_b.current_price)) * 100,
        2
    ) AS gap_percentage,
    CASE
        WHEN mp_a.current_price > mp_b.current_price THEN 'Market A Higher'
        WHEN mp_a.current_price < mp_b.current_price THEN 'Market B Higher'
        ELSE 'Equal'
    END AS price_comparison
FROM
    market_prices mp_a
    JOIN market_prices mp_b
        ON mp_a.crop_id = mp_b.crop_id
        AND mp_a.market_id != mp_b.market_id
    JOIN crops c ON mp_a.crop_id = c.crop_id
    JOIN markets ma ON mp_a.market_id = ma.market_id
    JOIN markets mb ON mp_b.market_id = mb.market_id
WHERE
    mp_a.market_id = :market_a_id
    AND mp_b.market_id = :market_b_id
ORDER BY
    ABS(mp_a.current_price - mp_b.current_price) DESC
```

**SQL Features:** Self-JOIN, CASE Expression, ABS(), LEAST()

### 8.3 Historical Price Trend Query

```sql
SELECT
    DATE_FORMAT(ph.record_date, '%Y-%m') AS month_key,
    DATE_FORMAT(ph.record_date, '%M %Y') AS month_name,
    ROUND(AVG(ph.price), 2) AS avg_price,
    SUM(ph.quantity_sold) AS total_quantity,
    MIN(ph.price) AS min_price,
    MAX(ph.price) AS max_price,
    COUNT(*) AS record_count
FROM
    price_history ph
    JOIN crops c ON ph.crop_id = c.crop_id
WHERE
    ph.crop_id = :crop_id
GROUP BY
    DATE_FORMAT(ph.record_date, '%Y-%m'),
    DATE_FORMAT(ph.record_date, '%M %Y')
ORDER BY
    DATE_FORMAT(ph.record_date, '%Y-%m') ASC
```

**SQL Features:** DATE_FORMAT(), GROUP BY, Aggregate Functions (AVG, SUM, MIN, MAX, COUNT)

### 8.4 Market Saturation Query

```sql
SELECT
    m.market_name,
    r.region_name,
    c.crop_name,
    SUM(ms.quantity) AS total_supply,
    COUNT(DISTINCT ms.farmer_id) AS farmer_count,
    ROUND(
        SUM(ms.quantity) / COUNT(DISTINCT ms.farmer_id),
        2
    ) AS saturation_index,
    ROUND(AVG(ms.price_per_unit), 2) AS avg_price,
    CASE
        WHEN SUM(ms.quantity) / COUNT(DISTINCT ms.farmer_id) > 150 THEN 'HIGH'
        WHEN SUM(ms.quantity) / COUNT(DISTINCT ms.farmer_id) > 100 THEN 'MEDIUM'
        ELSE 'LOW'
    END AS saturation_level
FROM
    market_supply ms
    JOIN markets m ON ms.market_id = m.market_id
    JOIN regions r ON m.region_id = r.region_id
    JOIN crops c ON ms.crop_id = c.crop_id
GROUP BY
    m.market_id, m.market_name, r.region_name, c.crop_id, c.crop_name
ORDER BY
    SUM(ms.quantity) / COUNT(DISTINCT ms.farmer_id) DESC
```

**SQL Features:** Multiple JOINs, GROUP BY, COUNT DISTINCT, CASE Expression

### 8.5 Top Crop by Region Query (Correlated Subquery)

```sql
SELECT
    region_revenue.region_name,
    region_revenue.state,
    region_revenue.crop_name,
    region_revenue.total_revenue,
    region_revenue.total_quantity,
    region_revenue.avg_price,
    region_revenue.farmer_count
FROM (
    SELECT
        r.region_id,
        r.region_name,
        r.state,
        c.crop_id,
        c.crop_name,
        SUM(ms.quantity * ms.price_per_unit) AS total_revenue,
        SUM(ms.quantity) AS total_quantity,
        ROUND(AVG(ms.price_per_unit), 2) AS avg_price,
        COUNT(DISTINCT ms.farmer_id) AS farmer_count
    FROM
        market_supply ms
        JOIN markets m ON ms.market_id = m.market_id
        JOIN regions r ON m.region_id = r.region_id
        JOIN crops c ON ms.crop_id = c.crop_id
    GROUP BY
        r.region_id, r.region_name, r.state, c.crop_id, c.crop_name
) region_revenue
WHERE
    region_revenue.total_revenue = (
        SELECT MAX(inner_rev.total_revenue)
        FROM (
            SELECT
                r2.region_id,
                SUM(ms2.quantity * ms2.price_per_unit) AS total_revenue
            FROM
                market_supply ms2
                JOIN markets m2 ON ms2.market_id = m2.market_id
                JOIN regions r2 ON m2.region_id = r2.region_id
            GROUP BY
                r2.region_id, ms2.crop_id
        ) inner_rev
        WHERE inner_rev.region_id = region_revenue.region_id
    )
ORDER BY
    region_revenue.total_revenue DESC
```

**SQL Features:** Correlated Subquery, Derived Tables, Multiple JOINs

### 8.6 Top Farmer by Region Query

```sql
SELECT
    farmer_revenue.region_name,
    farmer_revenue.state,
    farmer_revenue.farmer_name,
    farmer_revenue.total_revenue,
    farmer_revenue.total_quantity,
    farmer_revenue.supply_count
FROM (
    SELECT
        r.region_id,
        r.region_name,
        r.state,
        f.farmer_id,
        f.farmer_name,
        SUM(ms.quantity * ms.price_per_unit) AS total_revenue,
        SUM(ms.quantity) AS total_quantity,
        COUNT(ms.supply_id) AS supply_count
    FROM
        market_supply ms
        JOIN farmers f ON ms.farmer_id = f.farmer_id
        JOIN regions r ON f.region_id = r.region_id
    GROUP BY
        r.region_id, r.region_name, r.state, f.farmer_id, f.farmer_name
) farmer_revenue
WHERE
    farmer_revenue.total_revenue = (
        SELECT MAX(inner_rev.total_revenue)
        FROM (
            SELECT
                f2.region_id,
                f2.farmer_id,
                SUM(ms2.quantity * ms2.price_per_unit) AS total_revenue
            FROM
                market_supply ms2
                JOIN farmers f2 ON ms2.farmer_id = f2.farmer_id
            GROUP BY
                f2.region_id, f2.farmer_id
        ) inner_rev
        WHERE inner_rev.region_id = farmer_revenue.region_id
    )
ORDER BY
    farmer_revenue.total_revenue DESC
```

**SQL Features:** Correlated Subquery, Derived Tables, Revenue Calculation

---

## 9. UI/UX Design

### Design System

**Color Palette:**

- Primary: Emerald/Green (`#10b981`, `#059669`, `#047857`)
- Background: Green gradients (`#f0fdf4` → `#dcfce7`)
- Accent: White with transparency
- Error: Red (`#dc2626`)
- Warning: Amber (`#d97706`)
- Success: Green (`#059669`)

**UI Components:**

- **Glass Card Effect:** Semi-transparent backgrounds with blur
- **Gradient Buttons:** Primary actions with hover effects
- **Stats Cards:** Compact metric displays
- **Tables:** Zebra striping, hover effects
- **Forms:** Floating labels, validation states
- **Navigation:** Fixed top nav with glass effect

**Responsive Design:**

- Mobile-first approach
- Grid layouts with breakpoints
- Collapsible navigation
- Adaptive form layouts

### Typography

- Font: System UI / Default sans-serif
- Headings: Bold weights
- Body: Regular weight
- Code/Numbers: Monospace for farmer codes

---

## 10. Security Features

### 10.1 SQL Injection Prevention

- **PDO Prepared Statements:** All queries use parameterized queries

```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
```

### 10.2 XSS Prevention

- **Output Encoding:** All user data escaped with `htmlspecialchars()`

```php
<?= htmlspecialchars($user['name']) ?>
```

### 10.3 Password Security

- **Hashing:** `password_hash()` with PASSWORD_DEFAULT (bcrypt)
- **Verification:** `password_verify()` for login

### 10.4 Session Security

- PHP native sessions
- Session regeneration on login
- Secure session cookie parameters
- Session destruction on logout

### 10.5 Input Validation

- Server-side validation for all forms
- Type checking for numeric inputs
- Format validation (email, farmer code)
- Length restrictions

### 10.6 Access Control

- Route protection via `requireAuth()` middleware
- Farmer portal protected by code verification
- Separate session management for users and farmers

---

## 11. Data Flow Diagrams

### 11.1 User Authentication Flow

```
┌──────────┐    ┌──────────┐    ┌────────────┐    ┌──────────┐
│  Login   │───▶│ Validate │───▶│  Database  │───▶│ Session  │
│   Form   │    │  Input   │    │   Check    │    │ Creation │
└──────────┘    └──────────┘    └────────────┘    └──────────┘
                     │                │                 │
                     ▼                ▼                 ▼
                ┌──────────┐    ┌──────────┐    ┌──────────┐
                │  Error   │    │ Invalid  │    │Dashboard │
                │ Message  │    │  User    │    │ Redirect │
                └──────────┘    └──────────┘    └──────────┘
```

### 11.2 Farmer Supply Submission Flow

```
┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐
│  Enter   │───▶│ Validate │───▶│  Verify  │───▶│  Start   │
│   Code   │    │  Format  │    │ in DB    │    │ Session  │
└──────────┘    └──────────┘    └──────────┘    └──────────┘
                                                      │
                                                      ▼
┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐
│  Success │◀───│  Insert  │◀───│ Validate │◀───│  Supply  │
│ Message  │    │  Record  │    │  Inputs  │    │   Form   │
└──────────┘    └──────────┘    └──────────┘    └──────────┘
```

### 11.3 Analytics Query Flow

```
┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐
│  Select  │───▶│  Build   │───▶│ Execute  │───▶│  Render  │
│ Filters  │    │  Query   │    │   SQL    │    │  Table   │
└──────────┘    └──────────┘    └──────────┘    └──────────┘
```

---

## Quick Reference

### URLs

| Page              | URL                                      |
| ----------------- | ---------------------------------------- |
| Dashboard         | `/agrisense/index.php`                   |
| Login             | `/agrisense/auth/login.php`              |
| Signup            | `/agrisense/auth/signup.php`             |
| Logout            | `/agrisense/auth/logout.php`             |
| Farmer Portal     | `/agrisense/farmer/verify_code.php`      |
| Supply Form       | `/agrisense/farmer/update_crop.php`      |
| Price Anomaly     | `/agrisense/pages/price_anomaly.php`     |
| Market Gap        | `/agrisense/pages/market_gap.php`        |
| Price Trend       | `/agrisense/pages/price_trend.php`       |
| Market Saturation | `/agrisense/pages/market_saturation.php` |
| Top Crop          | `/agrisense/pages/top_crop_region.php`   |
| Top Farmer        | `/agrisense/pages/top_farmer_region.php` |

### Sample Farmer Codes (for testing)

```
100001 - Abdul Karim (Dhaka North)
100002 - Mohammad Rahim (Dhaka North)
100003 - Fazlur Rahman (Dhaka South)
100004 - Jamal Uddin (Dhaka South)
100005 - Kamal Hossain (Chittagong)
```

---

## Summary Statistics

| Metric               | Count |
| -------------------- | ----- |
| Total PHP Files      | 14    |
| Total SQL Files      | 7     |
| Database Tables      | 8     |
| Analytics Modules    | 6     |
| Controllers          | 2     |
| Auth Routes          | 3     |
| Farmer Portal Routes | 2     |

---

**Document Version:** 1.0  
**Last Updated:** January 22, 2026  
**Generated for:** AgriSense Project
