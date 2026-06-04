<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">会社名 <span class="text-red-500">*</span></label>
        <input type="text" name="company_name" value="{{ old('company_name', $company->company_name ?? '') }}"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('company_name') border-red-500 @enderror">
        @error('company_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">担当者名</label>
        <input type="text" name="contact_name" value="{{ old('contact_name', $company->contact_name ?? '') }}"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('contact_name') border-red-500 @enderror">
        @error('contact_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">メールアドレス</label>
        <input type="email" name="email" value="{{ old('email', $company->email ?? '') }}"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('email') border-red-500 @enderror">
        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">電話番号</label>
        <input type="text" name="phone" value="{{ old('phone', $company->phone ?? '') }}"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('phone') border-red-500 @enderror">
        @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">住所</label>
        <textarea name="address" rows="3"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('address') border-red-500 @enderror">{{ old('address', $company->address ?? '') }}</textarea>
        @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">備考</label>
        <textarea name="notes" rows="3"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('notes') border-red-500 @enderror">{{ old('notes', $company->notes ?? '') }}</textarea>
        @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
