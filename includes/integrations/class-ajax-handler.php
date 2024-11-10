<?php
namespace HUchatbots\Integrations;

class AjaxHandler {
    private $conversations_table;

    public function __construct() {
        global $wpdb;
        $this->conversations_table = $wpdb->prefix . 'huchatbots_conversations';
        error_log('HUchatbots: AjaxHandler constructed');
    }

    public function init() {
        add_action('wp_ajax_huchatbots_save_conversation', array($this, 'save_conversation'));
        add_action('wp_ajax_nopriv_huchatbots_save_conversation', array($this, 'save_conversation'));
        error_log('HUchatbots: AjaxHandler initialized');
    }

    public function save_conversation() {
        error_log('HUchatbots: save_conversation called');
        error_log('HUchatbots: POST data: ' . print_r($_POST, true));
        
        // Ensure we only output JSON
        header('Content-Type: application/json');
        
        try {
            if (!check_ajax_referer('huchatbots_nonce', 'nonce', false)) {
                throw new \Exception('Invalid nonce.');
            }

            if (!isset($_POST['conversation_id']) || !isset($_POST['course_id'])) {
                throw new \Exception('Missing required fields.');
            }

            $conversation_id = sanitize_text_field($_POST['conversation_id']);
            $course_id = intval($_POST['course_id']);
            $user_id = get_current_user_id();

            if (empty($conversation_id) || empty($course_id)) {
                throw new \Exception('Invalid or incomplete data.');
            }

            global $wpdb;

            // Verificar se a conversa já existe
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$this->conversations_table} WHERE conversation_id = %s",
                $conversation_id
            ));

            if ($existing) {
                error_log("HUchatbots: Conversation already exists - ID: $conversation_id");
                echo json_encode(['success' => true, 'message' => 'Conversation already exists.']);
                wp_die();
            }

            // Inserir nova conversa
            $result = $wpdb->insert(
                $this->conversations_table,
                [
                    'conversation_id' => $conversation_id,
                    'course_id' => $course_id,
                    'user_id' => $user_id,
                    'created_at' => current_time('mysql')
                ],
                ['%s', '%d', '%d', '%s']
            );

            if ($result === false) {
                throw new \Exception('Failed to save conversation to database. DB Error: ' . $wpdb->last_error);
            }

            error_log("HUchatbots: Conversation saved successfully - ID: $conversation_id");
            echo json_encode(['success' => true, 'message' => 'Conversation saved successfully.']);
        } catch (\Exception $e) {
            error_log('HUchatbots Error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error saving conversation: ' . $e->getMessage()]);
        }
        wp_die();
    }
}
