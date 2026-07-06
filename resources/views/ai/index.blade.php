@extends('layouts.app')
@section('title', 'AI Chat')
@section('content')
<div class="flex gap-6 h-[calc(100vh-8rem)]">
    <div class="flex-1 flex flex-col bg-white rounded-lg shadow-md overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b bg-gray-50">
            <div class="flex items-center gap-3">
                <h2 class="font-bold text-lg">AI Analysis</h2>
                <span class="text-xs text-gray-400">powered by Ollama</span>
            </div>
            <div class="flex items-center gap-2">
                <select id="modelSelect" class="border rounded px-2 py-1 text-sm">
                    @foreach($models as $m)
                    <option value="{{ $m['name'] }}">{{ $m['name'] }}</option>
                    @endforeach
                    <option value="llama3.2" selected>llama3.2</option>
                </select>
                <form action="{{ route('ai.clear') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-xs text-gray-500 hover:text-red-600 px-2 py-1">Clear</button>
                </form>
            </div>
        </div>

        <div id="chatMessages" class="flex-1 overflow-y-auto p-4 space-y-4">
            @forelse($messages as $msg)
            <div class="flex {{ $msg->role === 'user' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[80%] rounded-2xl px-4 py-2.5 text-sm {{ $msg->role === 'user' ? 'bg-purple-600 text-white rounded-br-md' : 'bg-gray-100 text-gray-800 rounded-bl-md' }}">
                    <div class="whitespace-pre-wrap">{{ $msg->content }}</div>
                    <div class="text-xs mt-1 opacity-60 {{ $msg->role === 'user' ? 'text-purple-200' : 'text-gray-400' }}">{{ $msg->created_at->format('H:i') }}</div>
                </div>
            </div>
            @empty
            <div class="flex items-center justify-center h-full text-gray-400">
                <div class="text-center">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <p class="text-sm">Ask anything about your company data</p>
                    <p class="text-xs mt-1">e.g. "How many companies are in London?"</p>
                </div>
            </div>
            @endforelse
            <div id="typingIndicator" class="hidden justify-start">
                <div class="bg-gray-100 rounded-2xl rounded-bl-md px-4 py-3">
                    <div class="flex gap-1.5">
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t p-4 bg-gray-50">
            <form id="chatForm" action="{{ route('ai.ask') }}" method="POST" class="flex gap-2">
                @csrf
                <input type="hidden" name="session_id" value="{{ $sessionId }}">
                <input type="text" id="questionInput" name="question" required autofocus
                       class="flex-1 border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400"
                       placeholder="Ask about your company data...">
                <button type="submit" id="sendBtn"
                        class="bg-purple-600 text-white rounded-xl px-5 py-2.5 text-sm font-medium hover:bg-purple-700 disabled:opacity-50">
                    Send
                </button>
            </form>
        </div>
    </div>

    <div class="w-64 flex flex-col gap-4">
        <div class="bg-white rounded-lg shadow-md p-4">
            <h3 class="font-bold text-sm mb-3">Pull Model</h3>
            <form action="{{ route('ai.pull-model') }}" method="POST" class="space-y-2">
                @csrf
                <input type="text" name="model" placeholder="llama3.2" required
                       class="w-full border rounded px-2 py-1.5 text-sm">
                <button type="submit" class="w-full bg-green-600 text-white rounded px-3 py-1.5 text-sm hover:bg-green-700">Pull</button>
            </form>
            @if(session('model_result'))
            <div class="mt-2 text-xs text-green-600">{{ session('model_result') }}</div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow-md p-4">
            <h3 class="font-bold text-sm mb-3">Quick Stats</h3>
            @php
                $total = \App\Models\Company::count();
                $byRoute = \App\Models\Company::selectRaw('route, count(*) as c')->groupBy('route')->orderByDesc('c')->limit(5)->pluck('c', 'route');
                $changes = ['new' => \App\Models\Company::where('change_type', 'new')->count(), 'updated' => \App\Models\Company::where('change_type', 'updated')->count()];
            @endphp
            <p class="text-2xl font-bold text-purple-600">{{ number_format($total) }}</p>
            <p class="text-xs text-gray-500 mb-3">total companies</p>
            @if($byRoute->count())
            <h4 class="text-xs font-medium text-gray-500 mb-1">Top Routes</h4>
            <ul class="text-xs space-y-1">
                @foreach($byRoute as $route => $count)
                <li class="flex justify-between"><span class="truncate">{{ $route ?: 'N/A' }}</span><span class="text-gray-500 ml-2">{{ $count }}</span></li>
                @endforeach
            </ul>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow-md p-4">
            <h3 class="font-bold text-sm mb-2">Suggestions</h3>
            <div class="space-y-1.5">
                @php
                $suggestions = [
                    'How many total companies?',
                    'What are the top 5 routes?',
                    'How many companies by each route type?',
                    'List companies in London',
                    'What types of ratings exist?',
                ];
                @endphp
                @foreach($suggestions as $s)
                <button class="suggestion-btn w-full text-left text-xs text-purple-700 hover:bg-purple-50 rounded px-2 py-1.5 transition" data-question="{{ $s }}">{{ $s }}</button>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
