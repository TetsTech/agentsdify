<?php
namespace HUchatbots\Admin;

class ChatbotList {
    public function __construct() {
        // O construtor permanece vazio
    }

    public function init() {
        add_action('admin_post_delete_chatbot', array($this, 'delete_chatbot'));
        add_action('admin_post_duplicate_chatbot', array($this, 'duplicate_chatbot'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    public function enqueue_admin_scripts($hook) {
        if ('huchatbots_page_chatbot-edit' !== $hook) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_script('huchatbots-admin-js', HUCHATBOTS_PLUGIN_URL . 'assets/js/huchatbots-admin.js', array('jquery'), HUCHATBOTS_VERSION, true);
    }

    public function render_chatbot_list_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Você não tem permissões suficientes para acessar esta página.', 'huchatbots'));
        }

        $chatbots = $this->get_all_chatbots();
        
        // Adiciona mensagens de sucesso
        $messages = [];
        if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
            $messages[] = __('Chatbot excluído com sucesso.', 'huchatbots');
        }
        if (isset($_GET['duplicated']) && $_GET['duplicated'] == 1) {
            $messages[] = __('Chatbot duplicado com sucesso.', 'huchatbots');
        }
        if (isset($_GET['updated']) && $_GET['updated'] == 1) {
            $messages[] = __('Chatbot atualizado com sucesso.', 'huchatbots');
        }

        // Inclui a view
        require_once HUCHATBOTS_PLUGIN_DIR . 'includes/admin/views/chatbot-list.php';
    }

    private function get_all_chatbots() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'huchatbots';
        return $wpdb->get_results("SELECT * FROM `$table_name` ORDER BY created_at DESC");
    }

    public function delete_chatbot() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Você não tem permissão para realizar esta ação.', 'huchatbots'));
        }

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$id) {
            wp_die(__('ID do chatbot inválido.', 'huchatbots'));
        }

        check_admin_referer('delete_chatbot_' . $id);

        global $wpdb;
        $table_name = $wpdb->prefix . 'huchatbots';
        $result = $wpdb->delete($table_name, array('id' => $id), array('%d'));

        if ($result === false) {
            wp_die(__('Erro ao excluir o chatbot.', 'huchatbots'));
        }

        wp_redirect(add_query_arg(['page' => 'huchatbots', 'deleted' => 1], admin_url('admin.php')));
        exit;
    }

    public function duplicate_chatbot() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Você não tem permissão para realizar esta ação.', 'huchatbots'));
        }

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$id) {
            wp_die(__('ID do chatbot inválido.', 'huchatbots'));
        }

        check_admin_referer('duplicate_chatbot_' . $id);

        global $wpdb;
        $table_name = $wpdb->prefix . 'huchatbots';
        
        $original_chatbot = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);
        if (!$original_chatbot) {
            wp_die(__('Chatbot não encontrado.', 'huchatbots'));
        }

        $new_chatbot = $original_chatbot;
        unset($new_chatbot['id']);
        $new_chatbot['bot_name'] = $new_chatbot['bot_name'] . ' ' . __('(Cópia)', 'huchatbots');
        $new_chatbot['created_at'] = current_time('mysql');

        $result = $wpdb->insert($table_name, $new_chatbot);

        if ($result === false) {
            wp_die(__('Erro ao duplicar o chatbot.', 'huchatbots'));
        }

        wp_redirect(add_query_arg(['page' => 'huchatbots', 'duplicated' => 1], admin_url('admin.php')));
        exit;
    }

    public function render_chatbot_edit_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Você não tem permissões suficientes para acessar esta página.', 'huchatbots'));
        }

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $chatbot = $this->get_chatbot($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->save_chatbot($id);
        }

        // Inclui a view
        require_once HUCHATBOTS_PLUGIN_DIR . 'includes/admin/views/chatbot-edit.php';
    }

    private function get_chatbot($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'huchatbots';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);
    }

    private function save_chatbot($id) {
        check_admin_referer('edit_chatbot_' . $id);

        global $wpdb;
        $table_name = $wpdb->prefix . 'huchatbots';

        $data = array(
            'bot_name' => sanitize_text_field($_POST['bot_name']),
            'course_id' => intval($_POST['course_id']),
            'api_key' => sanitize_text_field($_POST['api_key']),
            'api_url' => esc_url_raw($_POST['api_url']),
            'bot_avatar' => esc_url_raw($_POST['bot_avatar']),
            'updated_at' => current_time('mysql')
        );

        $format = array('%s', '%d', '%s', '%s', '%s', '%s');

        if ($id === 0) {
            $data['created_at'] = current_time('mysql');
            $wpdb->insert($table_name, $data, $format);
            $id = $wpdb->insert_id;
        } else {
            $wpdb->update($table_name, $data, array('id' => $id), $format, array('%d'));
        }

        wp_redirect(add_query_arg(['page' => 'huchatbots', 'action' => 'edit', 'id' => $id, 'updated' => 1], admin_url('admin.php')));
        exit;
    }
}
