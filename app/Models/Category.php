<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'color',
        'icon',
        'isActive',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'isActive' => 'boolean',
            'createdAt' => 'datetime',
            'updatedAt' => 'datetime',
        ];
    }

    /**
     * Validation rules
     *
     * @return array
     */
    public static function validationRules($isUpdate = false): array
    {
        $nameRule = $isUpdate ? 'sometimes|' : '';
        
        return [
            'name' => $nameRule . 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:255',
            'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
            'icon' => 'nullable|string|max:10',
            'isActive' => 'nullable|boolean',
        ];
    }
}

