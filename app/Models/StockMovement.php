<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Get the product that owns the stock movement.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user that owns the stock movement.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reference model based on reference_type and reference_id.
     */
    public function reference()
    {
        switch ($this->reference_type) {
            case 'sale':
                return $this->belongsTo(Sale::class, 'reference_id');
            case 'purchase':
                // Add purchase model when created
                return null;
            case 'adjustment':
                return null;
            default:
                return null;
        }
    }

    /**
     * Scope for incoming stock movements.
     */
    public function scopeIncoming($query)
    {
        return $query->where('type', 'in');
    }

    /**
     * Scope for outgoing stock movements.
     */
    public function scopeOutgoing($query)
    {
        return $query->where('type', 'out');
    }
}
