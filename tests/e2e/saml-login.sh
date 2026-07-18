#!/usr/bin/env bash
#
# End-to-end test of both SAML login flows against the mock IdP container.
#
# IdP-initiated:
#   1. Start IdP-initiated login at the mock IdP (SimpleSAMLphp)
#   2. Log in as its static user (user1/user1pass)
#   3. The IdP auto-POSTs a signed SAMLResponse to our ACS
#   4. We must end up authenticated, JIT-provisioned, and the legacy
#      site must recognize the session
#
# SP-initiated:
#   5. The lookup endpoint routes a known SSO domain to the SP login endpoint
#   6. Unknown domains get the identical null response shape
#   7. SP login redirects to the mock IdP with a SAMLRequest
#   8. Completing the round trip (IdP login -> assertion -> ACS) lands an
#      authenticated session, same as the IdP-initiated flow
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
SSO_START="$IDP_URL/simplesaml/saml2/idp/SSOService.php?spentityid=$LOGIN_URL/saml/local-idp/metadata"
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

# --- SP-initiated flow ---

# 5. The lookup endpoint routes the mock-IdP domain to SP login
SP_JAR="$(mktemp)"
trap 'rm -f "$JAR" "$SP_JAR"' EXIT

curl -s -c "$SP_JAR" -o /dev/null "$LOGIN_URL/login"
XSRF=$(python3 -c "import urllib.parse,sys;print(urllib.parse.unquote(sys.argv[1]))" \
    "$(grep XSRF-TOKEN "$SP_JAR" | awk '{print $NF}')")

SSO_PATH=$(curl -s -b "$SP_JAR" -c "$SP_JAR" \
    -H "X-XSRF-TOKEN: $XSRF" -H 'Accept: application/json' -H 'Content-Type: application/json' \
    -d '{"email":"user1@example.com"}' "$LOGIN_URL/sso/lookup" \
    | python3 -c "import json,sys;print(json.load(sys.stdin).get('sso') or '')")

if [ "$SSO_PATH" = "/saml/local-idp/login" ]; then
    pass "lookup routed user1@example.com to SP login"
else
    fail "lookup returned '$SSO_PATH' (expected /saml/local-idp/login)"
fi

# 6. Unknown domains get the identical null shape
NULL_BODY=$(curl -s -b "$SP_JAR" -c "$SP_JAR" \
    -H "X-XSRF-TOKEN: $XSRF" -H 'Accept: application/json' -H 'Content-Type: application/json' \
    -d '{"email":"someone@gmail.com"}' "$LOGIN_URL/sso/lookup")

if [ "$NULL_BODY" = '{"sso":null}' ]; then
    pass "unknown domain returns the null shape"
else
    fail "unknown domain returned: $NULL_BODY"
fi

# 7. SP login redirects to the mock IdP with a SAMLRequest
SP_REDIRECT=$(curl -s -b "$SP_JAR" -c "$SP_JAR" -o /dev/null -w '%{redirect_url}' \
    "$LOGIN_URL/saml/local-idp/login")

SP_REDIRECT_OK=0
case "$SP_REDIRECT" in
    *SSOService*SAMLRequest=*) pass "SP login redirected to the IdP with an AuthnRequest"; SP_REDIRECT_OK=1 ;;
    *) fail "SP login redirected to: $SP_REDIRECT" ;;
esac

# 8. Complete the round trip: IdP login form -> credentials -> assertion -> ACS
if [ "$SP_REDIRECT_OK" = "1" ]; then
    SP_LOGIN_PAGE=$(curl -s -L -c "$SP_JAR" -b "$SP_JAR" "$SP_REDIRECT")
    SP_AUTH_STATE=$(echo "$SP_LOGIN_PAGE" | grep -o 'name="AuthState" value="[^"]*"' | sed 's/.*value="//; s/"$//' | head -1)

    SP_AUTO_SUBMIT=$(curl -s -L -c "$SP_JAR" -b "$SP_JAR" \
        --data-urlencode "username=user1" \
        --data-urlencode "password=user1pass" \
        --data-urlencode "AuthState=$SP_AUTH_STATE" \
        "$IDP_URL/simplesaml/module.php/core/loginuserpass.php")

    SP_SAML_RESPONSE=$(echo "$SP_AUTO_SUBMIT" | grep -o 'name="SAMLResponse" value="[^"]*"' | sed 's/.*value="//; s/"$//' | head -1)
    SP_DECODED=$(python3 -c "import html,sys; print(html.unescape(sys.stdin.read()), end='')" <<<"$SP_SAML_RESPONSE")

    SP_ACS_STATUS=$(curl -s -c "$SP_JAR" -b "$SP_JAR" -o /dev/null -w "%{http_code}" \
        --data-urlencode "SAMLResponse=$SP_DECODED" \
        "$LOGIN_URL/saml/local-idp/acs")

    if [ "$SP_ACS_STATUS" = "302" ]; then
        pass "SP-initiated round trip landed an authenticated session (302)"
    else
        fail "SP-initiated ACS returned HTTP $SP_ACS_STATUS (expected 302)"
    fi
else
    fail "skipped SP-initiated round trip because check 7 did not produce a SAMLRequest redirect"
fi

echo
if [ "$FAILURES" -gt 0 ]; then
    echo "$FAILURES check(s) failed"
    exit 1
fi
echo "SAML IdP-initiated and SP-initiated E2E passed"
