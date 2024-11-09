<?php
// Evita acesso direto ao arquivo
if (!defined('ABSPATH')) {
    exit;
}

// Verifica as permissões do usuário
if (!current_user_can('manage_options')) {
    return;
}

// Salva as configurações se o formulário for submetido
if (isset($_POST['huchatbots_save_settings'])) {
    check_admin_referer('huchatbots_admin_settings');
    // Processa e salva as configurações aqui
    // Por exemplo:
    update_option('huchatbots_default_api_url', sanitize_text_field($_POST['default_api_url']));
    update_option('huchatbots_default_theme_color', sanitize_hex_color($_POST['default_theme_color']));
    
    // Exibe mensagem de sucesso
    echo '<div class="notice notice-success is-dismissible"><p>Configurações salvas com sucesso!</p></div>';
}

// Recupera as configurações atuais
$default_api_url = get_option('huchatbots_default_api_url', 'https://dify.humana.team');
$default_theme_color = get_option('huchatbots_default_theme_color', '#007bff');
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('huchatbots_admin_settings'); ?>
        
        <table class="form-table">
            <tr valign="top">
                <th scope="row">URL Padrão da API</th>
                <td>
                    <input type="url" name="default_api_url" value="<?php echo esc_url($default_api_url); ?>" class="regular-text">
                    <p class="description">Digite a URL padrão da API do Dify.</p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Cor do Tema Padrão</th>
                <td>
                    <input type="color" name="default_theme_color" value="<?php echo esc_attr($default_theme_color); ?>">
                    <p class="description">Escolha a cor padrão do tema para os Agentes.</p>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="huchatbots_save_settings" class="button-primary" value="Salvar Configurações">
        </p>
    </form>

    <h2>Chatbots Cadastrados</h2>
    <?php
    // Aqui você pode adicionar uma tabela ou lista dos chatbots cadastrados
    // Por exemplo:
    $chatbots = []; // Substitua isso por uma função que recupere os chatbots do banco de dados
    if (empty($chatbots)) {
        echo '<p>Nenhum Agente cadastrado ainda.</p>';
    } else {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Nome</th><th>Curso</th><th>Ações</th></tr></thead>';
        echo '<tbody>';
        foreach ($chatbots as $chatbot) {
            echo '<tr>';
            echo '<td>' . esc_html($chatbot['name']) . '</td>';
            echo '<td>' . esc_html($chatbot['course']) . '</td>';
            echo '<td>';
            echo '<a href="#" class="button">Editar</a> ';
            echo '<a href="#" class="button button-secondary">Excluir</a>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }
    ?>

    <p>
        <a href="<?php echo esc_url(admin_url('admin.php?page=huchatbots-new')); ?>" class="button button-primary">Adicionar Novo Agente</a>
    </p>
</div>
