<?php
/**
 * Liste des idées d'articles
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/views/_parts
 */

// Protection directe
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- SECTION: LISTE DES IDÉES -->
<!-- Cette section affiche la liste paginée des idées d'articles existantes
     ou un message si aucune idée n'est trouvée -->
<div class="lepost-admin-card">
    <h3>
        <?php esc_html_e('Liste des idées d\'articles', 'lepost-client'); ?>
        <span class="lepost-count-badge"><?php echo esc_html($total_idees); ?></span>
    </h3>
    
    <div id="lepost-idees-list" class="lepost-admin-table-container">
        <?php if (empty($idees)): ?>
            <!-- État vide: aucune idée trouvée -->
            <div class="lepost-admin-empty-state">
                <div class="lepost-admin-empty-icon">
                    <span class="dashicons dashicons-format-status"></span>
                </div>
                <h4><?php esc_html_e('Aucune idée d\'article trouvée', 'lepost-client'); ?></h4>
                <p><?php esc_html_e('Commencez par créer une nouvelle idée d\'article ci-dessus ou générez-en automatiquement.', 'lepost-client'); ?></p>
            </div>
        <?php else: ?>
            <!-- Tableau des idées -->
            <table class="lepost-admin-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('ID', 'lepost-client'); ?></th>
                        <th><?php esc_html_e('Titre', 'lepost-client'); ?></th>
                        <th><?php esc_html_e('Description', 'lepost-client'); ?></th>
                        <th><?php esc_html_e('Date', 'lepost-client'); ?></th>
                        <th><?php esc_html_e('Actions', 'lepost-client'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($idees as $idee): ?>
                        <tr data-id="<?php echo esc_attr($idee->id); ?>">
                            <td><?php echo esc_html($idee->id); ?></td>
                            <td class="lepost-idee-titre">
                                <strong><?php echo esc_html($idee->titre); ?></strong>
                            </td>
                            <td class="lepost-idee-description" data-full-description="<?php echo esc_attr($idee->description); ?>">
                                <?php 
                                echo wp_trim_words(
                                    esc_html($idee->description), 
                                    15, 
                                    '... <span class="lepost-voir-plus">' . 
                                    __('Voir plus', 'lepost-client') . '</span>'
                                ); 
                                ?>
                            </td>
                            <td class="lepost-idee-date">
                                <?php 
                                $date = date_i18n(
                                    get_option('date_format') . ' ' . get_option('time_format'),
                                    strtotime($idee->created_at)
                                );
                                echo esc_html($date); 
                                ?>
                            </td>
                            <td class="lepost-idee-actions">
                                <!-- Boutons d'action -->
                                <button type="button" class="button button-small lepost-edit-idee" title="<?php esc_attr_e('Modifier', 'lepost-client'); ?>">
                                    <span class="dashicons dashicons-edit"></span>
                                </button>
                                
                                <button type="button" class="button button-primary button-small lepost-generate-article" 
                                        data-id="<?php echo esc_attr($idee->id); ?>"
                                        title="<?php esc_attr_e('Générer un article', 'lepost-client'); ?>">
                                    <span class="dashicons dashicons-welcome-write-blog"></span>
                                </button>
                                
                                <button type="button" class="button button-small lepost-delete-idee" 
                                        data-id="<?php echo esc_attr($idee->id); ?>"
                                        title="<?php esc_attr_e('Supprimer', 'lepost-client'); ?>">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="lepost-admin-pagination">
                    <?php 
                    echo paginate_links(array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total' => $total_pages,
                        'current' => $current_page
                    ));
                    ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<!-- FIN SECTION: LISTE DES IDÉES --> 