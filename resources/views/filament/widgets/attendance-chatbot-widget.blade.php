<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                    </svg>
                    <h2 class="text-lg font-semibold">Attendance Assistant</h2>
                </div>
                <button 
                    type="button"
                    onclick="clearChat()"
                    class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                >
                    Clear Chat
                </button>
            </div>

            <p class="text-sm text-gray-600 dark:text-gray-400">
                Ask me anything about attendance! Try: "Show my attendance this month" or "Who is absent today?"
            </p>

            <!-- Chat Messages -->
            <div id="chat-messages" class="space-y-3 max-h-96 overflow-y-auto p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                <!-- Welcome message -->
                <div class="flex items-start space-x-2">
                    <div class="flex-shrink-0 w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"></path>
                            <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"></path>
                        </svg>
                    </div>
                    <div class="flex-1 bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm">
                        <p class="text-sm whitespace-pre-line">👋 Hi! I'm your attendance assistant. Ask me anything about attendance records, statistics, or today's status!</p>
                    </div>
                </div>
            </div>

            <!-- Input Form -->
            <div class="flex space-x-2">
                <input 
                    type="text" 
                    id="chat-input" 
                    placeholder="Ask a question..."
                    class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                    autocomplete="off"
                />
                <button 
                    type="button"
                    id="send-button"
                    class="px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </div>

            <!-- Quick Actions -->
            <div class="flex flex-wrap gap-2">
                <button 
                    type="button"
                    class="quick-query-btn text-xs px-3 py-1 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700"
                    data-query="Show my attendance this month"
                >
                    📊 My Attendance
                </button>
                <button 
                    type="button"
                    class="quick-query-btn text-xs px-3 py-1 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700"
                    data-query="Am I late today?"
                >
                    ⏰ Today's Status
                </button>
                <button 
                    type="button"
                    class="quick-query-btn text-xs px-3 py-1 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700"
                    data-query="Who is absent today?"
                >
                    👥 Absent Today
                </button>
                <button 
                    type="button"
                    class="quick-query-btn text-xs px-3 py-1 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700"
                    data-query="My work hours this week"
                >
                    🕐 Work Hours
                </button>
                <button 
                    type="button"
                    class="quick-query-btn text-xs px-3 py-1 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700"
                    data-query="help"
                >
                    ❓ Help
                </button>
            </div>
        </div>
    </x-filament::section>

    @push('scripts')
    <script>
        window.chatbotProcessing = false;

        window.sendMessage = function(event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            if (window.chatbotProcessing) return false;
            
            const input = document.getElementById('chat-input');
            const message = input.value.trim();
            
            if (!message) return false;
            
            console.log('Sending message:', message);
            
            // Add user message to chat
            window.addMessage(message, 'user');
            input.value = '';
            
            // Show loading
            window.chatbotProcessing = true;
            window.showLoading();
            
            // Get CSRF token from meta tag or Livewire
            let csrfToken = '{{ csrf_token() }}';
            const metaToken = document.querySelector('meta[name="csrf-token"]');
            if (metaToken) {
                csrfToken = metaToken.content;
            }
            
            console.log('CSRF Token:', csrfToken);
            console.log('Route URL:', '{{ route('chatbot.query') }}');
            
            // Send to backend
            fetch('{{ route('chatbot.query') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ query: message }),
                credentials: 'same-origin'
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Error response:', text);
                        throw new Error('Network response was not ok: ' + response.status);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                window.removeLoading();
                if (data.success) {
                    window.addMessage(data.message, 'bot');
                } else {
                    window.addMessage(data.message || 'Sorry, something went wrong.', 'bot');
                }
                window.chatbotProcessing = false;
            })
            .catch(error => {
                console.error('Chatbot error:', error);
                window.removeLoading();
                window.addMessage('❌ Sorry, I encountered an error. Please try again.', 'bot');
                window.chatbotProcessing = false;
            });
            
            return false;
        };

        window.quickQuery = function(query) {
            const input = document.getElementById('chat-input');
            input.value = query;
            window.sendMessage();
            return false;
        };

        window.addMessage = function(text, sender) {
            const messagesContainer = document.getElementById('chat-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'flex items-start space-x-2';
            
            if (sender === 'user') {
                messageDiv.innerHTML = `
                    <div class="flex-1 flex justify-end">
                        <div class="bg-primary-500 text-white rounded-lg p-3 shadow-sm max-w-md">
                            <p class="text-sm whitespace-pre-line">${window.escapeHtml(text)}</p>
                        </div>
                    </div>
                    <div class="flex-shrink-0 w-8 h-8 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                `;
            } else {
                messageDiv.innerHTML = `
                    <div class="flex-shrink-0 w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"></path>
                            <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"></path>
                        </svg>
                    </div>
                    <div class="flex-1 bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm">
                        <p class="text-sm whitespace-pre-line">${window.escapeHtml(text)}</p>
                    </div>
                `;
            }
            
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        };

        window.showLoading = function() {
            const messagesContainer = document.getElementById('chat-messages');
            const loadingDiv = document.createElement('div');
            loadingDiv.id = 'loading-indicator';
            loadingDiv.className = 'flex items-start space-x-2';
            loadingDiv.innerHTML = `
                <div class="flex-shrink-0 w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"></path>
                    </svg>
                </div>
                <div class="flex-1 bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm">
                    <div class="flex space-x-2">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                </div>
            `;
            messagesContainer.appendChild(loadingDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        };

        window.removeLoading = function() {
            const loading = document.getElementById('loading-indicator');
            if (loading) {
                loading.remove();
            }
        };

        window.clearChat = function() {
            const messagesContainer = document.getElementById('chat-messages');
            messagesContainer.innerHTML = `
                <div class="flex items-start space-x-2">
                    <div class="flex-shrink-0 w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"></path>
                            <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"></path>
                        </svg>
                    </div>
                    <div class="flex-1 bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm">
                        <p class="text-sm whitespace-pre-line">👋 Hi! I'm your attendance assistant. Ask me anything about attendance records, statistics, or today's status!</p>
                    </div>
                </div>
            `;
        };

        window.escapeHtml = function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };

        // Initialize chatbot when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeChatbot);
        } else {
            initializeChatbot();
        }
        
        // Also initialize after a delay for Livewire
        setTimeout(initializeChatbot, 1000);
        
        function initializeChatbot() {
            console.log('Initializing chatbot...');
            
            const input = document.getElementById('chat-input');
            const sendButton = document.getElementById('send-button');
            const quickQueryButtons = document.querySelectorAll('.quick-query-btn');
            
            console.log('Input found:', !!input);
            console.log('Send button found:', !!sendButton);
            console.log('Quick buttons found:', quickQueryButtons.length);
            
            // Focus input
            if (input) {
                input.focus();
                
                // Enter key support
                if (!input.hasAttribute('data-listener-added')) {
                    input.setAttribute('data-listener-added', 'true');
                    input.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            console.log('Enter pressed!');
                            window.sendMessage();
                        }
                    });
                }
            }
            
            // Send button click
            if (sendButton && !sendButton.hasAttribute('data-listener-added')) {
                sendButton.setAttribute('data-listener-added', 'true');
                sendButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Send button clicked!');
                    window.sendMessage();
                });
            }
            
            // Quick query buttons
            quickQueryButtons.forEach(function(button) {
                if (!button.hasAttribute('data-listener-added')) {
                    button.setAttribute('data-listener-added', 'true');
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        const query = this.getAttribute('data-query');
                        console.log('Quick query clicked:', query);
                        if (query && input) {
                            input.value = query;
                            window.sendMessage();
                        }
                    });
                }
            });
        }
    </script>
    @endpush
</x-filament-widgets::widget>
