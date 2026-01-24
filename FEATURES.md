# Features Overview

AgriSense provides a suite of analytical tools designed to support data-driven decision-making in agricultural markets. These features help farmers identify profitable selling opportunities, enable traders to monitor market dynamics, and allow analysts to detect patterns and risks across regions.

The system processes real-time and historical data from multiple sources—including market prices, farmer supply records, and climate risk assessments—to generate actionable insights for all stakeholders in the agricultural supply chain.

---

## 1. Dashboard Overview

**Purpose**

The Dashboard provides a centralized view of the entire agricultural market ecosystem. It solves the problem of scattered information by consolidating key metrics into a single, accessible interface.

**How It Works**

- Aggregates live counts from the database across four key dimensions:
  - **Crops Tracked**: Total number of crop varieties being monitored
  - **Active Markets**: Number of market locations with recorded data
  - **Regions**: Geographical coverage of the system
  - **Supply Records**: Total data points from farmer submissions

- Displays quick-access summaries:
  - Top crops by supply volume across regions
  - Top-performing farmers by contribution
  - Recent market activity indicators

- Data is fetched in real-time from the `crops`, `markets`, `regions`, and `market_supply` tables.

**Example**

A district agricultural officer logs in to check the current state of the market. The dashboard immediately shows that 15 crops are being tracked across 8 markets in 6 regions, with 2,450 supply records collected. This gives an instant snapshot of data coverage and system activity.

**Output / Result**

- Four KPI cards displaying aggregate statistics
- Quick summary tables for top crops and farmers
- Visual indicators for data freshness and system health

---

## 2. Smart Market Finder

**Purpose**

Helps farmers identify the most profitable market to sell a specific crop by analyzing price levels and competition. This addresses the common problem of farmers selling at suboptimal prices due to lack of market visibility.

**How It Works**

1. User selects a crop from the dropdown list
2. The system queries all markets where that crop is traded
3. For each market, it calculates:
   - **Average Price**: Mean current price for the selected crop
   - **Saturation Index**: Supply per farmer ratio (lower = less competition)
   - **Active Farmers**: Number of farmers supplying to that market
   - **Total Supply**: Volume already in the market
   - **Recommendation Score**: Composite metric balancing price and competition

4. Markets are classified into four categories:
   - **Highly Recommended**: High prices, low saturation
   - **Recommended**: Good balance of price and competition
   - **Consider**: Moderate opportunity
   - **Saturated**: High competition, potentially lower returns

**Example**

A rice farmer in Rajshahi wants to know where to sell. They select "Rice" and the system shows:

- Dhaka Central Market: ৳52/kg, Saturation Index 35 → "Highly Recommended"
- Chittagong Market: ৳48/kg, Saturation Index 120 → "Saturated"

The farmer chooses Dhaka Central Market for better returns.

**Output / Result**

- Ranked table of markets with prices, saturation metrics, and recommendations
- Color-coded recommendation labels (green for recommended, red for saturated)
- Sortable columns for custom analysis

---

## 3. Price Trend Analysis

**Purpose**

Enables users to understand how crop prices evolve over time. This historical perspective helps farmers plan planting schedules and traders anticipate market movements.

**How It Works**

1. User selects a crop to analyze
2. The system retrieves historical price records from the `price_history` table
3. Data is aggregated by month, calculating:
   - **Average Price**: Monthly mean price
   - **Minimum Price**: Lowest recorded price in the month
   - **Maximum Price**: Highest recorded price in the month
   - **Total Quantity Sold**: Volume transacted
   - **Record Count**: Number of data points

4. Results are ordered chronologically to show progression

**Example**

An onion trader wants to understand seasonal patterns. The analysis shows:

- January 2025: ৳35/kg average
- June 2025: ৳65/kg average (monsoon shortage)
- November 2025: ৳28/kg average (harvest surplus)

This pattern helps plan procurement and storage strategies.

**Output / Result**

- Monthly breakdown table with price statistics
- Visual indicators for price highs and lows
- Trend direction over the analysis period

---

## 4. Market Saturation Monitor

**Purpose**

Identifies markets where supply concentration is high relative to the number of farmers. This helps prevent oversupply situations where too many farmers compete in the same market.

**How It Works**

1. User can filter by specific crop or view all crops
2. For each market-crop combination, the system calculates:
   - **Total Supply**: Sum of all quantities supplied
   - **Farmer Count**: Number of distinct farmers supplying
   - **Saturation Index**: Total Supply ÷ Farmer Count
   - **Average Price**: Mean price in that market

3. Saturation levels are classified as:
   - **HIGH**: Index > 150 (many farmers, intense competition)
   - **MEDIUM**: Index 100-150 (moderate competition)
   - **LOW**: Index < 100 (favorable conditions for new entrants)

**Example**

