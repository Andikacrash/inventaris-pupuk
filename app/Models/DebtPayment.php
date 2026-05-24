<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebtPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'debt_id',
        'amount',
        'payment_date',
        'payment_method',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Get the debt that owns the payment.
     */
    public function debt()
    {
        return $this->belongsTo(Debt::class);
    }

    /**
     * Get the user that recorded the payment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
