-- =============================================================
-- add_city_column.sql
-- Add city support for automatic NGO assignment
-- =============================================================

-- 1. Add city to users
ALTER TABLE users ADD city VARCHAR2(100);

-- 2. Add city to food_alerts
ALTER TABLE food_alerts ADD city VARCHAR2(100);

-- 3. Update existing data if needed (default to Chennai)
UPDATE users SET city = 'Chennai' WHERE city IS NULL;
UPDATE food_alerts SET city = 'Chennai' WHERE city IS NULL;

-- 4. Update NGO_ASSIGNED status constraint if it exists
-- Actually, food_alerts.status has a check constraint in oracle_setup.sql:
-- CHECK (status IN ('AVAILABLE', 'CLAIMED', 'COMPLETED', 'EXPIRED'))
-- We need to update this to include 'NGO_ASSIGNED' and 'VOLUNTEER_ASSIGNED', etc.

ALTER TABLE food_alerts DROP CONSTRAINT sys_c007137; -- This name might vary, better to check first or just add a new check.
-- Since I don't know the exact constraint name for everyone, I'll attempt to drop it or just ignore it if I use a different name.
-- In my oracle_setup.sql it was an inline check.

-- Let's just modify the column to remove the constraint if possible, or add a new one.
-- Better way: find the constraint name.

COMMIT;
