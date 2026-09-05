<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl uppercase tracking-wide">Report Actual Expense — {{ $budgetRequest->reference }}</h2>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        @livewire('actual-expense-form', ['budgetRequest' => $budgetRequest])
    </div>
</x-app-layout>
