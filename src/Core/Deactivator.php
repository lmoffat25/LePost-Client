<?php
/**
 * Fonctionnalités de désactivation du plugin
 *
 * @package    LePostClient
 * @subpackage LePostClient/Core
 */

namespace LePostClient\Core;

/**
 * Classe de désactivation du plugin
 *
 * Cette classe définit toutes les fonctionnalités nécessaires à la désactivation du plugin.
 *
 * @package    LePostClient
 * @subpackage LePostClient/Core
 */
class Deactivator {

    /**
     * Méthode appelée lors de la désactivation du plugin
     *
     * Cette méthode est appelée lors de la désactivation du plugin et effectue
     * les opérations nécessaires pour nettoyer l'environnement WordPress.
     */
    public static function deactivate() {
        // Nettoyer les caches
        flush_rewrite_rules();
        
        // Suppression des tâches planifiées
        wp_clear_scheduled_hook('lepost_client_cron_check_api');
    }
}
