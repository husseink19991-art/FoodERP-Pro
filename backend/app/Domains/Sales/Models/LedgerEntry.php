<?php

namespace App\Domains\Sales\Models;

use App\Domains\Shared\Models\BaseTenantModel;

class LedgerEntry extends BaseTenantModel
{
    protected $fillable = [
        'tenant_id',
        'entity_type',
        'entity_id',
        'transaction_type',
        'reference_id',
        'reference_number',
        'debit',
        'credit',
        'balance_after',
        'description',
        'transaction_date',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];
}
