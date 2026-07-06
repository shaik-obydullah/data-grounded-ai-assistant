@extends('layouts.app')
@section('title', 'Import Details')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="flex justify-between items-start">
        <div>
            <h2 class="text-xl font-bold">Import: {{ $import->filename }}</h2>
            <p class="text-sm text-gray-500">{{ $import->created_at->format('d M Y H:i:s') }}</p>
        </div>
        <a href="{{ route('imports.index') }}" class="text-blue-600 hover:underline text-sm">Back</a>
    </div>
    <div class="grid grid-cols-4 gap-4 mt-4">
        <div class="bg-gray-50 p-3 rounded text-center">
            <div class="text-2xl font-bold">{{ $import->total_rows }}</div>
            <div class="text-xs text-gray-500">Total</div>
        </div>
        <div class="bg-green-50 p-3 rounded text-center">
            <div class="text-2xl font-bold text-green-600">{{ $import->new_rows }}</div>
            <div class="text-xs text-gray-500">New</div>
        </div>
        <div class="bg-yellow-50 p-3 rounded text-center">
            <div class="text-2xl font-bold text-yellow-600">{{ $import->updated_rows }}</div>
            <div class="text-xs text-gray-500">Updated</div>
        </div>
        <div class="bg-red-50 p-3 rounded text-center">
            <div class="text-2xl font-bold text-red-600">{{ $import->removed_rows }}</div>
            <div class="text-xs text-gray-500">Removed</div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-bold mb-4">Records</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left">Change</th>
                    <th class="px-3 py-2 text-left">Organisation</th>
                    <th class="px-3 py-2 text-left">Town/City</th>
                    <th class="px-3 py-2 text-left">County</th>
                    <th class="px-3 py-2 text-left">Type & Rating</th>
                    <th class="px-3 py-2 text-left">Route</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($companies as $company)
                <tr class="{{ $company->change_type === 'new' ? 'bg-green-50' : ($company->change_type === 'updated' ? 'bg-yellow-50' : ($company->change_type === 'removed' ? 'bg-red-50' : '')) }}">
                    <td class="px-3 py-2">
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            {{ $company->change_type === 'new' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $company->change_type === 'updated' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $company->change_type === 'removed' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $company->change_type === 'unchanged' ? 'bg-gray-100 text-gray-800' : '' }}">
                            {{ $company->change_type }}
                        </span>
                    </td>
                    <td class="px-3 py-2">{{ $company->organisation_name }}</td>
                    <td class="px-3 py-2">{{ $company->town_city }}</td>
                    <td class="px-3 py-2">{{ $company->county }}</td>
                    <td class="px-3 py-2">{{ $company->type_rating }}</td>
                    <td class="px-3 py-2">{{ $company->route }}</td>
                    <td class="px-3 py-2">
                        <a href="{{ route('companies.edit', $company) }}" class="text-blue-600 hover:underline text-xs">Edit</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $companies->links() }}</div>
</div>
@endsection
