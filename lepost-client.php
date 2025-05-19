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

// Synchroniser notre paramètre avec le système natif de WordPress
add_action('admin_init', function() {
    // Le chemin du plugin à mettre à jour
    $plugin_path = plugin_basename(__FILE__);
    
    // Récupérer notre paramètre
    $enable_auto_updates = get_option('lepost_client_settings', [])['enable_auto_updates'] ?? '0';
    
    // Récupérer la liste des plugins à mettre à jour automatiquement
    $auto_updates = (array) get_site_option('auto_update_plugins', []);
    
    // Si les mises à jour auto sont activées dans nos paramètres mais pas dans WordPress
    if ($enable_auto_updates === '1' && !in_array($plugin_path, $auto_updates)) {
        $auto_updates[] = $plugin_path;
        update_site_option('auto_update_plugins', $auto_updates);
    }
    // Si les mises à jour auto sont désactivées dans nos paramètres mais activées dans WordPress
    elseif ($enable_auto_updates === '0' && in_array($plugin_path, $auto_updates)) {
        $auto_updates = array_diff($auto_updates, [$plugin_path]);
        update_site_option('auto_update_plugins', $auto_updates);
    }
});

// Synchroniser le système natif de WordPress avec notre paramètre
add_action('update_option_auto_update_plugins', function($old_value, $new_value) {
    $plugin_path = plugin_basename(__FILE__);
    $settings = get_option('lepost_client_settings', []);
    
    // Si le plugin a été ajouté à la liste des mises à jour auto
    if (!in_array($plugin_path, (array)$old_value) && in_array($plugin_path, (array)$new_value)) {
        $settings['enable_auto_updates'] = '1';
        update_option('lepost_client_settings', $settings);
    }
    // Si le plugin a été retiré de la liste des mises à jour auto
    elseif (in_array($plugin_path, (array)$old_value) && !in_array($plugin_path, (array)$new_value)) {
        $settings['enable_auto_updates'] = '0';
        update_option('lepost_client_settings', $settings);
    }
}, 10, 2);

// Ajouter un lien vers les paramètres dans la liste des plugins
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=lepost-client&tab=settings') . '">' . __('Paramètres', 'lepost-client') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
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
