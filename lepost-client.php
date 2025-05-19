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
 * Version:           1.0.0
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

// Charger la bibliothèque de mise à jour
require_once __DIR__ . '/includes/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

// Définir les constantes du plugin
define('LEPOST_CLIENT_VERSION', '1.0.0');
define('LEPOST_CLIENT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LEPOST_CLIENT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LEPOST_CLIENT_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('LEPOST_API_BASE_URL', 'https://dev-wordpress.agence-web-prism.fr');

// Initialiser le checker sur GitHub
$updateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/lmoffat25/LePost-Client/',
    __FILE__,
    'lepost-client'
);
$updateChecker->setBranch('main');
$updateChecker->getVcsApi()->enableReleaseAssets();

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

// Contrôler les mises à jour automatiques
add_filter('auto_update_plugin', function($update, $item) {
    if ($item->plugin === plugin_basename(__FILE__)) {
        return (bool) get_option('lepost_client_auto_update', false);
    }
    return $update;
}, 10, 2);

// Démarrer le plugin
require_once LEPOST_CLIENT_PLUGIN_DIR . 'src/Core/Plugin.php';
$plugin = new LePostClient\Core\Plugin();
$plugin->run();
