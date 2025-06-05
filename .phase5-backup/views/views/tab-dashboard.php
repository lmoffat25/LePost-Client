<?php
/**
 * Vue du tableau de bord
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/views
 */

// Protection directe
if (!defined('ABSPATH')) {
    exit;
}

// Toutes les variables sont préparées et transmises par le contrôleur DashboardTab
?>

<!-- DASHBOARD PRINCIPAL -->
<!-- Ce fichier représente la vue principale du tableau de bord.
     Il est divisé en composants plus petits pour une meilleure organisation. -->
<div class="lepost-admin-section">
    <?php
    // Inclusion des différentes parties du tableau de bord
    
    // 1. Cartes de statistiques (idées, articles, crédits API)
    include LEPOST_CLIENT_PLUGIN_DIR . 'src/Admin/views/_parts/dashboard-statistics.php';
    
    // 2. Dernières idées d'articles
    include LEPOST_CLIENT_PLUGIN_DIR . 'src/Admin/views/_parts/dashboard-recent-ideas.php';
    ?>
</div>
<!-- FIN DASHBOARD PRINCIPAL --> 