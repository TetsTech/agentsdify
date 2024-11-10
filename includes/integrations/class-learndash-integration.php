<?php
namespace HUchatbots\Integrations;

class LearnDashIntegration {
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_chatbot_script'));
        add_action('wp_footer', array($this, 'render_chatbot_container'));
    }

    public function init() {
        // Initialization code if needed
    }

    public function enqueue_chatbot_script() {
        if ($this->is_learndash_lesson() && $this->is_chatbot_enabled_for_current_course()) {
            $course_id = $this->get_current_course_id();
            $chatbot_settings = $this->get_chatbot_settings($course_id);
    
            if ($chatbot_settings) {
                wp_enqueue_script('huchatbots-public', HUCHATBOTS_PLUGIN_URL . 'assets/js/huchatbots-public.js', array('jquery'), HUCHATBOTS_VERSION, true);
                
                $localized_data = array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('huchatbots_nonce'),
                    'course_id' => $course_id,
                    'dify_api_key' => $chatbot_settings['api_key'],
                    'dify_api_url' => $chatbot_settings['api_url'],
                    'is_learndash_lesson' => true,
                    'is_chatbot_enabled' => true
                );
    
                wp_localize_script('huchatbots-public', 'huchatbotsData', $localized_data);
    
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
                ?>
                <div id="huchatbot-container" 
                     data-course-id="<?php echo esc_attr($course_id); ?>"
                     data-theme-color="<?php echo esc_attr($chatbot_settings['theme_color']); ?>"
                     data-button-color="<?php echo esc_attr($chatbot_settings['button_color']); ?>"
                     style="display: none;">
                    <!-- O conteúdo do chatbot será renderizado aqui pelo JavaScript -->
                </div>
                <?php
                error_log('HUchatbots: Chatbot container rendered');
            } else {
                error_log('HUchatbots: No chatbot settings found for course ' . $course_id);
            }
        } else {
            error_log('HUchatbots: Conditions not met for rendering chatbot');
        }
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
