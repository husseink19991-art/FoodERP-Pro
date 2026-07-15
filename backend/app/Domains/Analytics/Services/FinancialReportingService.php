<?php

namespace App\Domains\Analytics\Services;

use App\Domains\Sales\Models\Invoice;
use Illuminate\Support\Facades\DB;

class FinancialReportingService
{
    /**
     * Get Profit & Loss Summary
     */
    public function getProfitLossSummary(string $tenantId, string $startDate, string $endDate)
    {
        $sales = Invoice::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw("SUM(total_amount) as total_revenue"),
                DB::raw("SUM(total_cost) as total_cogs")
            )
            ->first();

        $expenses = DB::table('expenses')
            ->where('tenant_id', $tenantId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        $revenue = $sales->total_revenue ?? 0;
        $cogs = $sales->total_cogs ?? 0;
        $grossProfit = $revenue - $cogs;
        $netProfit = $grossProfit - $expenses;

        return [
            'revenue' => $revenue,
            'cogs' => $cogs,
            'gross_profit' => $grossProfit,
            'expenses' => $expenses,
            'net_profit' => $netProfit,
            'margin_percentage' => $revenue > 0 ? ($netProfit / $revenue) * 100 : 0
        ];
    }

    /**
     * Get Tax/VAT Summary
     */
    public function getVATSummary(string $tenantId, string $year, string $quarter)
    {
        // Simplified VAT logic
        $vatRate = 0.15; // 15% VAT
        $sales = Invoice::where('tenant_id', $tenantId)
            ->whereYear('created_at', $year)
            ->sum('total_amount');
            
        return [
            'taxable_sales' => $sales,
            'vat_collected' => $sales * $vatRate,
            'vat_rate' => $vatRate * 100
        ];
    }
}
