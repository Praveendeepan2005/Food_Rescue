SET LINESIZE 200
COL owner FOR A20
COL table_name FOR A20
SELECT owner, table_name FROM all_tables WHERE table_name IN ('FOOD_ALERTS', 'USERS', 'CLAIMS');
EXIT;
