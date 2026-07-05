#!/usr/bin/env bash
#
# End-to-end test of the login -> main-site session handoff.
#
# Exercises the full production topology locally:
#   1. POST /login on the Laravel app (expects the Inertia 409 handoff)
#   2. The ApexInnovations session cookie is written to a jar
#   3. GET MyCurriculum.php on the legacy site with that cookie
#   4. The legacy site must recognize the session (via shared Redis)
#      and render the logged-in user
#
# Requires the docker compose stack (laravel.test, website, mysql, redis)
# and a seeded database: docker compose exec laravel.test php artisan migrate:fresh --seed
#
set -u

LOGIN_URL="${LOGIN_URL:-http://localhost:8090}"
SITE_URL="${SITE_URL:-http://localhost:8091}"
E2E_EMAIL="${E2E_EMAIL:-dev@example.com}"
E2E_PASSWORD="${E2E_PASSWORD:-password}"
E2E_FIRSTNAME="${E2E_FIRSTNAME:-Dev}"

JAR="$(mktemp)"
trap 'rm -f "$JAR"' EXIT
FAILURES=0

fail() { echo "FAIL: $1"; FAILURES=$((FAILURES + 1)); }
pass() { echo "ok:   $1"; }

# 1. Anonymous visitors must NOT be treated as logged in
if curl -s "$SITE_URL/MyCurriculum.php" | grep -qi "session has timed out"; then
    pass "anonymous visit shows the timed-out prompt"
else
    fail "anonymous visit did not show the timed-out prompt"
fi

# 2. Log in on the Laravel app
curl -s -c "$JAR" "$LOGIN_URL/login" -o /dev/null
XSRF=$(awk '/XSRF-TOKEN/ {print $7}' "$JAR" | python3 -c "import sys,urllib.parse; print(urllib.parse.unquote(sys.stdin.read().strip()))")

STATUS=$(curl -s -b "$JAR" -c "$JAR" \
    -H "X-Inertia: true" \
    -H "X-XSRF-TOKEN: $XSRF" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d "{\"email\":\"$E2E_EMAIL\",\"password\":\"$E2E_PASSWORD\"}" \
    -o /dev/null -w "%{http_code}" "$LOGIN_URL/login")

if [ "$STATUS" = "409" ]; then
    pass "login returned the Inertia handoff (409)"
else
    fail "login returned HTTP $STATUS (expected 409)"
fi

if grep -q "ApexInnovations" "$JAR"; then
    pass "ApexInnovations session cookie was set"
else
    fail "ApexInnovations session cookie missing from jar"
fi

# 3. The legacy site must recognize the session
PAGE=$(curl -s -b "$JAR" "$SITE_URL/MyCurriculum.php")

if echo "$PAGE" | grep -qi "session has timed out"; then
    fail "legacy site still shows the timed-out prompt with a valid session"
else
    pass "legacy site accepted the session"
fi

if echo "$PAGE" | grep -q "firstName: \"$E2E_FIRSTNAME\""; then
    pass "legacy site rendered the logged-in user ($E2E_FIRSTNAME)"
else
    fail "legacy site did not render the logged-in user"
fi

if echo "$PAGE" | grep -q "</html>"; then
    pass "MyCurriculum rendered to completion"
else
    fail "MyCurriculum output is truncated (check docker logs login-website-1)"
fi

# 4. The legacy site's logout link must point at THIS login app, not production
if echo "$PAGE" | grep -q "$LOGIN_URL/auth/logout"; then
    pass "logout link points at the local login app"
else
    fail "logout link does not point at $LOGIN_URL (production leak?)"
fi

# 5. Logging out must invalidate the session on both sides
curl -s -b "$JAR" -c "$JAR" -o /dev/null "$LOGIN_URL/auth/logout"
if curl -s -b "$JAR" "$SITE_URL/MyCurriculum.php" | grep -qi "session has timed out"; then
    pass "logout invalidated the shared session"
else
    fail "legacy site still recognizes the session after logout"
fi

echo
if [ "$FAILURES" -gt 0 ]; then
    echo "$FAILURES check(s) failed"
    exit 1
fi
echo "session handoff E2E passed"
