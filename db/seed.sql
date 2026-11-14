-- Lookup data
INSERT INTO soil_type (description) VALUES ('Loam'),('Clay Loam'),('Sandy Loam'),('Clay') ON CONFLICT DO NOTHING;
INSERT INTO irrigation_type (description) VALUES ('Rainfed'),('Irrigated'),('Drip Irrigation') ON CONFLICT DO NOTHING;
INSERT INTO crop_category (description) VALUES ('Cereal'),('Vegetable'),('Fruit'),('Legume'),('Root Crop') ON CONFLICT DO NOTHING;
INSERT INTO recommended_season (description) VALUES ('Wet Season'),('Dry Season'),('Year Round') ON CONFLICT DO NOTHING;
INSERT INTO planting_status (description) VALUES ('Planned'),('Growing'),('Harvested'),('Failed') ON CONFLICT DO NOTHING;
INSERT INTO quality_grade (description) VALUES ('Grade A'),('Grade B'),('Grade C') ON CONFLICT DO NOTHING;
INSERT INTO harvest_remark (description) VALUES ('Good Harvest'),('Acceptable Quality'),('Low Yield'),('Poor Quality'),('Damaged Harvest') ON CONFLICT DO NOTHING;
INSERT INTO role (description) VALUES ('Administrator'),('Staff') ON CONFLICT DO NOTHING;

-- Crops
INSERT INTO crop (crop_name, variety, crop_category_id, growing_period_days, recommended_season_id) VALUES
('Rice','NSIC Rc 222',1,115,1),
('Corn','Yellow Corn',1,100,2),
('Tomato','Diamante Max',2,90,2),
('Eggplant','Morena',2,100,3),
('Banana','Lakatan',3,300,3),
('Mango','Carabao',3,150,3),
('Peanut','BPI Pn 9',4,110,3),
('Cassava','Golden Yellow',5,240,3),
('Sweet Potato','NSIC Sp 30',5,120,3),
('Watermelon','Sweet 16',3,85,2);

-- Farmers
INSERT INTO farmer (first_name,last_name,contact_number,address,date_registered) VALUES
('Juan','Dela Cruz','09171234567','Cebu City','2025-10-26'),
('Maria','Santos','09181234567','Carcar City','2025-10-26'),
('Pedro','Reyes','09191234567','Danao City','2025-10-26'),
('Ana','Garcia','09201234567','Argao','2025-10-26'),
('Jose','Mendoza','09211234567','Bogo City','2025-10-26'),
('Ramon','Flores','09221234567','Toledo City','2025-10-26'),
('Elena','Cruz','09231234567','Naga City','2025-10-26'),
('Antonio','Lim','09241234567','Minglanilla','2025-10-26'),
('Rosa','Fernandez','09251234567','Liloan','2025-10-26'),
('Mario','Villanueva','09261234567','Compostela','2025-10-26'),
('Teresa','Navarro','09271234567','Talisay City','2025-10-26'),
('Carlos','Ramos','09281234567','Consolacion','2025-10-26'),
('Liza','Torres','09291234567','Dalaguete','2025-10-26'),
('Roberto','Castillo','09301234567','Barili','2025-10-26'),
('Susan','Aquino','09311234567','Sibonga','2025-10-26');

-- Farms (one per farmer)
INSERT INTO farm (farmer_id,farm_name,location,area_hectares,soil_type_id,irrigation_type_id) VALUES
(1,'Juan Farm','Cebu City',2.50,1,2),
(2,'Maria Farm','Carcar City',3.00,2,1),
(3,'Pedro Farm','Danao City',1.75,3,2),
(4,'Ana Farm','Argao',4.25,4,3),
(5,'Jose Farm','Bogo City',5.50,1,2),
(6,'Ramon Farm','Toledo City',2.75,2,1),
(7,'Elena Farm','Naga City',3.50,3,2),
(8,'Antonio Farm','Minglanilla',2.00,4,3),
(9,'Rosa Farm','Liloan',4.75,1,2),
(10,'Mario Farm','Compostela',3.25,2,1),
(11,'Teresa Farm','Talisay City',2.25,3,2),
(12,'Carlos Farm','Consolacion',5.00,4,3),
(13,'Liza Farm','Dalaguete',1.90,1,2),
(14,'Roberto Farm','Barili',4.00,2,1),
(15,'Susan Farm','Sibonga',3.75,3,2);

