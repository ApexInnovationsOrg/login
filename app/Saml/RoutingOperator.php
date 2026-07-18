<?php

namespace App\Saml;

/**
 * Match operators for routing rules — the single source of truth (spec:
 * docs/specs/2026-07-10-attribute-routing.md). Models cast to this enum,
 * validators use Rule::enum(), the router matches on cases, the API
 * serializes ->value; nothing compares raw operator strings.
 *
 * All operators compare case-insensitively except StrictWildcard —
 * Cloudflare's wildcard/strict-wildcard distinction, kept verbatim.
 */
enum RoutingOperator: string
{
    case Wildcard = 'wildcard';
    case StrictWildcard = 'strict_wildcard';
    case Equals = 'equals';
    case NotEquals = 'not_equals';
    case StartsWith = 'starts_with';
    case NotStartsWith = 'not_starts_with';
    case Contains = 'contains';
    case NotContains = 'not_contains';
    case EndsWith = 'ends_with';
    case NotEndsWith = 'not_ends_with';

    /**
     * SAML attributes are multi-valued: positive operators match when ANY
     * asserted value satisfies the comparison (absent attribute — an empty
     * $assertedValues — never matches); negated operators match when NO
     * asserted value satisfies the positive form (vacuously true when the
     * attribute is absent).
     */
    public function matchesAny(array $assertedValues, string $ruleValue): bool
    {
        $anySatisfies = collect($assertedValues)
            ->contains(fn ($asserted) => $this->satisfies((string) $asserted, $ruleValue));

        return $this->isNegated() ? ! $anySatisfies : $anySatisfies;
    }

    private function isNegated(): bool
    {
        return match ($this) {
            self::NotEquals, self::NotStartsWith, self::NotContains, self::NotEndsWith => true,
            default => false,
        };
    }

    private function satisfies(string $asserted, string $ruleValue): bool
    {
        return match ($this) {
            self::Wildcard => self::wildcardMatch($asserted, $ruleValue, caseSensitive: false),
            self::StrictWildcard => self::wildcardMatch($asserted, $ruleValue, caseSensitive: true),
            self::Equals, self::NotEquals => mb_strtolower($asserted) === mb_strtolower($ruleValue),
            self::StartsWith, self::NotStartsWith => str_starts_with(mb_strtolower($asserted), mb_strtolower($ruleValue)),
            self::Contains, self::NotContains => str_contains(mb_strtolower($asserted), mb_strtolower($ruleValue)),
            self::EndsWith, self::NotEndsWith => str_ends_with(mb_strtolower($asserted), mb_strtolower($ruleValue)),
        };
    }

    /**
     * Anchored *-pattern: * matches zero or more characters, everything
     * else is literal.
     */
    private static function wildcardMatch(string $asserted, string $pattern, bool $caseSensitive): bool
    {
        $regex = '/^'.str_replace('\*', '.*', preg_quote($pattern, '/')).'$/u'
            .($caseSensitive ? '' : 'i');

        // Malformed UTF-8 in $asserted makes preg_match return false (not 0/1);
        // the (bool) cast folds that into "no match" rather than throwing, by design.
        return (bool) preg_match($regex, $asserted);
    }
}
