<?php

namespace App\Domains\Sales\Models;

use App\Domains\Shared\Models\BaseTenantModel;

class InvoiceItem extends BaseTenantModel
{
    protected $table = 'invoice_items';

    protected $fillable = [
        'invoice_id', 'product_id', 'quantity', 'unit_price', 'unit_cost', 'subtotal',
    ];
}
