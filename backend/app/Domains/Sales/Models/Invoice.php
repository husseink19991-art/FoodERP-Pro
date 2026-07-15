<?php

namespace App\Domains\Sales\Models;

use App\Domains\Shared\Models\BaseTenantModel;

class Invoice extends BaseTenantModel
{
    protected $fillable = [
        'tenant_id', 'customer_id', 'sales_rep_id', 'invoice_number',
        'total_amount', 'total_cost', 'discount', 'tax', 'status', 'payment_type',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
    ];
}
