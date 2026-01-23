# 🌾 AgriSense - Project Report

## Agricultural Market Intelligence & Analytical Database System

**Prepared by:** AgriSense Development Team  
**Date:** January 23, 2026  
**Course:** Database Management Systems Laboratory  

---

## Table of Contents

1. [Objective / Motivation / Project Overview](#1-objective--motivation--project-overview)
2. [Requirements & Technology Stack](#2-requirements--technology-stack)
3. [How It Works (Step-by-Step with Screenshots)](#3-how-it-works-step-by-step)
4. [Limitations & Challenges](#4-limitations--challenges)
5. [Conclusion](#5-conclusion)

---

## 1. Objective / Motivation / Project Overview

### 1.1 What is AgriSense?

**AgriSense** is a comprehensive Agricultural Market Intelligence System designed specifically for the Bangladesh agricultural sector. It serves as a centralized platform that connects farmers, market analysts, and agricultural stakeholders through real-time market data, price analytics, and supply chain intelligence.

### 1.2 Motivation

The agricultural sector in Bangladesh faces several critical challenges:

| Challenge | Impact |
|-----------|--------|
| **Information Asymmetry** | Farmers lack real-time market price information, leading to unfair pricing |
| **Market Fragmentation** | Different markets have varying prices for the same crops |
| **Supply-Demand Mismatch** | Oversupply in certain markets causes price crashes |
| **Limited Data Access** | Historical price trends are not easily accessible |
| **Middleman Exploitation** | Farmers often sell at lower prices due to lack of market knowledge |

### 1.3 Project Objectives

1. **Empower Farmers** - Provide farmers with direct access to market intelligence
2. **Price Transparency** - Track and display real-time crop prices across multiple markets
3. **Anomaly Detection** - Identify unusual price fluctuations and potential market manipulation
4. **Supply Chain Analysis** - Monitor market saturation and oversupply situations
5. **Regional Analytics** - Identify top-performing crops and farmers by region
6. **Data-Driven Decisions** - Enable stakeholders to make informed agricultural decisions

### 1.4 Target Users

| User Type | Description | Access Level |
|-----------|-------------|--------------|
| **Administrators/Analysts** | Government officials, market analysts, researchers | Full dashboard access |
| **Farmers** | Agricultural producers who supply crops to markets | Farmer Portal (code-based) |
| **Market Managers** | Personnel managing agricultural markets | View market-specific data |

### 1.5 Key Features Overview (20+ Features)

| # | Feature | Description | Module |
|---|---------|-------------|--------|
| 1 | **Market Intelligence Dashboard** | Central hub displaying all KPIs, market summaries, and quick analytics | `index.php` |
| 2 | **Real-time KPI Tracking** | Live statistics for crops tracked, active markets, regions, and supply records | `index.php` |
| 3 | **User Authentication System** | Secure login/logout with email and password verification | `auth/` |
| 4 | **Secure Registration** | User signup with strong password validation (uppercase, lowercase, number, special char) | `auth/signup.php` |
| 5 | **Session-based Access Control** | Protected pages accessible only to authenticated users | `AuthController.php` |
| 6 | **Farmer Code Verification** | 6-digit unique code system for farmer identity verification | `farmer/verify_code.php` |
| 7 | **Farmer Supply Submission Portal** | Interface for farmers to submit crop supply data to markets | `farmer/update_crop.php` |
| 8 | **Smart Market Recommendation** | AI-like algorithm suggesting best markets based on price and saturation | `pages/smart_market.php` |
| 9 | **Price Anomaly Detection** | Identifies unusual price spikes or drops using statistical analysis | `pages/price_anomaly.php` |
| 10 | **Inter-Market Price Gap Analysis** | Compares prices of same crop across different markets (Self-JOIN) | `pages/market_price_gap.php` |
| 11 | **Historical Price Trend Analysis** | Visualizes price changes over time with monthly/yearly breakdown | `pages/price_trend.php` |
| 12 | **Seasonal Price Memory** | Year-over-year price comparison for same time periods | `pages/seasonal_price_memory.php` |
| 13 | **Oversupply Alert System** | Detects crops with supply exceeding threshold, warns of price crash risk | `pages/oversupply_alert.php` |
| 14 | **Market Saturation Analysis** | Calculates supply-per-farmer ratio to identify competitive markets | `pages/market_saturation.php` |
| 15 | **Climate Risk Advisory** | Region-wise climate risk warnings with mitigation recommendations | `pages/climate_risk_dashboard.php` |
| 16 | **Top Crop by Region Ranking** | Identifies highest-supply crops per geographic region | `pages/top_crop_region.php` |
| 17 | **Top Farmer by Region Ranking** | Ranks farmers by total supply/revenue within their region | `pages/top_farmer_region.php` |
| 18 | **Market Gap Analysis** | Identifies crops with significant price differences for arbitrage opportunities | `pages/market_gap.php` |
| 19 | **Multi-Market Price Comparison** | Side-by-side price comparison across all 12 markets | Dashboard Tables |
| 20 | **Region-wise Data Filtering** | Filter all analytics by specific geographic regions | All Pages |
| 21 | **Responsive Web Design** | Mobile-friendly interface using TailwindCSS | All Pages |
| 22 | **SQL Injection Prevention** | PDO prepared statements protecting all database queries | `db/connection.php` |
| 23 | **Password Encryption** | Bcrypt hashing (password_hash) for secure credential storage | `AuthController.php` |
| 24 | **Modular MVC Architecture** | Separated controllers, views, and database layers | Project Structure |
| 25 | **Shared Navigation System** | Consistent header/footer across all pages via PHP includes | `dashboard/partials/` |

#### Feature Categories

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         AGRISENSE FEATURE MAP                               │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                    🔐 AUTHENTICATION MODULE (4)                      │   │
│  │  • User Login/Logout        • Secure Registration                   │   │
│  │  • Session Management       • Password Encryption (bcrypt)          │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                    📊 DASHBOARD MODULE (3)                           │   │
│  │  • Market Intelligence Dashboard    • Real-time KPI Cards           │   │
│  │  • Multi-Market Price Tables                                        │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                    📈 PRICE ANALYTICS MODULE (6)                     │   │
│  │  • Price Anomaly Detection          • Historical Price Trends       │   │
│  │  • Seasonal Price Memory            • Inter-Market Price Gap        │   │
│  │  • Market Gap Analysis              • Multi-Market Comparison       │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                    🎯 MARKET INTELLIGENCE MODULE (4)                 │   │
│  │  • Smart Market Recommendation      • Market Saturation Analysis    │   │
│  │  • Oversupply Alert System          • Region-wise Filtering         │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                    🏆 RANKING & REPORTS MODULE (3)                   │   │
│  │  • Top Crop by Region               • Top Farmer by Region          │   │
│  │  • Regional Performance Analytics                                   │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                    👨‍🌾 FARMER PORTAL MODULE (2)                       │   │
│  │  • Farmer Code Verification         • Supply Submission Form        │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                    🌦️ CLIMATE MODULE (1)                             │   │
│  │  • Climate Risk Advisory Dashboard                                  │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                    🛡️ SECURITY & ARCHITECTURE (3)                    │   │
│  │  • SQL Injection Prevention         • Modular MVC Architecture      │   │
│  │  • Shared Navigation Components                                     │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                    📱 UI/UX MODULE (1)                               │   │
│  │  • Responsive Web Design (TailwindCSS)                              │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│                        TOTAL FEATURES: 25+                                  │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Requirements & Technology Stack

### 2.1 Software Requirements

| Category | Requirement | Version/Details |
|----------|-------------|-----------------|
| **Operating System** | Windows / Linux / macOS | Any modern OS |
| **Web Server** | Apache | 2.4+ (via XAMPP) |
| **Database Server** | MySQL / MariaDB | 5.7+ / 10.4+ |
| **PHP Runtime** | PHP | 7.4 or higher |
| **Web Browser** | Chrome / Firefox / Edge | Latest version recommended |

### 2.2 Hardware Requirements

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| **RAM** | 4 GB | 8 GB |
| **Storage** | 500 MB | 2 GB |
| **Processor** | Dual Core | Quad Core |
| **Network** | 1 Mbps | 10 Mbps |

### 2.3 Technology Stack

```
┌─────────────────────────────────────────────────────────────────┐
│                     TECHNOLOGY STACK                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│   ┌─────────────────────────────────────────────────────────┐   │
│   │                    FRONTEND LAYER                        │   │
│   │  • HTML5 - Page Structure                               │   │
│   │  • TailwindCSS (CDN) - Styling & Responsive Design      │   │
│   │  • JavaScript - Interactive Elements                     │   │
│   └─────────────────────────────────────────────────────────┘   │
│                            ↓                                    │
│   ┌─────────────────────────────────────────────────────────┐   │
│   │                    BACKEND LAYER                         │   │
│   │  • PHP 7.4+ - Server-side Logic                         │   │
│   │  • PDO (PHP Data Objects) - Database Connectivity       │   │
│   │  • PHP Sessions - User State Management                 │   │
│   └─────────────────────────────────────────────────────────┘   │
│                            ↓                                    │
│   ┌─────────────────────────────────────────────────────────┐   │
│   │                    DATABASE LAYER                        │   │
│   │  • MySQL/MariaDB - Relational Database                  │   │
│   │  • Prepared Statements - SQL Injection Prevention       │   │
│   └─────────────────────────────────────────────────────────┘   │
│                            ↓                                    │
│   ┌─────────────────────────────────────────────────────────┐   │
│   │                    SERVER LAYER                          │   │
│   │  • Apache HTTP Server - Web Server                      │   │
│   │  • XAMPP - Development Environment Bundle               │   │
│   └─────────────────────────────────────────────────────────┘   │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 2.4 Database Schema

The system uses **8 interconnected tables**:

| Table | Purpose | Key Relationships |
|-------|---------|-------------------|
| `users` | System user accounts | Independent |
| `regions` | Geographic divisions (10 Bangladesh divisions) | Referenced by markets, farmers |
| `markets` | Agricultural markets/haats (12 markets) | FK → regions |
| `crops` | Crop types and categories (15 crops) | Referenced by prices, supply |
| `farmers` | Farmer records with verification codes | FK → regions |
| `market_prices` | Current market prices | FK → crops, markets |
| `price_history` | Historical price data | FK → crops, markets |
| `market_supply` | Farmer supply submissions | FK → farmers, markets, crops |
| `climate_risk` | Climate risk advisory data | FK → regions |

### 2.5 Project File Structure

```
agrisense/
├── index.php                    # Main dashboard
├── package.json                 # Project dependencies
├── PROJECT_DETAILS.md           # Technical documentation
│
├── auth/                        # Authentication module
│   ├── login.php               # User login
│   ├── logout.php              # Session termination
│   └── signup.php              # User registration
│
├── controllers/                 # Business logic
│   ├── AuthController.php      # User authentication
│   └── FarmerUpdateController.php  # Farmer operations
│
├── db/                          # Database layer
│   └── connection.php          # PDO connection & helpers
│
├── dashboard/                   # Shared UI components
│   ├── assets/                 # CSS, JS files
│   └── partials/               # Header, Footer includes
│
├── farmer/                      # Farmer portal
│   ├── verify_code.php         # Code verification
│   └── update_crop.php         # Supply submission
│
├── pages/                       # Analytics modules
│   ├── smart_market.php        # Market recommendations
│   ├── seasonal_price_memory.php
│   ├── oversupply_alert.php
│   ├── climate_risk_dashboard.php
│   ├── market_price_gap.php
│   ├── price_trend.php
│   ├── top_crop_region.php
│   ├── top_farmer_region.php
│   └── ... (more pages)
│
└── sql/                         # SQL scripts
    ├── schema.sql              # Main schema + sample data
    └── ... (query files)
```

---

## 3. How It Works (Step-by-Step)

### Step 1: User Registration & Login

**Registration Process:**

```
┌──────────────────────────────────────────────────────────────────┐
│                        SIGNUP PAGE                               │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│   🌾 AgriSense - Create Account                                  │
│                                                                  │
│   ┌────────────────────────────────────────────────────────┐     │
│   │  Full Name:    [_________________________]             │     │
│   │                                                        │     │
│   │  Email:        [_________________________]             │     │
│   │                                                        │     │
│   │  Password:     [_________________________]             │     │
│   │                                                        │     │
│   │  ┌────────────────────────────────────────────────┐    │     │
│   │  │         [ Create Account ]                     │    │     │
│   │  └────────────────────────────────────────────────┘    │     │
│   └────────────────────────────────────────────────────────┘     │
│                                                                  │
│   Password Requirements:                                         │
│   ✓ Minimum 6 characters                                         │
│   ✓ At least 1 uppercase letter                                  │
│   ✓ At least 1 lowercase letter                                  │
│   ✓ At least 1 number                                            │
│   ✓ At least 1 special character (!@#$%^&*)                      │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

**Screenshot Reference:** Navigate to `/agrisense/auth/signup.php`

---

### Step 2: Main Dashboard

After login, users see the main dashboard:

```
┌──────────────────────────────────────────────────────────────────┐
│  🌾 AgriSense              [Farmer Portal]                       │
├──────────────────────────────────────────────────────────────────┤
│  Dashboard | Smart Market | Price Memory | Over-Supply | ...     │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │        Market Intelligence Dashboard                        │ │
│  │        Welcome, [User Name] 👋         📅 January 23, 2026  │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                                  │
│  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────────┐         │
│  │  🌾 15  │  │  🏪 12  │  │  🗺️ 10  │  │  📦 1,250   │         │
│  │  CROPS  │  │ MARKETS │  │ REGIONS │  │SUPPLY RECORDS│        │
│  │ TRACKED │  │ ACTIVE  │  │         │  │             │         │
│  └─────────┘  └─────────┘  └─────────┘  └─────────────┘         │
│                                                                  │
│  ┌──────────────────────────┐  ┌──────────────────────────┐     │
│  │ 🏆 Top Crop by Region    │  │ 👨‍🌾 Top Farmer by Region  │     │
│  ├──────────────────────────┤  ├──────────────────────────┤     │
│  │ Region    │ Crop │ Supply│  │ Farmer   │ Region │Supply│     │
│  │ Dhaka N   │ Rice │ 5,000 │  │ Karim    │ Dhaka  │ 800  │     │
│  │ Chittagong│Potato│ 3,200 │  │ Abdul    │ Rajshahi│ 650 │     │
│  │ ...       │ ...  │ ...   │  │ ...      │ ...    │ ...  │     │
│  └──────────────────────────┘  └──────────────────────────┘     │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

**Screenshot Reference:** Navigate to `/agrisense/` or `/agrisense/index.php`

---

### Step 3: Smart Market Recommendation

This feature helps farmers find the best market to sell their crops:

```
┌──────────────────────────────────────────────────────────────────┐
│  🎯 Smart Market Recommendation                                  │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Select Crop to Sell: [Rice (Aman) ▼]  [Find Best Markets]      │
│                                                                  │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ How It Works:                                              │  │
│  │ • Higher Average Price = Better for selling                │  │
│  │ • Lower Saturation (supply per farmer) = Less competition  │  │
│  │ • Markets are ranked by a combined score                   │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                  │
│  Results for Rice (Aman):                                        │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ #  │ Market          │ Avg Price │ Saturation │ Status     │  │
│  │────│─────────────────│───────────│────────────│────────────│  │
│  │ 1  │ Rajshahi Market │ ৳45/kg    │ Low        │ ★ HIGHLY   │  │
│  │ 2  │ Kawran Bazar    │ ৳42/kg    │ Medium     │ RECOMMENDED│  │
│  │ 3  │ Khatunganj      │ ৳40/kg    │ High       │ Consider   │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

**Screenshot Reference:** Navigate to `/agrisense/pages/smart_market.php`

---

### Step 4: Seasonal Price Memory

Compare current prices with historical data:

```
┌──────────────────────────────────────────────────────────────────┐
│  📅 Seasonal Price Memory                                        │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Compare prices with the same period last year                   │
│                                                                  │
│  Filter by Market: [All Markets ▼]  [Analyze]                   │
│                                                                  │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ Legend:                                                    │  │
│  │ ⬆ UP (>5% increase)  ⬇ DOWN (>5% decrease)  ➡ STABLE     │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                  │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ Crop     │ Market        │ Current │ Last Year │ Change   │  │
│  │──────────│───────────────│─────────│───────────│──────────│  │
│  │ Onion    │ Kawran Bazar  │ ৳80/kg  │ ৳65/kg    │ ⬆ +23%   │  │
│  │ Potato   │ Khatunganj    │ ৳35/kg  │ ৳40/kg    │ ⬇ -12%   │  │
│  │ Rice     │ Rajshahi      │ ৳45/kg  │ ৳44/kg    │ ➡ +2%    │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

**Screenshot Reference:** Navigate to `/agrisense/pages/seasonal_price_memory.php`

---

### Step 5: Oversupply Alert System

Detect potential price crashes due to oversupply:

```
┌──────────────────────────────────────────────────────────────────┐
│  ⚠️ Crop Over-Supply Detection                                   │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Threshold: [40%] (crops above this % increase flagged)          │
│                                                                  │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ Crop      │ Recent Supply │ Avg Supply │ Growth  │ Risk    │  │
│  │───────────│───────────────│────────────│─────────│─────────│  │
│  │ Tomato    │ 15,000 kg     │ 8,000 kg   │ +87%    │ 🔴 HIGH │  │
│  │ Potato    │ 12,000 kg     │ 7,500 kg   │ +60%    │ 🔴 HIGH │  │
│  │ Onion     │ 8,500 kg      │ 6,000 kg   │ +42%    │ 🟡 ELEV │  │
│  │ Rice      │ 5,000 kg      │ 4,800 kg   │ +4%     │ 🟢 NORM │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                  │
│  ⚠️ Warning: HIGH risk crops may experience price crashes!       │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

**Screenshot Reference:** Navigate to `/agrisense/pages/oversupply_alert.php`

---

### Step 6: Climate Risk Advisory

View region-wise climate risks:

```
┌──────────────────────────────────────────────────────────────────┐
│  🌦️ Climate Risk Advisory Dashboard                             │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ DHAKA NORTH                                                │  │
│  │ ┌──────────────────────────────────────────────────────┐   │  │
│  │ │ 🌊 Flood Risk     │ Moderate │ Plant flood-resistant │   │  │
│  │ │ 🌡️ Heat Stress    │ High     │ Use shade nets        │   │  │
│  │ └──────────────────────────────────────────────────────┘   │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                  │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ CHITTAGONG                                                 │  │
│  │ ┌──────────────────────────────────────────────────────┐   │  │
│  │ │ 🌀 Cyclone Risk   │ Critical │ Secure infrastructure │   │  │
│  │ │ 🧂 Salinity       │ High     │ Use salt-tolerant crops│   │  │
│  │ └──────────────────────────────────────────────────────┘   │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

**Screenshot Reference:** Navigate to `/agrisense/pages/climate_risk_dashboard.php`

---

### Step 7: Farmer Portal (Supply Submission)

**Step 7a: Code Verification**

```
┌──────────────────────────────────────────────────────────────────┐
│  👨‍🌾 Farmer Portal - Verification                                │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│           Enter Your 6-Digit Farmer Code                         │
│                                                                  │
│        ┌────┐ ┌────┐ ┌────┐ ┌────┐ ┌────┐ ┌────┐                │
│        │  1 │ │  2 │ │  3 │ │  4 │ │  5 │ │  6 │                │
│        └────┘ └────┘ └────┘ └────┘ └────┘ └────┘                │
│                                                                  │
│                    [ Verify Code ]                               │
│                                                                  │
│   Note: Your farmer code was provided during registration        │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

**Step 7b: Supply Submission Form**

```
┌──────────────────────────────────────────────────────────────────┐
│  👨‍🌾 Farmer Portal - Submit Supply                               │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Welcome, Abdul Rahman! 🌾                                       │
│                                                                  │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │  Select Crop:     [Rice (Aman) ▼]                         │  │
│  │                                                            │  │
│  │  Select Market:   [Kawran Bazar (Dhaka North) ▼]          │  │
│  │                                                            │  │
│  │  Quantity (kg):   [________500________]                    │  │
│  │                                                            │  │
│  │  Price per kg:    [________45_________]                    │  │
│  │                                                            │  │
│  │  ┌──────────────────────────────────────────────────────┐  │  │
│  │  │              [ Submit Supply Data ]                  │  │  │
│  │  └──────────────────────────────────────────────────────┘  │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                  │
│  ✓ Your submission will be recorded in the market database      │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

**Screenshot Reference:** 
- Navigate to `/agrisense/farmer/verify_code.php` for verification
- Navigate to `/agrisense/farmer/update_crop.php` for submission

---

### Step 8: Price Trend Analysis

View historical price trends for any crop:

```
┌──────────────────────────────────────────────────────────────────┐
│  📈 Price Trend Analysis                                         │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Select Crop: [Rice (Aman) ▼]  [View Trends]                    │
│                                                                  │
│  Price Trend for Rice (Aman):                                    │
│                                                                  │
│  ৳50 │                                    ●                     │
│      │                           ●───────●                       │
│  ৳45 │              ●───────────●                                │
│      │     ●───────●                                             │
│  ৳40 │────●                                                      │
│      │                                                           │
│  ৳35 └────────────────────────────────────────────────────────  │
│       Jan  Feb  Mar  Apr  May  Jun  Jul  Aug  Sep  Oct  Nov     │
│                                                                  │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │ Month      │ Avg Price │ Min    │ Max    │ Qty Sold       │  │
│  │────────────│───────────│────────│────────│────────────────│  │
│  │ January    │ ৳45.00    │ ৳42.00 │ ৳48.00 │ 12,500 kg      │  │
│  │ December   │ ৳44.50    │ ৳41.00 │ ৳47.00 │ 10,200 kg      │  │
│  └────────────────────────────────────────────────────────────┘  │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

**Screenshot Reference:** Navigate to `/agrisense/pages/price_trend.php`

---

### Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                        AGRISENSE DATA FLOW                          │
└─────────────────────────────────────────────────────────────────────┘

   ┌─────────────┐                              ┌─────────────┐
   │   FARMER    │                              │    USER     │
   │  (Mobile/   │                              │ (Analyst/   │
   │   Desktop)  │                              │   Admin)    │
   └──────┬──────┘                              └──────┬──────┘
          │                                            │
          │ 6-digit Code                              │ Email/Password
          ▼                                            ▼
   ┌─────────────┐                              ┌─────────────┐
   │   Farmer    │                              │    Auth     │
   │   Portal    │                              │   System    │
   │verify_code  │                              │  login.php  │
   └──────┬──────┘                              └──────┬──────┘
          │                                            │
          │ Verified                                   │ Authenticated
          ▼                                            ▼
   ┌─────────────┐                              ┌─────────────┐
   │   Supply    │                              │  Dashboard  │
   │   Form      │                              │  & Reports  │
   │update_crop  │                              │  index.php  │
   └──────┬──────┘                              └──────┬──────┘
          │                                            │
          │ INSERT                                     │ SELECT
          ▼                                            ▼
   ┌─────────────────────────────────────────────────────────┐
   │                    MySQL DATABASE                        │
   │  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐    │
   │  │ farmers │  │ markets │  │  crops  │  │  users  │    │
   │  └────┬────┘  └────┬────┘  └────┬────┘  └─────────┘    │
   │       │            │            │                       │
   │       └────────────┼────────────┘                       │
   │                    ▼                                    │
   │            ┌──────────────┐                             │
   │            │market_supply │──────► Analytics Queries    │
   │            │price_history │                             │
   │            │market_prices │                             │
   │            └──────────────┘                             │
   └─────────────────────────────────────────────────────────┘
```

---

## 4. Limitations & Challenges

### 4.1 Technical Limitations

| Limitation | Description | Potential Solution |
|------------|-------------|-------------------|
| **No Real-time Updates** | Dashboard doesn't auto-refresh with new data | Implement WebSockets or AJAX polling |
| **Single Server Architecture** | All components run on one server | Implement load balancing, microservices |
| **No Mobile App** | Web-only interface, less accessible for farmers | Develop React Native / Flutter app |
| **Basic Charts** | Text-based data display, no interactive graphs | Integrate Chart.js or D3.js |
| **No API Endpoints** | Cannot integrate with external systems | Build RESTful API layer |
| **Session-based Auth Only** | No token-based authentication | Implement JWT authentication |

### 4.2 Functional Limitations

| Limitation | Description | Impact |
|------------|-------------|--------|
| **No Multi-language Support** | English only interface | Limits farmer accessibility |
| **No Offline Mode** | Requires internet connection | Rural areas may have connectivity issues |
| **No Image Upload** | Cannot upload crop photos | Quality verification not possible |
| **No SMS Notifications** | No alerts for price changes | Farmers miss critical updates |
| **No Payment Integration** | Cannot process transactions | Limited to information only |

### 4.3 Development Challenges Faced

1. **Database Design Complexity**
   - Challenge: Designing normalized schema for complex agricultural relationships
   - Solution: Created ERD first, used foreign keys appropriately

2. **SQL Query Optimization**
   - Challenge: Correlated subqueries were slow on large datasets
   - Solution: Used derived tables and proper indexing

3. **Session Management**
   - Challenge: Handling both user and farmer authentication
   - Solution: Created separate session variables with distinct prefixes

4. **UI/UX Consistency**
   - Challenge: Maintaining consistent look across 11+ pages
   - Solution: Created shared header/footer partials

5. **Security Implementation**
   - Challenge: Preventing SQL injection and XSS attacks
   - Solution: Used PDO prepared statements and htmlspecialchars()

6. **Data Validation**
   - Challenge: Ensuring data integrity across forms
   - Solution: Implemented both client-side and server-side validation

### 4.4 Scope Limitations

| Not Included | Reason |
|--------------|--------|
| Machine Learning predictions | Out of DBMS course scope |
| Blockchain integration | Complexity beyond project timeline |
| GPS-based market location | Would require map API integration |
| Multi-tenant architecture | Single organization focus |

---

## 5. Conclusion

### 5.1 Project Summary

**AgriSense** successfully demonstrates the application of database management concepts to solve real-world agricultural challenges in Bangladesh. The system provides:

✅ **Comprehensive Market Intelligence** - Real-time price tracking across 12 markets  
✅ **Advanced SQL Analytics** - Using JOINs, subqueries, and aggregate functions  
✅ **Secure Authentication** - For both administrators and farmers  
✅ **User-Friendly Interface** - Professional dashboard with TailwindCSS  
✅ **Data-Driven Insights** - Anomaly detection, trend analysis, and recommendations  

### 5.2 Learning Outcomes

Through this project, we gained practical experience in:

1. **Database Design** - Creating normalized relational schemas
2. **Complex SQL Queries** - Self-joins, correlated subqueries, CTEs
3. **Web Development** - Full-stack PHP/MySQL application
4. **Security Practices** - Password hashing, prepared statements, session management
5. **UI/UX Design** - Responsive design with TailwindCSS
6. **Project Management** - Version control, documentation, modular code structure

### 5.3 Future Enhancements

| Priority | Enhancement | Expected Impact |
|----------|-------------|-----------------|
| High | Mobile Application | 60% more farmer engagement |
| High | Bangla Language Support | 80% improved accessibility |
| Medium | Interactive Charts | Better data visualization |
| Medium | SMS Alerts | Real-time price notifications |
| Medium | RESTful API | Third-party integrations |
| Low | ML Price Predictions | Advanced analytics |
| Low | Blockchain Traceability | Supply chain transparency |

### 5.4 Final Remarks

AgriSense demonstrates how database systems can empower agricultural communities. By providing transparent market information, the system helps reduce information asymmetry between farmers and market intermediaries. The project serves as a foundation for more advanced agricultural information systems that can contribute to food security and farmer prosperity in Bangladesh.

---

## Appendix

### A. SQL Query Examples Used

```sql
-- 1. Price Anomaly Detection (Subquery)
SELECT crop_name, current_price, 
       (SELECT AVG(current_price) FROM market_prices) AS avg_price
FROM market_prices mp JOIN crops c ON mp.crop_id = c.crop_id
WHERE current_price > (SELECT AVG(current_price) * 1.2 FROM market_prices);

-- 2. Top Farmer by Region (Correlated Subquery)
SELECT farmer_name, region_name, total_revenue
FROM farmer_revenue fr
WHERE total_revenue = (SELECT MAX(total_revenue) 
                       FROM farmer_revenue 
                       WHERE region_id = fr.region_id);

-- 3. Inter-Market Price Gap (Self-JOIN)
SELECT c.crop_name, m1.market_name, m2.market_name,
       ABS(mp1.current_price - mp2.current_price) AS price_gap
FROM market_prices mp1
JOIN market_prices mp2 ON mp1.crop_id = mp2.crop_id 
                       AND mp1.market_id < mp2.market_id
JOIN crops c ON mp1.crop_id = c.crop_id
JOIN markets m1 ON mp1.market_id = m1.market_id
JOIN markets m2 ON mp2.market_id = m2.market_id;
```

### B. Sample Farmer Codes for Testing

| Farmer Name | Code | Region |
|-------------|------|--------|
| Abdul Rahman | 123456 | Dhaka North |
| Fatima Begum | 234567 | Chittagong |
| Karim Mia | 345678 | Rajshahi |

### C. References

1. PHP Documentation - https://www.php.net/docs.php
2. MySQL Reference Manual - https://dev.mysql.com/doc/
3. TailwindCSS Documentation - https://tailwindcss.com/docs
4. XAMPP Official Site - https://www.apachefriends.org/

---

**Report Prepared By:** AgriSense Development Team  
**Date:** January 23, 2026  
**Version:** 1.0

---

*End of Report*
