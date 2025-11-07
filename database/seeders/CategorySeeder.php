<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Food & Groceries',
                'description' => 'Groceries, restaurants, food delivery',
                'color' => '#e74c3c',
                'icon' => '🍔',
                'isActive' => true,
            ],
            [
                'name' => 'Transportation',
                'description' => 'Gas, public transport, taxi, car maintenance',
                'color' => '#3498db',
                'icon' => '🚗',
                'isActive' => true,
            ],
            [
                'name' => 'Utilities',
                'description' => 'Electricity, water, gas, internet, phone',
                'color' => '#f39c12',
                'icon' => '💡',
                'isActive' => true,
            ],
            [
                'name' => 'Entertainment',
                'description' => 'Movies, games, hobbies, subscriptions',
                'color' => '#9b59b6',
                'icon' => '🎮',
                'isActive' => true,
            ],
            [
                'name' => 'Healthcare',
                'description' => 'Medical expenses, medicines, insurance',
                'color' => '#2ecc71',
                'icon' => '⚕️',
                'isActive' => true,
            ],
            [
                'name' => 'Education',
                'description' => 'Tuition, books, courses, training',
                'color' => '#1abc9c',
                'icon' => '📚',
                'isActive' => true,
            ],
            [
                'name' => 'Shopping',
                'description' => 'Clothing, electronics, home goods',
                'color' => '#e91e63',
                'icon' => '🛍️',
                'isActive' => true,
            ],
            [
                'name' => 'Housing',
                'description' => 'Rent, mortgage, maintenance',
                'color' => '#795548',
                'icon' => '🏠',
                'isActive' => true,
            ],
            [
                'name' => 'Salary',
                'description' => 'Monthly salary and wages',
                'color' => '#4caf50',
                'icon' => '💰',
                'isActive' => true,
            ],
            [
                'name' => 'Business',
                'description' => 'Business income and profits',
                'color' => '#ff9800',
                'icon' => '💼',
                'isActive' => true,
            ],
            [
                'name' => 'Investments',
                'description' => 'Investment returns, dividends',
                'color' => '#00bcd4',
                'icon' => '📈',
                'isActive' => true,
            ],
            [
                'name' => 'Other',
                'description' => 'Miscellaneous expenses and income',
                'color' => '#607d8b',
                'icon' => '📌',
                'isActive' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        $this->command->info('Categories seeded successfully!');
    }
}

