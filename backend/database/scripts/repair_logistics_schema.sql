-- =============================================================
-- Comprehensive Logistics Schema Repair
-- =============================================================

-- 1. Ensure columns exist in FOOD_ALERTS
DECLARE
  v_count NUMBER;
BEGIN
  -- delivery_status
  SELECT count(*) INTO v_count FROM user_tab_columns WHERE table_name = 'FOOD_ALERTS' AND column_name = 'DELIVERY_STATUS';
  IF v_count = 0 THEN
    EXECUTE IMMEDIATE 'ALTER TABLE food_alerts ADD (delivery_status VARCHAR2(50) DEFAULT ''PENDING'')';
  END IF;

  -- vol_lat
  SELECT count(*) INTO v_count FROM user_tab_columns WHERE table_name = 'FOOD_ALERTS' AND column_name = 'VOL_LAT';
  IF v_count = 0 THEN
    EXECUTE IMMEDIATE 'ALTER TABLE food_alerts ADD (vol_lat NUMBER(10, 7))';
  END IF;

  -- vol_lng
  SELECT count(*) INTO v_count FROM user_tab_columns WHERE table_name = 'FOOD_ALERTS' AND column_name = 'VOL_LNG';
  IF v_count = 0 THEN
    EXECUTE IMMEDIATE 'ALTER TABLE food_alerts ADD (vol_lng NUMBER(10, 7))';
  END IF;

  -- assigned_ngo_id
  SELECT count(*) INTO v_count FROM user_tab_columns WHERE table_name = 'FOOD_ALERTS' AND column_name = 'ASSIGNED_NGO_ID';
  IF v_count = 0 THEN
    EXECUTE IMMEDIATE 'ALTER TABLE food_alerts ADD (assigned_ngo_id NUMBER)';
  END IF;
  
  -- city
  SELECT count(*) INTO v_count FROM user_tab_columns WHERE table_name = 'FOOD_ALERTS' AND column_name = 'CITY';
  IF v_count = 0 THEN
    EXECUTE IMMEDIATE 'ALTER TABLE food_alerts ADD (city VARCHAR2(100))';
  END IF;

  -- preparation_time
  SELECT count(*) INTO v_count FROM user_tab_columns WHERE table_name = 'FOOD_ALERTS' AND column_name = 'PREPARATION_TIME';
  IF v_count = 0 THEN
    EXECUTE IMMEDIATE 'ALTER TABLE food_alerts ADD (preparation_time VARCHAR2(100))';
  END IF;

  -- contact_number
  SELECT count(*) INTO v_count FROM user_tab_columns WHERE table_name = 'FOOD_ALERTS' AND column_name = 'CONTACT_NUMBER';
  IF v_count = 0 THEN
    EXECUTE IMMEDIATE 'ALTER TABLE food_alerts ADD (contact_number VARCHAR2(20))';
  END IF;

  -- special_instructions
  SELECT count(*) INTO v_count FROM user_tab_columns WHERE table_name = 'FOOD_ALERTS' AND column_name = 'SPECIAL_INSTRUCTIONS';
  IF v_count = 0 THEN
    EXECUTE IMMEDIATE 'ALTER TABLE food_alerts ADD (special_instructions VARCHAR2(1000))';
  END IF;

END;
/

-- 2. Ensure columns exist in CLAIMS
DECLARE
  v_count NUMBER;
BEGIN
  -- picked_up_at
  SELECT count(*) INTO v_count FROM user_tab_columns WHERE table_name = 'CLAIMS' AND column_name = 'PICKED_UP_AT';
  IF v_count = 0 THEN
    EXECUTE IMMEDIATE 'ALTER TABLE claims ADD (picked_up_at DATE)';
  END IF;

  -- completed_at
  SELECT count(*) INTO v_count FROM user_tab_columns WHERE table_name = 'CLAIMS' AND column_name = 'COMPLETED_AT';
  IF v_count = 0 THEN
    EXECUTE IMMEDIATE 'ALTER TABLE claims ADD (completed_at DATE)';
  END IF;
END;
/

-- 3. Harmonize Constraints
BEGIN
  -- Drop existing status check on food_alerts
  FOR r IN (SELECT constraint_name FROM user_constraints 
            WHERE table_name = 'FOOD_ALERTS' AND constraint_type = 'C'
            AND search_condition_vc LIKE '%status%' AND search_condition_vc NOT LIKE '%delivery_status%') LOOP
    EXECUTE IMMEDIATE 'ALTER TABLE food_alerts DROP CONSTRAINT ' || r.constraint_name;
  END LOOP;
  
  -- Drop existing delivery_status check
  FOR r IN (SELECT constraint_name FROM user_constraints 
            WHERE table_name = 'FOOD_ALERTS' AND constraint_type = 'C'
            AND search_condition_vc LIKE '%delivery_status%') LOOP
    EXECUTE IMMEDIATE 'ALTER TABLE food_alerts DROP CONSTRAINT ' || r.constraint_name;
  END LOOP;
END;
/

ALTER TABLE food_alerts ADD CONSTRAINT chk_fa_status 
CHECK (status IN ('AVAILABLE', 'PENDING', 'NGO_ASSIGNED', 'VOLUNTEER_ASSIGNED', 'PICKUP_STARTED', 'FOOD_PICKED_UP', 'FOOD_PICKED', 'DELIVERING', 'DELIVERED', 'COMPLETED', 'EXPIRED', 'CLAIMED'));

ALTER TABLE food_alerts ADD CONSTRAINT chk_fa_del_status 
CHECK (delivery_status IN ('PENDING', 'ACCEPTED', 'PICKUP_STARTED', 'FOOD_PICKED_UP', 'FOOD_PICKED', 'DELIVERING', 'DELIVERED', 'COMPLETED'));

COMMIT;
EXIT;
