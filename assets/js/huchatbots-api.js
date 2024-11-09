const HUchatbotsAPI = {
    sendMessage: function(message, courseId, conversationId) {
        const requestBody = {
            action: 'huchatbots_proxy',
            course_id: courseId,
            nonce: huchatbotsData.nonce,
            query: message,
            conversation_id: conversationId,
            user: 'edu.ibrahim@humana.team'
        };

        console.log('HUchatbots: Sending request:', requestBody);

        return fetch(huchatbotsData.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': huchatbotsData.nonce
            },
            body: JSON.stringify(requestBody)
        })
        .then(response => {
            console.log('HUchatbots: Response status:', response.status);
            console.log('HUchatbots: Response headers:', Object.fromEntries(response.headers.entries()));
            return response.text().then(text => ({
                status: response.status,
                text: text
            }));
        })
        .then(({status, text}) => {
            console.log('HUchatbots: Raw response:', text);
            if (text === '0') {
                throw new Error('WordPress returned "0". This usually indicates a PHP error.');
            }
            try {
                return JSON.parse(text);
            } catch (error) {
                console.error('HUchatbots: Error parsing JSON:', error);
                throw new Error(`Invalid JSON response from server (status ${status}): ${text}`);
            }
        });
    },

    saveConversation: function(conversationId, courseId) {
        const requestBody = {
            action: 'huchatbots_save_conversation',
            course_id: courseId,
            conversation_id: conversationId,
            nonce: huchatbotsData.nonce
        };
        console.log('HUchatbots: Saving conversation:', requestBody);

        return fetch(huchatbotsData.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': huchatbotsData.nonce
            },
            body: JSON.stringify(requestBody)
        })
        .then(response => {
            if (response.ok) {
                console.log('HUchatbots: Conversation saved successfully');
                return true;
            } else {
                throw new Error('Failed to save conversation');
            }
        });
    }
};
