<?php

namespace App\Domains\Sales\Models;

use App\Domains\Shared\Models\BaseTenantModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends BaseTenantModel
{
    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'address',
        'latitude',
        'longitude',
        'current_balance',
        'credit_limit',
        'payment_terms_days',
        'is_blocked',
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class, 'entity_id')
            ->where('entity_type', 'customer')
            ->orderBy('transaction_date', 'desc');
    }
}
