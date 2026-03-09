SET LINESIZE 200
COL status FOR A20
COL delivery_status FOR A20
SELECT alert_id, status, delivery_status FROM food_alerts;
SELECT alert_id, status FROM claims;
EXIT;
