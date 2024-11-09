<?php
namespace HUchatbots\Frontend;

class Shortcode {
    /**
     * Registra o shortcode com o WordPress.
     */
    public function register() {
        add_shortcode('huchatbot', array($this, 'chatbot_shortcode'));
    }

    /**
     * Callback para o shortcode do chatbot.
     *
     * @param array $atts Atributos passados para o shortcode.
     * @return string HTML do chatbot.
     */
    public function chatbot_shortcode($atts) {
        // Mescla os atributos padrão com os fornecidos
        $atts = shortcode_atts(array(
            'id' => '',  // ID do chatbot
            'theme' => 'default',  // Tema do chatbot
        ), $atts, 'huchatbot');

        // Verifica se um ID de chatbot foi fornecido
        if (empty($atts['id'])) {
            return '<p>Erro: ID do chatbot não especificado.</p>';
        }

        // Recupera as configurações do chatbot do banco de dados
        $chatbot = $this->get_chatbot_settings($atts['id']);

        if (!$chatbot) {
            return '<p>Erro: Chatbot não encontrado.</p>';
        }

        // Enfileira os scripts e estilos necessários
        $this->enqueue_chatbot_assets();

        // Gera o HTML do chatbot
        $output = $this->generate_chatbot_html($chatbot, $atts['theme']);

        return $output;
    }

    /**
     * Recupera as configurações do chatbot do banco de dados.
     *
     * @param int $id ID do chatbot.
     * @return array|false Configurações do chatbot ou false se não encontrado.
     */
    private function get_chatbot_settings($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'huchatbots';
        
        $chatbot = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $id
        ), ARRAY_A);

        return $chatbot;
    }

    /**
     * Enfileira os scripts e estilos necessários para o chatbot.
     */
    private function enqueue_chatbot_assets() {
        wp_enqueue_style('huchatbot-style', HUCHATBOTS_PLUGIN_URL . 'assets/css/frontend.css', array(), HUCHATBOTS_VERSION);
        wp_enqueue_script('huchatbot-script', HUCHATBOTS_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), HUCHATBOTS_VERSION, true);
        
        // Passa variáveis para o script
        wp_localize_script('huchatbot-script', 'huchatbotData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('huchatbot-nonce')
        ));
    }

    /**
     * Gera o HTML do chatbot.
     *
     * @param array $chatbot Configurações do chatbot.
     * @param string $theme Tema do chatbot.
     * @return string HTML do chatbot.
     */
    private function generate_chatbot_html($chatbot, $theme) {
        ob_start();
        ?>
        <div id="huchatbot-<?php echo esc_attr($chatbot['id']); ?>" 
             class="huchatbot-container <?php echo esc_attr($theme); ?>"
             data-id="<?php echo esc_attr($chatbot['id']); ?>"
             data-api-key="<?php echo esc_attr($chatbot['api_key']); ?>"
             data-api-url="<?php echo esc_url($chatbot['api_url']); ?>">
            <div class="huchatbot-header" style="background-color: <?php echo esc_attr($chatbot['theme_color']); ?>;">
                <h3><?php echo esc_html($chatbot['bot_name']); ?></h3>
            </div>
            <div class="huchatbot-messages"></div>
            <div class="huchatbot-input">
                <input type="text" placeholder="Digite sua mensagem...">
                <button style="background-color: <?php echo esc_attr($chatbot['button_color']); ?>;">Enviar</button>
            </div>
        </div>
        <style>
            #huchatbot-<?php echo esc_attr($chatbot['id']); ?> {
                font-family: <?php echo esc_attr($chatbot['font_family']); ?>;
            }
        </style>
        <?php
        return ob_get_clean();
    }
}
