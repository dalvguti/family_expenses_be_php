<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    /**
     * Get all expenses
     * GET /api/expenses
     */
    public function index(Request $request)
    {
        try {
            $query = Expense::query();

            // Filter by category
            if ($request->has('category') && $request->category !== null) {
                $query->where('category', 'LIKE', '%' . $request->category . '%');
            }

            // Filter by paidBy
            if ($request->has('paidBy') && $request->paidBy !== null) {
                $query->where('paidBy', 'LIKE', '%' . $request->paidBy . '%');
            }

            // Filter by transaction type
            if ($request->has('transactionType') && $request->transactionType !== null) {
                $query->where('transactionType', $request->transactionType);
            }

            // Filter by date range
            if ($request->has('startDate') && $request->startDate !== null) {
                $query->where('date', '>=', $request->startDate);
            }
            if ($request->has('endDate') && $request->endDate !== null) {
                $query->where('date', '<=', $request->endDate);
            }

            // Sorting
            if ($request->has('sort') && $request->sort !== null) {
                $sortFields = explode(',', $request->sort);
                foreach ($sortFields as $field) {
                    if (str_starts_with($field, '-')) {
                        $query->orderBy(substr($field, 1), 'DESC');
                    } else {
                        $query->orderBy($field, 'ASC');
                    }
                }
            } else {
                $query->orderBy('date', 'DESC');
            }

            // Limit
            if ($request->has('limit') && $request->limit !== null) {
                $query->limit((int)$request->limit);
            }

            $expenses = $query->get();

            return response()->json([
                'success' => true,
                'count' => $expenses->count(),
                'data' => $expenses,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getTraceAsString(),
            ], 500);
        }
    }

    /**
     * Get single expense
     * GET /api/expenses/{id}
     */
    public function show($id)
    {
        try {
            $expense = Expense::find($id);

            if (!$expense) {
                return response()->json([
                    'success' => false,
                    'message' => 'Expense not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $expense,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create expense
     * POST /api/expenses
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), Expense::validationRules());

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $expense = Expense::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $expense,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update expense
     * PUT /api/expenses/{id}
     */
    public function update(Request $request, $id)
    {
        $expense = Expense::find($id);

        if (!$expense) {
            return response()->json([
                'success' => false,
                'message' => 'Expense not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), Expense::validationRules(true));

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $expense->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $expense,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete expense
     * DELETE /api/expenses/{id}
     */
    public function destroy($id)
    {
        try {
            $expense = Expense::find($id);

            if (!$expense) {
                return response()->json([
                    'success' => false,
                    'message' => 'Expense not found',
                ], 404);
            }

            $expense->delete();

            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get expense statistics
     * GET /api/expenses/stats
     */
    public function stats()
    {
        try {
            $currentDate = now();
            $currentMonthStart = $currentDate->copy()->startOfMonth();

            // Total expenses
            $totalExpenses = Expense::where('transactionType', 'expense')
                ->sum('amount');

            // Total earnings
            $totalEarnings = Expense::where('transactionType', 'earning')
                ->sum('amount');

            // Current month expenses
            $currentMonthExpenses = Expense::where('transactionType', 'expense')
                ->where('date', '>=', $currentMonthStart)
                ->sum('amount');

            // Current month earnings
            $currentMonthEarnings = Expense::where('transactionType', 'earning')
                ->where('date', '>=', $currentMonthStart)
                ->sum('amount');

            // Expenses by category
            $expensesByCategory = Expense::select('category')
                ->selectRaw('SUM(amount) as total')
                ->selectRaw('COUNT(id) as count')
                ->where('transactionType', 'expense')
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

            return response()->json([
                'success' => true,
                'totalExpenses' => (float)$totalExpenses,
                'totalEarnings' => (float)$totalEarnings,
                'netBalance' => (float)($totalEarnings - $totalExpenses),
                'currentMonthExpenses' => (float)$currentMonthExpenses,
                'currentMonthEarnings' => (float)$currentMonthEarnings,
                'currentMonthNet' => (float)($currentMonthEarnings - $currentMonthExpenses),
                'expensesByCategory' => $expensesByCategory,
                'earningsByCategory' => $earningsByCategory,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

