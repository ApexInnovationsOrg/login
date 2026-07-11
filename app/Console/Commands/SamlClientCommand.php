<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Models\Organization;
use App\Models\SamlClient;
use App\Models\SamlDepartmentRule;
use App\Models\SamlOrgRule;
use App\Models\System;
use App\Saml\SamlClientManager;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class SamlClientCommand extends Command
{
    protected $signature = 'saml:client
        {action : list, describe, create, update, enable, disable, or routing}
        {slug? : client slug (all actions except list/create)}
        {--name= : display name}
        {--slug= : explicit slug (create only; defaults to slugged name)}
        {--org= : owning organization ID (exactly one of --org/--system)}
        {--system= : owning system ID (exactly one of --org/--system)}
        {--department= : default department ID (omit for the finish-account flow)}
        {--no-department : clear the default department (update only; conflicts with --department)}
        {--jit : enable just-in-time provisioning}
        {--no-jit : disable just-in-time provisioning}
        {--metadata= : path to an IdP metadata XML file}
        {--domains= : comma-separated email domains for SP-initiated SSO routing (replaces the list)}
        {--admin-portal : mark the client as asserting admin-portal (Employee) identities}
        {--no-admin-portal : clear the admin-portal marker}
        {--wizard : create a client interactively (create action only)}
        {--set= : inline JSON object with org_rules/department_rules keys (routing action only)}
        {--set-file= : path to a JSON file with org_rules/department_rules keys (routing action only)}
        {--clear : replace routing rules with empty lists (routing action only)}';

    protected $description = 'Manage SAML SSO client configurations';

    /**
     * Non-empty sentinel for the wizard's "no default department" choice.
     * Laravel Prompts' search() treats an empty-string selection as "required",
     * so the None option cannot use '' as its key.
     */
    private const NO_DEPARTMENT = 'none';

    public function handle(SamlClientManager $manager): int
    {
        try {
            return match ($this->argument('action')) {
                'list' => $this->listClients($manager),
                'create' => $this->createClient($manager),
                'describe' => $this->describeClient($manager),
                'update' => $this->updateClient($manager),
                'enable' => $this->toggle($manager, true),
                'disable' => $this->toggle($manager, false),
                'routing' => $this->routingAction($manager),
                default => $this->failWith('Unknown action. Use: list, describe, create, update, enable, disable, routing.'),
            };
        } catch (ValidationException $e) {
            foreach ($e->errors() as $messages) {
                foreach ($messages as $message) {
                    $this->error($message);
                }
            }

            return self::FAILURE;
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function listClients(SamlClientManager $manager): int
    {
        $rows = SamlClient::orderBy('name')->get()->map(function (SamlClient $client) use ($manager) {
            $cert = $manager->certificateStatus($client);

            return [
                $client->slug,
                $client->name,
                $client->enabled ? 'yes' : 'no',
                $client->jit_enabled ? 'yes' : 'no',
                $client->admin_portal ? 'yes' : '',
                ($client->ownedByOrganization() ? 'org #' : 'system #').$client->owner_id,
                $client->department_id ?? '-',
                implode(', ', $client->email_domains ?? []),
                $cert['expires_at']?->toDateString() ?? '-',
                $cert['expiring'] ? 'EXPIRING' : '',
            ];
        });

        $this->table(['Slug', 'Name', 'Enabled', 'JIT', 'Admin', 'Owner', 'Dept', 'Domains', 'Cert expires', ''], $rows->all());

        return self::SUCCESS;
    }

    private function describeClient(SamlClientManager $manager): int
    {
        $client = $this->resolveClient();
        $cert = $manager->certificateStatus($client);

        $this->line("Name: {$client->name}");
        $this->line("Slug: {$client->slug}");
        $this->line('Enabled: '.($client->enabled ? 'yes' : 'no'));
        $this->line('JIT provisioning: '.($client->jit_enabled ? 'yes' : 'no'));
        $this->line('Admin portal: '.($client->admin_portal ? 'yes' : 'no'));
        $this->line('Owner: '.$client->owner_type.' '.$client->owner_id.' ('.($client->ownerName() ?? 'unknown').')');
        $this->line('Department ID: '.($client->department_id ?? 'none (users select their department at finish-account)'));
        $this->line('Email domains: '.(implode(', ', $client->email_domains ?? []) ?: 'none (IdP-initiated only)'));
        $this->line('ACS URL: '.$client->acsUrl());
        $this->line('Metadata URL: '.$client->metadataUrl());
        $this->line('IdP Entity ID: '.$client->idp_entity_id);
        $this->line('IdP SSO URL: '.$client->idp_sso_url);
        $this->line('IdP certificate expires: '.($cert['expires_at']?->toDateString() ?? '-'));
        if ($cert['expiring']) {
            $this->warn('IdP certificate is expiring soon!');
        }
        $this->line('Org rules: '.$client->orgRules()->count());
        $this->line('Department rules: '.$client->departmentRules()->count());

        return self::SUCCESS;
    }

    private function createClient(SamlClientManager $manager): int
    {
        if ($this->option('wizard')) {
            $input = $this->runWizard();
        } else {
            $input = array_filter([
                'name' => $this->option('name'),
                'slug' => $this->option('slug'),
                'department_id' => $this->option('department'),
            ], fn ($v) => $v !== null);

            if (! $this->mergeOwnerOption($input)) {
                return $this->failWith('Provide exactly one of --org or --system.');
            }
        }

        if (! $this->option('wizard') && ($domains = $this->domainsOption()) !== null) {
            $input['email_domains'] = $domains;
        }

        if (! $this->option('wizard') && $this->option('admin-portal')) {
            $input['admin_portal'] = true;
        }

        $client = $manager->create($input);

        // --jit/--no-jit still apply to the flag path; the wizard sets jit in $input,
        // so applyCommonOptions is a no-op there (no --jit flag passed).
        $client = $this->applyCommonOptions($manager, $client);

        $this->info("Created {$client->name} ({$client->slug}). Give the customer:");
        $this->line('  ACS URL:      '.$client->acsUrl());
        $this->line('  Metadata URL: '.$client->metadataUrl());
        $this->line('  Entity ID:    '.config('saml.sp.entity_id'));
        $this->line('Then: saml:client update '.$client->slug.' --metadata=<their-metadata.xml> && saml:client enable '.$client->slug);

        return self::SUCCESS;
    }

    /**
     * Gather client-creation input interactively.
     *
     * @return array{name: string, slug: string, owner_type: string, owner_id: int,
     *               department_id: int|null, jit_enabled: bool, attribute_map?: array}
     */
    private function runWizard(): array
    {
        $name = text(
            label: 'Client display name',
            required: true,
        );

        $slug = text(
            label: 'URL slug',
            default: Str::slug($name),
            required: true,
        );

        $ownerType = select(
            label: 'Owned by',
            options: ['organization' => 'Organization', 'system' => 'System (spans its organizations)'],
            default: 'organization',
        );

        if ($ownerType === 'system') {
            $ownerId = (int) search(
                label: 'System',
                options: fn (string $value) => $this->wizardSystemOptions($value),
                placeholder: 'Type to search systems',
            );

            $departmentId = null;
        } else {
            $ownerId = (int) search(
                label: 'Organization',
                options: fn (string $value) => $this->wizardOrganizationOptions($value),
                placeholder: 'Type to search organizations',
            );

            $departmentChoice = search(
                label: 'Default department',
                options: fn (string $value) => $this->wizardDepartmentOptions($ownerId, $value),
                placeholder: 'Type to search, or choose None',
            );
            $departmentId = $departmentChoice === self::NO_DEPARTMENT ? null : (int) $departmentChoice;
        }

        $jit = confirm(
            label: 'Auto-create unknown users on first login?',
            default: true,
        );

        $input = [
            'name' => $name,
            'slug' => $slug,
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'department_id' => $departmentId,
            'jit_enabled' => $jit,
        ];

        $domains = text(
            label: 'Email domains for SSO routing (comma-separated, blank to skip)',
            default: '',
        );

        if (trim($domains) !== '') {
            $input['email_domains'] = array_values(array_filter(array_map('trim', explode(',', $domains))));
        }

        if (confirm(label: 'Customize attribute names? (needed for Entra/Azure)', default: false)) {
            $input['attribute_map'] = [
                'email' => text(label: 'Email attribute name', default: 'email', required: true),
                'first_name' => text(label: 'First name attribute name', default: 'firstName', required: true),
                'last_name' => text(label: 'Last name attribute name', default: 'lastName', required: true),
            ];
        }

        return $input;
    }

    private function updateClient(SamlClientManager $manager): int
    {
        $client = $this->resolveClient();
        if (! $client) {
            return self::FAILURE;
        }

        $fields = array_filter([
            'name' => $this->option('name'),
            'department_id' => $this->option('department'),
        ], fn ($v) => $v !== null);

        if ($this->option('org') !== null || $this->option('system') !== null) {
            if (! $this->mergeOwnerOption($fields)) {
                return $this->failWith('Provide exactly one of --org or --system.');
            }
        }

        if ($this->option('no-department') && $this->option('department') !== null) {
            return $this->failWith('Provide --department or --no-department, not both.');
        }

        if ($this->option('no-department')) {
            $fields['department_id'] = null;
        }

        if (($domains = $this->domainsOption()) !== null) {
            $fields['email_domains'] = $domains;
        }

        if ($fields !== []) {
            $client = $manager->update($client, $fields);
        }

        $client = $this->applyCommonOptions($manager, $client);

        if ($path = $this->option('metadata')) {
            if (! is_file($path)) {
                $this->error("Metadata file not found: $path");

                return self::FAILURE;
            }
            $client = $manager->updateFromIdpMetadata($client, file_get_contents($path));
            $this->info("IdP metadata applied: {$client->idp_entity_id}");
        }

        $this->info("Updated {$client->slug}.");

        return self::SUCCESS;
    }

    private function toggle(SamlClientManager $manager, bool $enabled): int
    {
        $client = $this->resolveClient();
        if (! $client) {
            return self::FAILURE;
        }

        $manager->setEnabled($client, $enabled);
        $this->info("{$client->slug} ".($enabled ? 'enabled' : 'disabled').'.');

        return self::SUCCESS;
    }

    private function routingAction(SamlClientManager $manager): int
    {
        $client = $this->resolveClient();
        if (! $client) {
            return self::FAILURE;
        }

        if ($this->option('clear') || $this->option('set') !== null || $this->option('set-file') !== null) {
            return $this->replaceRoutingRules($manager, $client);
        }

        $this->renderRoutingRules($client);

        return self::SUCCESS;
    }

    private function replaceRoutingRules(SamlClientManager $manager, SamlClient $client): int
    {
        if ($this->option('clear')) {
            $orgRules = [];
            $departmentRules = [];
        } else {
            $json = $this->option('set') !== null
                ? $this->option('set')
                : $this->readSetFile((string) $this->option('set-file'));

            if ($json === null) {
                return self::FAILURE;
            }

            $decoded = json_decode($json, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                $this->error('Invalid JSON: '.json_last_error_msg());

                return self::FAILURE;
            }

            $orgRules = $decoded['org_rules'] ?? [];
            $departmentRules = $decoded['department_rules'] ?? [];
        }

        $manager->replaceRoutingRules($client, $orgRules, $departmentRules);

        $this->info("Routing rules replaced for {$client->slug}.");

        return self::SUCCESS;
    }

    private function readSetFile(string $path): ?string
    {
        if (! is_file($path)) {
            $this->error("Routing rules file not found: $path");

            return null;
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            $this->error("Could not read routing rules file: $path");

            return null;
        }

        return $contents;
    }

    private function renderRoutingRules(SamlClient $client): void
    {
        $orgRules = $client->orgRules;
        $departmentRules = $client->departmentRules;

        if ($orgRules->isEmpty()) {
            $this->line('Org rules: none');
        } else {
            $orgNames = Organization::whereIn('ID', $orgRules->pluck('organization_id')->unique())->pluck('Name', 'ID');

            $this->line('Org rules:');
            foreach ($orgRules as $index => $rule) {
                $orgLabel = ($orgNames->get($rule->organization_id) ?? 'unknown')." ({$rule->organization_id})";
                $this->line('org '.($index + 1).'. '.$this->describeRuleSentence($rule, $orgLabel));
            }
        }

        if ($departmentRules->isEmpty()) {
            $this->line('Department rules: none');
        } else {
            $this->line('Department rules:');
            foreach ($departmentRules as $index => $rule) {
                $this->line('dept '.($index + 1).'. '.$this->describeRuleSentence($rule, "\"{$rule->department_name}\""));
            }
        }
    }

    /**
     * @param  SamlOrgRule|SamlDepartmentRule  $rule
     */
    private function describeRuleSentence($rule, string $target): string
    {
        if ($rule->isCatchAll()) {
            return 'everyone →';
        }

        $operator = str_replace('_', ' ', $rule->operator->value);

        return "{$rule->attribute} {$operator} \"{$rule->value}\" → {$target}";
    }

    private function applyCommonOptions(SamlClientManager $manager, SamlClient $client): SamlClient
    {
        if ($this->option('jit')) {
            $client = $manager->update($client, ['jit_enabled' => true]);
        }
        if ($this->option('no-jit')) {
            $client = $manager->update($client, ['jit_enabled' => false]);
        }
        if ($this->option('admin-portal')) {
            $client = $manager->update($client, ['admin_portal' => true]);
        }
        if ($this->option('no-admin-portal')) {
            $client = $manager->update($client, ['admin_portal' => false]);
        }

        return $client;
    }

    /**
     * Resolve --org/--system into an owner_type/owner_id pair and merge it
     * into $input, shared by create (where exactly one is required) and
     * update (where re-parenting is optional, but both is still an error).
     * Both call sites emit the same "Provide exactly one of --org or
     * --system." failure via failWith() when this returns false; neither
     * call site distinguishes "neither given" from "both given" in the
     * message.
     *
     * @param  array<string, mixed>  $input
     */
    private function mergeOwnerOption(array &$input): bool
    {
        $org = $this->option('org');
        $system = $this->option('system');

        if (($org === null) === ($system === null)) {
            return false;
        }

        $input += [
            'owner_type' => $org !== null ? 'organization' : 'system',
            'owner_id' => $org ?? $system,
        ];

        return true;
    }

    /**
     * Split --domains for the manager; '' clears the list, null means not passed.
     *
     * @return array<int, string>|null
     */
    private function domainsOption(): ?array
    {
        $raw = $this->option('domains');

        if ($raw === null) {
            return null;
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw)), fn ($d) => $d !== ''));
    }

    private function resolveClient(): ?SamlClient
    {
        $client = SamlClient::where('slug', $this->argument('slug'))->first();

        if (! $client) {
            $this->error('No client with slug "'.$this->argument('slug').'". Try: saml:client list');
        }

        return $client;
    }

    /**
     * @return array<int, string> Organizations.ID => Name, filtered by search.
     */
    protected function wizardOrganizationOptions(string $search): array
    {
        return Organization::query()
            ->when($search !== '', fn ($q) => $q->where('Name', 'like', '%'.$search.'%'))
            ->orderBy('Name')
            ->limit(25)
            ->pluck('Name', 'ID')
            ->all();
    }

    /**
     * @return array<int, string> Systems.ID => Name, filtered by search.
     */
    protected function wizardSystemOptions(string $search): array
    {
        return System::query()
            ->when($search !== '', fn ($q) => $q->where('Name', 'like', '%'.$search.'%'))
            ->orderBy('Name')
            ->limit(25)
            ->pluck('Name', 'ID')
            ->all();
    }

    /**
     * @return array<int|string, string> Leading 'none' => "None …", then the org's
     *                                   active departments (ID => Name) by search.
     */
    protected function wizardDepartmentOptions(int $orgId, string $search): array
    {
        $departments = Department::query()
            ->where('OrganizationID', $orgId)
            ->where('Active', 'Y')
            ->when($search !== '', fn ($q) => $q->where('Name', 'like', '%'.$search.'%'))
            ->orderBy('Name')
            ->limit(25)
            ->pluck('Name', 'ID')
            ->all();

        // Sentinel key is non-empty: Laravel Prompts' search() rejects an empty-string
        // selection as "required", so an '' key can never be chosen. Converted to null
        // in runWizard().
        return [self::NO_DEPARTMENT => 'None — users choose at finish-account'] + $departments;
    }

    // Named failWith: the base Command class already defines fail()
    private function failWith(string $message): int
    {
        $this->error($message);

        return self::FAILURE;
    }
}
