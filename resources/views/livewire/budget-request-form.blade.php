<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <form wire:submit.prevent="submit" class="card space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Spender *</label>
                <select wire:model.live="spender_id" class="input-field">
                    <option value="">Select spender</option>
                    @foreach($spenders as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
                @error('spender_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Team *</label>
                <select wire:model="team_id" class="input-field">
                    <option value="">Select team</option>
                    @foreach($teams as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
                @error('team_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Category *</label>
                <select wire:model="category_id" class="input-field">
                    <option value="">Select category</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Amount (BDT) *</label>
                <input type="number" step="0.01" wire:model="amount_bdt" class="input-field">
                @error('amount_bdt') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Objective / campaign *</label>
            <input type="text" wire:model="objective" class="input-field">
            @error('objective') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Description *</label>
            <textarea wire:model="description" rows="3" class="input-field"></textarea>
            @error('description') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Expected outcome *</label>
            <textarea wire:model="expected_outcome" rows="2" class="input-field"></textarea>
            @error('expected_outcome') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Request date *</label>
                <input type="date" wire:model.live="request_date" class="input-field">
                @error('request_date') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Budget year</label>
                <input type="number" wire:model="budget_year" class="input-field">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Budget month</label>
                <input type="number" min="1" max="12" wire:model="budget_month" class="input-field">
            </div>
        </div>

        @if($isBackdated)
            <div class="border border-[var(--yamaha-red)] p-4 space-y-3">
                <p class="text-sm text-[var(--yamaha-red)] font-medium">Backdated request — Super Admin clearance required before approval chain.</p>
                <div>
                    <label class="block text-sm font-medium mb-1">Backdate reason *</label>
                    <textarea wire:model="backdate_reason" rows="2" class="input-field"></textarea>
                    @error('backdate_reason') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Prior approval evidence</label>
                    <textarea wire:model="backdate_evidence" rows="2" class="input-field" placeholder="Who approved verbally/email, or attach below"></textarea>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Activity start *</label>
                <input type="date" wire:model="activity_start_date" class="input-field">
                @error('activity_start_date') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Activity end *</label>
                <input type="date" wire:model="activity_end_date" class="input-field">
                @error('activity_end_date') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Location *</label>
                <input type="text" wire:model="location" class="input-field">
                @error('location') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Vendor (optional)</label>
                <input type="text" wire:model="vendor" class="input-field">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Internal notes</label>
            <textarea wire:model="internal_notes" rows="2" class="input-field"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Attachments (optional)</label>
            <input type="file" wire:model="attachments" multiple class="input-field">
            @error('attachments.*') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-3">
            <button type="button" wire:click="saveDraft" class="btn-secondary">Save draft</button>
            <button type="submit" class="btn-primary">Submit request</button>
        </div>
    </form>
</div>
