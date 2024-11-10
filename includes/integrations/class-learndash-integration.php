<?php
namespace HUchatbots\Integrations;

class LearnDashIntegration {
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_chatbot_script'));
        add_action('wp_footer', array($this, 'render_chatbot_container'));
    }

    public function enqueue_chatbot_script() {
        if ($this->is_learndash_lesson() && $this->is_chatbot_enabled_for_current_course()) {
            wp_enqueue_script('huchatbots-frontend', HUCHATBOTS_PLUGIN_URL . 'assets/js/huchatbots-public.js', array('jquery'), HUCHATBOTS_VERSION, true);
            wp_localize_script('huchatbots-frontend', 'huchatbotsData', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('huchatbots_nonce'),
                'course_id' => $this->get_current_course_id()
            ));
        }
    }

    public function render_chatbot_container() {
        if ($this->is_learndash_lesson() && $this->is_chatbot_enabled_for_current_course()) {
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
            }
        }
    }

    private function is_learndash_lesson() {
        return function_exists('learndash_get_post_type_slug') && 
               is_singular(learndash_get_post_type_slug('lesson'));
    }

    private function get_current_course_id() {
        if (function_exists('learndash_get_course_id')) {
            return learndash_get_course_id();
        }
        return 0;
    }

    private function is_chatbot_enabled_for_current_course() {
        $course_id = $this->get_current_course_id();
        return $this->is_chatbot_enabled_for_course($course_id);
    }

    private function is_chatbot_enabled_for_course($course_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'huchatbots';
        $chatbot = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE course_id = %d",
            $course_id
        ));
        return !empty($chatbot);
    }

    private function get_chatbot_settings($course_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'huchatbots';
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE course_id = %d",
            $course_id
        ), ARRAY_A);
    }
}

new LearnDashIntegration();
