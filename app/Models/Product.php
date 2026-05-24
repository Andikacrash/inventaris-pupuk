<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'type',
        'unit',
        'price',
        'stock_quantity',
        'minimum_stock',
        'description',
        'image',
        'barcode',
        'supplier_id',
        'category_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'minimum_stock' => 'integer',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the supplier that owns the product.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the sale items for the product.
     */
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Get the stock movements for the product.
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Check if product stock is low.
     */
    public function isStockLow()
    {
        return $this->stock_quantity <= $this->minimum_stock;
    }

    /**
     * Get stock status.
     */
    public function getStockStatusAttribute()
    {
        if ($this->stock_quantity == 0) {
            return 'Habis';
        } elseif ($this->isStockLow()) {
            return 'Hampir Habis';
        } else {
            return 'Tersedia';
        }
    }
}
