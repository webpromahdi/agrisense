# AgriSense

Agricultural Market Intelligence & Analytical Database System

## Project Overview

AgriSense is a web-based agricultural market intelligence platform designed to help farmers, traders, and agricultural analysts make data-driven decisions. The system aggregates market prices, supply data, and climate risk information across multiple regions and markets.

### Target Users

- Farmers seeking optimal markets for their produce
- Agricultural traders analyzing price trends
- Market analysts monitoring supply and demand patterns
- Agricultural extension officers advising farmers

### Key Use Cases

- Identify the most profitable markets for specific crops
- Track historical price trends and seasonal patterns
- Monitor market saturation and supply levels
- Access climate risk advisories by region
- Submit and track crop supply records

## Features

- **User Authentication**: Secure registration and login with session management
- **Dashboard Overview**: Real-time statistics on crops, markets, regions, and supply records
- **Smart Market Finder**: Recommends optimal markets based on price and saturation analysis
- **Price Trend Analysis**: Historical price visualization with monthly aggregations
- **Market Saturation Monitor**: Tracks supply concentration across markets
- **Oversupply Alerts**: Identifies markets with excess supply conditions
- **Price Anomaly Detection**: Flags unusual price movements
- **Market Price Gap Analysis**: Compares prices across different markets
- **Seasonal Price Memory**: Analyzes recurring seasonal price patterns
- **Climate Risk Dashboard**: Regional climate advisories and risk assessments
- **Top Crop/Farmer by Region**: Rankings and performance metrics
- **Farmer Supply Portal**: Allows farmers to submit supply records via verification code

## Tech Stack

| Layer    | Technology                             |
| -------- | -------------------------------------- |
| Backend  | PHP 7.4+ (Native, no framework)        |
| Frontend | Tailwind CSS (CDN), Vanilla JavaScript |
| Database | MySQL / MariaDB                        |
| Server   | Apache (XAMPP recommended)             |
| Fonts    | Google Fonts (Poppins)                 |

## Project Structure

```
agrisense/
├── index.php                  # Main dashboard homepage
├── auth/
│   ├── login.php              # User login page
│   ├── logout.php             # Session termination
│   └── signup.php             # User registration
├── controllers/
│   ├── AuthController.php     # Authentication logic
│   └── FarmerUpdateController.php  # Farmer supply submission
├── dashboard/
│   └── partials/
│       ├── header.php         # Shared header with navigation
│       └── footer.php         # Shared footer
├── db/
│   └── connection.php         # Database connection and helpers
├── farmer/
│   ├── verify_code.php        # Farmer code verification
│   └── update_crop.php        # Crop supply submission form
├── pages/
│   ├── smart_market.php       # Smart market recommendations
│   ├── price_trend.php        # Price trend analysis
│   ├── market_saturation.php  # Market saturation monitor
│   ├── oversupply_alert.php   # Oversupply detection
│   ├── price_anomaly.php      # Price anomaly detection
│   ├── market_price_gap.php   # Market price comparisons
│   ├── seasonal_price_memory.php  # Seasonal patterns
│   ├── climate_risk_dashboard.php # Climate risk advisories
│   ├── top_crop_region.php    # Top crops by region
│   └── top_farmer_region.php  # Top farmers by region
└── sql/
    ├── schema.sql             # Database structure
    └── seed_data.sql          # Sample data
```

## Setup & Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache web server with mod_rewrite
- XAMPP, WAMP, or similar local development stack

### Installation Steps

1. **Clone or extract the project**

   ```bash
   cd C:\xampp\htdocs
   git clone <repository-url> agrisense
   ```

2. **Create the database**

   ```bash
   mysql -u root -p < agrisense/sql/schema.sql
   ```

3. **Load sample data (optional)**

   ```bash
   mysql -u root -p agrisense < agrisense/sql/seed_data.sql
   ```

4. **Configure database connection**

   Edit `db/connection.php` if your credentials differ from defaults:

   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'agrisense');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

5. **Start Apache and MySQL**

   Launch XAMPP Control Panel and start Apache and MySQL services.

6. **Access the application**

   Open `http://localhost/agrisense` in your browser.

## Usage

### Basic Flow

1. **Registration**: Navigate to `/auth/signup.php` to create an account
2. **Login**: Access `/auth/login.php` with your credentials
3. **Dashboard**: View system statistics on the main dashboard
4. **Analysis Pages**: Use the navigation menu to access various analysis tools
5. **Farmer Portal**: Farmers can verify their code at `/farmer/verify_code.php` and submit supply records

### Farmer Supply Submission

1. Go to the Farmer Portal
2. Enter the unique 6-character farmer code
3. Once verified, submit crop supply details (crop, market, quantity, price)

## Security Notes

### Password Handling

- Passwords are hashed using `password_hash()` with `PASSWORD_DEFAULT` algorithm
- Password verification uses `password_verify()`
- Minimum requirements: 6 characters, uppercase, lowercase, number, special character

### Input Validation

- All user inputs are validated server-side
- Email validation using `filter_var()` with `FILTER_VALIDATE_EMAIL`
- Prepared statements used for all database queries (PDO)
- Input trimming applied to prevent whitespace issues

### Session Management

- Session-based authentication with secure cookie parameters
- Session data cleared on logout
- Protected routes redirect unauthenticated users to login

### Recommendations for Production

- Move database credentials to environment variables
- Enable HTTPS
- Set `session.cookie_secure` and `session.cookie_httponly` in php.ini
- Implement CSRF token protection
- Add rate limiting for login attempts

## Development Guidelines

### Code Style

- Follow PSR-12 coding standards for PHP
- Use meaningful variable and function names
- Keep functions focused on a single responsibility
- Maximum line length: 120 characters

### Commenting

- Add docblocks to all classes and public methods
- Use inline comments sparingly, only for complex logic
- Keep comments up-to-date with code changes

### File Organization

- Controllers handle business logic
- Views (PHP files in pages/) handle presentation
- Database logic centralized in `db/connection.php`

### Git Commit Conventions

- Use present tense: "Add feature" not "Added feature"
- Prefix with type: `feat:`, `fix:`, `docs:`, `refactor:`, `style:`
- Keep subject line under 50 characters
- Reference issue numbers when applicable

Examples:

```
feat: add price anomaly detection page
fix: resolve session timeout on dashboard
docs: update installation instructions
refactor: extract common query functions
```

## Future Improvements

- [ ] RESTful API for mobile application integration
- [ ] Data export functionality (CSV, PDF reports)
- [ ] Email notifications for price alerts
- [ ] Multi-language support
- [ ] Role-based access control (Admin, Analyst, Farmer)
- [ ] Integration with external weather APIs
- [ ] Automated data import from government market databases

## License

Internal / Educational Project

---

For questions or contributions, please contact the project maintainer.
