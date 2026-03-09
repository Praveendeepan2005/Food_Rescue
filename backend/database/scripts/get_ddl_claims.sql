SET LONG 20000;
SELECT dbms_metadata.get_ddl('TABLE', 'CLAIMS') FROM dual;
EXIT;
