<?php
/**
 * Vue unifiée des paramètres du plugin
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/views
 */

// Protection directe
if (!defined('ABSPATH')) {
    exit;
}

// Récupération des paramètres pour les partiels
$settings = get_option('lepost_client_settings', [
    'autopost_articles' => true,
    'default_status' => 'draft',
    'default_category' => 0,
    'enable_auto_updates' => '0'
]);

$content_settings = get_option('lepost_content_settings', [
    'company_info' => '',
    'writing_style' => [
        'article' => ''
    ]
]);

?>
<form method="post" action="options.php">
    <div class="lepost-settings-content">
        <?php 
        // Les données de l'entreprise et style d'écriture
        include LEPOST_CLIENT_PLUGIN_DIR . 'src/Admin/views/_parts/settings-content.php';
       
        // Les paramètres généraux
        include LEPOST_CLIENT_PLUGIN_DIR . 'src/Admin/views/_parts/settings-general.php'; 
        
        // Section des mises à jour
        settings_fields('lepost_client_settings');
        do_settings_sections('lepost_client_settings');
        
        submit_button();
        ?>
    </div>
</form> 