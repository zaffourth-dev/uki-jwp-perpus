<?php

namespace App\Http\Controllers;

use App\Models\Fine;
use Illuminate\Support\Facades\DB;

class FineController extends Controller
{
    public function index()
    {
        $fines = Fine::with(['loan.user', 'loan.book'])
            ->when(
                auth()->user()->role === 'user',
                fn ($query) => $query->whereHas('loan', fn ($loanQuery) => $loanQuery->where('user_id', auth()->id()))
            )
            ->latest()
            ->paginate(10);

        $view = auth()->user()->role === 'admin' ? 'admin.fines.index' : 'user.fines.index';

        return view($view, compact('fines'));
    }

    public function markPaid(Fine $fine)
    {
        $wasPaid = DB::transaction(function () use ($fine) {
            $fine = Fine::whereKey($fine->id)->lockForUpdate()->firstOrFail();
            if ($fine->status === 'paid') {
                return true;
            }

            $fine->update(['status' => 'paid']);

            return false;
        });

        if ($wasPaid) {
            return redirect()->route('fines.index')
                ->with('error', 'Denda ini sudah ditandai lunas.');
        }

        return redirect()->route('fines.index')
            ->with('success', 'Pembayaran denda berhasil dicatat.');
    }
}
