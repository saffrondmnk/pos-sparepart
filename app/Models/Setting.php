<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'company_name',
        'receipt_title',
        'logo_path',
        'app_title',
        'receipt_address',
        'receipt_phone',
    ];

    public static function getSettings(): self
    {
        return self::firstOrCreate([], [
            'company_name' => 'Car Spare Parts POS',
            'receipt_title' => 'Car Spare Parts POS',
            'app_title' => 'Laravel',
        ]);
    }
}
