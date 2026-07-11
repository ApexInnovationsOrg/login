<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    /**
     * Realistic hospital departments. unique() guards the legacy
     * UNIQUE(Name, OrganizationID) index when one test creates several
     * departments; the pool must stay comfortably larger than any single
     * test's appetite.
     */
    public const NAMES = [
        'Emergency', 'Cardiology', 'ICU', 'Radiology', 'Oncology',
        'Pediatrics', 'Surgery', 'Labor & Delivery', 'Neurology',
        'Orthopedics', 'Pharmacy', 'Laboratory', 'Respiratory Therapy',
        'Physical Therapy', 'Behavioral Health', 'Infection Control',
        'Case Management', 'Wound Care', 'Cath Lab', 'Dialysis',
        'Endoscopy', 'Anesthesiology', 'NICU', 'Telemetry',
    ];

    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'Name' => $this->faker->randomElement(self::NAMES),
            'Active' => 'Y',
            'OrganizationID' => Organization::factory(),
        ];
    }
}
