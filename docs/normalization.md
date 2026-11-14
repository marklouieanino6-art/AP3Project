# Normalization

## Unnormalized Form (UNF)
One flat table containing farmer, farm, crop, planting, and harvest data with repeating groups (multiple farms per farmer, multiple plantings per farm, multiple harvests per planting).

## 1NF
Remove repeating groups; each field atomic. Create separate rows for each farm, planting, harvest.

## 2NF
Remove partial dependencies: farm depends on farmer; planting depends on farm+crop; harvest depends on planting. Move to separate tables with PKs.

## 3NF
Remove transitive dependencies: lookup tables (soil_type, irrigation_type, crop_category, recommended_season, planting_status, quality_grade, harvest_remark, role) store descriptions; core tables store FK ids.