-- Plantings (28)
INSERT INTO planting (farm_id,crop_id,planting_date,area_planted,seed_quantity,expected_harvest_date,status_id) VALUES
(1,1,'2025-11-01',1.00,25.00,'2026-02-24',2),
(1,2,'2025-11-05',1.00,20.00,'2026-02-13',2),
(2,3,'2025-11-10',0.50,5.00,'2026-02-08',3),
(2,4,'2025-11-12',0.50,4.00,'2026-02-20',2),
(3,5,'2025-11-15',1.00,10.00,'2026-09-11',2),
(3,6,'2025-11-18',1.00,8.00,'2026-04-16',3),
(4,7,'2025-11-20',0.75,15.00,'2026-03-10',2),
(4,8,'2025-11-22',1.00,12.00,'2026-07-19',3),
(5,9,'2025-11-25',1.00,18.00,'2026-03-25',2),
(5,10,'2025-11-28',0.50,3.00,'2026-02-21',3),
(6,1,'2025-12-01',1.20,30.00,'2026-03-26',2),
(6,2,'2025-12-03',1.00,22.00,'2026-03-13',3),
(7,3,'2025-12-05',0.60,6.00,'2026-03-05',2),
(7,4,'2025-12-07',0.60,5.00,'2026-03-17',3),
(8,5,'2025-12-09',1.10,11.00,'2026-10-05',2),
(8,6,'2025-12-11',1.00,9.00,'2026-05-10',3),
(9,7,'2025-12-13',0.80,16.00,'2026-04-02',2),
(9,8,'2025-12-15',1.00,13.00,'2026-08-12',3),
(10,9,'2025-12-17',1.00,19.00,'2026-04-16',2),
(10,10,'2025-12-19',0.50,3.50,'2026-03-14',3),
(11,1,'2025-12-21',1.10,28.00,'2026-04-15',2),
(11,2,'2025-12-23',1.00,21.00,'2026-04-02',3),
(12,3,'2025-12-25',0.70,7.00,'2026-03-25',2),
(12,4,'2025-12-27',0.60,5.50,'2026-04-06',3),
(13,5,'2025-12-29',1.00,10.50,'2026-10-25',2),
(14,6,'2025-12-31',1.00,8.50,'2026-05-30',3),
(15,7,'2026-01-02',0.80,16.50,'2026-04-21',2),
(15,8,'2026-01-04',1.00,13.50,'2026-09-01',3);

-- Harvests for first 20 plantings
INSERT INTO harvest (planting_id,harvest_date,quantity_kg,selling_price_per_kg,quality_grade_id,remark_id) VALUES
(1,'2026-02-25',900,22.00,1,1),
(2,'2026-02-14',1025,25.50,2,2),
(3,'2026-02-09',1150,29.00,3,1),
(4,'2026-02-21',1275,32.50,1,2),
(5,'2026-09-12',1400,36.00,2,1),
(6,'2026-04-17',1525,39.50,3,2),
(7,'2026-03-11',1650,43.00,1,1),
(8,'2026-07-20',1775,46.50,2,2),
(9,'2026-03-26',1900,22.00,3,1),
(10,'2026-02-22',2025,25.50,1,2),
(11,'2026-03-27',2150,29.00,2,1),
(12,'2026-03-14',2275,32.50,3,2),
(13,'2026-03-06',2400,36.00,1,1),
(14,'2026-03-18',2525,39.50,2,2),
(15,'2026-10-06',2650,43.00,3,1),
(16,'2026-05-11',2775,46.50,1,2),
(17,'2026-04-03',2900,22.00,2,1),
(18,'2026-08-13',3025,25.50,3,2),
(19,'2026-04-17',3150,29.00,1,1),
(20,'2026-03-15',3275,32.50,2,2);

-- Users
INSERT INTO app_user (username,password_hash,full_name,role_id) VALUES
('admin','$2y$12$CkKrFATPwH/z6wowFQHlauFtdxpRTBUdmwHzKZ396GZ0c9jvLxCmu','Administrator',1),
('staff','$2y$12$ike3KOk1/lwIFEEjOJiBbOT59ZrP5gfT67MIXpQ8rGwO7LY3d9ESW','Staff User',2);
