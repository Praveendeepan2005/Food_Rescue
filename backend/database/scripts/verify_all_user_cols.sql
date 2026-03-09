SET LINESIZE 200
COLUMN column_name FORMAT A30
SELECT column_name FROM user_tab_columns WHERE table_name = 'USERS' 
AND column_name IN ('NAME', 'EMAIL', 'PASSWORD', 'ROLE', 'PHONE', 'CITY', 'LATITUDE', 'LONGITUDE', 'BELONGS_TO_NGO_ID');
EXIT;
