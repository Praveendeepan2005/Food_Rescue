SET LINESIZE 200
SET PAGESIZE 100
COL column_name FOR A30
COL data_type FOR A20
SELECT column_name, data_type, data_length FROM user_tab_columns WHERE table_name = 'FOOD_ALERTS';
SELECT column_name, data_type, data_length FROM user_tab_columns WHERE table_name = 'CLAIMS';
EXIT;
