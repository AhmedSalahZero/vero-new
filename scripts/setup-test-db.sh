#!/usr/bin/env bash
#
# Builds the isolated test database used by tests/Feature/Security.
#
# - Copies STRUCTURE ONLY (tables, triggers, routines — no rows) from the local
#   development database into the test database.
# - Seeds the 4 spatie roles with the fixed ids the code relies on
#   (User::getUsersWithRoles hard-codes them) and the permissions catalogue.
#
# Usage:
#   MYSQL_PWD=secret bash scripts/setup-test-db.sh
#   SRC_DB=veroanalysis TEST_DB=property_managements_test DB_USERNAME=root DB_HOST=127.0.0.1 \
#       MYSQL_PWD=secret bash scripts/setup-test-db.sh
#
# Safety: refuses to run unless TEST_DB ends with "_test" and differs from SRC_DB.
# The test DB is DROPPED and recreated. Never point this at production.

set -euo pipefail

SRC_DB="${SRC_DB:-veroanalysis}"
TEST_DB="${TEST_DB:-property_managements_test}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"

if [[ ! "$TEST_DB" =~ ^[A-Za-z0-9_]+_test$ ]]; then
	echo "Refusing: TEST_DB ('$TEST_DB') must match ^[A-Za-z0-9_]+_test\$" >&2
	exit 1
fi
if [[ ! "$SRC_DB" =~ ^[A-Za-z0-9_]+$ ]]; then
	echo "Refusing: SRC_DB ('$SRC_DB') contains invalid characters" >&2
	exit 1
fi
if [[ "$SRC_DB" == "$TEST_DB" ]]; then
	echo "Refusing: SRC_DB and TEST_DB must differ" >&2
	exit 1
fi
if [[ -z "${MYSQL_PWD:-}" ]]; then
	echo "MYSQL_PWD must be set (it is passed to mysql via the environment, never on the command line)" >&2
	exit 1
fi
export MYSQL_PWD

MYSQL=(mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME")
MYSQLDUMP=(mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME")

echo "Recreating database $TEST_DB ..."
"${MYSQL[@]}" -e "DROP DATABASE IF EXISTS \`$TEST_DB\`; CREATE DATABASE \`$TEST_DB\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "Copying structure (no data) from $SRC_DB ..."
"${MYSQLDUMP[@]}" --no-data --triggers --routines --skip-events --single-transaction \
	--no-tablespaces --skip-comments --set-gtid-purged=OFF "$SRC_DB" \
	| sed -E 's/DEFINER=`[^`]+`@`[^`]+`//g; s/ AUTO_INCREMENT=[0-9]+//g' \
	| "${MYSQL[@]}" "$TEST_DB"

echo "Seeding roles and permissions ..."
"${MYSQL[@]}" "$TEST_DB" <<SQL
INSERT INTO roles (id, name, scope, guard_name, created_at, updated_at) VALUES
	(1, 'super-admin', NULL, 'web', NOW(), NOW()),
	(2, 'company-admin', 'admin', 'web', NOW(), NOW()),
	(3, 'user', 'admin', 'web', NOW(), NOW()),
	(4, 'manager', 'admin', 'web', NOW(), NOW());
INSERT INTO permissions (id, name, guard_name, created_at, updated_at)
	SELECT id, name, guard_name, created_at, updated_at FROM \`$SRC_DB\`.permissions;
INSERT INTO migrations SELECT * FROM \`$SRC_DB\`.migrations;
SQL

TABLES=$("${MYSQL[@]}" -N -e "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='$TEST_DB'")
TRIGGERS=$("${MYSQL[@]}" -N -e "SELECT COUNT(*) FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA='$TEST_DB'")
PERMS=$("${MYSQL[@]}" -N -e "SELECT COUNT(*) FROM \`$TEST_DB\`.permissions")
echo "Done: $TABLES tables, $TRIGGERS triggers, $PERMS permissions in $TEST_DB."
