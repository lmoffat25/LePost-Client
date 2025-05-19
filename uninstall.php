<?php
/**
 * Processus de désinstallation du plugin LePost Client
 * 
 * Ce fichier est exécuté lorsque le plugin est désinstallé.
 *
 * @package LePostClient
 */

// Si la désinstallation n'est pas appelée depuis WordPress, sortir
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Supprimer les options du plugin
delete_option('lepost_client_api_key');
delete_option('lepost_client_api_url');
delete_option('lepost_client_settings');

// Supprimer les tables personnalisées
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}lepost_idees");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}lepost_articles");

// Supprimer les transients
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient_lepost_client_%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient_timeout_lepost_client_%'");

// Nettoyer les caches
wp_cache_flush();
