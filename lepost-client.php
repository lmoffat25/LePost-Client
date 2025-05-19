<?php
/**
 * LePost Client
 *
 * @package           LePostClient
 * @author            LePost
 * @copyright         2023 LePost
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       LePost Client
 * Plugin URI:        https://lepost.ai
 * Description:       Plugin client pour l'API LePost, générateur d'idées d'articles et d'articles.
 * Version:           1.0.1
 * Author:            LePost
 * Author URI:        https://lepost.ai
 * Text Domain:       lepost-client
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Définir les constantes du plugin
define('LEPOST_CLIENT_VERSION', '1.0.1');
define('LEPOST_CLIENT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LEPOST_CLIENT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LEPOST_CLIENT_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('LEPOST_API_BASE_URL', 'https://dev-wordpress.agence-web-prism.fr');

// Intégration de la gestion des mises à jour
require_once LEPOST_CLIENT_PLUGIN_DIR . 'includes/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$updateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/lmoffat25/LePost-Client', // URL de votre dépôt GitHub
    __FILE__,
    'lepost-client'
);

// Optionnel : Configuration des mises à jour
$updateChecker->setBranch('main'); // Branche à surveiller
$updateChecker->getVcsApi()->enableReleaseAssets(); // Activer les assets de release

// Contrôler les mises à jour automatiques
add_filter('auto_update_plugin', function($update, $item) {
    if ($item->plugin === plugin_basename(__FILE__)) {
        return (bool) get_option('lepost_client_auto_update', false);
    }
    return $update;
}, 10, 2);

// Activer/désactiver les mises à jour automatiques depuis la page des plugins
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    // Récupérer l'état actuel de l'option
    $auto_updates_enabled = (bool) get_option('lepost_client_auto_update', false);
    
    // Ajouter un lien vers les paramètres
    $settings_link = '<a href="' . admin_url('admin.php?page=lepost-client&tab=settings') . '">' . __('Paramètres', 'lepost-client') . '</a>';
    array_unshift($links, $settings_link);
    
    return $links;
});

// Ajouter un contrôle personnalisé pour les mises à jour automatiques dans la liste des plugins
add_action('after_plugin_row_' . plugin_basename(__FILE__), function() {
    $auto_updates_enabled = (bool) get_option('lepost_client_auto_update', false);
    $current_status = $auto_updates_enabled ? 'activées' : 'désactivées';
    $toggle_status = $auto_updates_enabled ? 'désactiver' : 'activer';
    $nonce = wp_create_nonce('lepost_toggle_auto_updates');
    
    echo '<tr class="plugin-update-tr active"><td colspan="4" class="plugin-update colspanchange">
        <div class="notice inline notice-info notice-alt">
            <p>Les mises à jour automatiques sont actuellement <strong>' . $current_status . '</strong>. 
            <a href="' . admin_url('admin.php?page=lepost-client&tab=settings') . '">Changer ce paramètre</a> ou 
            <a href="' . admin_url('admin-post.php?action=lepost_toggle_auto_updates&nonce=' . $nonce) . '">' . $toggle_status . ' maintenant</a>.
            </p>
        </div>
    </td></tr>';
});

// Traiter l'action de bascule des mises à jour automatiques
add_action('admin_post_lepost_toggle_auto_updates', function() {
    // Vérifier le nonce pour la sécurité
    if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'lepost_toggle_auto_updates')) {
        wp_die('Action non autorisée.');
    }
    
    // Récupérer l'état actuel et le basculer
    $current_value = (bool) get_option('lepost_client_auto_update', false);
    update_option('lepost_client_auto_update', !$current_value);
    
    // Rediriger vers la page des plugins avec un message
    wp_redirect(admin_url('plugins.php?lepost_auto_updates_toggled=1'));
    exit;
});

// Afficher un message après le changement de statut
add_action('admin_notices', function() {
    if (isset($_GET['lepost_auto_updates_toggled'])) {
        $status = (bool) get_option('lepost_client_auto_update', false) ? 'activées' : 'désactivées';
        echo '<div class="notice notice-success is-dismissible"><p>Les mises à jour automatiques du plugin LePost Client ont été <strong>' . $status . '</strong>.</p></div>';
    }
});

// Autoloader pour les classes du plugin
spl_autoload_register(function ($class) {
    // Préfixe du namespace du plugin
    $prefix = 'LePostClient\\';
    
    // Vérifie si la classe utilise le namespace du plugin
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Chemin relatif de la classe
    $relative_class = substr($class, $len);
    
    // Remplace les séparateurs de namespace par des séparateurs de répertoire
    $file = LEPOST_CLIENT_PLUGIN_DIR . 'src/' . str_replace('\\', '/', $relative_class) . '.php';
    
    // Si le fichier existe, le charger
    if (file_exists($file)) {
        require $file;
    }
});

// Activation et désactivation du plugin
register_activation_hook(__FILE__, function() {
    require_once LEPOST_CLIENT_PLUGIN_DIR . 'src/Core/Activator.php';
    LePostClient\Core\Activator::activate();
});

register_deactivation_hook(__FILE__, function() {
    require_once LEPOST_CLIENT_PLUGIN_DIR . 'src/Core/Deactivator.php';
    LePostClient\Core\Deactivator::deactivate();
});

// Démarrer le plugin
require_once LEPOST_CLIENT_PLUGIN_DIR . 'src/Core/Plugin.php';
$plugin = new LePostClient\Core\Plugin();
$plugin->run();
