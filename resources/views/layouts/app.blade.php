<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Company Directory')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex space-x-8">
                    <a href="{{ route('imports.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600">Import CSV</a>
                    <a href="{{ route('companies.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600">Companies</a>
                    <a href="{{ route('ai.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600">AI Analysis</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 px-4">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ $errors->first() }}</div>
        @endif
        @yield('content')
    </main>
@yield('scripts')
</body>
</html>
