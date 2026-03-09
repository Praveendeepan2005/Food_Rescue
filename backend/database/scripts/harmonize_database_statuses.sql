-- =============================================================
-- Database Cleanup: Status Harmonization
-- =============================================================

-- Harmonize food_alerts statuses
UPDATE food_alerts SET status = 'FOOD_PICKED_UP' WHERE status = 'PICKED_UP';
UPDATE food_alerts SET status = 'COMPLETED' WHERE status = 'DELIVERED';
UPDATE food_alerts SET status = 'VOLUNTEER_ASSIGNED' WHERE status = 'ACCEPTED';

-- Harmonize claims statuses
UPDATE claims SET status = 'FOOD_PICKED_UP' WHERE status = 'PICKED_UP';
UPDATE claims SET status = 'COMPLETED' WHERE status = 'DELIVERED';
UPDATE claims SET status = 'PICKUP_STARTED' WHERE status = 'ON_THE_WAY';

COMMIT;
EXIT;
