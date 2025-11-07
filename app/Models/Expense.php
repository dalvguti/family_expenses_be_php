<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'description',
        'amount',
        'category',
        'date',
        'paidBy',
        'transactionType',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'datetime',
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
        $required = $isUpdate ? 'sometimes|' : '';
        
        return [
            'description' => $required . 'required|string|max:255',
            'amount' => $required . 'required|numeric|min:0',
            'category' => $required . 'required|string|max:255',
            'date' => 'nullable|date',
            'paidBy' => $required . 'required|string|max:255',
            'transactionType' => 'nullable|in:expense,earning',
        ];
    }
}

