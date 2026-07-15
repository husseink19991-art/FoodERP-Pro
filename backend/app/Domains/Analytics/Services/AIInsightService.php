<?php

namespace App\Domains\Analytics\Services;

use App\Domains\LLM\Services\LLMFailoverService;
use App\Domains\Sales\Models\Invoice;
use App\Domains\Product\Models\ProductBatch;
use App\Domains\Sales\Models\Customer;
use Illuminate\Support\Facades\DB;

class AIInsightService
{
    protected LLMFailoverService $llm;

    public function __construct(LLMFailoverService $llm)
    {
        $this->llm = $llm;
    }

    /**
     * Generate executive insights based on tenant data
     */
    public function generateExecutiveInsights(string $tenantId)
    {
        // 1. Gather Data Context
        $dataContext = $this->gatherDataContext($tenantId);

        // 2. Construct Prompt
        $prompt = $this->buildPrompt($dataContext);

        // 3. Get AI Response via Failover Service
        try {
            return $this->llm->generateText($prompt, [
                'temperature' => 0.5,
                'max_tokens' => 500
            ]);
        } catch (\Exception $e) {
            return "AI Insights currently unavailable. Please check system logs.";
        }
    }

    protected function gatherDataContext(string $tenantId): array
    {
        $salesToday = Invoice::where('tenant_id', $tenantId)->whereDate('created_at', now())->sum('total_amount');
        $expiringSoon = ProductBatch::where('tenant_id', $tenantId)
            ->where('current_quantity', '>', 0)
            ->where('expiry_date', '<=', now()->addDays(30))
            ->count();
        $totalOverdue = DB::table('debts')
            ->where('tenant_id', $tenantId)
            ->where('due_date', '<', now())
            ->sum('remaining_balance');

        return [
            'sales_today' => $salesToday,
            'expiring_batches_30d' => $expiringSoon,
            'total_overdue_debt' => $totalOverdue,
            'top_selling_category' => 'Grains', // Simplified for prompt
        ];
    }

    protected function buildPrompt(array $data): string
    {
        return "You are an elite business analyst for FoodERP Pro. 
        Analyze the following ERP data for today:
        - Total Sales Today: \${$data['sales_today']}
        - Batches Expiring in 30 days: {$data['expiring_batches_30d']}
        - Total Overdue Customer Debt: \${$data['total_overdue_debt']}
        - Top Category: {$data['top_selling_category']}
        
        Provide 3 concise, actionable executive insights in a professional tone. 
        Focus on risk mitigation and revenue growth. Format as bullet points.";
    }
}
