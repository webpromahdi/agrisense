# AgriSense System Technical Audit Report

## 1️⃣ Database Version
- **Version:** 10.4.32-MariaDB
- **Note:** MariaDB 10.4 is compatible with MySQL 5.7+ spatial features.

## 2️⃣ Table Structures

### `farmers`
```sql
CREATE TABLE `farmers` (
  `farmer_id` int(11) NOT NULL AUTO_INCREMENT,
  `farmer_name` varchar(150) NOT NULL,
  `region_id` int(11) NOT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `farmer_code` varchar(6) NOT NULL,
  `farm_size_acres` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`farmer_id`),
  UNIQUE KEY `farmer_code` (`farmer_code`),
  KEY `region_id` (`region_id`),
  CONSTRAINT `farmers_ibfk_1` FOREIGN KEY (`region_id`) REFERENCES `regions` (`region_id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
```

### `market_supply`
```sql
CREATE TABLE `market_supply` (
  `supply_id` int(11) NOT NULL AUTO_INCREMENT,
  `farmer_id` int(11) NOT NULL,
  `market_id` int(11) NOT NULL,
  `crop_id` int(11) NOT NULL,
  `quantity` decimal(12,2) NOT NULL,
  `price_per_unit` decimal(10,2) NOT NULL,
  `supply_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`supply_id`),
  KEY `farmer_id` (`farmer_id`),
  KEY `market_id` (`market_id`),
  KEY `crop_id` (`crop_id`),
  CONSTRAINT `market_supply_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`farmer_id`),
  CONSTRAINT `market_supply_ibfk_2` FOREIGN KEY (`market_id`) REFERENCES `markets` (`market_id`),
  CONSTRAINT `market_supply_ibfk_3` FOREIGN KEY (`crop_id`) REFERENCES `crops` (`crop_id`)
) ENGINE=InnoDB AUTO_INCREMENT=170 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
```

### `markets`
```sql
CREATE TABLE `markets` (
  `market_id` int(11) NOT NULL AUTO_INCREMENT,
  `market_name` varchar(150) NOT NULL,
  `region_id` int(11) NOT NULL,
  `location` varchar(200) DEFAULT NULL,
  `market_type` enum('wholesale','retail','both') DEFAULT 'wholesale',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`market_id`),
  KEY `region_id` (`region_id`),
  CONSTRAINT `markets_ibfk_1` FOREIGN KEY (`region_id`) REFERENCES `regions` (`region_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
```

### `regions`
```sql
CREATE TABLE `regions` (
  `region_id` int(11) NOT NULL AUTO_INCREMENT,
  `region_name` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`region_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
```

### `price_history`
```sql
CREATE TABLE `price_history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `crop_id` int(11) NOT NULL,
  `market_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity_sold` decimal(12,2) DEFAULT NULL,
  `record_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`history_id`),
  KEY `crop_id` (`crop_id`),
  KEY `market_id` (`market_id`),
  CONSTRAINT `price_history_ibfk_1` FOREIGN KEY (`crop_id`) REFERENCES `crops` (`crop_id`),
  CONSTRAINT `price_history_ibfk_2` FOREIGN KEY (`market_id`) REFERENCES `markets` (`market_id`)
) ENGINE=InnoDB AUTO_INCREMENT=690 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
```

## 3️⃣ Sample Data (5 rows each)

### `farmers`
| farmer_id | farmer_name | region_id | contact_number | farmer_code | farm_size_acres | created_at |
|---|---|---|---|---|---|---|
| 1 | Abdul Karim | 1 | 01712345678 | 100001 | 4.50 | 2026-01-24 14:15:28 |
| 2 | Mohammad Rahim | 1 | 01712345679 | 100002 | 3.00 | 2026-01-24 14:15:28 |
| 3 | Fazlur Rahman | 1 | 01812345680 | 100003 | 5.00 | 2026-01-24 14:15:28 |
| 4 | Kamal Hossain | 1 | 01812345681 | 100004 | 6.50 | 2026-01-24 14:15:28 |
| 5 | Jahanara Begum | 1 | 01812345682 | 100005 | 2.50 | 2026-01-24 14:15:28 |

### `market_supply`
| supply_id | farmer_id | market_id | crop_id | quantity | price_per_unit | supply_date | created_at |
|---|---|---|---|---|---|---|---|
| 1 | 1 | 1 | 1 | 450.00 | 68.00 | 2026-01-10 | 2026-01-24 14:15:29 |
| 2 | 2 | 1 | 1 | 380.00 | 68.00 | 2026-01-12 | 2026-01-24 14:15:29 |
| 3 | 3 | 1 | 1 | 420.00 | 68.00 | 2026-01-15 | 2026-01-24 14:15:29 |
| 4 | 4 | 1 | 1 | 550.00 | 68.00 | 2026-01-18 | 2026-01-24 14:15:29 |
| 5 | 5 | 1 | 1 | 320.00 | 68.00 | 2026-01-20 | 2026-01-24 14:15:29 |

### `markets`
| market_id | market_name | region_id | location | market_type | created_at |
|---|---|---|---|---|---|
| 1 | Karwan Bazar | 1 | Dhaka City | wholesale | 2026-01-24 14:15:28 |
| 2 | Jatrabari Bazar | 2 | Jatrabari, Dhaka | wholesale | 2026-01-24 14:15:28 |
| 3 | Mohammadpur Krishi Market | 1 | Mohammadpur | both | 2026-01-24 14:15:28 |
| 4 | Rajshahi Wholesale Market | 4 | Rajshahi City | wholesale | 2026-01-24 14:15:28 |
| 5 | Rangpur City Market | 7 | Rangpur City | both | 2026-01-24 14:15:28 |

## 4️⃣ Location Data Check

- **Do we store latitude and longitude?**
  - ❌ **NO.** Neither `farmers` nor `markets` tables have latitude/longitude columns.
- **How are farmer locations stored?**
  - They are stored relationally via `region_id` which links to the `regions` table (containing `region_name` and `state`).
  - The `markets` table has a simple text-based `location` column (e.g., "Dhaka City").

## 5️⃣ Current Indexes

- **`farmers`**: `PRIMARY KEY`, `UNIQUE (farmer_code)`, `KEY (region_id)`
- **`market_supply`**: `PRIMARY KEY`, `KEY (farmer_id)`, `KEY (market_id)`, `KEY (crop_id)`
- **`markets`**: `PRIMARY KEY`, `KEY (region_id)`
- **`price_history`**: `PRIMARY KEY`, `KEY (crop_id)`, `KEY (market_id)`

## 6️⃣ MySQL Capabilities Check

- **Is MySQL version 8.0 or above?**  
  - ⚠️ **Technically No**, it is MariaDB 10.4.32 (Compatible).
- **Is spatial data type (POINT) supported?**  
  - ✅ **YES.**
- **Is ST_Distance_Sphere() available?**  
  - ✅ **YES.**

## 7️⃣ Environment Details

- **Running on:** XAMPP (Localhost)
- **Stored Procedures:** ✅ Allowed
- **Alter Tables:** ✅ Allowed
