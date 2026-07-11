<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    /**
     * Realistic hospital department names. definition() picks a random
     * (non-unique) name; per-org uniqueness of department names is
     * guaranteed only by OrganizationFactory::withDepartments(), which
     * sequences distinct names. Bare multi-department creates against
     * one org must pass explicit Name values.
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
