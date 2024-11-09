<?php
namespace HUchatbots\Admin;

class AdminMenu {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_admin_menu() {
        add_menu_page(
            'HUchatbots',
            'HUchatbots',
            'manage_options',
            'huchatbots',
            array($this, 'display_main_page'),
            'dashicons-format-chat',
            30
        );

        add_submenu_page(
            'huchatbots',
            __('Lista de Agentes', 'huchatbots'),
            __('Todos os Agentes', 'huchatbots'),
            'manage_options',
            'huchatbots',
            array($this, 'display_main_page')
        );

        add_submenu_page(
            'huchatbots',
            __('Adicionar Novo Agente', 'huchatbots'),
            __('Adicionar Novo', 'huchatbots'),
            'manage_options',
            'huchatbots-new',
            array($this, 'display_new_chatbot_page')
        );

        add_submenu_page(
            'huchatbots',
            __('Configurações Globais', 'huchatbots'),
            __('Configurações Globais', 'huchatbots'),
            'manage_options',
            'huchatbots-settings',
            array($this, 'display_settings_page')
        );
    }

    public function register_settings() {
        register_setting('huchatbots_settings', 'huchatbots_default_api_url', 'esc_url_raw');
        register_setting('huchatbots_settings', 'huchatbots_default_theme_color', 'sanitize_hex_color');
        register_setting('huchatbots_settings', 'huchatbots_default_button_color', 'sanitize_hex_color');
        register_setting('huchatbots_settings', 'huchatbots_default_font_family', 'sanitize_text_field');
        register_setting('huchatbots_settings', 'huchatbots_default_avatar');

    }

    public function display_main_page() {
        $chatbot_list = new ChatbotList();
        $chatbot_list->render_chatbot_list_page();
    }

    public function display_new_chatbot_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Você não tem permissões suficientes para acessar esta página.', 'huchatbots'));
        }
        require_once HUCHATBOTS_PLUGIN_DIR . 'includes/admin/views/chatbot-new.php';
    }

    public function display_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Você não tem permissões suficientes para acessar esta página.', 'huchatbots'));
        }
        
        // Exibe mensagens de erro/sucesso
        settings_errors('huchatbots_messages');
        
        require_once HUCHATBOTS_PLUGIN_DIR . 'includes/admin/views/settings.php';
    }
}
