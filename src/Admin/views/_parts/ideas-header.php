<?php
/**
 * En-tête du gestionnaire d'idées d'articles
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/views/_parts
 */

// Protection directe
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- SECTION: EN-TÊTE DU GESTIONNAIRE D'IDÉES -->
<!-- Cette section affiche le titre et la description de l'onglet -->
<div class="lepost-admin-section-header">
    <h2 class="lepost-admin-section-title">
        <span class="dashicons dashicons-lightbulb"></span>
        <?php esc_html_e('Gestionnaire d\'idées d\'articles', 'lepost-client'); ?>
    </h2>
    <p class="lepost-admin-section-description">
        <?php esc_html_e('Créez, gérez et organisez vos idées d\'articles. Vous pouvez créer manuellement des idées ou les générer automatiquement via l\'API LePost.', 'lepost-client'); ?>
    </p>
</div>
<!-- FIN SECTION: EN-TÊTE DU GESTIONNAIRE D'IDÉES --> 