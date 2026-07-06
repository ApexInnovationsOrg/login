<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Models\Organization;
use App\Models\SamlClient;
use App\Saml\SamlClientManager;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class SamlClientCommand extends Command
{
    protected $signature = 'saml:client
        {action : list, create, update, enable, or disable}
        {slug? : client slug (all actions except list/create)}
        {--name= : display name}
        {--slug= : explicit slug (create only; defaults to slugged name)}
        {--org= : Apex organization ID}
        {--department= : default department ID (omit for the finish-account flow)}
        {--jit : enable just-in-time provisioning}
        {--no-jit : disable just-in-time provisioning}
        {--metadata= : path to an IdP metadata XML file}';

    protected $description = 'Manage SAML SSO client configurations';

    public function handle(SamlClientManager $manager): int
    {
        try {
            return match ($this->argument('action')) {
                'list' => $this->listClients($manager),
                'create' => $this->createClient($manager),
                'update' => $this->updateClient($manager),
                'enable' => $this->toggle($manager, true),
                'disable' => $this->toggle($manager, false),
                default => $this->failWith('Unknown action. Use: list, create, update, enable, disable.'),
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
                $client->organization_id,
                $client->department_id ?? '-',
                $cert['expires_at']?->toDateString() ?? '-',
                $cert['expiring'] ? 'EXPIRING' : '',
            ];
        });

        $this->table(['Slug', 'Name', 'Enabled', 'JIT', 'Org', 'Dept', 'Cert expires', ''], $rows->all());

        return self::SUCCESS;
    }

    private function createClient(SamlClientManager $manager): int
    {
        $client = $manager->create(array_filter([
            'name' => $this->option('name'),
            'slug' => $this->option('slug'),
            'organization_id' => $this->option('org'),
            'department_id' => $this->option('department'),
        ], fn ($v) => $v !== null));

        $client = $this->applyCommonOptions($manager, $client);

        $this->info("Created {$client->name} ({$client->slug}). Give the customer:");
        $this->line('  ACS URL:      '.$client->acsUrl());
        $this->line('  Metadata URL: '.$client->metadataUrl());
        $this->line('  Entity ID:    '.config('saml.sp.entity_id'));
        $this->line('Then: saml:client update '.$client->slug.' --metadata=<their-metadata.xml> && saml:client enable '.$client->slug);

        return self::SUCCESS;
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

        return $client;
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
     * @return array<int|string, string> Leading '' => "None …", then the org's
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

        return ['' => 'None — users choose at finish-account'] + $departments;
    }

    // Named failWith: the base Command class already defines fail()
    private function failWith(string $message): int
    {
        $this->error($message);

        return self::FAILURE;
    }
}
