<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'title',
        'author',
        'publisher',
        'stock',
    ];

    protected $casts = [
        'stock' => 'integer',
    ];

    /**
     * Relasi ke riwayat peminjaman buku.
     */
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }
}
