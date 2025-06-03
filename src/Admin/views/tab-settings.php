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
<div class="lepost-settings-content">
    
    <!-- CONTENT SETTINGS FORM -->
    <form method="post" action="options.php">
        <?php 
        settings_fields('lepost_content_settings_group');
        do_settings_sections('lepost_content_settings_group');
        
        // Les données de l'entreprise et style d'écriture
        include LEPOST_CLIENT_PLUGIN_DIR . 'src/Admin/views/_parts/settings-content.php';
        
        submit_button(__('Enregistrer les paramètres de contenu', 'lepost-client'));
        ?>
    </form>
    
    <!-- GENERAL SETTINGS FORM -->
    <form method="post" action="options.php">
        <?php 
        settings_fields('lepost_client_settings_group');
        do_settings_sections('lepost_client_settings_group');
        
        // Les paramètres généraux
        include LEPOST_CLIENT_PLUGIN_DIR . 'src/Admin/views/_parts/settings-general.php'; 
        
        submit_button(__('Enregistrer les paramètres généraux', 'lepost-client'));
        ?>
    </form>
    
</div> 