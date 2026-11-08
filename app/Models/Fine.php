<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{
    protected $fillable = [
        'loan_id',
        'amount',
        'reason',
        'status',
    ];

    protected $casts = [
        'loan_id' => 'integer',
        'amount' => 'decimal:2',
    ];

    /**
     * Relasi ke transaksi peminjaman terkait.
     */
    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
