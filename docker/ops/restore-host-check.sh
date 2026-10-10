#!/bin/bash
set -euo pipefail
sql='SELECT table_schema,COUNT(*) FROM information_schema.tables WHERE table_schema NOT IN ("mysql","sys","performance_schema","information_schema") GROUP BY table_schema;'
echo 'Host source schema/table counts:'
mysql -uroot -N -B -e "$sql"
echo 'Isolated restored schema/table counts:'
docker exec partsmall-restore-validation-20261010 mysql -uroot -N -B -e "$sql"
echo 'Isolated database integrity check:'
checks='SELECT CONCAT("CHECK TABLE `",REPLACE(table_schema,"`","``"),"`.`",REPLACE(table_name,"`","``"),"` QUICK;") FROM information_schema.tables WHERE table_schema IN ("cheragh_bot","partsmall","ruby100","yadakgate_agent","yadakgate_price") AND table_type="BASE TABLE";'
docker exec partsmall-restore-validation-20261010 mysql -uroot -N -B -e "$checks" |
  docker exec -i partsmall-restore-validation-20261010 mysql -uroot -N -B |
  awk -F '\t' '$3 == "status" && $4 == "OK" { checked++; next } { failed++; print } END { print "Tables checked:", checked; if (failed || !checked) exit 1 }'
