<?php

namespace App\Livewire;

use App\Models\BudgetRequest;
use App\Services\ActualExpenseService;
use Livewire\Component;
use Livewire\WithFileUploads;

class ActualExpenseForm extends Component
{
    use WithFileUploads;

    public BudgetRequest $budgetRequest;

    public $amount_bdt = '';
    public $spend_date = '';
    public $vendor = '';
    public $description = '';
    public $overrun_justification = '';
    public $close_request = false;
    public $attachments = [];

    public function mount(BudgetRequest $budgetRequest): void
    {
        $this->authorize('reportActuals', $budgetRequest);
        $this->budgetRequest = $budgetRequest;
        $this->spend_date = now()->format('Y-m-d');
        $this->vendor = $budgetRequest->vendor ?? '';
    }

    protected function rules(): array
    {
        $approved = (float) ($this->budgetRequest->approved_amount_bdt ?? $this->budgetRequest->amount_bdt);
        $currentTotal = $this->budgetRequest->totalActualAmount();
        $newTotal = $currentTotal + (float) ($this->amount_bdt ?: 0);

        $rules = [
            'amount_bdt' => ['required', 'numeric', 'min:0.01'],
            'spend_date' => ['required', 'date'],
            'vendor' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'close_request' => ['boolean'],
            'attachments' => ['required', 'array', 'min:1'],
            'attachments.*' => ['file', 'max:10240'],
        ];

        if ($newTotal > $approved) {
            $rules['overrun_justification'] = ['required', 'string', 'min:10'];
        }

        return $rules;
    }

    public function save(ActualExpenseService $service)
    {
        $this->validate();

        $service->record($this->budgetRequest, [
            'amount_bdt' => $this->amount_bdt,
            'spend_date' => $this->spend_date,
            'vendor' => $this->vendor,
            'description' => $this->description,
            'overrun_justification' => $this->overrun_justification ?: null,
        ], $this->attachments, (bool) $this->close_request);

        session()->flash('success', $this->close_request
            ? 'Actual expense recorded and request closed.'
            : 'Actual expense recorded.');

        return redirect()->route('requests.show', $this->budgetRequest);
    }

    public function render()
    {
        $approved = (float) ($this->budgetRequest->approved_amount_bdt ?? $this->budgetRequest->amount_bdt);
        $currentTotal = $this->budgetRequest->totalActualAmount();

        return view('livewire.actual-expense-form', [
            'approved' => $approved,
            'currentTotal' => $currentTotal,
            'projectedTotal' => $currentTotal + (float) ($this->amount_bdt ?: 0),
        ]);
    }
}
