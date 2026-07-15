<?php

namespace App\Domains\Sales\Models;

use App\Domains\Shared\Models\BaseTenantModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends BaseTenantModel
{
    protected $fillable = [
        'tenant_id',
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'current_balance',
        'credit_limit',
        'payment_terms_days',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class, 'entity_id')
            ->where('entity_type', 'supplier')
            ->orderBy('transaction_date', 'desc');
    }
}
