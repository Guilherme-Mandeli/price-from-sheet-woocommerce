jQuery(document).ready(function($) {
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
                        html += '<h4>Erros encontrados:</h4><ul>';
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
                $('#wcpfs-results-content').html('<div class="wcpfs-error">Erro na comunicação com o servidor.</div>');
            }
        });
    });
});