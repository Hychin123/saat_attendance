<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chatbot Test - SAAT Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-t-2xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">Attendance Assistant</h1>
                            <p class="text-sm text-gray-500">AI-Powered Chatbot Test</p>
                        </div>
                    </div>
                    <button 
                        onclick="window.location.href='/admin'"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
                    >
                        Back to Admin
                    </button>
                </div>
            </div>

            <!-- Info Section -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Logged in as:</strong> {{ auth()->user()->name }} ({{ auth()->user()->role?->name ?? 'No Role' }})
                        </p>
                    </div>
                </div>
            </div>

            <!-- Chat Container -->
            <div class="bg-white shadow-lg rounded-b-2xl">
                <!-- Messages Area -->
                <div id="chat-messages" class="h-96 overflow-y-auto p-6 space-y-4 bg-gray-50">
                    <!-- Welcome Message -->
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"></path>
                            </svg>
                        </div>
                        <div class="flex-1 bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                            <p class="text-sm text-gray-800 whitespace-pre-line">👋 <strong>Welcome!</strong> I'm your attendance assistant. 

Try asking me:
• "Show my attendance this month"
• "Am I late today?"
• "Who is absent today?"
• "My work hours this week"

Type "help" for more commands!</p>
                        </div>
                    </div>
                </div>

                <!-- Input Area -->
                <div class="border-t border-gray-200 p-4 bg-white rounded-b-2xl">
                    <form id="chatbot-form" onsubmit="sendMessage(event)" class="flex space-x-3">
                        <input 
                            type="text" 
                            id="chat-input" 
                            placeholder="Ask me anything about attendance..."
                            class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            autocomplete="off"
                            required
                        />
                        <button 
                            type="submit"
                            id="send-button"
                            class="px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg hover:from-blue-600 hover:to-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all shadow-md hover:shadow-lg"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                        </button>
                    </form>

                    <!-- Quick Actions -->
                    <div class="mt-4 flex flex-wrap gap-2">
                        <button 
                            onclick="quickQuery('Show my attendance this month')"
                            class="text-sm px-4 py-2 bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition"
                        >
                            📊 My Attendance
                        </button>
                        <button 
                            onclick="quickQuery('Am I late today?')"
                            class="text-sm px-4 py-2 bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition"
                        >
                            ⏰ Today Status
                        </button>
                        <button 
                            onclick="quickQuery('Who is absent today?')"
                            class="text-sm px-4 py-2 bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition"
                        >
                            👥 Absent Today
                        </button>
                        <button 
                            onclick="quickQuery('My work hours this week')"
                            class="text-sm px-4 py-2 bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition"
                        >
                            🕐 Work Hours
                        </button>
                        <button 
                            onclick="quickQuery('help')"
                            class="text-sm px-4 py-2 bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition"
                        >
                            ❓ Help
                        </button>
                    </div>
                </div>
            </div>

            <!-- Examples Section -->
            <div class="mt-6 bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">💡 Example Queries</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <h3 class="font-medium text-gray-700 mb-2">Personal Queries:</h3>
                        <ul class="space-y-1 text-sm text-gray-600">
                            <li>• "Show my attendance this month"</li>
                            <li>• "Am I late today?"</li>
                            <li>• "My work hours this week"</li>
                            <li>• "My attendance statistics"</li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-700 mb-2">Manager/Admin Queries:</h3>
                        <ul class="space-y-1 text-sm text-gray-600">
                            <li>• "Who is absent today?"</li>
                            <li>• "Who is late today?"</li>
                            <li>• "Show attendance for John"</li>
                            <li>• "Overall statistics"</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let isProcessing = false;

        function sendMessage(event) {
            event.preventDefault();
            
            if (isProcessing) return;
            
            const input = document.getElementById('chat-input');
            const message = input.value.trim();
            
            if (!message) return;
            
            addMessage(message, 'user');
            input.value = '';
            
            isProcessing = true;
            showLoading();
            
            fetch('{{ route('chatbot.query') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ query: message })
            })
            .then(response => response.json())
            .then(data => {
                removeLoading();
                if (data.success) {
                    addMessage(data.message, 'bot');
                } else {
                    addMessage(data.message || 'Sorry, something went wrong.', 'bot');
                }
                isProcessing = false;
            })
            .catch(error => {
                removeLoading();
                addMessage('❌ Sorry, I encountered an error. Please try again.', 'bot');
                console.error('Chatbot error:', error);
                isProcessing = false;
            });
        }

        function quickQuery(query) {
            const input = document.getElementById('chat-input');
            input.value = query;
            document.getElementById('chatbot-form').dispatchEvent(new Event('submit'));
        }

        function addMessage(text, sender) {
            const messagesContainer = document.getElementById('chat-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'flex items-start space-x-3';
            
            if (sender === 'user') {
                messageDiv.innerHTML = `
                    <div class="flex-1 flex justify-end">
                        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg shadow-md p-4 max-w-md">
                            <p class="text-sm whitespace-pre-line">${escapeHtml(text)}</p>
                        </div>
                    </div>
                    <div class="flex-shrink-0 w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                `;
            } else {
                messageDiv.innerHTML = `
                    <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"></path>
                        </svg>
                    </div>
                    <div class="flex-1 bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                        <p class="text-sm text-gray-800 whitespace-pre-line">${escapeHtml(text)}</p>
                    </div>
                `;
            }
            
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function showLoading() {
            const messagesContainer = document.getElementById('chat-messages');
            const loadingDiv = document.createElement('div');
            loadingDiv.id = 'loading-indicator';
            loadingDiv.className = 'flex items-start space-x-3';
            loadingDiv.innerHTML = `
                <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"></path>
                    </svg>
                </div>
                <div class="flex-1 bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                    <div class="flex space-x-2">
                        <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                </div>
            `;
            messagesContainer.appendChild(loadingDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function removeLoading() {
            const loading = document.getElementById('loading-indicator');
            if (loading) loading.remove();
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('chat-input').focus();
        });
    </script>
</body>
</html>
