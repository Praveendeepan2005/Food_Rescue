-- =============================================================
-- update_logistics_schema.sql
-- Support for automatic NGO assignment and logistics flow
-- =============================================================

-- 1. Add city to users (for donors and NGOs)
ALTER TABLE users ADD city VARCHAR2(100);

-- 2. Add city and update status for food_alerts
ALTER TABLE food_alerts ADD city VARCHAR2(100);

-- 3. Update food_alerts status constraint to support new flow
-- First, identifying the constraint is hard without knowing name. 
-- We'll just define a new check constraint that covers everything.
-- But we should try to remove the old one if it exists. 
-- In Oracle, we can't easily drop a constraint by type/condition without PL/SQL.

BEGIN
  FOR r IN (SELECT constraint_name FROM user_constraints 
            WHERE table_name = 'FOOD_ALERTS' AND constraint_type = 'C'
            AND search_condition_vc LIKE '%status%') LOOP
    EXECUTE IMMEDIATE 'ALTER TABLE food_alerts DROP CONSTRAINT ' || r.constraint_name;
  END LOOP;
END;
/

-- Add new status constraint
ALTER TABLE food_alerts ADD CONSTRAINT chk_food_alerts_status 
CHECK (status IN ('PENDING', 'NGO_ASSIGNED', 'VOLUNTEER_ASSIGNED', 'PICKUP_STARTED', 'FOOD_PICKED_UP', 'DELIVERING', 'DELIVERED', 'COMPLETED', 'EXPIRED', 'AVAILABLE'));

-- 4. Ensure coordinates are optional for user profile (already nullable)

-- 5. Add NGO details if missing (latitude/longitude already there)

COMMIT;
