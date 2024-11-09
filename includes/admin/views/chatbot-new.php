<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options')) {
    wp_die(__('Você não tem permissão para acessar esta página.', 'huchatbots'));
}

// Obtém a lista de cursos do LearnDash
$courses = get_posts(array(
    'post_type' => 'sfwd-courses',
    'numberposts' => -1,
));

// Enfileira os scripts necessários para o uploader de mídia
wp_enqueue_media();
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php
    // Exibe mensagens de erro ou sucesso
    if (isset($_GET['message'])) {
        if ($_GET['message'] == 'chatbot_add_failed') {
            echo '<div class="notice notice-error"><p>' . esc_html__('Erro ao adicionar o chatbot.', 'huchatbots') . '</p></div>';
        }
    }
    ?>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="huchatbots-form">
        <input type="hidden" name="action" value="huchatbots_save_new_chatbot">
        <?php wp_nonce_field('huchatbots_save_new_chatbot', 'huchatbots_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row"><label for="course_id"><?php esc_html_e('Curso', 'huchatbots'); ?></label></th>
                <td>
                    <select name="course_id" id="course_id" required>
                        <option value=""><?php esc_html_e('Selecione um curso', 'huchatbots'); ?></option>
                        <?php foreach ($courses as $course) : ?>
                            <option value="<?php echo esc_attr($course->ID); ?>"><?php echo esc_html($course->post_title); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="bot_name"><?php esc_html_e('Nome do Agente', 'huchatbots'); ?></label></th>
                <td><input name="bot_name" type="text" id="bot_name" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="bot_avatar"><?php esc_html_e('Avatar do Agente', 'huchatbots'); ?></label></th>
                <td>
                    <input name="bot_avatar" type="text" id="bot_avatar" class="regular-text" readonly>
                    <input type="button" class="button-secondary" value="<?php esc_attr_e('Escolher imagem', 'huchatbots'); ?>" id="upload_avatar_button">
                    <p class="description"><?php esc_html_e('Escolha uma imagem para o avatar do Agente.', 'huchatbots'); ?></p>
                    <div id="avatar_preview" style="margin-top: 10px;"></div>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="api_key"><?php esc_html_e('Chave da API', 'huchatbots'); ?></label></th>
                <td><input name="api_key" type="text" id="api_key" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="api_url"><?php esc_html_e('URL da API', 'huchatbots'); ?></label></th>
                <td><input name="api_url" type="url" id="api_url" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="theme_color"><?php esc_html_e('Cor do Tema', 'huchatbots'); ?></label></th>
                <td><input name="theme_color" type="color" id="theme_color" value="#343541" aria-label="<?php esc_attr_e('Cor do Tema', 'huchatbots'); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><label for="button_color"><?php esc_html_e('Cor do Botão', 'huchatbots'); ?></label></th>
                <td><input name="button_color" type="color" id="button_color" value="#19C37D" aria-label="<?php esc_attr_e('Cor do Botão', 'huchatbots'); ?>"></td>
            </tr>
        </table>
        
        <?php submit_button(__('Adicionar Chatbot', 'huchatbots')); ?>
    </form>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#upload_avatar_button').click(function(e) {
        e.preventDefault();
        var image = wp.media({
            title: '<?php esc_html_e('Escolher Avatar do Chatbot', 'huchatbots'); ?>',
            multiple: false
        }).open()
        .on('select', function(e){
            var uploaded_image = image.state().get('selection').first();
            var image_url = uploaded_image.toJSON().url;
            $('#bot_avatar').val(image_url);
            $('#avatar_preview').html('<img src="' + image_url + '" style="max-width: 100px; max-height: 100px;">');
        });
    });

    // Validação do formulário
    $('#huchatbots-form').submit(function(e) {
        var requiredFields = ['course_id', 'bot_name', 'api_key', 'api_url'];
        var isValid = true;

        requiredFields.forEach(function(field) {
            if (!$('#' + field).val()) {
                isValid = false;
                $('#' + field).addClass('error');
            } else {
                $('#' + field).removeClass('error');
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('<?php esc_html_e('Por favor, preencha todos os campos obrigatórios.', 'huchatbots'); ?>');
        }
    });
});
</script>
