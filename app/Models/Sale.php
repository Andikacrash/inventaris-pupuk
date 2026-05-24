<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'customer_name',
        'customer_phone',
        'delivery_method',
        'delivery_address',
        'delivery_phone',
        'sale_date',
        'discount',
        'total_amount',
        'payment_method',
        'status',
        'payment_amount',
        'change_amount',
        'debt_amount',
        'debt_status',
        'user_id',
    ];


    protected $casts = [
        'sale_date' => 'date',
        'total_amount' => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'debt_amount' => 'decimal:2',
    ];

    /**
     * Get the debt for the sale.
     */
    public function debt()
    {
        return $this->hasOne(Debt::class);
    }

    /**
     * Get the user that owns the sale.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sale items for the sale.
     */
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Alias items() for compatibility with report service
     */
    public function items()
    {
        return $this->saleItems();
    }

    /**
     * Get the products through sale items.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'sale_items')
            ->withPivot('quantity', 'unit_price', 'subtotal')
            ->withTimestamps();
    }

    /**
     * Generate invoice number.
     */
    public static function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $lastSale = self::whereDate('created_at', today())->latest()->first();

        if ($lastSale) {
            $lastNumber = (int) substr($lastSale->invoice_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
