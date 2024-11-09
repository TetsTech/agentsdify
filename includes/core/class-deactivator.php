<?php
namespace HUchatbots\Core;

class Deactivator {
    public static function deactivate() {
        self::maybe_delete_tables();
        self::maybe_delete_options();
        self::clear_scheduled_events();
        self::deactivation_notice();
    }

    private static function maybe_delete_tables() {
        // Descomente as linhas abaixo se você quiser excluir as tabelas na desativação
        // Tenha cuidado, pois isso resultará em perda de dados
        /*
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}huchatbots");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}huchatbots_conversations");
        */
    }

    private static function maybe_delete_options() {
        // Descomente as linhas abaixo se você quiser excluir as opções na desativação
        /*
        delete_option('huchatbots_version');
        delete_option('huchatbots_default_api_url');
        delete_option('huchatbots_default_theme_color');
        delete_option('huchatbots_default_button_color');
        */
    }

    private static function clear_scheduled_events() {
        // Limpe quaisquer eventos agendados que o seu plugin possa ter criado
        wp_clear_scheduled_hook('huchatbots_daily_cleanup');
        // Adicione mais eventos conforme necessário
    }

    private static function deactivation_notice() {
        // Adicione uma opção que será verificada na próxima carga da página de administração
        // para exibir uma mensagem de desativação
        add_option('huchatbots_deactivated', '1');
    }
}
