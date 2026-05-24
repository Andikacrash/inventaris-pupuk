<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallmentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'debt_id',
        'total_amount',
        'installment_count',
        'installment_amount',
        'frequency',
        'start_date',
        'end_date',
        'paid_amount',
        'paid_count',
        'status',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the debt that owns the installment plan.
     */
    public function debt()
    {
        return $this->belongsTo(Debt::class);
    }

    /**
     * Get the user that created the installment plan.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the installment payments for the plan.
     */
    public function installmentPayments()
    {
        return $this->hasMany(InstallmentPayment::class);
    }

    /**
     * Get remaining amount.
     */
    public function getRemainingAmountAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Get remaining installments.
     */
    public function getRemainingCountAttribute()
    {
        return $this->installment_count - $this->paid_count;
    }
}
