@extends('layouts.app')
@section('title', 'Import CSV')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h2 class="text-xl font-bold mb-4">Upload CSV File</h2>

    <div id="uploadForm" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700">CSV File</label>
            <input type="file" id="csvFile" accept=".csv,.txt" required
                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <p class="text-xs text-gray-500 mt-1">Expected columns: Organisation Name, Town/City, County, Type & Rating, Route</p>
        </div>
        <button type="button" id="uploadBtn" onclick="uploadCsv()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Upload & Import</button>
    </div>

    <div id="progressSection" class="hidden space-y-3 mt-4">
        <div class="flex items-center justify-between text-sm">
            <span id="progressLabel" class="font-medium text-gray-700">Uploading...</span>
            <span id="progressPercent" class="text-gray-500">0%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
            <div id="progressBar" class="bg-blue-600 h-4 rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
        <p id="progressStatus" class="text-xs text-gray-500">Preparing upload...</p>
    </div>

    <div id="errorSection" class="hidden mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
</div>

<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-bold mb-4">Import History</h2>
    @if($imports->isEmpty())
        <p class="text-gray-500">No imports yet.</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">File</th>
                        <th class="px-4 py-2 text-left">Date</th>
                        <th class="px-4 py-2 text-right">Total</th>
                        <th class="px-4 py-2 text-right text-green-600">New</th>
                        <th class="px-4 py-2 text-right text-yellow-600">Updated</th>
                        <th class="px-4 py-2 text-right text-red-600">Removed</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($imports as $import)
                    <tr>
                        <td class="px-4 py-2">{{ $import->filename }}</td>
                        <td class="px-4 py-2">{{ $import->created_at->format('d M Y H:i') }}</td>
                        <td class="px-4 py-2 text-right">{{ $import->total_rows }}</td>
                        <td class="px-4 py-2 text-right text-green-600">{{ $import->new_rows }}</td>
                        <td class="px-4 py-2 text-right text-yellow-600">{{ $import->updated_rows }}</td>
                        <td class="px-4 py-2 text-right text-red-600">{{ $import->removed_rows }}</td>
                        <td class="px-4 py-2"><a href="{{ route('imports.show', $import) }}" class="text-blue-600 hover:underline">View</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
let pollingTimer = null;

function uploadCsv() {
    const fileInput = document.getElementById('csvFile');
    const file = fileInput.files[0];
    if (!file) return;

    const form = document.getElementById('uploadForm');
    const uploadBtn = document.getElementById('uploadBtn');
    const progressSection = document.getElementById('progressSection');
    const errorSection = document.getElementById('errorSection');
    const progressBar = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');
    const progressLabel = document.getElementById('progressLabel');
    const progressStatus = document.getElementById('progressStatus');

    errorSection.classList.add('hidden');
    progressSection.classList.remove('hidden');
    uploadBtn.disabled = true;
    uploadBtn.classList.add('opacity-50', 'cursor-not-allowed');

    const formData = new FormData();
    formData.append('csv_file', file);
    formData.append('_token', document.querySelector('input[name="_token"]').value);

    const xhr = new XMLHttpRequest();

    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            progressBar.style.width = percent + '%';
            progressPercent.textContent = percent + '%';
            progressStatus.textContent = 'Uploading ' + file.name + ' (' + formatBytes(e.loaded) + ' / ' + formatBytes(e.total) + ')';
        }
    });

    xhr.addEventListener('load', function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            const response = JSON.parse(xhr.responseText);
            progressLabel.textContent = 'Processing...';
            progressBar.style.width = '0%';
            progressPercent.textContent = '0%';
            progressStatus.textContent = 'Starting import...';
            startProcessing(response.id);
        } else {
            let msg = 'Upload failed';
            try {
                const err = JSON.parse(xhr.responseText);
                msg = err.message || err.error || msg;
            } catch(e) {}
            showError(msg);
        }
    });

    xhr.addEventListener('error', function() {
        showError('Network error occurred during upload');
    });

    xhr.open('POST', '{{ route('imports.store') }}');
    xhr.send(formData);
}

function startProcessing(importId) {
    const progressBar = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');
    const progressLabel = document.getElementById('progressLabel');
    const progressStatus = document.getElementById('progressStatus');

    const processData = new FormData();
    processData.append('_token', document.querySelector('input[name="_token"]').value);

    const processXhr = new XMLHttpRequest();

    processXhr.addEventListener('load', function() {
        if (processXhr.status >= 200 && processXhr.status < 300) {
            const response = JSON.parse(processXhr.responseText);
            stopPolling();
            if (response.redirect) {
                window.location.href = response.redirect;
            }
        } else {
            stopPolling();
            let msg = 'Processing failed';
            try {
                const err = JSON.parse(processXhr.responseText);
                msg = err.error || msg;
            } catch(e) {}
            showError(msg);
        }
    });

    processXhr.addEventListener('error', function() {
        stopPolling();
        showError('Network error during processing');
    });

    processXhr.open('POST', '{{ url('imports') }}/' + importId + '/process');
    processXhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    processXhr.setRequestHeader('Accept', 'application/json');
    processXhr.send(processData);

    startPolling(importId);
}

function startPolling(importId) {
    const progressBar = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');
    const progressStatus = document.getElementById('progressStatus');

    pollingTimer = setInterval(function() {
        fetch('{{ url('imports') }}/' + importId + '/progress', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            const p = data.progress || 0;
            progressBar.style.width = p + '%';
            progressPercent.textContent = p + '%';
            if (data.status === 'processing') {
                progressStatus.textContent = 'Processing rows... (' + p + '%)';
            } else if (data.status === 'completed') {
                progressStatus.textContent = 'Import complete!';
                progressBar.style.width = '100%';
                progressPercent.textContent = '100%';
            } else if (data.status === 'failed') {
                stopPolling();
                showError('Import processing failed');
            }
        })
        .catch(function() {});
    }, 1500);
}

function stopPolling() {
    if (pollingTimer) {
        clearInterval(pollingTimer);
        pollingTimer = null;
    }
}

function showError(msg) {
    const errorSection = document.getElementById('errorSection');
    errorSection.textContent = msg;
    errorSection.classList.remove('hidden');

    const uploadBtn = document.getElementById('uploadBtn');
    uploadBtn.disabled = false;
    uploadBtn.classList.remove('opacity-50', 'cursor-not-allowed');
}

function formatBytes(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
}
</script>
@endsection
