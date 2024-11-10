<?php
/*
Plugin Name: HUchatbots
Description: Sistema avançado de gerenciamento de múltiplos chatbots para LearnDash
Version: 1.0.0
Author: Humana Labs
Text Domain: huchatbots
Domain Path: /languages
*/

// Evita acesso direto ao arquivo
if (!defined('ABSPATH')) {
    exit;
}

// Define constantes
define('HUCHATBOTS_VERSION', '1.0.0');
define('HUCHATBOTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HUCHATBOTS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('HUCHATBOTS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Função de ativação
function huchatbots_activate() {
    $activator_file = HUCHATBOTS_PLUGIN_DIR . 'includes/core/class-activator.php';
    if (file_exists($activator_file)) {
        require_once $activator_file;
        HUchatbots\Core\Activator::activate();
    } else {
        wp_die('Arquivo de ativação não encontrado. Por favor, reinstale o plugin HUchatbots.');
    }
}
register_activation_hook(__FILE__, 'huchatbots_activate');

// Função de desativação
function huchatbots_deactivate() {
    $deactivator_file = HUCHATBOTS_PLUGIN_DIR . 'includes/core/class-deactivator.php';
    if (file_exists($deactivator_file)) {
        require_once $deactivator_file;
        HUchatbots\Core\Deactivator::deactivate();
    } else {
        error_log('Arquivo de desativação do HUchatbots não encontrado.');
    }
}
register_deactivation_hook(__FILE__, 'huchatbots_deactivate');

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'HUchatbots\\';
    $base_dir = HUCHATBOTS_PLUGIN_DIR . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    } else {
        error_log("HUchatbots: File not found for class $class at $file");
    }
});

// Inicia o plugin
function run_huchatbots() {
    error_log('HUchatbots: Initializing plugin');

    // Verifica se o LearnDash está ativo
    if (!class_exists('SFWD_LMS')) {
        add_action('admin_notices', 'huchatbots_learndash_missing_notice');
        error_log('HUchatbots: LearnDash not found');
        return;
    }

    // Carrega o arquivo de texto do plugin
    load_plugin_textdomain('huchatbots', false, dirname(HUCHATBOTS_PLUGIN_BASENAME) . '/languages/');

    try {
        $main_class_file = HUCHATBOTS_PLUGIN_DIR . 'includes/admin/class-huchatbots.php';
        $admin_menu_file = HUCHATBOTS_PLUGIN_DIR . 'includes/admin/class-admin-menu.php';
        $chatbot_list_file = HUCHATBOTS_PLUGIN_DIR . 'includes/admin/class-chatbot-list.php';
        $ajax_handler_file = HUCHATBOTS_PLUGIN_DIR . 'includes/integrations/class-ajax-handler.php';

        $files_to_require = [$main_class_file, $admin_menu_file, $chatbot_list_file, $ajax_handler_file];

        foreach ($files_to_require as $file) {
            if (!file_exists($file)) {
                throw new Exception("Required file not found: $file");
            }
            require_once $file;
        }

        if (!class_exists('HUchatbots\Admin\HUchatbots')) {
            throw new Exception('HUchatbots\Admin\HUchatbots class not found');
        }
        if (!class_exists('HUchatbots\Admin\AdminMenu')) {
            throw new Exception('HUchatbots\Admin\AdminMenu class not found');
        }
        if (!class_exists('HUchatbots\Admin\ChatbotList')) {
            throw new Exception('HUchatbots\Admin\ChatbotList class not found');
        }
        if (!class_exists('HUchatbots\Integrations\AjaxHandler')) {
            throw new Exception('HUchatbots\Integrations\AjaxHandler class not found');
        }

        $plugin = new HUchatbots\Admin\HUchatbots();
        $admin_menu = new HUchatbots\Admin\AdminMenu();
        $chatbot_list = new HUchatbots\Admin\ChatbotList();
        $ajax_handler = new HUchatbots\Integrations\AjaxHandler();

        $chatbot_list->init(); // Inicializa a classe ChatbotList
        $ajax_handler->init(); // Inicializa o manipulador AJAX

        $plugin->run();
        error_log('HUchatbots: Plugin initialized successfully');
    } catch (Exception $e) {
        error_log('HUchatbots Error: ' . $e->getMessage());
        add_action('admin_notices', function() use ($e) {
            echo '<div class="notice notice-error"><p>' . esc_html__('Erro ao inicializar HUchatbots: ', 'huchatbots') . esc_html($e->getMessage()) . '</p></div>';
        });
    }
}
add_action('plugins_loaded', 'run_huchatbots');

// Aviso de que o LearnDash não está instalado
function huchatbots_learndash_missing_notice() {
    ?>
    <div class="notice notice-error">
        <p><?php esc_html_e('HUchatbots requer que o LearnDash esteja instalado e ativado.', 'huchatbots'); ?></p>
    </div>
    <?php
}
