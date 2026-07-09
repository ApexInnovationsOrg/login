<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Models\Organization;
use App\Models\SamlClient;
use App\Saml\SamlClientManager;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\search;
use function Laravel\Prompts\text;

class SamlClientCommand extends Command
{
    protected $signature = 'saml:client
        {action : list, describe, create, update, enable, or disable}
        {slug? : client slug (all actions except list/create)}
        {--name= : display name}
        {--slug= : explicit slug (create only; defaults to slugged name)}
        {--org= : Apex organization ID}
        {--department= : default department ID (omit for the finish-account flow)}
        {--jit : enable just-in-time provisioning}
        {--no-jit : disable just-in-time provisioning}
        {--metadata= : path to an IdP metadata XML file}
        {--domains= : comma-separated email domains for SP-initiated SSO routing (replaces the list)}
        {--admin-portal : mark the client as asserting admin-portal (Employee) identities}
        {--no-admin-portal : clear the admin-portal marker}
        {--wizard : create a client interactively (create action only)}';

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
                default => $this->failWith('Unknown action. Use: list, describe, create, update, enable, disable.'),
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
                $client->organization_id,
                $client->department_id ?? '-',
                implode(', ', $client->email_domains ?? []),
                $cert['expires_at']?->toDateString() ?? '-',
                $cert['expiring'] ? 'EXPIRING' : '',
            ];
        });

        $this->table(['Slug', 'Name', 'Enabled', 'JIT', 'Admin', 'Org', 'Dept', 'Domains', 'Cert expires', ''], $rows->all());

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
        $this->line("Organization ID: {$client->organization_id}");
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

        return self::SUCCESS;
    }

    private function createClient(SamlClientManager $manager): int
    {
        $input = $this->option('wizard')
            ? $this->runWizard()
            : array_filter([
                'name' => $this->option('name'),
                'slug' => $this->option('slug'),
                'organization_id' => $this->option('org'),
                'department_id' => $this->option('department'),
            ], fn ($v) => $v !== null);

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
     * @return array{name: string, slug: string, organization_id: int,
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

        $organizationId = (int) search(
            label: 'Organization',
            options: fn (string $value) => $this->wizardOrganizationOptions($value),
            placeholder: 'Type to search organizations',
        );

        $departmentChoice = search(
            label: 'Default department',
            options: fn (string $value) => $this->wizardDepartmentOptions($organizationId, $value),
            placeholder: 'Type to search, or choose None',
        );
        $departmentId = $departmentChoice === self::NO_DEPARTMENT ? null : (int) $departmentChoice;

        $jit = confirm(
            label: 'Auto-create unknown users on first login?',
            default: true,
        );

        $input = [
            'name' => $name,
            'slug' => $slug,
            'organization_id' => $organizationId,
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
            'organization_id' => $this->option('org'),
            'department_id' => $this->option('department'),
        ], fn ($v) => $v !== null);

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
