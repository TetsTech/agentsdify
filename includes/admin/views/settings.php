<?php
if (!defined('ABSPATH')) {
    exit;
}

// Enfileira os scripts necessários para o uploader de mídia
wp_enqueue_media();
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form method="post" action="options.php">
        <?php settings_fields('huchatbots_settings'); ?>
        <?php do_settings_sections('huchatbots_settings'); ?>
        
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php esc_html_e('URL Padrão da API Dify', 'huchatbots'); ?></th>
                <td><input type="url" name="huchatbots_default_api_url" value="<?php echo esc_url(get_option('huchatbots_default_api_url')); ?>" class="regular-text" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Cor Padrão do Tema', 'huchatbots'); ?></th>
                <td><input type="color" name="huchatbots_default_theme_color" value="<?php echo esc_attr(get_option('huchatbots_default_theme_color', '#343541')); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Cor Padrão do Botão', 'huchatbots'); ?></th>
                <td><input type="color" name="huchatbots_default_button_color" value="<?php echo esc_attr(get_option('huchatbots_default_button_color', '#19C37D')); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e('Avatar Padrão do Agente', 'huchatbots'); ?></th>
                <td>
                    <input type="text" name="huchatbots_default_avatar" id="huchatbots_default_avatar" value="<?php echo esc_url(get_option('huchatbots_default_avatar')); ?>" class="regular-text" />
                    <input type="button" class="button-secondary" value="<?php esc_attr_e('Escolher imagem', 'huchatbots'); ?>" id="upload_avatar_button" />
                    <p class="description"><?php esc_html_e('Escolha uma imagem para o avatar padrão do Agente.', 'huchatbots'); ?></p>
                    <div id="avatar_preview" style="margin-top: 10px;">
                        <?php 
                        $default_avatar = get_option('huchatbots_default_avatar');
                        if (!empty($default_avatar)) {
                            echo '<img src="' . esc_url($default_avatar) . '" style="max-width: 100px; max-height: 100px;">';
                        }
                        ?>
                    </div>
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#upload_avatar_button').click(function(e) {
        e.preventDefault();
        var image = wp.media({
            title: '<?php esc_html_e('Escolher Avatar Padrão do Agente', 'huchatbots'); ?>',
            multiple: false
        }).open()
        .on('select', function(e){
            var uploaded_image = image.state().get('selection').first();
            var image_url = uploaded_image.toJSON().url;
            $('#huchatbots_default_avatar').val(image_url);
            $('#avatar_preview').html('<img src="' + image_url + '" style="max-width: 100px; max-height: 100px;">');
        });
    });
});
</script>
