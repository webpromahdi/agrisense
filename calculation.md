# AgriSense Feature Calculations

Quick reference for all feature calculations in the system.

---

## 1. Smart Market Finder

```
Saturation Index = Total Supply ÷ Farmer Count
Recommendation Score = (Avg Price / 100) - (Saturation Index / 10)
```

**Classification:**
| Condition | Label |
|-----------|-------|
| Saturation < 50 AND Price > Average | Highly Recommended |
| Saturation < 100 | Recommended |
| Saturation < 150 | Consider |
| Saturation ≥ 150 | Saturated |

---

## 2. Price Trend Analysis

```
Monthly Avg Price = AVG(price)
Min Price = MIN(price)
Max Price = MAX(price)
Total Quantity = SUM(quantity_sold)
```

---

## 3. Market Saturation Monitor

```
Saturation Index = Total Supply ÷ Farmer Count
```

**Levels:**
| Index Value | Level |
|-------------|-------|
| > 150 | HIGH |
| 100 - 150 | MEDIUM |
| < 100 | LOW |

---

## 4. Oversupply Alert

```
Growth % = ((Recent Supply - Historical Avg) / Historical Avg) × 100
```

- **Recent Supply** = Sum of quantity in last 30 days
- **Historical Avg** = AVG(quantity) × 30 (records older than 30 days)

**Risk Levels:**
| Condition | Risk |
|-----------|------|
| Growth > Threshold (default 40%) | HIGH |
| Growth > Threshold / 2 | ELEVATED |
| Below threshold | NORMAL |

---

## 5. Price Anomaly Detection

```
Deviation % = ((Current Price - Avg Price) / Avg Price) × 100
```

- Flags anomaly when `|Deviation %| > Threshold` (default 20%)

---

## 6. Seasonal Price Memory

```
Percent Change = ((Current Price - Last Year Price) / Last Year Price) × 100
```

**Direction:**
| Condition | Direction |
|-----------|-----------|
| Current > Last Year × 1.05 | UP |
| Current < Last Year × 0.95 | DOWN |
| Otherwise | STABLE |

---

## 7. Market Price Gap

```
Price Gap = |Market A Price - Market B Price|
Gap % = (Price Gap / MIN(Price A, Price B)) × 100
```

---

## 8. Top Crop by Region

```
Total Revenue = SUM(Quantity × Price Per Unit)
```

Shows the crop with **MAX revenue** per region.

---

## 9. Top Farmer by Region

```
Total Revenue = SUM(Quantity × Price Per Unit)
```

Shows the farmer with **MAX revenue** per region.

---

## 10. Climate Risk Dashboard

No calculation — displays predefined data from `climate_risk` table.

**Severity Order:** Critical → High → Moderate → Low
