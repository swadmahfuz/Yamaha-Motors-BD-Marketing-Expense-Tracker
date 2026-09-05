<?php

namespace App\Http\Controllers;

use App\Models\BudgetRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function requests(Request $request): StreamedResponse
    {
        $filename = 'budget-requests-'.now()->format('Y-m-d-His').'.csv';

        $query = BudgetRequest::query()->with(['spender', 'team', 'category', 'initiator']);

        if ($request->filled('year')) {
            $query->where('budget_year', $request->year);
        }

        if ($request->filled('month')) {
            $query->where('budget_month', $request->month);
        }

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Reference', 'Status', 'Initiator', 'Spender', 'Team', 'Category',
                'Amount BDT', 'Approved BDT', 'Actual BDT', 'Budget Month',
                'Request Date', 'Backdated', 'Objective',
            ]);

            $query->chunk(100, function ($requests) use ($handle) {
                foreach ($requests as $r) {
                    fputcsv($handle, [
                        $r->reference,
                        $r->status->label(),
                        $r->initiator->name,
                        $r->spender->name,
                        $r->team->name,
                        $r->category->name,
                        $r->amount_bdt,
                        $r->approved_amount_bdt,
                        $r->totalActualAmount(),
                        $r->budgetPeriodLabel(),
                        $r->request_date->format('Y-m-d'),
                        $r->is_backdated ? 'Yes' : 'No',
                        $r->objective,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
