<?php
/**
 * Fonctionnalités d'activation du plugin
 *
 * @package    LePostClient
 * @subpackage LePostClient/Core
 */

namespace LePostClient\Core;

/**
 * Classe d'activation du plugin
 *
 * Cette classe définit toutes les fonctionnalités nécessaires à l'activation du plugin.
 *
 * @package    LePostClient
 * @subpackage LePostClient/Core
 */
class Activator {

    /**
     * Méthode appelée lors de l'activation du plugin
     *
     * Cette méthode est appelée lors de l'activation du plugin et effectue
     * les opérations nécessaires pour configurer l'environnement WordPress.
     */
    public static function activate() {
        // Création des tables personnalisées si nécessaire
        self::create_tables();
        
        // Ajouter des options par défaut
        self::add_default_options();
        
        // Nettoyer les caches
        flush_rewrite_rules();
    }
    
    /**
     * Création des tables en base de données
     */
    private static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table des idées d'articles
        $table_idees = $wpdb->prefix . 'lepost_idees';
        
        $sql_idees = "CREATE TABLE $table_idees (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            titre varchar(255) NOT NULL,
            description text NOT NULL,
            mots_cles text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            statut varchar(50) DEFAULT 'draft' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        // Table des articles générés
        $table_articles = $wpdb->prefix . 'lepost_articles';
        
        $sql_articles = "CREATE TABLE $table_articles (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            idee_id mediumint(9),
            titre varchar(255) NOT NULL,
            contenu longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            statut varchar(50) DEFAULT 'draft' NOT NULL,
            post_id bigint(20) DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY idee_id (idee_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_idees);
        dbDelta($sql_articles);
    }
    
    /**
     * Ajout des options par défaut
     */
    private static function add_default_options() {
        // API Key - vide par défaut
        if (!get_option('lepost_client_api_key')) {
            add_option('lepost_client_api_key', '');
        }
        
        // URL de l'API - peut être modifiée dans les réglages
        if (!get_option('lepost_client_api_url')) {
            add_option('lepost_client_api_url', 'https://dev-wordpress.agence-web-prism.fr');
        }
        
        // Autres options par défaut...
        if (!get_option('lepost_client_settings')) {
            add_option('lepost_client_settings', [
                'autopost_articles' => true,  // Toujours activé
                'default_category' => 1,
                'default_status' => 'draft'
            ]);
        } else {
            // Force la mise à jour des paramètres existants
            $current_settings = get_option('lepost_client_settings', []);
            $current_settings['autopost_articles'] = true;
            update_option('lepost_client_settings', $current_settings);
            error_log('LePost Activator: Paramètres mis à jour pour activer autopost_articles');
        }
    }
}
