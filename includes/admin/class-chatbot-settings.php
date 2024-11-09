<?php
namespace HUchatbots\Admin;

class ChatbotSettings {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_chatbot_settings_page'));
        add_action('admin_post_save_chatbot_settings', array($this, 'save_chatbot_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    public function add_chatbot_settings_page() {
        add_submenu_page(
            'edit.php?post_type=sfwd-courses',
            'Configurações do Chatbot',
            'Chatbot',
            'manage_options',
            'dify-chatbot-settings',
            array($this, 'render_settings_page')
        );
    }

    public function enqueue_admin_scripts($hook) {
        if ('sfwd-courses_page_dify-chatbot-settings' !== $hook) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_script('huchatbots-admin-js', HUCHATBOTS_PLUGIN_URL . 'assets/js/huchatbots-admin.js', array('jquery'), HUCHATBOTS_VERSION, true);
    }

    public function render_settings_page() {
        $course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
        $chatbot = $this->get_chatbot_settings($course_id);
        
        // Inclui o template
        require_once HUCHATBOTS_PLUGIN_DIR . 'includes/admin/views/chatbot-edit.php';
    }

    public function save_chatbot_settings() {
        if (!isset($_POST['chatbot_settings_nonce']) || !wp_verify_nonce($_POST['chatbot_settings_nonce'], 'save_chatbot_settings')) {
            wp_die('Ação não autorizada.');
        }

        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        if (!$course_id) {
            wp_die('ID do curso inválido.');
        }

        // Log dos dados recebidos
        error_log('Dados recebidos no save_chatbot_settings: ' . print_r($_POST, true));

        $settings = array(
            'api_key' => sanitize_text_field($_POST['api_key']),
            'api_url' => esc_url_raw($_POST['api_url']),
            'theme_color' => sanitize_hex_color($_POST['theme_color']),
            'font_family' => sanitize_text_field($_POST['font_family']),
            'button_color' => sanitize_hex_color($_POST['button_color']),
            'bot_name' => sanitize_text_field($_POST['bot_name']),
            'bot_avatar' => esc_url_raw($_POST['bot_avatar']),
        );

        // Log das configurações após sanitização
        error_log('Configurações a serem salvas: ' . print_r($settings, true));

        $result = $this->update_chatbot_settings($course_id, $settings);

        if ($result === false) {
            wp_die('Erro ao salvar as configurações do chatbot.');
        }

        wp_redirect(add_query_arg(array('page' => 'dify-chatbot-settings', 'course_id' => $course_id, 'updated' => 'true'), admin_url('edit.php?post_type=sfwd-courses')));
        exit;
    }

    private function get_chatbot_settings($course_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'huchatbots';
        $chatbot = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE course_id = %d",
            $course_id
        ), ARRAY_A);

        return $chatbot ?: array();
    }

    private function update_chatbot_settings($course_id, $settings) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'huchatbots';

        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE course_id = %d",
            $course_id
        ));

        if ($existing) {
            $result = $wpdb->update($table_name, $settings, array('course_id' => $course_id));
        } else {
            $settings['course_id'] = $course_id;
            $result = $wpdb->insert($table_name, $settings);
        }

        // Log do resultado da operação no banco de dados
        error_log('Resultado da operação no banco de dados: ' . print_r($result, true));
        if ($result === false) {
            error_log('Erro do MySQL: ' . $wpdb->last_error);
        }

        return $result !== false;
    }
}

new ChatbotSettings();
