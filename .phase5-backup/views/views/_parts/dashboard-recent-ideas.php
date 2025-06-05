<?php
/**
 * Section des idées récentes du tableau de bord
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/views/_parts
 */

// Protection directe
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- SECTION: IDÉES RÉCENTES -->
<!-- Cette section affiche les 5 dernières idées d'articles créées.
     Si aucune idée n'est présente, un message et un bouton pour en créer sont affichés. -->
<div class="lepost-admin-card">
    <h3>
        <span class="dashicons dashicons-list-view"></span>
        <?php esc_html_e('Dernières idées d\'articles', 'lepost-client'); ?>
    </h3>
    
    <?php
    if (empty($recent_idees)): ?>
        <!-- Message affiché quand aucune idée n'existe -->
        <div class="lepost-empty-list">
            <p><?php esc_html_e('Aucune idée d\'article n\'a été créée.', 'lepost-client'); ?></p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-client&tab=ideas')); ?>" class="button button-primary">
                <span class="dashicons dashicons-plus"></span>
                <?php esc_html_e('Créer ma première idée', 'lepost-client'); ?>
            </a>
        </div>
    <?php else: ?>
        <!-- Tableau des idées récentes -->
        <table class="lepost-admin-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Titre', 'lepost-client'); ?></th>
                    <th><?php esc_html_e('Date', 'lepost-client'); ?></th>
                    <th><?php esc_html_e('Actions', 'lepost-client'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_idees as $idee): ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($idee->titre); ?></strong>
                        </td>
                        <td>
                            <?php 
                            $date = date_i18n(
                                get_option('date_format'),
                                strtotime($idee->created_at)
                            );
                            echo esc_html($date); 
                            ?>
                        </td>
                        <td>
                            <!-- Boutons d'action: voir et générer -->
                            <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-client&tab=ideas')); ?>" class="button button-small">
                                <span class="dashicons dashicons-visibility"></span>
                            </a>
                            <a href="#" class="button button-primary button-small lepost-generate-article" data-id="<?php echo esc_attr($idee->id); ?>">
                                <span class="dashicons dashicons-welcome-write-blog"></span>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Pied de carte avec lien pour voir toutes les idées -->
        <div class="lepost-admin-card-footer">
            <a href="<?php echo esc_url(admin_url('admin.php?page=lepost-client&tab=ideas')); ?>" class="button">
                <?php esc_html_e('Voir toutes les idées', 'lepost-client'); ?>
            </a>
        </div>
    <?php endif; ?>
</div>
<!-- FIN SECTION: IDÉES RÉCENTES --> 