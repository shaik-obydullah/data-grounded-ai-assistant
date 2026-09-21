@extends('layouts.app')
@section('title', 'Companies')

@section('content')
<div class="grid grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-lg shadow text-center">
        <div class="text-2xl font-bold">{{ $stats['total'] }}</div>
        <div class="text-xs text-gray-500">Total Companies</div>
    </div>
    <div class="bg-green-50 p-4 rounded-lg shadow text-center">
        <div class="text-2xl font-bold text-green-600">{{ $stats['new'] }}</div>
        <div class="text-xs text-gray-500">New</div>
    </div>
    <div class="bg-yellow-50 p-4 rounded-lg shadow text-center">
        <div class="text-2xl font-bold text-yellow-600">{{ $stats['updated'] }}</div>
        <div class="text-xs text-gray-500">Updated</div>
    </div>
    <div class="bg-red-50 p-4 rounded-lg shadow text-center">
        <div class="text-2xl font-bold text-red-600">{{ $stats['removed'] }}</div>
        <div class="text-xs text-gray-500">Removed</div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">All Companies</h2>
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="border rounded px-3 py-1.5 text-sm">
            <select name="change_type" class="border rounded px-3 py-1.5 text-sm">
                <option value="">All</option>
                <option value="new" {{ request('change_type') === 'new' ? 'selected' : '' }}>New</option>
                <option value="updated" {{ request('change_type') === 'updated' ? 'selected' : '' }}>Updated</option>
                <option value="removed" {{ request('change_type') === 'removed' ? 'selected' : '' }}>Removed</option>
                <option value="unchanged" {{ request('change_type') === 'unchanged' ? 'selected' : '' }}>Unchanged</option>
            </select>
            <button type="submit" class="bg-gray-200 px-3 py-1.5 rounded text-sm">Filter</button>
        </form>
    </div>

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
                    <th class="px-3 py-2 text-left">Website</th>
                    <th class="px-3 py-2 text-left">HR Phone</th>
                    <th class="px-3 py-2 text-left">HR Email</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($companies as $company)
                <tr>
                    <td class="px-3 py-2">
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            {{ $company->change_type === 'new' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $company->change_type === 'updated' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $company->change_type === 'removed' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $company->change_type === 'unchanged' ? 'bg-gray-100 text-gray-800' : '' }}">
                            {{ $company->change_type }}
                        </span>
                    </td>
                    <td class="px-3 py-2 font-medium">{{ $company->organisation_name }}</td>
                    <td class="px-3 py-2">{{ $company->town_city }}</td>
                    <td class="px-3 py-2">{{ $company->county }}</td>
                    <td class="px-3 py-2">{{ $company->type_rating }}</td>
                    <td class="px-3 py-2">{{ $company->route }}</td>
                    <td class="px-3 py-2 max-w-[120px] truncate">{{ $company->website_url }}</td>
                    <td class="px-3 py-2">{{ $company->hr_phone }}</td>
                    <td class="px-3 py-2 max-w-[160px] truncate">{{ $company->hr_email }}</td>
                    <td class="px-3 py-2">
                        <a href="{{ route('companies.edit', $company) }}" class="text-blue-600 hover:underline text-xs">Edit</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="px-3 py-4 text-center text-gray-500">No companies found. Import a CSV first.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $companies->links() }}</div>
</div>
@endsection
