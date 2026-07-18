<?php

namespace Tests\Unit;

use App\Saml\RoutingOperator;
use PHPUnit\Framework\TestCase;

class RoutingOperatorTest extends TestCase
{
    public function test_equals_is_case_insensitive_and_matches_any_value(): void
    {
        $this->assertTrue(RoutingOperator::Equals->matchesAny(['Radiology', 'ICU'], 'icu'));
        $this->assertFalse(RoutingOperator::Equals->matchesAny(['ICU-North'], 'icu'));
    }

    public function test_not_equals_requires_no_value_to_match(): void
    {
        $this->assertFalse(RoutingOperator::NotEquals->matchesAny(['ICU', 'ER'], 'icu'));
        $this->assertTrue(RoutingOperator::NotEquals->matchesAny(['ER'], 'icu'));
        // Vacuous: absent attribute (no values) satisfies every negated operator
        $this->assertTrue(RoutingOperator::NotEquals->matchesAny([], 'icu'));
    }

    public function test_positive_operators_never_match_absent_attribute(): void
    {
        foreach ([RoutingOperator::Equals, RoutingOperator::Contains, RoutingOperator::StartsWith,
            RoutingOperator::EndsWith, RoutingOperator::Wildcard, RoutingOperator::StrictWildcard] as $op) {
            $this->assertFalse($op->matchesAny([], 'x'), $op->value);
        }
    }

    public function test_contains_starts_ends_and_negations(): void
    {
        $this->assertTrue(RoutingOperator::Contains->matchesAny(['CN=ICU-Nurses,OU=Clinical'], 'icu-nurses'));
        $this->assertFalse(RoutingOperator::NotContains->matchesAny(['CN=ICU-Nurses'], 'icu'));
        $this->assertTrue(RoutingOperator::StartsWith->matchesAny(['DEPT-042'], 'dept-'));
        $this->assertFalse(RoutingOperator::NotStartsWith->matchesAny(['DEPT-042'], 'DEPT'));
        $this->assertTrue(RoutingOperator::EndsWith->matchesAny(['team-nursing'], 'NURSING'));
        $this->assertTrue(RoutingOperator::NotEndsWith->matchesAny(['team-nursing'], 'admin'));
    }

    public function test_wildcard_is_anchored_and_case_insensitive(): void
    {
        $this->assertTrue(RoutingOperator::Wildcard->matchesAny(['CN=ICU-Nurses,OU=Clinical'], 'cn=icu-*'));
        $this->assertFalse(RoutingOperator::Wildcard->matchesAny(['XCN=ICU-Nurses'], 'CN=*')); // anchored
        $this->assertTrue(RoutingOperator::Wildcard->matchesAny(['anything at all'], '*'));
        // Literal regex metacharacters must not leak through preg_quote
        $this->assertTrue(RoutingOperator::Wildcard->matchesAny(['a.b+c'], 'a.b+c'));
        $this->assertFalse(RoutingOperator::Wildcard->matchesAny(['aXb+c'], 'a.b+c'));
    }

    public function test_strict_wildcard_is_case_sensitive(): void
    {
        $this->assertTrue(RoutingOperator::StrictWildcard->matchesAny(['CN=ICU-01'], 'CN=ICU-*'));
        $this->assertFalse(RoutingOperator::StrictWildcard->matchesAny(['cn=icu-01'], 'CN=ICU-*'));
    }
}
