<?php

namespace App\Saml;

use App\Models\SamlAttributeObservation;
use App\Models\SamlClient;
use Illuminate\Support\Facades\Log;

/**
 * Records which attribute NAMES an IdP asserts, per client, so the routing
 * rule editor can offer real attributes instead of hand-typed strings.
 * Names only — values are PHI and are never read or stored (spec:
 * docs/specs/2026-07-11-known-attributes.md). Runs on the login hot path
 * after the assertion is validated; any failure is swallowed so it can
 * never break a login.
 */
class KnownAttributeCollector
{
    public function capture(SamlClient $client, array $attributeNames): void
    {
        try {
            // Admin-portal clients assert Employee identities, not routing
            // attributes — nothing to capture.
            if ($client->admin_portal) {
                return;
            }

            // Exclude the identity attributes (the attribute_map's VALUES);
            // they're already handled by the fixed map and would be dropdown noise.
            $identity = array_values($client->attribute_map ?? []);
            $candidates = array_values(array_diff($attributeNames, $identity));

            if ($candidates === []) {
                return;
            }

            $now = now();

            $existingNames = SamlAttributeObservation::query()
                ->where('saml_client_id', $client->id)
                ->whereIn('name', $candidates)
                ->pluck('name')
                ->all();

            SamlAttributeObservation::upsert(
                array_map(fn (string $name) => [
                    'saml_client_id' => $client->id,
                    'name' => $name,
                    'first_seen_at' => $now,
                    'last_seen_at' => $now,
                    'observation_count' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $candidates),
                ['saml_client_id', 'name'],       // unique-by (matches the migration's unique index)
                ['last_seen_at', 'updated_at'],   // on duplicate: bump last_seen only; first_seen_at & count untouched
            );

            if ($existingNames !== []) {
                SamlAttributeObservation::query()
                    ->where('saml_client_id', $client->id)
                    ->whereIn('name', $existingNames)
                    ->increment('observation_count', 1, ['updated_at' => $now]);
            }

            $known = $client->known_attributes;
            $fresh = array_values(array_diff($candidates, $known));

            if ($fresh !== []) {
                $client->known_attributes = array_values(array_unique(array_merge($known, $fresh)));
                $client->save();
            }
        } catch (\Throwable $e) {
            Log::warning('known-attribute capture failed', [
                'client' => $client->slug,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
