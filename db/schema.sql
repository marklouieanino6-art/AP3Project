-- Agriculture Information System schema (Supabase PostgreSQL)
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Lookup tables
CREATE TABLE IF NOT EXISTS soil_type (
  soil_type_id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  description varchar(50) NOT NULL UNIQUE
);
CREATE TABLE IF NOT EXISTS irrigation_type (
  irrigation_type_id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  description varchar(50) NOT NULL UNIQUE
);
CREATE TABLE IF NOT EXISTS crop_category (
  crop_category_id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  description varchar(50) NOT NULL UNIQUE
);
CREATE TABLE IF NOT EXISTS recommended_season (
  recommended_season_id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  description varchar(50) NOT NULL UNIQUE
);
CREATE TABLE IF NOT EXISTS planting_status (
  status_id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  description varchar(50) NOT NULL UNIQUE
);
CREATE TABLE IF NOT EXISTS quality_grade (
  quality_grade_id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  description varchar(50) NOT NULL UNIQUE
);
CREATE TABLE IF NOT EXISTS harvest_remark (
  remark_id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  description varchar(50) NOT NULL UNIQUE
);
CREATE TABLE IF NOT EXISTS role (
  role_id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  description varchar(50) NOT NULL UNIQUE
);

-- Core tables
CREATE TABLE IF NOT EXISTS farmer (
  farmer_id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  first_name varchar(50) NOT NULL,
  last_name varchar(50) NOT NULL,
  contact_number varchar(20),
  address varchar(150),
  date_registered date NOT NULL,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now(),
  created_by integer,
  updated_by integer
);

CREATE TABLE IF NOT EXISTS farm (
  farm_id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  farmer_id integer NOT NULL REFERENCES farmer(farmer_id) ON DELETE RESTRICT,
  farm_name varchar(100) NOT NULL,
  location varchar(150),
  area_hectares numeric(8,2) NOT NULL CHECK (area_hectares > 0),
  soil_type_id integer REFERENCES soil_type(soil_type_id) ON DELETE RESTRICT,
  irrigation_type_id integer REFERENCES irrigation_type(irrigation_type_id) ON DELETE RESTRICT,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now(),
  created_by integer,
  updated_by integer,
  UNIQUE (farmer_id, farm_name)
);

CREATE TABLE IF NOT EXISTS crop (
  crop_id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  crop_name varchar(80) NOT NULL,
  variety varchar(80),
  crop_category_id integer REFERENCES crop_category(crop_category_id) ON DELETE RESTRICT,
  growing_period_days integer CHECK (growing_period_days > 0),
  recommended_season_id integer REFERENCES recommended_season(recommended_season_id) ON DELETE RESTRICT,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now(),
  created_by integer,
  updated_by integer,
  UNIQUE (crop_name, variety)
);

CREATE TABLE IF NOT EXISTS planting (
  planting_id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  farm_id integer NOT NULL REFERENCES farm(farm_id) ON DELETE RESTRICT,
  crop_id integer NOT NULL REFERENCES crop(crop_id) ON DELETE RESTRICT,
  planting_date date NOT NULL,
  area_planted numeric(8,2) NOT NULL CHECK (area_planted > 0),
  seed_quantity numeric(8,2) NOT NULL CHECK (seed_quantity > 0),
  expected_harvest_date date CHECK (expected_harvest_date >= planting_date),
  status_id integer REFERENCES planting_status(status_id) ON DELETE RESTRICT,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now(),
  created_by integer,
  updated_by integer
);

CREATE TABLE IF NOT EXISTS harvest (
  harvest_id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  planting_id integer NOT NULL REFERENCES planting(planting_id) ON DELETE RESTRICT,
  harvest_date date NOT NULL,
  quantity_kg numeric(10,2) NOT NULL CHECK (quantity_kg > 0),
  selling_price_per_kg numeric(10,2) NOT NULL CHECK (selling_price_per_kg >= 0),
  quality_grade_id integer REFERENCES quality_grade(quality_grade_id) ON DELETE RESTRICT,
  remark_id integer REFERENCES harvest_remark(remark_id) ON DELETE RESTRICT,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now(),
  created_by integer,
  updated_by integer
);

CREATE TABLE IF NOT EXISTS app_user (
  user_id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  username varchar(50) NOT NULL UNIQUE,
  password_hash text NOT NULL,
  full_name varchar(100) NOT NULL,
  role_id integer NOT NULL REFERENCES role(role_id) ON DELETE RESTRICT,
  created_at timestamptz NOT NULL DEFAULT now()
);

-- updated_at trigger function
CREATE OR REPLACE FUNCTION set_updated_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = now();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- apply updated_at triggers
DO $$
DECLARE t text;
BEGIN
  FOR t IN SELECT unnest(ARRAY['farmer','farm','crop','planting','harvest']) LOOP
    EXECUTE format('DROP TRIGGER IF EXISTS trg_%s_updated ON %s;', t, t);
    EXECUTE format('CREATE TRIGGER trg_%s_updated BEFORE UPDATE ON %s FOR EACH ROW EXECUTE FUNCTION set_updated_at();', t, t);
  END LOOP;
END $$;

-- harvest_date >= planting_date trigger
CREATE OR REPLACE FUNCTION check_harvest_date()
RETURNS TRIGGER AS $$
DECLARE pd date;
BEGIN
  SELECT planting_date INTO pd FROM planting WHERE planting_id = NEW.planting_id;
  IF NEW.harvest_date < pd THEN
    RAISE EXCEPTION 'harvest_date must be >= planting_date';
  END IF;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_harvest_date ON harvest;
CREATE TRIGGER trg_harvest_date BEFORE INSERT OR UPDATE ON harvest
FOR EACH ROW EXECUTE FUNCTION check_harvest_date();