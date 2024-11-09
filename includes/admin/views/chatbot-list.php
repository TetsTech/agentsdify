<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('HUchatbots - Lista de Agentes', 'huchatbots'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=huchatbots-new')); ?>" class="page-title-action"><?php esc_html_e('Adicionar Novo Chatbot', 'huchatbots'); ?></a>
    
    <?php
    if (!empty($messages)) {
        foreach ($messages as $message) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
        }
    }
    ?>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e('ID', 'huchatbots'); ?></th>
                <th><?php esc_html_e('Nome do Curso', 'huchatbots'); ?></th>
                <th><?php esc_html_e('Nome do Agente', 'huchatbots'); ?></th>
                <th><?php esc_html_e('Data de Criação', 'huchatbots'); ?></th>
                <th><?php esc_html_e('Ações', 'huchatbots'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (!empty($chatbots)):
                foreach ($chatbots as $chatbot): 
            ?>
                <tr>
                    <td><?php echo esc_html($chatbot->id); ?></td>
                    <td><?php echo esc_html(get_the_title($chatbot->course_id)); ?></td>
                    <td><?php echo esc_html($chatbot->bot_name); ?></td>
                    <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($chatbot->created_at))); ?></td>
                    <td>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=dify-chatbot-settings&course_id=' . $chatbot->course_id)); ?>" class="button"><?php esc_html_e('Editar', 'huchatbots'); ?></a>
                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=delete_chatbot&id=' . $chatbot->id), 'delete_chatbot_' . $chatbot->id)); ?>" class="button" onclick="return confirm('<?php esc_attr_e('Tem certeza que deseja excluir este chatbot?', 'huchatbots'); ?>');"><?php esc_html_e('Excluir', 'huchatbots'); ?></a>
                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=duplicate_chatbot&id=' . $chatbot->id), 'duplicate_chatbot_' . $chatbot->id)); ?>" class="button"><?php esc_html_e('Duplicar', 'huchatbots'); ?></a>
                    </td>
                </tr>
            <?php 
                endforeach;
            else:
            ?>
                <tr>
                    <td colspan="6"><?php esc_html_e('Nenhum chatbot encontrado.', 'huchatbots'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
