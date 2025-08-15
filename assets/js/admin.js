jQuery(document).ready(function($) {
    // Event listener para mudança no input de arquivo
    $('#wcpfs_file').on('change', function() {
        var fileInput = $(this);
        var file = this.files[0];
        var submitBtn = $('.wcpfs-import-btn');
        
        // Remove classes anteriores
        fileInput.removeClass('accepted');
        
        if (file) {
            var maxSize = 10 * 1024 * 1024; // 10MB em bytes
            if (file.size > maxSize) {
                alert('Arquivo muito grande. Máximo permitido: 10MB');
                fileInput.val(''); // Limpa o input
                submitBtn.prop('disabled', true);
                return;
            }
            
            // Verifica se o arquivo tem uma extensão válida
            var fileName = file.name.toLowerCase();
            var validExtensions = ['.csv', '.xlsx', '.xls'];
            var isValidFile = validExtensions.some(function(ext) {
                return fileName.endsWith(ext);
            });
            
            if (isValidFile && file.size > 0) {
                // Adiciona a classe accepted se o arquivo é válido
                fileInput.addClass('accepted');
                // HABILITA o botão de importação
                submitBtn.prop('disabled', false);
            } else {
                // DESABILITA o botão se o arquivo não é válido
                submitBtn.prop('disabled', true);
            }
        } else {
            // DESABILITA o botão se nenhum arquivo foi selecionado
            submitBtn.prop('disabled', true);
        }
    });
    $('#wcpfs-import-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'wcpfs_import_prices');
        formData.append('nonce', wcpfs_ajax.nonce);
        
        // Mostra loading
        $('#wcpfs-import-results').show();
        $('#wcpfs-results-content').html('<div class="wcpfs-loading"></div> Importando preços...');
        
        $.ajax({
            url: wcpfs_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    var html = '<div class="wcpfs-success">' + response.message + '</div>';
                    
                    if (response.errors && response.errors.length > 0) {
                        html += '<h4>' + wcpfs_ajax.i18n.errors_found + '</h4><ul>';
                        response.errors.forEach(function(error) {
                            html += '<li class="wcpfs-error">' + error + '</li>';
                        });
                        html += '</ul>';
                    }
                    
                    $('#wcpfs-results-content').html(html);
                } else {
                    $('#wcpfs-results-content').html('<div class="wcpfs-error">' + response.message + '</div>');
                }
            },
            error: function() {
                $('#wcpfs-results-content').html('<div class="wcpfs-error">' + wcpfs_ajax.i18n.server_error + '</div>');
            }
        });
    });
    
    // Event listener para o botão de fechar o modal
    $(document).on('click', '.wcpfs-close-btn', function() {
        $('#wcpfs-import-results').slideUp(300);
    });
});