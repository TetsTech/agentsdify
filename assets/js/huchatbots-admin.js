(function($) {
    'use strict';

    $(document).ready(function() {
        console.log('HUchatbots admin script loaded');

        // Funcionalidade de upload de avatar
        $('#upload_avatar_button').click(function(e) {
            e.preventDefault();
            console.log('Upload avatar button clicked');
            var image = wp.media({ 
                title: 'Upload Avatar Image',
                multiple: false
            }).open()
            .on('select', function(e){
                var uploaded_image = image.state().get('selection').first();
                var image_url = uploaded_image.toJSON().url;
                console.log('Selected image URL:', image_url);
                $('#bot_avatar').val(image_url);
                $('#avatar_preview').html('<img src="' + image_url + '" style="max-width: 100px; max-height: 100px;">');
            });
        });

        // Confirmação de exclusão
        $('.delete-chatbot').click(function(e) {
            if (!confirm('Tem certeza que deseja excluir este Agente?')) {
                e.preventDefault();
            }
        });

        // Validação do formulário
        $('form').submit(function(e) {
            console.log('Form submitted');
            var botName = $('#bot_name').val();
            var courseId = $('#course_id').val();
            var apiKey = $('#api_key').val();
            var apiUrl = $('#api_url').val();
            var botAvatar = $('#bot_avatar').val();

            console.log('Form data:', {
                botName: botName,
                courseId: courseId,
                apiKey: apiKey,
                apiUrl: apiUrl,
                botAvatar: botAvatar
            });

            if (!botName || !courseId || !apiKey || !apiUrl) {
                alert('Por favor, preencha todos os campos obrigatórios.');
                e.preventDefault();
            }
        });

        // Carregamento dinâmico de cursos (se aplicável)
        $('#course_id').on('change', function() {
            var courseId = $(this).val();
            console.log('Selected course ID:', courseId);
            // Aqui você pode adicionar lógica para carregar informações específicas do curso, se necessário
        });

        // Toggle de seções avançadas (se aplicável)
        $('.toggle-advanced').click(function(e) {
            e.preventDefault();
            $('.advanced-settings').toggle();
        });

        // Inicialização de tooltips (se você estiver usando uma biblioteca de UI)
        if ($.fn.tooltip) {
            $('[data-toggle="tooltip"]').tooltip();
        }
    });

})(jQuery);
