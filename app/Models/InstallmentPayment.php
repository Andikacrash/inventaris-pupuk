<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallmentPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'installment_plan_id',
        'debt_id',
        'installment_number',
        'amount',
        'due_date',
        'payment_date',
        'status',
        'payment_method',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'payment_date' => 'date',
    ];

    /**
     * Get the installment plan that owns the payment.
     */
    public function installmentPlan()
    {
        return $this->belongsTo(InstallmentPlan::class);
    }

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

    /**
     * Check if payment is overdue.
     */
    public function isOverdue()
    {
        return $this->status === 'pending' && $this->due_date < now();
    }
}
