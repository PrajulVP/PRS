<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'name',
        'description',
        'slug',
        'value',
    ];

    /**
     * Helper to get setting value by slug with optional default
     */
    public static function getValue(string $slug, $default = null)
    {
        $setting = static::where('slug', $slug)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Helper to set setting value by slug
     */
    public static function setValue(string $slug, $value, string $name = null, string $description = null)
    {
        return static::updateOrCreate(
            ['slug' => $slug],
            ['value' => $value, 'name' => $name ?? $slug, 'description' => $description]
        );
    }
}
