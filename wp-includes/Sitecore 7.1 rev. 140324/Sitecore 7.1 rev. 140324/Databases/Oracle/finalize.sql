UPDATE "RolesInRoles"
SET
  "ApplicationName" = '1C3E8077ADB940168F06F27ED5695F3B'
WHERE
  "ApplicationName" IS NULL
/
BEGIN
  FOR myschema IN ( SELECT SYS_CONTEXT('userenv','current_schema') n FROM DUAL )
  LOOP
    FOR ut IN (SELECT * FROM USER_CATALOG WHERE TABLE_TYPE = 'TABLE')
    LOOP
     dbms_output.put_line(myschema.n || ' - ' || ut.TABLE_NAME);
     EXECUTE IMMEDIATE 'ALTER TABLE "' || ut.TABLE_NAME || '" RENAME TO T';
     DBMS_STATS.GATHER_TABLE_STATS(myschema.n, 'T');
     EXECUTE IMMEDIATE 'ALTER TABLE T RENAME TO "' || ut.TABLE_NAME || '"';
    END LOOP;
  END LOOP;
END;
/
exit