The system shows that Potato in Bogra Market has a saturation index of 180 (HIGH) with 45 farmers, while the same crop in Rangpur Market has an index of 60 (LOW) with only 12 farmers. A new farmer would be advised to consider Rangpur.

**Output / Result**

- Table listing all market-crop combinations with saturation metrics
- Color-coded saturation level badges (red for HIGH, yellow for MEDIUM, green for LOW)
- Sortable by saturation index to quickly identify opportunities

---

## 5. Oversupply Alerts

**Purpose**

Provides early warning when a crop's recent supply significantly exceeds historical averages. This prevents market crashes due to unexpected oversupply.

**How It Works**

1. User sets a threshold percentage (default: 40%)
2. The system compares:
   - **Recent Supply**: Total quantity in the last 30 days
   - **Historical Average**: Average monthly supply from older records

3. Growth percentage is calculated: ((Recent - Historical) / Historical) × 100

4. Risk classification:
   - **HIGH**: Growth exceeds the threshold
   - **ELEVATED**: Growth exceeds half the threshold
   - **NORMAL**: Growth within acceptable range

5. Additional context provided:
   - Number of farmers currently supplying
   - Number of markets receiving the crop

**Example**

With a 40% threshold, the system alerts:

- Tomato: Recent supply 5,200 kg vs historical 2,800 kg → 85% growth → HIGH RISK
- Cauliflower: Recent supply 1,800 kg vs historical 1,500 kg → 20% growth → NORMAL

Traders and farmers are warned about potential tomato price drops.

**Output / Result**

- Alert table showing crops with supply growth metrics
- Risk level badges with color coding
- Threshold adjustment control for sensitivity tuning

---

## 6. Price Anomaly Detection

**Purpose**

Flags unusual price deviations that may indicate data errors, market manipulation, or exceptional supply/demand conditions. This ensures data quality and highlights opportunities or risks.

**How It Works**

1. User sets a deviation threshold (default: 20%, range: 5-50%)
2. For each market-crop record, the system:
   - Calculates the average price across all markets for that crop
   - Compares the current market price to this average
   - Computes deviation percentage: ((Current - Average) / Average) × 100

3. Only records exceeding the threshold (positive or negative) are displayed

4. Results are sorted by deviation magnitude (highest first)

**Example**

With a 20% threshold:

- Mango at Sylhet Market: ৳120/kg vs average ৳85/kg → +41% deviation (flagged)
- Rice at Dhaka Market: ৳55/kg vs average ৳52/kg → +6% deviation (not flagged)

The mango anomaly prompts investigation—is it a premium variety or a data entry error?

**Output / Result**

- Table of anomalous price records with deviation percentages
- Current price vs. market average comparison
- Sorted by deviation magnitude for quick prioritization

---

## 7. Market Price Gap Analysis

**Purpose**

Reveals price differences for the same crop across different markets. This helps traders identify arbitrage opportunities and farmers understand regional price variations.

**How It Works**

1. User selects a crop to analyze
2. The system performs pairwise comparison of all markets trading that crop
3. For each market pair, it calculates:
   - **Price Gap**: Absolute difference in current prices
   - **Gap Percentage**: Price difference relative to the lower price
   - **Price Comparison**: Which market has the higher price

4. Results are sorted by price gap (largest first)

**Example**

For Potato:

- Dhaka Central vs Khulna Market: ৳45/kg vs ৳32/kg → Gap ৳13 (40.6%)
- Dhaka Central vs Chittagong: ৳45/kg vs ৳42/kg → Gap ৳3 (7.1%)

A trader could buy in Khulna and sell in Dhaka for significant profit margins.

**Output / Result**

- Pairwise comparison table of all market combinations
- Price gap in absolute and percentage terms
- Direction indicator showing which market is higher-priced

---

## 8. Seasonal Price Memory

**Purpose**

Compares current prices with the same period from the previous year. This reveals whether prices are following historical seasonal patterns or deviating unexpectedly.

**How It Works**

1. User can filter by specific market or view all markets
2. The system matches current month records with the same month last year
3. For each crop-market combination, it calculates:
   - **Current Price**: Average price this period
   - **Last Year Price**: Average price same month, previous year
   - **Percent Change**: Year-over-year price movement

4. Direction classification:
   - **UP**: Current price > 5% above last year
   - **DOWN**: Current price > 5% below last year
   - **STABLE**: Within ±5% of last year

**Example**

In January:

- Rice at Dhaka: ৳55/kg now vs ৳48/kg last year → +14.6% (UP)
- Wheat at Rajshahi: ৳42/kg now vs ৳44/kg last year → -4.5% (STABLE)

This shows rice is unusually expensive compared to seasonal norms.

**Output / Result**

- Year-over-year comparison table by crop and market
- Percent change with directional indicators (arrows)
- Color-coded direction badges (green UP, red DOWN, gray STABLE)

---

## 9. Climate Risk Dashboard

