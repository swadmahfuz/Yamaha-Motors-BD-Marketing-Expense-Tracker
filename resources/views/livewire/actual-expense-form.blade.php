<div class="card space-y-4">
    <div class="text-sm text-gray-600">
        Approved: BDT {{ number_format($approved, 2) }} |
        Reported so far: BDT {{ number_format($currentTotal, 2) }} |
        After this entry: BDT {{ number_format($projectedTotal, 2) }}
        @if($projectedTotal > $approved)
            <span class="badge badge-red ml-2">Overrun — justification required</span>
        @endif
    </div>

    <form wire:submit.prevent="save" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Actual amount (BDT) *</label>
                <input type="number" step="0.01" wire:model="amount_bdt" class="input-field">
                @error('amount_bdt') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Spend date *</label>
                <input type="date" wire:model="spend_date" class="input-field">
                @error('spend_date') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Vendor / payee *</label>
                <input type="text" wire:model="vendor" class="input-field">
                @error('vendor') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">What was purchased / done *</label>
            <textarea wire:model="description" rows="3" class="input-field"></textarea>
            @error('description') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        @if($projectedTotal > $approved)
            <div>
                <label class="block text-sm font-medium mb-1">Overrun justification *</label>
                <textarea wire:model="overrun_justification" rows="3" class="input-field"></textarea>
                @error('overrun_justification') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        @endif

        <div>
            <label class="block text-sm font-medium mb-1">Receipt / invoice attachments *</label>
            <input type="file" wire:model="attachments" multiple class="input-field">
            @error('attachments') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" wire:model="close_request" class="rounded border-gray-300 text-[var(--yamaha-red)]">
            Mark request closed (releases unused commitment)
        </label>

        <button type="submit" class="btn-primary">Record actual expense</button>
    </form>
</div>
