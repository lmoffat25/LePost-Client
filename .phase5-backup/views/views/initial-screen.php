<div class="wrap lepost-client-initial-screen">
    <h1><?php echo esc_html__('Bienvenue sur LePost Client', 'lepost-client'); ?></h1>

    <div class="lepost-client-intro">
        <div class="lepost-client-logo">
            <img src="<?php echo LEPOST_CLIENT_PLUGIN_URL; ?>assets/images/lepost-logo.png" alt="LePost Logo">
        </div>
        <div class="lepost-client-description">
            <h2><?php echo esc_html__('Génération d\'idées d\'articles et de contenu IA', 'lepost-client'); ?></h2>
            <p>
                <?php echo esc_html__('LePost Client est un plugin qui vous permet de générer des idées d\'articles et du contenu en utilisant l\'API LePost.', 'lepost-client'); ?>
            </p>
            <p>
                <?php echo esc_html__('Pour commencer, vous devez configurer votre clé API. Cette clé vous sera fournie lors de votre inscription sur lepost.ai.', 'lepost-client'); ?>
            </p>
        </div>
    </div>

    <div class="lepost-client-setup-card">
        <h3><?php echo esc_html__('Configuration de votre clé API', 'lepost-client'); ?></h3>
        
        <div class="lepost-client-api-form">
            <div class="form-group">
                <label for="lepost-api-key"><?php echo esc_html__('Clé API LePost', 'lepost-client'); ?></label>
                <input type="text" id="lepost-api-key" name="lepost-api-key" class="regular-text" placeholder="<?php echo esc_attr__('Saisissez votre clé API ici', 'lepost-client'); ?>">
            </div>
            
            <div class="form-actions">
                <button type="button" id="lepost-save-api-key" class="button button-primary">
                    <?php echo esc_html__('Enregistrer et vérifier la clé API', 'lepost-client'); ?>
                </button>
                <span class="spinner"></span>
            </div>
            
            <div id="lepost-api-message" class="lepost-client-message" style="display: none;"></div>
        </div>
        
        <div class="lepost-client-get-key">
            <p>
                <?php echo esc_html__('Vous n\'avez pas encore de clé API ?', 'lepost-client'); ?>
                <a href="https://lepost.ai/register" target="_blank"><?php echo esc_html__('Inscrivez-vous sur LePost.ai', 'lepost-client'); ?></a>
            </p>
        </div>
    </div>

    <div class="lepost-client-features">
        <h3><?php echo esc_html__('Fonctionnalités principales', 'lepost-client'); ?></h3>
        
        <div class="lepost-client-features-grid">
            <div class="lepost-client-feature-card">
                <div class="feature-icon dashicons dashicons-lightbulb"></div>
                <h4><?php echo esc_html__('Génération d\'idées d\'articles', 'lepost-client'); ?></h4>
                <p><?php echo esc_html__('Créez facilement des idées d\'articles pertinentes pour votre audience.', 'lepost-client'); ?></p>
            </div>
            
            <div class="lepost-client-feature-card">
                <div class="feature-icon dashicons dashicons-edit"></div>
                <h4><?php echo esc_html__('Génération de contenu', 'lepost-client'); ?></h4>
                <p><?php echo esc_html__('Transformez vos idées en articles complets et bien structurés.', 'lepost-client'); ?></p>
            </div>
            
            <div class="lepost-client-feature-card">
                <div class="feature-icon dashicons dashicons-admin-post"></div>
                <h4><?php echo esc_html__('Publication directe', 'lepost-client'); ?></h4>
                <p><?php echo esc_html__('Publiez directement les articles générés sur votre site WordPress.', 'lepost-client'); ?></p>
            </div>
            
            <div class="lepost-client-feature-card">
                <div class="feature-icon dashicons dashicons-chart-line"></div>
                <h4><?php echo esc_html__('Amélioration du SEO', 'lepost-client'); ?></h4>
                <p><?php echo esc_html__('Contenu optimisé pour le référencement et l\'engagement des lecteurs.', 'lepost-client'); ?></p>
            </div>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        $('#lepost-save-api-key').on('click', function() {
            var apiKey = $('#lepost-api-key').val();
            var $button = $(this);
            var $spinner = $button.next('.spinner');
            var $message = $('#lepost-api-message');
            
            if (!apiKey) {
                $message.removeClass('notice-success').addClass('notice-error').html('<?php echo esc_js(__('Veuillez saisir une clé API.', 'lepost-client')); ?>').show();
                return;
            }
            
            $button.prop('disabled', true);
            $spinner.addClass('is-active');
            $message.hide();
            
            $.ajax({
                url: lepost_client_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'lepost_save_api_key',
                    nonce: lepost_client_params.nonce,
                    api_key: apiKey
                },
                success: function(response) {
                    if (response.success) {
                        $message.removeClass('notice-error').addClass('notice-success').html(response.data.message).show();
                        // Rediriger vers le tableau de bord après un court délai
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    } else {
                        $message.removeClass('notice-success').addClass('notice-error').html(response.data).show();
                    }
                },
                error: function() {
                    $message.removeClass('notice-success').addClass('notice-error').html('<?php echo esc_js(__('Erreur de communication avec le serveur.', 'lepost-client')); ?>').show();
                },
                complete: function() {
                    $button.prop('disabled', false);
                    $spinner.removeClass('is-active');
                }
            });
        });
    });
</script>

<style>
    .lepost-client-initial-screen {
        max-width: 1200px;
        margin: 20px auto;
    }
    
    .lepost-client-intro {
        display: flex;
        align-items: center;
        margin-bottom: 30px;
        background: #fff;
        border-radius: 5px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .lepost-client-logo {
        margin-right: 30px;
    }
    
    .lepost-client-logo img {
        max-width: 150px;
        height: auto;
    }
    
    .lepost-client-description h2 {
        margin-top: 0;
    }
    
    .lepost-client-setup-card {
        background: #fff;
        border-radius: 5px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .lepost-client-api-form {
        margin-bottom: 20px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
    }
    
    .form-actions {
        display: flex;
        align-items: center;
    }
    
    .spinner {
        float: none;
        margin-left: 10px;
    }
    
    .lepost-client-message {
        margin-top: 15px;
        padding: 10px 15px;
        border-radius: 3px;
    }
    
    .notice-success {
        background-color: #f0f9e8;
        border-left: 4px solid #46b450;
        color: #3c763d;
    }
    
    .notice-error {
        background-color: #fbeaea;
        border-left: 4px solid #dc3232;
        color: #a94442;
    }
    
    .lepost-client-get-key {
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #eee;
    }
    
    .lepost-client-features {
        background: #fff;
        border-radius: 5px;
        padding: 25px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .lepost-client-features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .lepost-client-feature-card {
        text-align: center;
        padding: 20px 15px;
        border-radius: 5px;
        background: #f9f9f9;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .lepost-client-feature-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .feature-icon {
        font-size: 2.5em;
        margin-bottom: 10px;
        color: #2271b1;
    }
    
    .lepost-client-feature-card h4 {
        margin: 0 0 10px;
    }
    
    .lepost-client-feature-card p {
        margin: 0;
        color: #666;
    }
    
    @media (max-width: 782px) {
        .lepost-client-intro {
            flex-direction: column;
            text-align: center;
        }
        
        .lepost-client-logo {
            margin-right: 0;
            margin-bottom: 20px;
        }
    }
</style>
