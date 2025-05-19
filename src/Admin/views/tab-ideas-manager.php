<?php
/**
 * Vue du gestionnaire d'idées d'articles
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/views
 */

// Protection directe
if (!defined('ABSPATH')) {
    exit;
}

// Variables disponibles :
// $idees - tableau d'objets idées
// $total_idees - nombre total d'idées
// $current_page - page courante
// $this->per_page - nombre d'idées par page

$total_pages = ceil($total_idees / $this->per_page);
?>

<!-- GESTIONNAIRE D'IDÉES PRINCIPAL -->
<!-- Ce fichier représente la vue principale du gestionnaire d'idées d'articles.
     Il est divisé en composants plus petits pour une meilleure organisation. -->
<div class="lepost-admin-section">
    <?php 
    // Inclusion des différentes parties du gestionnaire d'idées
    
    // 1. En-tête avec titre et description
    include LEPOST_CLIENT_PLUGIN_DIR . 'src/Admin/views/_parts/ideas-header.php';
    
    // 2. Messages de notification
    include LEPOST_CLIENT_PLUGIN_DIR . 'src/Admin/views/_parts/ideas-notifications.php';
    
    // 3. Formulaire de création/édition d'idée et importation CSV
    include LEPOST_CLIENT_PLUGIN_DIR . 'src/Admin/views/_parts/ideas-form.php';
    
    // 4. Liste des idées d'articles
    include LEPOST_CLIENT_PLUGIN_DIR . 'src/Admin/views/_parts/ideas-list.php';
    ?>
</div>

<?php
// 5. Modales (génération d'idées et affichage complet)
include LEPOST_CLIENT_PLUGIN_DIR . 'src/Admin/views/_parts/ideas-modals.php';

// 6. Scripts JavaScript
include LEPOST_CLIENT_PLUGIN_DIR . 'src/Admin/views/_parts/ideas-scripts.php';
?>
<!-- FIN GESTIONNAIRE D'IDÉES PRINCIPAL -->
