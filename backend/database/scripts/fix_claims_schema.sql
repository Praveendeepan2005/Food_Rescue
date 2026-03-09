-- =============================================================
-- Fix Claims Table: Add Identity Column
-- =============================================================

-- 1. Drop the existing table if it exists (backup data if any? it's mostly fresh dev)
-- To be safe, we'll try to drop it.
BEGIN
   EXECUTE IMMEDIATE 'DROP TABLE claims CASCADE CONSTRAINTS';
EXCEPTION
   WHEN OTHERS THEN
      IF SQLCODE != -942 THEN
         RAISE;
      END IF;
END;
/

-- 2. Recreate with Identity
CREATE TABLE claims (
    claim_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    alert_id NUMBER NOT NULL,
    volunteer_id NUMBER NOT NULL,
    claimed_at DATE DEFAULT SYSDATE,
    picked_up_at DATE,
    completed_at DATE,
    status VARCHAR2(20) DEFAULT 'ACTIVE',
    CONSTRAINT fk_claim_alert FOREIGN KEY (alert_id) REFERENCES food_alerts(alert_id),
    CONSTRAINT fk_claim_vol FOREIGN KEY (volunteer_id) REFERENCES users(user_id)
);

COMMIT;
