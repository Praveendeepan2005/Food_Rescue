-- =============================================================
-- Final Logistics Constraint Fix
-- =============================================================

-- 1. Harmonize delivery_status constraint
BEGIN
  FOR r IN (SELECT constraint_name FROM user_constraints 
            WHERE table_name = 'FOOD_ALERTS' AND constraint_type = 'C'
            AND search_condition_vc LIKE '%delivery_status%') LOOP
    EXECUTE IMMEDIATE 'ALTER TABLE food_alerts DROP CONSTRAINT ' || r.constraint_name;
  END LOOP;
END;
/

ALTER TABLE food_alerts ADD CONSTRAINT chk_delivery_status 
CHECK (delivery_status IN ('PENDING', 'ACCEPTED', 'PICKUP_STARTED', 'FOOD_PICKED_UP', 'FOOD_PICKED', 'DELIVERING', 'DELIVERED', 'COMPLETED'));

-- 2. Ensure assigned_ngo_id exists and is NOT NULL if possible (or at least exists)
-- (It already exists from map_tracking_schema or others)
ALTER TABLE food_alerts MODIFY (status VARCHAR2(30));
ALTER TABLE food_alerts MODIFY (delivery_status VARCHAR2(30));

-- 3. Harmonize status constraint again to be absolutely sure
BEGIN
  FOR r IN (SELECT constraint_name FROM user_constraints 
            WHERE table_name = 'FOOD_ALERTS' AND constraint_type = 'C'
            AND search_condition_vc LIKE '%status%' AND search_condition_vc NOT LIKE '%delivery_status%') LOOP
    EXECUTE IMMEDIATE 'ALTER TABLE food_alerts DROP CONSTRAINT ' || r.constraint_name;
  END LOOP;
END;
/

ALTER TABLE food_alerts ADD CONSTRAINT chk_food_alerts_status_final
CHECK (status IN ('AVAILABLE', 'PENDING', 'NGO_ASSIGNED', 'VOLUNTEER_ASSIGNED', 'PICKUP_STARTED', 'FOOD_PICKED_UP', 'FOOD_PICKED', 'DELIVERING', 'DELIVERED', 'COMPLETED', 'EXPIRED', 'CLAIMED'));

COMMIT;
EXIT;
