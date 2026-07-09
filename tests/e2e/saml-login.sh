#!/usr/bin/env bash
#
# End-to-end test of IdP-initiated SAML login against the mock IdP container.
#
#   1. Start IdP-initiated login at the mock IdP (SimpleSAMLphp)
#   2. Log in as its static user (user1/user1pass)
#   3. The IdP auto-POSTs a signed SAMLResponse to our ACS
#   4. We must end up authenticated, JIT-provisioned, and the legacy
#      site must recognize the session
#
set -u

LOGIN_URL="${LOGIN_URL:-http://localhost:8090}"
SITE_URL="${SITE_URL:-http://localhost:8091}"
IDP_URL="${IDP_URL:-http://localhost:8092}"

JAR="$(mktemp)"
trap 'rm -f "$JAR"' EXIT
FAILURES=0

fail() { echo "FAIL: $1"; FAILURES=$((FAILURES + 1)); }
pass() { echo "ok:   $1"; }

# 1. Kick off IdP-initiated SSO for our SP entity
SSO_START="$IDP_URL/simplesaml/saml2/idp/SSOService.php?spentityid=$LOGIN_URL/saml/metadata"
LOGIN_PAGE=$(curl -s -L -c "$JAR" -b "$JAR" "$SSO_START")

AUTH_STATE=$(echo "$LOGIN_PAGE" | grep -o 'name="AuthState" value="[^"]*"' | sed 's/.*value="//; s/"$//' | head -1)
if [ -n "$AUTH_STATE" ]; then
    pass "mock IdP presented its login form"
else
    fail "could not find AuthState on the mock IdP login form"
fi

# 2. Log in as the static test user; the IdP responds with an auto-submit form
AUTO_SUBMIT=$(curl -s -L -c "$JAR" -b "$JAR" \
    --data-urlencode "username=user1" \
    --data-urlencode "password=user1pass" \
    --data-urlencode "AuthState=$AUTH_STATE" \
    "$IDP_URL/simplesaml/module.php/core/loginuserpass.php")

SAML_RESPONSE=$(echo "$AUTO_SUBMIT" | grep -o 'name="SAMLResponse" value="[^"]*"' | sed 's/.*value="//; s/"$//' | head -1)
if [ -n "$SAML_RESPONSE" ]; then
    pass "mock IdP issued a SAMLResponse"
else
    fail "no SAMLResponse in IdP output"
fi

# 3. Deliver it to our ACS (HTML-decode the base64 payload first)
DECODED=$(python3 -c "import html,sys; print(html.unescape(sys.stdin.read()), end='')" <<<"$SAML_RESPONSE")

ACS_STATUS=$(curl -s -c "$JAR" -b "$JAR" -o /dev/null -w "%{http_code}" \
    --data-urlencode "SAMLResponse=$DECODED" \
    "$LOGIN_URL/saml/local-idp/acs")

if [ "$ACS_STATUS" = "302" ]; then
    pass "ACS accepted the assertion (302)"
else
    fail "ACS returned HTTP $ACS_STATUS (expected 302)"
fi

# 4. The legacy site must recognize the session
PAGE=$(curl -s -b "$JAR" "$SITE_URL/MyCurriculum.php")
if echo "$PAGE" | grep -qi "session has timed out"; then
    fail "legacy site does not recognize the SAML session"
else
    pass "legacy site accepted the SAML session"
fi

echo
if [ "$FAILURES" -gt 0 ]; then
    echo "$FAILURES check(s) failed"
    exit 1
fi
echo "SAML IdP-initiated E2E passed"
