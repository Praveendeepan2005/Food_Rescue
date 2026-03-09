-- Final Synchronization
UPDATE food_alerts fa
SET fa.status = (SELECT c.status FROM claims c WHERE c.alert_id = fa.alert_id AND c.status != 'CANCELLED')
WHERE EXISTS (SELECT 1 FROM claims c WHERE c.alert_id = fa.alert_id AND c.status != 'CANCELLED');

UPDATE food_alerts SET delivery_status = status WHERE delivery_status = 'PENDING' AND status IN ('PICKUP_STARTED', 'FOOD_PICKED_UP', 'DELIVERING', 'DELIVERED', 'COMPLETED');

COMMIT;
EXIT;
