<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    private const LOAN_PERIOD_DAYS = 7;

    private const MAX_ACTIVE_LOANS = 3;

    public function index()
    {
        $loans = Loan::with(['user', 'book'])
            ->when(auth()->user()->role === 'user', fn ($query) => $query->where('user_id', auth()->id()))
            ->latest('loan_date')
            ->paginate(10);

        $view = auth()->user()->role === 'admin' ? 'admin.loans.index' : 'user.loans.index';

        return view($view, compact('loans'));
    }

    public function create()
    {
        $users = User::where('role', 'user')->orderBy('name')->get();
        $books = Book::where('stock', '>', 0)->orderBy('title')->get();

        return view('admin.loans.create', compact('users', 'books'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'book_id' => ['required', 'integer', 'exists:books,id'],
        ]);

        $result = DB::transaction(function () use ($validated) {
            $user = User::whereKey($validated['user_id'])->lockForUpdate()->firstOrFail();
            if ($user->role !== 'user') {
                return 'invalid_member';
            }

            $activeLoans = Loan::where('user_id', $user->id)
                ->where('status', 'borrowed')
                ->count();
            if ($activeLoans >= self::MAX_ACTIVE_LOANS) {
                return 'loan_limit';
            }

            $book = Book::whereKey($validated['book_id'])->lockForUpdate()->firstOrFail();
            if ($book->stock < 1) {
                return 'out_of_stock';
            }

            $book->decrement('stock');
            Loan::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'loan_date' => today(),
                'due_date' => today()->addDays(self::LOAN_PERIOD_DAYS),
                'status' => 'borrowed',
            ]);

            return 'created';
        });

        if ($result !== 'created') {
            $messages = [
                'invalid_member' => 'Peminjaman hanya dapat dicatat untuk anggota.',
                'loan_limit' => 'Anggota sudah mencapai batas 3 pinjaman aktif.',
                'out_of_stock' => 'Stok buku sudah habis.',
            ];

            return back()->withInput()->with('error', $messages[$result]);
        }

        return redirect()->route('loans.index')
            ->with('success', 'Peminjaman berhasil dicatat.');
    }

    public function returnBook(Loan $loan)
    {
        $result = DB::transaction(function () use ($loan) {
            $loan = Loan::whereKey($loan->id)->lockForUpdate()->firstOrFail();
            if ($loan->status !== 'borrowed') {
                return ['status' => 'already_returned'];
            }

            $returnedAt = today();
            $overdueDays = max(0, (int) $loan->due_date->diffInDays($returnedAt, false));

            $loan->update([
                'status' => 'returned',
                'return_date' => $returnedAt,
            ]);
            Book::whereKey($loan->book_id)->increment('stock');

            if ($overdueDays > 0) {
                $loan->fines()->create([
                    'amount' => $overdueDays * 1000,
                    'reason' => "Terlambat {$overdueDays} hari",
                    'status' => 'unpaid',
                ]);
            }

            return ['status' => 'returned', 'overdue_days' => $overdueDays];
        });

        if ($result['status'] === 'already_returned') {
            return redirect()->route('loans.index')
                ->with('error', 'Peminjaman ini sudah dikembalikan.');
        }

        $message = 'Buku berhasil dikembalikan.';
        if ($result['overdue_days'] > 0) {
            $message .= ' Denda keterlambatan telah dibuat.';
        }

        return redirect()->route('loans.index')->with('success', $message);
    }
}
