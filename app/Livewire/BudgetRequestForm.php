<?php

namespace App\Livewire;

use App\Enums\BudgetRequestStatus;
use App\Models\BudgetRequest;
use App\Models\Category;
use App\Models\Team;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\AttachmentService;
use App\Services\AuditService;
use Livewire\Component;
use Livewire\WithFileUploads;

class BudgetRequestForm extends Component
{
    use WithFileUploads;

    public ?BudgetRequest $budgetRequest = null;

    public $spender_id = '';
    public $team_id = '';
    public $category_id = '';
    public $objective = '';
    public $description = '';
    public $expected_outcome = '';
    public $amount_bdt = '';
    public $request_date = '';
    public $budget_year = '';
    public $budget_month = '';
    public $backdate_reason = '';
    public $backdate_evidence = '';
    public $activity_start_date = '';
    public $activity_end_date = '';
    public $location = '';
    public $vendor = '';
    public $internal_notes = '';
    public $attachments = [];

    public function mount(?BudgetRequest $budgetRequest = null): void
    {
        $this->request_date = now()->format('Y-m-d');
        $this->budget_year = now()->year;
        $this->budget_month = now()->month;

        if ($budgetRequest?->exists) {
            $this->authorize('update', $budgetRequest);
            $this->budgetRequest = $budgetRequest;
            $this->fill($budgetRequest->only([
                'spender_id', 'team_id', 'category_id', 'objective', 'description',
                'expected_outcome', 'amount_bdt', 'backdate_reason', 'backdate_evidence',
                'location', 'vendor', 'internal_notes',
            ]));
            $this->request_date = $budgetRequest->request_date->format('Y-m-d');
            $this->budget_year = $budgetRequest->budget_year;
            $this->budget_month = $budgetRequest->budget_month;
            $this->activity_start_date = $budgetRequest->activity_start_date->format('Y-m-d');
            $this->activity_end_date = $budgetRequest->activity_end_date->format('Y-m-d');
        }
    }

    public function updatedSpenderId($value): void
    {
        $spender = User::find($value);
        if ($spender?->team_id) {
            $this->team_id = $spender->team_id;
        }
    }

    public function updatedRequestDate($value): void
    {
        if ($value) {
            $date = \Carbon\Carbon::parse($value);
            $this->budget_year = $date->year;
            $this->budget_month = $date->month;
        }
    }

    protected function rules(): array
    {
        $rules = [
            'spender_id' => ['required', 'exists:users,id'],
            'team_id' => ['required', 'exists:teams,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'objective' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'expected_outcome' => ['required', 'string'],
            'amount_bdt' => ['required', 'numeric', 'min:1'],
            'request_date' => ['required', 'date'],
            'budget_year' => ['required', 'integer'],
            'budget_month' => ['required', 'integer', 'min:1', 'max:12'],
            'activity_start_date' => ['required', 'date'],
            'activity_end_date' => ['required', 'date', 'after_or_equal:activity_start_date'],
            'location' => ['required', 'string', 'max:255'],
            'vendor' => ['nullable', 'string', 'max:255'],
            'internal_notes' => ['nullable', 'string'],
            'attachments.*' => ['nullable', 'file', 'max:10240'],
        ];

        if ($this->request_date && \Carbon\Carbon::parse($this->request_date)->lt(today())) {
            $rules['backdate_reason'] = ['required', 'string', 'min:10'];
        }

        return $rules;
    }

    public function saveDraft(
        AuditService $audit,
        AttachmentService $attachmentService,
    ) {
        $this->validate();

        $data = $this->payload();
        $data['status'] = BudgetRequestStatus::Draft;
        $data['initiator_id'] = auth()->id();
        $data['is_backdated'] = \Carbon\Carbon::parse($this->request_date)->lt(today());

        if ($this->budgetRequest) {
            $old = $this->budgetRequest->toArray();
            $this->budgetRequest->update($data);
            $audit->log('budget_request.draft_updated', $this->budgetRequest, $old, $data);
        } else {
            $data['reference'] = $this->generateReference();
            $this->budgetRequest = BudgetRequest::create($data);
            $audit->log('budget_request.draft_created', $this->budgetRequest, null, $data);
        }

        if ($this->attachments) {
            $attachmentService->storeMany($this->budgetRequest, $this->attachments, 'request-attachments');
        }

        session()->flash('success', 'Draft saved.');

        return redirect()->route('requests.show', $this->budgetRequest);
    }

    public function submit(
        ApprovalService $approvalService,
        AuditService $audit,
        AttachmentService $attachmentService,
    ) {
        $this->validate();

        $data = $this->payload();
        $data['initiator_id'] = auth()->id();

        if ($this->budgetRequest) {
            $this->budgetRequest->update($data);
        } else {
            $data['reference'] = $this->generateReference();
            $data['status'] = BudgetRequestStatus::Draft;
            $this->budgetRequest = BudgetRequest::create($data);
        }

        if ($this->attachments) {
            $attachmentService->storeMany($this->budgetRequest, $this->attachments, 'request-attachments');
        }

        $approvalService->submit($this->budgetRequest->fresh());

        session()->flash('success', 'Budget request submitted.');

        return redirect()->route('requests.show', $this->budgetRequest);
    }

    private function payload(): array
    {
        return [
            'spender_id' => $this->spender_id,
            'team_id' => $this->team_id,
            'category_id' => $this->category_id,
            'objective' => $this->objective,
            'description' => $this->description,
            'expected_outcome' => $this->expected_outcome,
            'amount_bdt' => $this->amount_bdt,
            'request_date' => $this->request_date,
            'budget_year' => $this->budget_year,
            'budget_month' => $this->budget_month,
            'backdate_reason' => $this->backdate_reason ?: null,
            'backdate_evidence' => $this->backdate_evidence ?: null,
            'activity_start_date' => $this->activity_start_date,
            'activity_end_date' => $this->activity_end_date,
            'location' => $this->location,
            'vendor' => $this->vendor ?: null,
            'internal_notes' => $this->internal_notes ?: null,
        ];
    }

    private function generateReference(): string
    {
        return 'YMB-'.now()->format('Ym').'-'.str_pad((string) (BudgetRequest::count() + 1), 4, '0', STR_PAD_LEFT);
    }

    public function render()
    {
        return view('livewire.budget-request-form', [
            'spenders' => User::selectableAsSpender()->orderBy('name')->get(),
            'teams' => Team::where('is_active', true)->orderBy('name')->get(),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'isBackdated' => $this->request_date && \Carbon\Carbon::parse($this->request_date)->lt(today()),
        ]);
    }
}
