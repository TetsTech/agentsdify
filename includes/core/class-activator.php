<?php
namespace HUchatbots\Core;

class Activator {
    public static function activate() {
        try {
            self::create_tables();
            self::set_default_options();
            error_log("HUchatbots: Activation completed successfully.");
        } catch (\Exception $e) {
            error_log("HUchatbots activation error: " . $e->getMessage());
            wp_die('Erro ao ativar o plugin HUchatbots. Por favor, verifique o log de erros e tente novamente.');
        }
    }

    public static function create_tables() {
        self::create_chatbot_table();
        self::create_conversation_table();
    }

    private static function create_chatbot_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'huchatbots';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            course_id bigint(20) NOT NULL,
            api_key varchar(255) NOT NULL,
            api_url varchar(255) NOT NULL,
            theme_color varchar(7) DEFAULT '#343541',
            font_family varchar(255) DEFAULT 'Arial, sans-serif',
            button_color varchar(7) DEFAULT '#19C37D',
            bot_name varchar(255) DEFAULT 'Course Assistant',
            welcome_message text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY course_id (course_id)
        ) $charset_collate;";

        self::execute_db_delta($sql, $table_name);
    }

    private static function create_conversation_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'huchatbots_conversations';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            conversation_id varchar(255) NOT NULL,
            user_id bigint(20) NOT NULL,
            course_id bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY course_id (course_id),
            KEY conversation_id (conversation_id)
        ) $charset_collate;";

        self::execute_db_delta($sql, $table_name);
    }

    private static function execute_db_delta($sql, $table_name) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        if (!self::table_exists($table_name)) {
            throw new \Exception("Failed to create table: $table_name");
        }

        error_log("HUchatbots: Table $table_name created or updated successfully.");
    }

    private static function set_default_options() {
        $options = [
            'huchatbots_version' => HUCHATBOTS_VERSION,
            'huchatbots_default_api_url' => 'https://api.dify.ai',
            'huchatbots_default_theme_color' => '#343541',
            'huchatbots_default_button_color' => '#19C37D'
        ];

        foreach ($options as $option_name => $option_value) {
            if (get_option($option_name) === false) {
                add_option($option_name, $option_value);
            }
        }

        error_log("HUchatbots: Default options set or updated successfully.");
    }

    private static function table_exists($table_name) {
        global $wpdb;
        $query = $wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->esc_like($table_name));
        return $wpdb->get_var($query) === $table_name;
    }
}
