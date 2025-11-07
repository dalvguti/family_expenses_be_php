<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Get monthly report
     * GET /api/reports/monthly
     */
    public function monthly(Request $request)
    {
        if (!$request->has('year') || !$request->has('month')) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide year and month',
            ], 400);
        }

        try {
            $year = $request->year;
            $month = $request->month;

            $startDate = date('Y-m-d 00:00:00', strtotime("$year-$month-01"));
            $endDate = date('Y-m-t 23:59:59', strtotime("$year-$month-01"));

            // Get all expenses for the month
            $transactions = Expense::whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'ASC')
                ->get();

            // Total expenses
            $totalExpensesData = Expense::selectRaw('SUM(amount) as total, COUNT(id) as count')
                ->where('transactionType', 'expense')
                ->whereBetween('date', [$startDate, $endDate])
                ->first();

            // Total earnings
            $totalEarningsData = Expense::selectRaw('SUM(amount) as total, COUNT(id) as count')
                ->where('transactionType', 'earning')
                ->whereBetween('date', [$startDate, $endDate])
                ->first();

            // Expenses by category
            $expensesByCategory = Expense::select('category')
                ->selectRaw('SUM(amount) as total')
                ->selectRaw('COUNT(id) as count')
                ->where('transactionType', 'expense')
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy('category')
                ->orderByDesc('total')
                ->get()
                ->map(function ($item) {
                    return [
                        'category' => $item->category,
                        'total' => (float)$item->total,
                        'count' => (int)$item->count,
                    ];
                });

            // Earnings by category
            $earningsByCategory = Expense::select('category')
                ->selectRaw('SUM(amount) as total')
                ->selectRaw('COUNT(id) as count')
                ->where('transactionType', 'earning')
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy('category')
                ->orderByDesc('total')
                ->get()
                ->map(function ($item) {
                    return [
                        'category' => $item->category,
                        'total' => (float)$item->total,
                        'count' => (int)$item->count,
                    ];
                });

            // Expenses by person
            $expensesByPerson = Expense::select('paidBy')
                ->selectRaw('SUM(amount) as total')
                ->selectRaw('COUNT(id) as count')
                ->where('transactionType', 'expense')
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy('paidBy')
                ->orderByDesc('total')
                ->get()
                ->map(function ($item) {
                    return [
                        'paidBy' => $item->paidBy,
                        'total' => (float)$item->total,
                        'count' => (int)$item->count,
                    ];
                });

            // Earnings by person
            $earningsByPerson = Expense::select('paidBy')
                ->selectRaw('SUM(amount) as total')
                ->selectRaw('COUNT(id) as count')
                ->where('transactionType', 'earning')
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy('paidBy')
                ->orderByDesc('total')
                ->get()
                ->map(function ($item) {
                    return [
                        'paidBy' => $item->paidBy,
                        'total' => (float)$item->total,
                        'count' => (int)$item->count,
                    ];
                });

            $totalExpenses = (float)($totalExpensesData->total ?? 0);
            $totalEarnings = (float)($totalEarningsData->total ?? 0);

            return response()->json([
                'success' => true,
                'totalExpenses' => $totalExpenses,
                'totalEarnings' => $totalEarnings,
                'netBalance' => $totalEarnings - $totalExpenses,
                'expenseCount' => (int)($totalExpensesData->count ?? 0),
                'earningCount' => (int)($totalEarningsData->count ?? 0),
                'transactions' => $transactions,
                'expensesByCategory' => $expensesByCategory,
                'earningsByCategory' => $earningsByCategory,
                'expensesByPerson' => $expensesByPerson,
                'earningsByPerson' => $earningsByPerson,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get yearly report
     * GET /api/reports/yearly
     */
    public function yearly(Request $request)
    {
        if (!$request->has('year')) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide year',
            ], 400);
        }

        try {
            $year = $request->year;

            $startDate = date('Y-m-d 00:00:00', strtotime("$year-01-01"));
            $endDate = date('Y-m-d 23:59:59', strtotime("$year-12-31"));

            // Monthly breakdown for expenses
            $monthlyExpenses = Expense::selectRaw('MONTH(date) as month')
                ->selectRaw('SUM(amount) as total')
                ->selectRaw('COUNT(id) as count')
                ->where('transactionType', 'expense')
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy(DB::raw('MONTH(date)'))
                ->orderBy(DB::raw('MONTH(date)'), 'ASC')
                ->get()
                ->keyBy('month');

            // Monthly breakdown for earnings
            $monthlyEarnings = Expense::selectRaw('MONTH(date) as month')
                ->selectRaw('SUM(amount) as total')
                ->selectRaw('COUNT(id) as count')
                ->where('transactionType', 'earning')
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy(DB::raw('MONTH(date)'))
                ->orderBy(DB::raw('MONTH(date)'), 'ASC')
                ->get()
                ->keyBy('month');

            // Combine monthly data
            $monthlyBreakdown = [];
            for ($month = 1; $month <= 12; $month++) {
                $expenseData = $monthlyExpenses->get($month);
                $earningData = $monthlyEarnings->get($month);
                
                $expenses = (float)($expenseData->total ?? 0);
                $earnings = (float)($earningData->total ?? 0);

                $monthlyBreakdown[] = [
                    'month' => $month,
                    'expenses' => $expenses,
                    'earnings' => $earnings,
                    'netBalance' => $earnings - $expenses,
                    'expenseCount' => (int)($expenseData->count ?? 0),
                    'earningCount' => (int)($earningData->count ?? 0),
                ];
            }

            return response()->json([
                'success' => true,
                'monthlyBreakdown' => $monthlyBreakdown,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

