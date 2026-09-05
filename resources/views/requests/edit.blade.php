<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl uppercase tracking-wide">Edit Draft — {{ $budgetRequest->reference }}</h2>
    </x-slot>

    @livewire('budget-request-form', ['budgetRequest' => $budgetRequest])
</x-app-layout>