const chatMessages = document.getElementById('chatMessages');
const chatForm = document.getElementById('chatForm');
const questionInput = document.getElementById('questionInput');
const sendBtn = document.getElementById('sendBtn');
const typingIndicator = document.getElementById('typingIndicator');
const modelSelect = document.getElementById('modelSelect');

function scrollToBottom() {
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function addMessage(role, content, time) {
    const div = document.createElement('div');
    div.className = `flex ${role === 'user' ? 'justify-end' : 'justify-start'} message-fade-in`;
    div.innerHTML = `
        <div class="max-w-[80%] rounded-2xl px-4 py-2.5 text-sm ${role === 'user' ? 'bg-purple-600 text-white rounded-br-md' : 'bg-gray-100 text-gray-800 rounded-bl-md'}">
            <div class="whitespace-pre-wrap">${escapeHtml(content)}</div>
            <div class="text-xs mt-1 opacity-60 ${role === 'user' ? 'text-purple-200' : 'text-gray-400'}">${time}</div>
        </div>`;
    chatMessages.insertBefore(div, typingIndicator);
    scrollToBottom();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showTyping() {
    typingIndicator.classList.remove('hidden');
    typingIndicator.classList.add('flex');
    scrollToBottom();
}

function hideTyping() {
    typingIndicator.classList.add('hidden');
    typingIndicator.classList.remove('flex');
}

chatForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    const question = questionInput.value.trim();
    if (!question) return;

    const now = new Date();
    addMessage('user', question, now.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}));

    try {
        const formData = new FormData(this);
        formData.set('question', question);
        formData.set('model', modelSelect.value);
        questionInput.value = '';
        sendBtn.disabled = true;
        showTyping();

        const response = await fetch('{{ route('ai.ask') }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData,
            credentials: 'same-origin',
            signal: AbortSignal.timeout(300000)
        });

        hideTyping();
        if (!response.ok) {
            const text = await response.text();
            addMessage('assistant', 'Server returned ' + response.status + ': ' + text.substring(0, 200), new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}));
            return;
        }
        const data = await response.json();
        addMessage('assistant', data.answer, new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}));
    } catch (err) {
        hideTyping();
        const msg = err.name === 'SyntaxError' ? 'Server returned invalid response (not JSON). Check server logs.' : (err.message || 'Request failed');
        addMessage('assistant', 'Error: ' + msg, new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}));
    } finally {
        sendBtn.disabled = false;
        questionInput.focus();
    }
});

document.querySelectorAll('.suggestion-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        questionInput.value = this.dataset.question;
        questionInput.focus();
    });
});

scrollToBottom();
</script>

<style>
.message-fade-in {
    animation: fadeIn 0.3s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
#chatMessages {
    scroll-behavior: smooth;
}
.animate-bounce {
    animation: bounce 1.2s infinite;
}
</style>
@endsection
