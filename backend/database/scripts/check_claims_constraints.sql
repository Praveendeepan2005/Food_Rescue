SET LONG 20000
SELECT search_condition FROM user_constraints WHERE table_name = 'CLAIMS' AND constraint_type = 'C';
EXIT;
