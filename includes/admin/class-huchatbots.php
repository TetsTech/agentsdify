<?php
namespace HUchatbots\Admin;

use HUchatbots\Core\Loader;
use Exception;

class HUchatbots {
    private $loader;
    private $plugin_name;
    private $version;

    public function __construct() {
        $this->plugin_name = 'huchatbots';
        $this->version = HUCHATBOTS_VERSION;
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        $files_to_require = [
            'includes/core/class-loader.php',
            'includes/admin/class-admin-menu.php',
            'includes/admin/class-chatbot-list.php',
            'includes/admin/class-chatbot-manager.php',
            'includes/admin/class-chatbot-settings.php',
            'includes/frontend/class-public-display.php'
        ];

        foreach ($files_to_require as $file) {
            $file_path = HUCHATBOTS_PLUGIN_DIR . $file;
            if (!file_exists($file_path)) {
                throw new Exception("Required file not found: $file");
            }
            require_once $file_path;
        }

        $this->loader = new Loader();
    }

    private function define_admin_hooks() {
        $admin_menu = new AdminMenu();
        $this->loader->add_action('admin_menu', $admin_menu, 'add_admin_menu');

        $chatbot_list = new ChatbotList();
        $this->loader->add_action('admin_init', $chatbot_list, 'init');

        $chatbot_manager = new ChatbotManager();
        $this->loader->add_action('admin_post_huchatbots_save_chatbot', $chatbot_manager, 'save_chatbot');
        $this->loader->add_action('admin_post_huchatbots_delete_chatbot', $chatbot_manager, 'delete_chatbot');

        $chatbot_settings = new ChatbotSettings();
        $this->loader->add_action('admin_menu', $chatbot_settings, 'add_chatbot_settings_page');
        $this->loader->add_action('admin_post_save_chatbot_settings', $chatbot_settings, 'save_chatbot_settings');
    }

    private function define_public_hooks() {
        $public_display = new \HUchatbots\Integrations\LearnDashIntegration($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('wp_enqueue_scripts', $public_display, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $public_display, 'enqueue_scripts');
        $this->loader->add_action('wp_footer', $public_display, 'display_chatbot');
    }

    public function run() {
        try {
            $this->loader->run();
        } catch (Exception $e) {
            error_log('HUchatbots Error: ' . $e->getMessage());
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error"><p>' . esc_html__('Erro ao executar HUchatbots: ', 'huchatbots') . esc_html($e->getMessage()) . '</p></div>';
            });
        }
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_loader() {
        return $this->loader;
    }

    public function get_version() {
        return $this->version;
    }
}
