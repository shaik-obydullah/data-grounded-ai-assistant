<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'organisation_name',
        'town_city',
        'county',
        'type_rating',
        'route',
        'website_url',
        'hr_phone',
        'hr_email',
        'csv_checksum',
        'csv_import_id',
        'change_type',
    ];
}
