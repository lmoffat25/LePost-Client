<?php
/**
 * Vue de configuration des paramètres de génération de contenu
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/views
 */

// Protection directe
if (!defined('ABSPATH')) {
    exit;
}

// Récupération des paramètres
$content_settings = get_option('lepost_content_settings', [
    'company_info' => '',
    'writing_style' => [
        'article' => ''
    ]
]);

// Localisation des données pour JavaScript
wp_localize_script('jquery', 'lepost_content_settings', [
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('lepost_content_nonce'),
    'api_base_url' => LEPOST_API_BASE_URL,
    'i18n' => [
        'error_generating' => __('Erreur lors de la génération de l\'article.', 'lepost-client'),
        'network_error' => __('Erreur de connexion au serveur.', 'lepost-client'),
        'subject_required' => __('Le sujet est requis.', 'lepost-client')
    ]
]);
?>

<!-- PARAMÈTRES DE GÉNÉRATION DE CONTENU -->
<!-- Ce fichier représente la vue principale des paramètres de génération de contenu.
     Il est divisé en composants plus petits pour une meilleure organisation. -->
<div class="lepost-admin-section">
    <?php
    // Inclusion des différentes parties des paramètres de contenu
    
    // 1. Formulaire de configuration des paramètres
    include LEPOST_CLIENT_PLUGIN_DIR . 'src/Admin/views/_parts/content-settings-form.php';
    
    // 2. Section de test de génération d'article
    include LEPOST_CLIENT_PLUGIN_DIR . 'src/Admin/views/_parts/content-test-generator.php';
    
    // 3. Scripts JavaScript
    include LEPOST_CLIENT_PLUGIN_DIR . 'src/Admin/views/_parts/content-settings-scripts.php';
    ?>
</div>
<!-- FIN PARAMÈTRES DE GÉNÉRATION DE CONTENU --> 