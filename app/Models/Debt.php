<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'customer_name',
        'customer_phone',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'due_date',
        'status',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    /**
     * Get the sale that owns the debt.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the user that created the debt.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get debt payments.
     */
    public function payments()
    {
        return $this->hasMany(DebtPayment::class);
    }

    /**
     * Get installment plans for the debt.
     */
    public function installmentPlans()
    {
        return $this->hasMany(InstallmentPlan::class);
    }

    /**
     * Get active installment plan.
     */
    public function activeInstallmentPlan()
    {
        return $this->hasOne(InstallmentPlan::class)->where('status', 'active');
    }

    /**
     * Check if debt is overdue.
     */
    public function isOverdue()
    {
        if (! $this->due_date) {
            return false;
        }

        return $this->status !== 'paid' && $this->due_date < now();
    }

    /**
     * Normalisasi nomor telepon untuk mengelompokkan pelanggan yang sama.
     */
    public static function normalizePhone(?string $phone): string
    {
        if ($phone === null || trim($phone) === '') {
            return '';
        }

        return preg_replace('/\D/', '', $phone) ?? '';
    }

    /**
     * Kunci grup: nama + HP (jika HP ada). Tanpa HP, hanya nama (risiko nama sama beda orang — sebaiknya selalu isi HP di kasir).
     */
    public function groupKey(): string
    {
        $phone = self::normalizePhone($this->customer_phone);
        $name = mb_strtolower(preg_replace('/\s+/u', ' ', trim((string) $this->customer_name)));

        return $phone !== '' ? 'p:'.$phone.'|n:'.$name : 'n:'.$name;
    }
}
