-- =============================================================
-- Update Schema: Add Donor Dashboard & Profile Columns
-- =============================================================

-- 1. Update Users table for Profile
ALTER TABLE users ADD address VARCHAR2(255);
ALTER TABLE users ADD org_name VARCHAR2(100);

-- 2. Update Food Alerts table for enhanced donor donation form
ALTER TABLE food_alerts ADD category VARCHAR2(20);
ALTER TABLE food_alerts ADD preparation_time VARCHAR2(100);
ALTER TABLE food_alerts ADD pickup_address VARCHAR2(255);
ALTER TABLE food_alerts ADD contact_number VARCHAR2(15);
ALTER TABLE food_alerts ADD special_instructions VARCHAR2(500);

-- Verify changes
DESC users;
DESC food_alerts;

COMMIT;
