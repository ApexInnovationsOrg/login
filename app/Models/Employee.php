<?php

namespace App\Models;

/**
 * Apex staff — the admin portal's auth table (website_admin/doLogon.php).
 * Read-only from this app's perspective: SSO matches rows, never writes them.
 */
class Employee extends LegacyModel
{
    protected $table = 'Employees';

    public $timestamps = false;
}
