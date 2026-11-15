# Database Normalization

## Unnormalized Form (UNF)
One flat table with repeating groups.

| farmer_name | farm_name | crop_name | planting_date | harvest_date | qty |
|---|---|---|---|---|---|
| Juan | Farm1 | Rice | 2025-11-01 | 2026-02-25 | 900 |

## 1NF
Atomic values; one row per fact.

| farmer_name | farm_name | crop_name | planting_date |
|---|---|---|---|
| Juan | Farm1 | Rice | 2025-11-01 |

## 2NF
Split by dependency.

**farmer**: farmer_id, name
**farm**: farm_id, farmer_id, name
**planting**: planting_id, farm_id, crop_id, date

## 3NF
Lookup tables for descriptions.

**crop**: crop_id, crop_name, crop_category_id (FK)
**planting_status**: status_id, description