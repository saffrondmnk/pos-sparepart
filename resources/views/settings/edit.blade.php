@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">System Settings</h1>
            <p class="mt-1 text-sm text-gray-600">Customize your POS system appearance and receipt information.</p>
        </div>

        @if(session('success'))
        <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="p-6">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Logo Upload -->
                    <div>
                        <label for="logo" class="block text-sm font-medium text-gray-700">Company Logo</label>
                        <div class="mt-2 flex items-center gap-6">
                            @if($settings->logo_path && file_exists(public_path($settings->logo_path)))
                            <div class="flex-shrink-0">
                                <img src="{{ asset($settings->logo_path) }}" alt="Company Logo" class="h-20 w-20 object-contain rounded border border-gray-200">
                            </div>
                            @else
                            <div class="flex-shrink-0 h-20 w-20 rounded border border-gray-200 bg-gray-50 flex items-center justify-center">
                                <svg class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            @endif
                            <div class="flex-1">
                                <input type="file" name="logo" id="logo" accept="image/*"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <p class="mt-1 text-xs text-gray-500">Upload a logo to display in the dashboard navigation and receipts. Recommended: Square image, max 2MB (JPEG, PNG, JPG, SVG)</p>
                            </div>
                        </div>
                        @error('logo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- App Title (Browser Tab) -->
                    <div>
                        <label for="app_title" class="block text-sm font-medium text-gray-700">
                            Browser Tab Title
                            <span class="text-gray-400 text-xs ml-1">(Appears in browser tab)</span>
                        </label>
                        <input type="text" name="app_title" id="app_title" value="{{ old('app_title', $settings->app_title) }}" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('app_title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Company Name -->
                    <div>
                        <label for="company_name" class="block text-sm font-medium text-gray-700">
                            Company Name
                            <span class="text-gray-400 text-xs ml-1">(Used in reports and system-wide)</span>
                        </label>
                        <input type="text" name="company_name" id="company_name" value="{{ old('company_name', $settings->company_name) }}" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('company_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Receipt Title -->
                    <div>
                        <label for="receipt_title" class="block text-sm font-medium text-gray-700">
                            Receipt Title
                            <span class="text-gray-400 text-xs ml-1">(Appears at the top of printed receipts)</span>
                        </label>
                        <input type="text" name="receipt_title" id="receipt_title" value="{{ old('receipt_title', $settings->receipt_title) }}" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('receipt_title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Receipt Address -->
                    <div>
                        <label for="receipt_address" class="block text-sm font-medium text-gray-700">
                            Receipt Address
                            <span class="text-gray-400 text-xs ml-1">(Optional - appears on receipts)</span>
                        </label>
                        <textarea name="receipt_address" id="receipt_address" rows="2"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('receipt_address', $settings->receipt_address) }}</textarea>
                        @error('receipt_address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Receipt Phone -->
                    <div>
                        <label for="receipt_phone" class="block text-sm font-medium text-gray-700">
                            Receipt Phone
                            <span class="text-gray-400 text-xs ml-1">(Optional - appears on receipts)</span>
                        </label>
                        <input type="text" name="receipt_phone" id="receipt_phone" value="{{ old('receipt_phone', $settings->receipt_phone) }}"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('receipt_phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection