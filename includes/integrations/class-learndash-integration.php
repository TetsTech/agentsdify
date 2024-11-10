<?php
namespace HUchatbots\Integrations;

class LearnDashIntegration {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'render_chatbot_container'));
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, HUCHATBOTS_PLUGIN_URL . 'assets/css/huchatbots-public.css', array(), $this->version, 'all');
        error_log('HUchatbots: Styles enqueued');
    }

    public function enqueue_scripts() {
        if ($this->is_learndash_lesson() && $this->is_chatbot_enabled_for_current_course()) {
            $course_id = $this->get_current_course_id();
            $chatbot_settings = $this->get_chatbot_settings($course_id);

            if ($chatbot_settings) {
                wp_enqueue_script($this->plugin_name, HUCHATBOTS_PLUGIN_URL . 'assets/js/huchatbots-public.js', array('jquery'), $this->version, true);
                
                $localized_data = array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('huchatbots_nonce'),
                    'course_id' => $course_id,
                    'is_learndash_lesson' => true,
                    'is_chatbot_enabled' => true,
                    'plugin_url' => HUCHATBOTS_PLUGIN_URL,
                    'dify_api_key' => $chatbot_settings['api_key'] ?? '',
                    'dify_api_url' => $chatbot_settings['api_url'] ?? ''
                );

                wp_localize_script($this->plugin_name, 'huchatbotsData', $localized_data);

                wp_add_inline_script($this->plugin_name, 'console.log("HUchatbots script loaded"); console.log("HUchatbots data:", huchatbotsData);', 'before');

                error_log('HUchatbots: Script enqueued with data: ' . print_r(wp_json_encode($localized_data), true));
            } else {
                error_log('HUchatbots: Chatbot settings not found for course ID: ' . $course_id);
            }
        } else {
            error_log('HUchatbots: Script not enqueued. Is LearnDash lesson: ' . ($this->is_learndash_lesson() ? 'Yes' : 'No') . ', Is chatbot enabled: ' . ($this->is_chatbot_enabled_for_current_course() ? 'Yes' : 'No'));
        }
    }

    public function render_chatbot_container() {
        error_log('HUchatbots: render_chatbot_container called');
        if ($this->is_learndash_lesson() && $this->is_chatbot_enabled_for_current_course()) {
            error_log('HUchatbots: Conditions met for rendering chatbot');
            $course_id = $this->get_current_course_id();
            $chatbot_settings = $this->get_chatbot_settings($course_id);
            
            if ($chatbot_settings) {
                $this->render_chatbot_html($chatbot_settings);
                error_log('HUchatbots: Chatbot container rendered');
            } else {
                error_log('HUchatbots: No chatbot settings found for course ' . $course_id);
            }
        } else {
            error_log('HUchatbots: Conditions not met for rendering chatbot');
        }
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
            /* CSS styles here */
        </style>
        <?php
        echo '<!-- HUchatbots End -->';
        error_log('HUchatbots: Rendering chatbot for course ID: ' . $settings['course_id']);
    }

    private function is_learndash_lesson() {
        $is_lesson = function_exists('learndash_get_post_type_slug') && 
                     is_singular(learndash_get_post_type_slug('lesson'));
        error_log('HUchatbots: Is LearnDash lesson: ' . ($is_lesson ? 'Yes' : 'No'));
        return $is_lesson;
    }

    private function get_current_course_id() {
        $course_id = 0;
        if (function_exists('learndash_get_course_id')) {
            $course_id = learndash_get_course_id();
        }
        error_log('HUchatbots: Current course ID: ' . $course_id);
        return $course_id;
    }

    private function is_chatbot_enabled_for_current_course() {
        $course_id = $this->get_current_course_id();
        $is_enabled = $this->is_chatbot_enabled_for_course($course_id);
        error_log('HUchatbots: Chatbot enabled for course ' . $course_id . ': ' . ($is_enabled ? 'Yes' : 'No'));
        return $is_enabled;
    }

    private function is_chatbot_enabled_for_course($course_id) {
        if (!$this->table_exists()) {
            error_log('HUchatbots: Database table does not exist');
            return false;
        }
        global $wpdb;
        $table_name = $wpdb->prefix . 'huchatbots';
        $chatbot = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE course_id = %d",
            $course_id
        ));
        return !empty($chatbot);
    }

    private function get_chatbot_settings($course_id) {
        if (!$this->table_exists()) {
            error_log('HUchatbots: Database table does not exist');
            return null;
        }
        global $wpdb;
        $table_name = $wpdb->prefix . 'huchatbots';
        $settings = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE course_id = %d",
            $course_id
        ), ARRAY_A);
        error_log('HUchatbots: Chatbot settings for course ' . $course_id . ': ' . print_r($settings, true));
        return $settings;
    }

    private function table_exists() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'huchatbots';
        return $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    }
}
