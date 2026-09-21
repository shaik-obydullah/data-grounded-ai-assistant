<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('organisation_name', 'ilike', "%{$search}%")
                  ->orWhere('town_city', 'ilike', "%{$search}%")
                  ->orWhere('county', 'ilike', "%{$search}%")
                  ->orWhere('type_rating', 'ilike', "%{$search}%")
                  ->orWhere('route', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('change_type')) {
            $query->where('change_type', $request->change_type);
        }

        $companies = $query->orderBy('organisation_name')->paginate(50)->withQueryString();
        $stats = [
            'total' => Company::count(),
            'new' => Company::where('change_type', 'new')->count(),
            'updated' => Company::where('change_type', 'updated')->count(),
            'removed' => Company::where('change_type', 'removed')->count(),
        ];

        return view('companies.index', compact('companies', 'stats'));
    }

    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'website_url' => 'nullable|url|max:255',
            'hr_phone' => 'nullable|string|max:50',
            'hr_email' => 'nullable|email|max:255',
        ]);

        $company->update($validated);

        return redirect()->route('companies.index')->with('success', 'Company updated successfully');
    }

    public function show(Company $company)
    {
        return view('companies.show', compact('company'));
    }
}
