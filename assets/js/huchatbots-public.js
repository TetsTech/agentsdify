(function($) {
    document.addEventListener('DOMContentLoaded', function() {
        console.log('HUchatbots: DOM fully loaded and parsed');

        if (typeof huchatbotsData === 'undefined') {
            console.error('HUchatbots: huchatbotsData is not defined. Check if wp_localize_script is called correctly.');
            return;
        }
        console.log('HUchatbots: huchatbotsData:', huchatbotsData);

        const chatbotWrapper = document.getElementById('huchatbot-wrapper');
        if (!chatbotWrapper) {
            console.error('HUchatbots: Chatbot wrapper not found');
            return;
        }
        console.log('HUchatbots: Chatbot wrapper found');

        const chatbotToggle = document.getElementById('huchatbot-toggle');
        const chatbotContainer = document.getElementById('huchatbot-container');
        const chatbotHeader = chatbotContainer.querySelector('.huchatbot-header');
        const chatbotClose = chatbotContainer.querySelector('.huchatbot-close');
        const chatbotMaximize = chatbotContainer.querySelector('.huchatbot-maximize');
        const messagesContainer = chatbotContainer.querySelector('.huchatbot-messages');
        const textarea = chatbotContainer.querySelector('textarea');
        const sendButton = chatbotContainer.querySelector('button');

        const courseId = chatbotContainer.dataset.courseId;
        let conversationId = null;

        const difyApiKey = huchatbotsData.dify_api_key;
        const difyApiUrl = huchatbotsData.dify_api_url;

        const defaultStyle = {
            width: '400px',
            height: '600px',
            right: '20px',
            bottom: '20px',
            top: 'auto',
            left: 'auto'
        };
        let isMaximized = false;

        console.log('HUchatbots: Initializing chatbot script');
        console.log('HUchatbots: Dify API URL:', difyApiUrl);
        console.log('HUchatbots: Course ID:', courseId);

        function toggleChatbot() {
            console.log('HUchatbots: Toggling chatbot');
            chatbotContainer.classList.toggle('huchatbot-visible');
            if (chatbotContainer.classList.contains('huchatbot-visible')) {
                chatbotContainer.style.display = 'flex';
                if (isMaximized) {
                    maximizeChatbot();
                } else {
                    resetChatbotPosition();
                }
            } else {
                chatbotContainer.style.display = 'none';
                resetChatbotPosition();
            }
            console.log('HUchatbots: Chatbot visible:', chatbotContainer.classList.contains('huchatbot-visible'));
        }

        function resetChatbotPosition() {
            chatbotContainer.style.transition = 'all 0.3s ease';
            Object.assign(chatbotContainer.style, defaultStyle);
            isMaximized = false;
            chatbotMaximize.innerHTML = '<i class="bb-icon bb-icon-expand"></i>';
        }

        function maximizeChatbot() {
            chatbotContainer.style.transition = 'all 0.3s ease';
            Object.assign(chatbotContainer.style, {
                width: '100%',
                height: '100%',
                top: '0',
                right: '0',
                bottom: '0'
            });
            chatbotMaximize.innerHTML = '<i class="bb-icon bb-icon-l"></i>';
            isMaximized = true;
        }

        function toggleMaximize() {
            if (isMaximized) {
                resetChatbotPosition();
            } else {
                maximizeChatbot();
            }
        }

        chatbotToggle.addEventListener('click', toggleChatbot);
        chatbotClose.addEventListener('click', function() {
            chatbotContainer.classList.remove('huchatbot-visible');
            chatbotContainer.style.display = 'none';
            resetChatbotPosition();
        });
        chatbotMaximize.addEventListener('click', toggleMaximize);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isMaximized) {
                toggleMaximize();
            }
        });

        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        sendButton.addEventListener('click', sendMessage);
        textarea.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        function sendMessage() {
            const message = textarea.value.trim();
            if (message === '') return;

            console.log('HUchatbots: Sending message:', message);

            addMessage('user', message);
            textarea.value = '';
            textarea.style.height = 'auto';

            const typingIndicator = addMessage('bot', '', true);

            const requestBody = {
                inputs: {},
                query: message,
                response_mode: 'streaming',
                conversation_id: conversationId,
                user: 'edu.ibrahim@humana.team'
            };

            fetch(`${difyApiUrl}/v1/chat-messages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${difyApiKey}`
                },
                body: JSON.stringify(requestBody)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let botResponse = '';
                let hasConversationId = false;

                function readStream() {
                    return reader.read().then(({ done, value }) => {
                        if (done) {
                            console.log('Stream complete');
                            typingIndicator.remove();
                            addMessage('bot', botResponse);
                            return;
                        }
                        const chunk = decoder.decode(value, { stream: true });
                        const lines = chunk.split('\n');
                        lines.forEach(line => {
                            if (line.startsWith('data: ')) {
                                try {
                                    const jsonData = JSON.parse(line.slice(6));
                                    // Captura o conversation_id do primeiro evento que o contém
                                    if (!hasConversationId && jsonData.conversation_id) {
                                        conversationId = jsonData.conversation_id;
                                        hasConversationId = true;
                                        console.log('Conversation ID captured:', conversationId);
                                        saveConversation(conversationId);
                                    }
                                    // Acumula a resposta do bot
                                    if (jsonData.event === 'agent_message' && jsonData.answer) {
                                        botResponse += jsonData.answer;
                                        updateTypingIndicator(typingIndicator, botResponse);
                                    }
                                } catch (error) {
                                    console.error('Error parsing JSON:', error);
                                    console.error('Raw line:', line);
                                }
                            }
                        });
                        return readStream();
                    });
                }

                return readStream();
            })
            .catch(error => {
                console.error('HUchatbots: Error details:', error);
                typingIndicator.remove();
                addMessage('error', 'Desculpe, o Professor está fora do ar no momento, estamos solucionando e em breve retornará. Detalhes do erro: ' + error.message);
            });
        }

        function addMessage(sender, text, isTyping = false) {
            const messageElement = document.createElement('div');
            messageElement.classList.add('huchatbot-message', `huchatbot-message-${sender}`);
            
            if (sender === 'bot' || sender === 'error') {
                const avatarImg = document.createElement('img');
                avatarImg.src = chatbotContainer.querySelector('.huchatbot-header .huchatbot-avatar').src;
                avatarImg.alt = 'Bot Avatar';
                avatarImg.classList.add('huchatbot-message-avatar');
                messageElement.appendChild(avatarImg);
            }

            const textElement = document.createElement('p');
            textElement.textContent = text;
            messageElement.appendChild(textElement);

            if (isTyping) {
                messageElement.classList.add('huchatbot-typing');
            }

            messagesContainer.appendChild(messageElement);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            return messageElement;
        }

        function updateTypingIndicator(indicator, text) {
            const textElement = indicator.querySelector('p');
            textElement.textContent = text;
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function saveConversation(conversationId) {
            if (!conversationId) {
                console.error('No conversation ID provided');
                return;
            }

            console.log('Saving conversation:', conversationId);

            const data = new FormData();
            data.append('action', 'huchatbots_save_conversation');
            data.append('conversation_id', conversationId);
            data.append('course_id', courseId);
            data.append('nonce', huchatbotsData.nonce);

            fetch(huchatbotsData.ajax_url, {
                method: 'POST',
                credentials: 'same-origin',
                body: data
            })
            .then(response => response.text())
            .then(text => {
                try {
                    const result = JSON.parse(text);
                    if (result.success) {
                        console.log('Conversation saved successfully:', conversationId);
                    } else {
                        console.error('Failed to save conversation:', result.data);
                    }
                } catch (e) {
                    console.error('Error parsing server response:', e);
                    console.error('Raw response:', text);
                }
            })
            .catch(error => {
                console.error('Error saving conversation:', error);
                console.error('Error details:', error.message);
            });
        }

        resetChatbotPosition();
    });
})(jQuery);
