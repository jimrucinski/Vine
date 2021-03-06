DECLARE 
  "counter" NUMBER;
BEGIN
  SELECT COUNT(*) INTO "counter" FROM DBA_ROLES WHERE ROLE = 'SCROLE';
  
  IF "counter" = 0 THEN
    EXECUTE IMMEDIATE 'CREATE ROLE scRole';
  END IF;
END;
/
GRANT CREATE SESSION TO SCROLE
/
GRANT CREATE TABLE TO SCROLE
/
GRANT CREATE VIEW TO SCROLE
/
GRANT CREATE MATERIALIZED VIEW TO SCROLE
/
GRANT CREATE PROCEDURE TO SCROLE
/
GRANT CREATE SYNONYM TO SCROLE
/
GRANT CREATE DATABASE LINK TO SCROLE
/
GRANT CREATE SEQUENCE TO SCROLE
/
GRANT CREATE TRIGGER TO SCROLE
/
GRANT CREATE JOB TO SCROLE
/
exit

