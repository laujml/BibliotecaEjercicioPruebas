<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester_name',
        'book_id',
        'user_id',
        'return_at',
    ];

    protected $casts = [
        'return_at' => 'datetime',
    ];

    // El préstamo está activo si no tiene fecha de devolución
    public function isActive(): Attribute
    {
        return Attribute::make(
            get: fn () => is_null($this->return_at),
        );
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
