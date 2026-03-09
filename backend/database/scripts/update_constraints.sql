-- =============================================================
-- Update Status Constraints
-- =============================================================

-- 1. Remove old constraint on food_alerts status if exists
-- We don't know the name exactly, so we allow errors or drop by pattern if we can.
-- Usually, we can just add a new one or modify.

-- Let's just try to update the status list.
-- We'll look for the constraint name first.
DECLARE
    l_const_name VARCHAR2(100);
BEGIN
    SELECT constraint_name INTO l_const_name 
    FROM user_constraints 
    WHERE table_name = 'FOOD_ALERTS' 
    AND search_condition_vc LIKE '%status%';
    
    EXECUTE IMMEDIATE 'ALTER TABLE food_alerts DROP CONSTRAINT ' || l_const_name;
EXCEPTION
    WHEN OTHERS THEN NULL;
END;
/

ALTER TABLE food_alerts ADD CONSTRAINT check_fa_status 
CHECK (status IN ('AVAILABLE', 'CLAIMED', 'COMPLETED', 'EXPIRED'));

-- 2. Similar for claims
DECLARE
    l_const_name VARCHAR2(100);
BEGIN
    SELECT constraint_name INTO l_const_name 
    FROM user_constraints 
    WHERE table_name = 'CLAIMS' 
    AND search_condition_vc LIKE '%status%';
    
    EXECUTE IMMEDIATE 'ALTER TABLE claims DROP CONSTRAINT ' || l_const_name;
EXCEPTION
    WHEN OTHERS THEN NULL;
END;
/

ALTER TABLE claims ADD CONSTRAINT check_claim_status 
CHECK (status IN ('ACTIVE', 'ON_THE_WAY', 'PICKED_UP', 'DELIVERED', 'COMPLETED', 'CANCELLED'));

COMMIT;
