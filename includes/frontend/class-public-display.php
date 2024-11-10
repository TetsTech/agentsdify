<?php
namespace HUchatbots\Frontend;

class PublicDisplay {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'display_chatbot'));
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, HUCHATBOTS_PLUGIN_URL . 'assets/css/huchatbots-public.css', array(), $this->version, 'all');
        error_log('HUchatbots: Styles enqueued');
    }

    public function enqueue_scripts() {
        error_log('HUchatbots: Enqueuing scripts');
        
        wp_enqueue_script($this->plugin_name, HUCHATBOTS_PLUGIN_URL . 'assets/js/huchatbots-public.js', array('jquery'), $this->version, true);

        $course_id = learndash_get_course_id();
        $chatbot_settings = $this->get_chatbot_settings($course_id);

        $localized_data = array(
            'course_id' => $course_id,
            'dify_api_key' => $chatbot_settings['api_key'],
            'dify_api_url' => $chatbot_settings['api_url']
        );
        wp_localize_script($this->plugin_name, 'huchatbotsData', $localized_data);

        wp_add_inline_script($this->plugin_name, 'console.log("HUchatbots script loaded"); console.log("HUchatbots data:", huchatbotsData);', 'before');

        error_log('HUchatbots: Scripts enqueued. Localized data: ' . print_r($localized_data, true));
    }

    public function display_chatbot() {
        error_log('HUchatbots: display_chatbot called');

        if (!function_exists('learndash_get_post_type_slug') || 
            get_post_type() !== learndash_get_post_type_slug('lesson')) {
            error_log('HUchatbots: Not a LearnDash lesson page, chatbot not displayed');
            return;
        }
    
        $course_id = learndash_get_course_id();
        error_log('HUchatbots: Course ID: ' . $course_id);

        $chatbot_settings = $this->get_chatbot_settings($course_id);
    
        if (empty($chatbot_settings)) {
            error_log('HUchatbots: No chatbot settings found for this course');
            return;
        }
    
        error_log('HUchatbots: Chatbot settings: ' . print_r($chatbot_settings, true));
        $this->render_chatbot_html($chatbot_settings);
    }    

    private function get_chatbot_settings($course_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'huchatbots';
        
        $chatbot = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE course_id = %d",
            $course_id
        ), ARRAY_A);

        return $chatbot;
    }

    private function render_chatbot_html($settings) {
        $avatar_url = esc_url($settings['bot_avatar'] ?? HUCHATBOTS_PLUGIN_URL . 'assets/images/default-avatar.png');
        echo '<!-- HUchatbots Start -->';
        ?>
        <div id="huchatbot-wrapper" class="huchatbot-wrapper">
            <div id="huchatbot-toggle" class="huchatbot-toggle">
                <img src="<?php echo $avatar_url; ?>" alt="Chatbot Avatar" class="huchatbot-avatar">
            </div>
            <div id="huchatbot-container" class="huchatbot-container" data-course-id="<?php echo esc_attr($settings['course_id']); ?>">
                <div class="huchatbot-header">
                    <img src="<?php echo $avatar_url; ?>" alt="Chatbot Avatar" class="huchatbot-avatar">
                    <h3><?php echo esc_html($settings['bot_name']); ?></h3>
                    <button class="huchatbot-maximize" title="Maximizar">&#x26F6;</button>
                    <button class="huchatbot-close" title="Fechar">&times;</button>
                </div>
                <div class="huchatbot-messages">
                    <div class="huchatbot-message huchatbot-message-bot">
                        <img src="<?php echo $avatar_url; ?>" alt="Bot Avatar" class="huchatbot-message-avatar">
                        <p>Olá, eu sou o professor e estou aqui para tirar suas duvidas!</p>
                    </div>
                </div>
                <div class="huchatbot-input">
                    <textarea placeholder="<?php esc_attr_e('Digite sua mensagem...', 'huchatbots'); ?>"></textarea>
                    <button>
                        <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </div>
                <div class="huchatbot-resize-handle"></div>
            </div>
        </div>
        <style>
             .huchatbot-wrapper {
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 9999;
                font-family: 'Söhne', 'Helvetica Neue', Arial, sans-serif;
            }
            .huchatbot-toggle {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background-color: #202123;
                cursor: pointer;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
            }
            .huchatbot-avatar {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .huchatbot-container {
                position: fixed;
                bottom: 20px;
                right: 20px;
                width: 350px;
                min-width: 350px;
                height: 500px;
                min-height: 500px;
                background-color: #ffffff;
                border: 1px solid #ccc;
                border-radius: 10px;
                overflow: hidden;
                display: flex;
                flex-direction: column;
                transition: all 0.3s ease;
            }
            .huchatbot-header {
                display: flex;
                align-items: center;
                padding: 10px 15px;
                background-color: #202123;
                border-bottom: 1px solid #4d4d4f;
            }
            .huchatbot-header .huchatbot-avatar {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                margin-right: 10px;
            }
            .huchatbot-header h3 {
                margin: 0;
                font-size: 16px;
                color: #ffffff;
                flex-grow: 1;
            }
            .huchatbot-close,
            .huchatbot-maximize {
                background: none;
                border: none;
                font-size: 20px;
                cursor: pointer;
                color: #ffffff;
                margin-left: 10px;
            }
            .huchatbot-messages {
                flex-grow: 1;
                overflow-y: auto;
                padding: 15px;
                background-color: #343541;
            }
            .huchatbot-input {
                display: flex;
                padding: 15px;
                background-color: #40414f;
                border-top: 1px solid #4d4d4f;
            }
            .huchatbot-input textarea {
                flex-grow: 1;
                margin-right: 10px;
                padding: 8px;
                border: 1px solid #565869;
                border-radius: 5px;
                resize: none;
                height: 50px;
                font-family: inherit;
                background-color: #40414f;
                color: #000000 !important;
            }
            .huchatbot-input textarea::placeholder {
                color: #8e8ea0;
            }
            .huchatbot-input button {
                background-color: #202123;
                color: #ffffff;
                border: none;
                border-radius: 5px;
                padding: 8px 12px;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .huchatbot-input button:hover {
                background-color: #2b2c2f;
            }
            .huchatbot-message {
                display: flex;
                align-items: flex-start;
                margin-bottom: 15px;
            }
            .huchatbot-message-avatar {
                width: 30px;
                height: 30px;
                border-radius: 50%;
                margin-right: 10px;
            }
            .huchatbot-message-content {
                background-color: #444654;
                padding: 10px;
                border-radius: 10px;
                max-width: 80%;
            }
            .huchatbot-message-content p {
                margin: 0;
                color: #ffffff;
            }
            .huchatbot-message.user {
                justify-content: flex-end;
                color: #ffffff;
            }
            .huchatbot-message.user .huchatbot-message-content {
                background-color: #19c37d;
            }
            .huchatbot-container {
                display: none;
            }
            .huchatbot-container.huchatbot-visible {
                display: flex;
            }
            .huchatbot-container.huchatbot-maximized {
                width: 100% !important;
                height: 100% !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                bottom: 0 !important;
            }
            .huchatbot-resize-handle {
                position: absolute;
                top: 0;
                left: 0;
                width: 10px;
                height: 10px;
                cursor: nwse-resize;
                z-index: 1000;
            }
        </style>
        <?php
        echo '<!-- HUchatbots End -->';
        error_log('HUchatbots: Rendering chatbot for course ID: ' . $settings['course_id']);
    }    
}