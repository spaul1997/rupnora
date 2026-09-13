@csrf
@if (isset($faq))
    @method('PUT')
@endif

<div class="max-w-2xl space-y-6">
    <div class="admin-card space-y-5 p-6">
        <x-admin.form.input label="Question" name="question" :value="$faq->question ?? null" required />
        <x-admin.form.textarea label="Answer" name="answer" :value="$faq->answer ?? null" :rows="5" required />
        <x-admin.form.select label="Category" name="category" required :value="$faq->category ?? null" :options="collect($categories)->mapWithKeys(fn($c) => [$c => ucwords(str_replace('_',' ',$c))])" />
        <x-admin.form.input label="Sort Order" name="sort_order" type="number" :value="$faq->sort_order ?? 0" />
        <label class="flex items-center gap-2.5 text-sm text-gray-700">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $faq->is_active ?? true)) class="h-4 w-4 rounded border-gray-300 text-champagne-dark focus:ring-champagne-dark/40">
            Active (visible on storefront)
        </label>
    </div>
    <button type="submit" class="admin-btn-primary">{{ isset($faq) ? 'Update FAQ' : 'Create FAQ' }}</button>
</div>
