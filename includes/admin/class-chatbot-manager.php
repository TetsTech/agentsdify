<?php
namespace HUchatbots\Admin;

class ChatbotManager {
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'huchatbots';

        add_action('admin_post_huchatbots_save_new_chatbot', array($this, 'save_new_chatbot'));
        add_action('admin_post_huchatbots_update_chatbot', array($this, 'update_chatbot'));
        add_action('admin_post_huchatbots_delete_chatbot', array($this, 'delete_chatbot'));
    }

    public function save_new_chatbot() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Você não tem permissão para realizar esta ação.', 'huchatbots'));
        }

        check_admin_referer('huchatbots_save_new_chatbot', 'huchatbots_nonce');

        $chatbot_data = $this->sanitize_chatbot_data($_POST);

        global $wpdb;
        $result = $wpdb->insert($this->table_name, $chatbot_data);

        if ($result === false) {
            wp_redirect(add_query_arg('message', 'chatbot_add_failed', admin_url('admin.php?page=huchatbots-new')));
        } else {
            wp_redirect(add_query_arg('message', 'chatbot_added', admin_url('admin.php?page=huchatbots')));
        }
        exit;
    }

    public function update_chatbot() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Você não tem permissão para realizar esta ação.', 'huchatbots'));
        }

        check_admin_referer('huchatbots_update_chatbot', 'huchatbots_nonce');

        $chatbot_id = isset($_POST['chatbot_id']) ? intval($_POST['chatbot_id']) : 0;
        if (!$chatbot_id) {
            wp_die(__('ID do chatbot inválido.', 'huchatbots'));
        }

        $chatbot_data = $this->sanitize_chatbot_data($_POST);

        global $wpdb;
        $result = $wpdb->update($this->table_name, $chatbot_data, array('id' => $chatbot_id));

        if ($result === false) {
            wp_redirect(add_query_arg('message', 'chatbot_update_failed', admin_url('admin.php?page=huchatbots-edit&id=' . $chatbot_id)));
        } else {
            wp_redirect(add_query_arg('message', 'chatbot_updated', admin_url('admin.php?page=huchatbots')));
        }
        exit;
    }

    public function delete_chatbot() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Você não tem permissão para realizar esta ação.', 'huchatbots'));
        }

        $chatbot_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$chatbot_id) {
            wp_die(__('ID do chatbot inválido.', 'huchatbots'));
        }

        check_admin_referer('delete_chatbot_' . $chatbot_id);

        global $wpdb;
        $result = $wpdb->delete($this->table_name, array('id' => $chatbot_id), array('%d'));

        if ($result === false) {
            wp_redirect(add_query_arg('message', 'chatbot_delete_failed', admin_url('admin.php?page=huchatbots')));
        } else {
            wp_redirect(add_query_arg('message', 'chatbot_deleted', admin_url('admin.php?page=huchatbots')));
        }
        exit;
    }

    public function get_all_chatbots() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$this->table_name} ORDER BY id DESC");
    }

    public function get_chatbot($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id));
    }

    private function sanitize_chatbot_data($data) {
        return array(
            'course_id' => isset($data['course_id']) ? intval($data['course_id']) : 0,
            'api_key' => sanitize_text_field($data['api_key']),
            'api_url' => esc_url_raw($data['api_url']),
            'theme_color' => sanitize_hex_color($data['theme_color']),
            'font_family' => sanitize_text_field($data['font_family']),
            'button_color' => sanitize_hex_color($data['button_color']),
            'bot_name' => sanitize_text_field($data['bot_name']),
            'bot_avatar' => esc_url_raw($data['bot_avatar']),
            'welcome_message' => sanitize_textarea_field($data['welcome_message']),
        );
    }
}
