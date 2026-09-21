@extends('layouts.app')
@section('title', 'Edit Company')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6 max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold">Edit: {{ $company->organisation_name }}</h2>
        <a href="{{ route('companies.index') }}" class="text-blue-600 hover:underline text-sm">Back</a>
    </div>

    <div class="bg-gray-50 p-4 rounded mb-6 text-sm">
        <div class="grid grid-cols-2 gap-2">
            <div><span class="font-medium">Town/City:</span> {{ $company->town_city }}</div>
            <div><span class="font-medium">County:</span> {{ $company->county }}</div>
            <div><span class="font-medium">Type & Rating:</span> {{ $company->type_rating }}</div>
            <div><span class="font-medium">Route:</span> {{ $company->route }}</div>
            <div><span class="font-medium">Change:</span> <span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100">{{ $company->change_type }}</span></div>
        </div>
    </div>

    <form action="{{ route('companies.update', $company) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700">Website URL</label>
            <input type="url" name="website_url" value="{{ old('website_url', $company->website_url) }}"
                   class="mt-1 block w-full border rounded-md px-3 py-2 text-sm" placeholder="https://example.com">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">HR Phone Number</label>
            <input type="text" name="hr_phone" value="{{ old('hr_phone', $company->hr_phone) }}"
                   class="mt-1 block w-full border rounded-md px-3 py-2 text-sm" placeholder="+44 20 1234 5678">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">HR Email Address</label>
            <input type="email" name="hr_email" value="{{ old('hr_email', $company->hr_email) }}"
                   class="mt-1 block w-full border rounded-md px-3 py-2 text-sm" placeholder="hr@example.com">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Save</button>
    </form>
</div>
@endsection
