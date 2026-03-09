-- =============================================================
-- Harmonize Claims Table Constraints
-- =============================================================

BEGIN
  FOR r IN (SELECT constraint_name FROM user_constraints 
            WHERE table_name = 'CLAIMS' AND constraint_type = 'C'
            AND search_condition_vc LIKE '%status%') LOOP
    EXECUTE IMMEDIATE 'ALTER TABLE claims DROP CONSTRAINT ' || r.constraint_name;
  END LOOP;
END;
/

ALTER TABLE claims ADD CONSTRAINT chk_claims_status_final
CHECK (status IN ('ACTIVE', 'PENDING', 'ACCEPTED', 'VOLUNTEER_ASSIGNED', 'PICKUP_STARTED', 'FOOD_PICKED_UP', 'FOOD_PICKED', 'DELIVERING', 'DELIVERED', 'COMPLETED', 'CANCELLED', 'CLAIMED'));

COMMIT;
EXIT;
