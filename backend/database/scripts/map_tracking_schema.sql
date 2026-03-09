-- =============================================================
-- map_tracking_schema.sql
-- Add tracking support for Swiggy/Zomato style delivery
-- =============================================================

-- 1. Add tracking columns to FOOD_ALERTS table
-- We use these 6 columns as requested for Donor, NGO, and Volunteer coordinates
ALTER TABLE food_alerts ADD (
    ngo_lat          NUMBER(10, 7),
    ngo_lng          NUMBER(10, 7),
    vol_lat          NUMBER(10, 7),
    vol_lng          NUMBER(10, 7),
    delivery_status  VARCHAR2(20) DEFAULT 'PENDING' 
                        CHECK (delivery_status IN ('PENDING', 'ACCEPTED', 'PICKUP_STARTED', 'FOOD_PICKED', 'DELIVERING', 'DELIVERED')),
    track_distance   VARCHAR2(20),
    track_eta        VARCHAR2(20)
);

-- Note: donor_lat and donor_lng already exist in food_alerts as LATITUDE and LONGITUDE.
-- We will use those existing columns fordonor position to avoid redundancy.

-- 2. Add assigned_vol_id to food_alerts for direct tracking mapping
ALTER TABLE food_alerts ADD assigned_vol_id NUMBER;
ALTER TABLE food_alerts ADD CONSTRAINT fk_alert_vol FOREIGN KEY (assigned_vol_id) REFERENCES users(user_id);

-- Optional: Update NGO locations in sample data if needed
UPDATE users SET latitude = 12.972442, longitude = 77.580643 WHERE role = 'NGO' AND email = 'ngo@test.com';

COMMIT;