**Purpose**

Provides region-specific climate advisories to help farmers prepare for weather-related risks. This addresses the vulnerability of agricultural operations to climate events.

**How It Works**

1. User can filter by specific region or view all regions
2. The system retrieves climate risk records containing:
   - **Risk Type**: Flood, Drought, Salinity, Cyclone, Waterlogging
   - **Severity Level**: Critical, High, Moderate, Low
   - **Advisory Text**: Specific guidance for farmers
   - **Season**: When the risk is most relevant

3. Results are sorted by severity (Critical first) for prioritization

**Example**

For Khulna region:

- **Salinity** | Severity: High | "Monitor soil salinity levels. Consider salt-tolerant rice varieties." | Dry Season
- **Cyclone** | Severity: Moderate | "Ensure grain storage is secured. Monitor weather updates." | Monsoon

Farmers in Khulna can prepare accordingly.

**Output / Result**

- Risk advisory table with severity indicators
- Color-coded severity badges (red Critical, orange High, yellow Moderate, green Low)
- Actionable advisory text for each risk
- Seasonal applicability information

---

## 10. Top Crop / Farmer by Region

**Purpose**

Identifies the highest-performing crop and farmer in each region based on revenue generation. This provides benchmarks and recognition for successful agricultural practices.

**How It Works**

### Top Crop by Region

1. Aggregates all supply records by region and crop
2. Calculates total revenue: Quantity × Price per Unit
3. Identifies the crop generating maximum revenue in each region
4. Additional metrics: total quantity, average price, farmer count

### Top Farmer by Region

1. Aggregates all supply records by region and farmer
2. Calculates total revenue from all farmer's transactions
3. Identifies the top-earning farmer in each region
4. Additional metrics: total quantity supplied, number of supply transactions

**Example**

**Top Crops:**

- Dhaka Region: Rice → ৳2,450,000 revenue, 45,000 kg supplied
- Chittagong Region: Vegetables → ৳1,890,000 revenue, 32,000 kg supplied

**Top Farmers:**

- Dhaka Region: Abdul Karim → ৳185,000 revenue, 3,200 kg supplied
- Rangpur Region: Fatema Begum → ৳142,000 revenue, 2,800 kg supplied

**Output / Result**

- Ranked leaderboard tables for crops and farmers by region
- Revenue figures with quantity and transaction metrics
- Visual ranking indicators (gold, silver, bronze for top 3)

---

## 11. Farmer Supply Portal

**Purpose**

Enables registered farmers to directly submit their crop supply records to the system. This ensures continuous data collection and gives farmers a stake in the information ecosystem.

**How It Works**

### Verification Process

1. Farmer enters their unique 6-digit farmer code
2. System validates the code format (must be exactly 6 digits)
3. Code is matched against the `farmers` table
4. If valid, farmer identity and region are retrieved
5. Farmer session is established for secure access

### Supply Submission

1. Verified farmer accesses the submission form
2. Required fields:
   - **Crop**: Selected from available crop list
   - **Market**: Selected from available market list
   - **Quantity**: Amount supplied (positive number)
   - **Price per Unit**: Selling price achieved

3. Validation ensures all fields are complete and valid
4. Record is inserted into `market_supply` with current date
5. Confirmation displayed upon successful submission

**Example**

Farmer Rahim (Code: 123456) from Bogra:

1. Enters code 123456 → Verified as "Rahim Ahmed, Bogra Region"
2. Submits: Rice, 500 kg, Bogra Central Market, ৳54/kg
3. System confirms: "Supply record submitted successfully"

This data immediately becomes available in all analytical features.

**Output / Result**

- Verification confirmation with farmer name and region
- Supply submission form with dropdown selections
- Success/error messages for submission status
- Option to submit multiple records in sequence

---

## Summary

| Feature                   | Primary Benefit              | Key Metric           |
| ------------------------- | ---------------------------- | -------------------- |
| Dashboard Overview        | System health at a glance    | Aggregate counts     |
| Smart Market Finder       | Optimal selling location     | Recommendation score |
| Price Trend Analysis      | Historical price patterns    | Monthly averages     |
| Market Saturation Monitor | Competition assessment       | Saturation index     |
| Oversupply Alerts         | Early warning system         | Growth percentage    |
| Price Anomaly Detection   | Data quality & opportunities | Deviation percentage |
| Market Price Gap Analysis | Arbitrage identification     | Price gap            |
| Seasonal Price Memory     | Year-over-year comparison    | Percent change       |
| Climate Risk Dashboard    | Risk preparedness            | Severity level       |
| Top Crop/Farmer by Region | Performance benchmarks       | Revenue ranking      |
| Farmer Supply Portal      | Data contribution            | Submission success   |

---

_This document is intended for presentation to evaluators, developers, and stakeholders. All features are implemented and operational within the AgriSense system._
