<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>Editar Chatbot</h1>
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <input type="hidden" name="action" value="save_chatbot_settings">
        <input type="hidden" name="course_id" value="<?php echo esc_attr($chatbot['course_id']); ?>">
        <?php wp_nonce_field('save_chatbot_settings', 'chatbot_settings_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th><label for="course_id">Curso</label></th>
                <td>
                    <select name="course_id" id="course_id">
                        <?php
                        $courses = get_posts(['post_type' => 'sfwd-courses', 'numberposts' => -1]);
                        foreach ($courses as $course) {
                            $selected = $chatbot['course_id'] == $course->ID ? 'selected' : '';
                            echo '<option value="' . $course->ID . '" ' . $selected . '>' . esc_html($course->post_title) . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="api_key">Chave da API Dify</label></th>
                <td><input type="text" name="api_key" id="api_key" class="regular-text" value="<?php echo esc_attr($chatbot['api_key']); ?>"></td>
            </tr>
            <tr>
                <th><label for="api_url">URL da API Dify</label></th>
                <td><input type="url" name="api_url" id="api_url" class="regular-text" value="<?php echo esc_url($chatbot['api_url']); ?>"></td>
            </tr>
            <tr>
                <th><label for="bot_name">Nome do Agente</label></th>
                <td><input type="text" name="bot_name" id="bot_name" class="regular-text" value="<?php echo esc_attr($chatbot['bot_name']); ?>"></td>
            </tr>
            <tr>
                <th><label for="bot_avatar">Avatar do Bot</label></th>
                <td>
                    <input type="text" name="bot_avatar" id="bot_avatar" class="regular-text" value="<?php echo esc_url($chatbot['bot_avatar']); ?>" readonly>
                    <input type="button" class="button-secondary" value="Upload Avatar" id="upload_avatar_button">
                    <p class="description">Clique no botão para fazer upload de uma imagem para o avatar do Agente.</p>
                    <div id="avatar_preview" style="margin-top: 10px;">
                        <?php if (!empty($chatbot['bot_avatar'])): ?>
                            <img src="<?php echo esc_url($chatbot['bot_avatar']); ?>" style="max-width: 100px; max-height: 100px;">
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th><label for="theme_color">Cor do Tema</label></th>
                <td><input type="color" name="theme_color" id="theme_color" value="<?php echo esc_attr($chatbot['theme_color']); ?>"></td>
            </tr>
            <tr>
                <th><label for="button_color">Cor do Botão</label></th>
                <td><input type="color" name="button_color" id="button_color" value="<?php echo esc_attr($chatbot['button_color']); ?>"></td>
            </tr>
        </table>
        
        <?php submit_button('Atualizar Agente'); ?>
    </form>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#upload_avatar_button').click(function(e) {
        e.preventDefault();
        var image = wp.media({ 
            title: 'Upload Avatar Image',
            multiple: false
        }).open()
        .on('select', function(e){
            var uploaded_image = image.state().get('selection').first();
            var image_url = uploaded_image.toJSON().url;
            $('#bot_avatar').val(image_url);
            $('#avatar_preview').html('<img src="' + image_url + '" style="max-width: 100px; max-height: 100px;">');
        });
    });
});
</script>
