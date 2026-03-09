-- Update constraints to support user's specific status strings
BEGIN
  FOR r IN (SELECT constraint_name FROM user_constraints 
            WHERE table_name = 'FOOD_ALERTS' AND constraint_type = 'C'
            AND (search_condition_vc LIKE '%status%' OR search_condition_vc LIKE '%delivery_status%')) LOOP
    EXECUTE IMMEDIATE 'ALTER TABLE food_alerts DROP CONSTRAINT ' || r.constraint_name;
  END LOOP;
  
  FOR r IN (SELECT constraint_name FROM user_constraints 
            WHERE table_name = 'CLAIMS' AND constraint_type = 'C'
            AND search_condition_vc LIKE '%status%') LOOP
    EXECUTE IMMEDIATE 'ALTER TABLE claims DROP CONSTRAINT ' || r.constraint_name;
  END LOOP;
END;
/

ALTER TABLE food_alerts ADD CONSTRAINT chk_fa_status 
CHECK (status IN ('AVAILABLE', 'PENDING', 'NGO_ASSIGNED', 'VOLUNTEER_ASSIGNED', 'Assigned', 'Picked Up', 'On Delivery', 'Delivered', 'COMPLETED', 'EXPIRED', 'CLAIMED', 'FOOD_PICKED_UP', 'PICKUP_STARTED', 'DELIVERING'));

ALTER TABLE food_alerts ADD CONSTRAINT chk_fa_del_status 
CHECK (delivery_status IN ('PENDING', 'ACCEPTED', 'Assigned', 'Picked Up', 'On Delivery', 'Delivered', 'COMPLETED', 'FOOD_PICKED_UP', 'PICKUP_STARTED', 'DELIVERING'));

ALTER TABLE claims ADD CONSTRAINT chk_claims_status 
CHECK (status IN ('ACTIVE', 'VOLUNTEER_ASSIGNED', 'Assigned', 'Picked Up', 'On Delivery', 'Delivered', 'COMPLETED', 'CANCELLED', 'FOOD_PICKED_UP', 'PICKUP_STARTED', 'DELIVERING'));

COMMIT;
EXIT;
