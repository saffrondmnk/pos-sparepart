<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'quantity_before',
        'quantity_after',
        'quantity_changed',
        'type',
        'notes',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getChangeTypeLabel(): string
    {
        return match($this->type) {
            'add' => 'Stock Added',
            'subtract' => 'Stock Reduced',
            'set' => 'Stock Set',
            default => 'Stock Updated',
        };
    }

    public function getChangeTypeColor(): string
    {
        return match($this->type) {
            'add' => 'green',
            'subtract' => 'red',
            'set' => 'blue',
            default => 'gray',
        };
    }
}
