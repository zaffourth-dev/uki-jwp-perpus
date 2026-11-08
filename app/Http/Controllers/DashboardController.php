<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()->role === 'admin') {
            return view('admin.dashboard.index', [
                'bookCount' => Book::count(),
                'memberCount' => User::where('role', 'user')->count(),
                'activeLoanCount' => Loan::where('status', 'borrowed')->count(),
                'unpaidFineCount' => Fine::where('status', 'unpaid')->count(),
                'recentLoans' => Loan::with(['user', 'book'])->latest('loan_date')->limit(5)->get(),
            ]);
        }

        $userId = auth()->id();

        return view('user.dashboard.index', [
            'activeLoanCount' => Loan::where('user_id', $userId)->where('status', 'borrowed')->count(),
            'unpaidFineTotal' => Fine::where('status', 'unpaid')
                ->whereHas('loan', fn ($query) => $query->where('user_id', $userId))
                ->sum('amount'),
            'availableBookCount' => Book::where('stock', '>', 0)->count(),
            'recentLoans' => Loan::with('book')
                ->where('user_id', $userId)
                ->latest('loan_date')
                ->limit(5)
                ->get(),
        ]);
    }
}
