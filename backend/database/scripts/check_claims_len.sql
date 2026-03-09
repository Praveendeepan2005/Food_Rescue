SET LINESIZE 100;
COL column_name FOR A20;
COL data_type FOR A15;
SELECT column_name, data_type, data_length FROM user_tab_columns WHERE table_name = 'CLAIMS';
EXIT;
