<?php
/**
 * Tableau de bord
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/TabsManager
 */

namespace LePostClient\Admin\TabsManager;

use LePostClient\Api\Api;
use LePostClient\ContentType\Idee;
use LePostClient\ContentType\Article;

/**
 * Classe DashboardTab
 *
 * Cette classe gère l'onglet du tableau de bord de l'interface d'administration.
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/TabsManager
 */
class DashboardTab extends AbstractSettingsTab {

    /**
     * Instance de la classe API
     *
     * @since    1.0.0
     * @access   private
     * @var      Api    $api    Instance de l'API
     */
    private $api;

    /**
     * Constructeur de la classe
     *
     * @since    1.0.0
     */
    public function __construct() {
        parent::__construct('dashboard', __('Tableau de bord', 'lepost-client'), 5);
        $this->api = new Api();
    }

    /**
     * Retourne l'icône à utiliser pour l'onglet
     * 
     * @since    1.0.0
     * @return   string    Classe CSS de l'icône Dashicons
     */
    public function get_icon() {
        return 'dashicons-dashboard';
    }

    /**
     * Gère la logique de l'actualisation des crédits
     *
     * @since    1.0.0
     * @return   boolean   Vrai si une actualisation a été effectuée
     */
    public function handle_credits_refresh() {
        if (isset($_GET['refresh_credits']) && $_GET['refresh_credits'] === '1') {
            delete_option('lepost_client_credits');
            // Rediriger vers la même page sans le paramètre pour éviter les rechargements multiples
            wp_redirect(remove_query_arg('refresh_credits'));
            exit;
            return true;
        }
        return false;
    }

    /**
     * Récupère les statistiques pour le tableau de bord
     *
     * @since    1.0.0
     * @return   array    Tableau contenant les statistiques
     */
    public function get_statistics() {
        $idee_model = new Idee();
        $idees_count = $idee_model->count_all();

        $article_model = new Article();
        $articles_count = $article_model->count_all();

        return [
            'idees_count' => $idees_count,
            'articles_count' => $articles_count
        ];
    }

    /**
     * Récupère les informations sur les crédits API
     *
     * @since    1.0.0
     * @return   array    Tableau contenant les informations sur les crédits
     */
    public function get_api_credits_info() {
        $account_info = $this->api->get_account_info();
        $api_credits = $account_info['credits'];

        // Informations détaillées sur les crédits si disponibles
        $credits_used = 0;
        $total_credits = 0;
        if ($account_info['success'] && isset($account_info['account']['credits'])) {
            $credits_info = $account_info['account']['credits'];
            $credits_used = isset($credits_info['credits_used']) ? (int) $credits_info['credits_used'] : 0;
            $total_credits = isset($credits_info['total_credits_allocated']) ? (int) $credits_info['total_credits_allocated'] : 0;
        }

        return [
            'api_credits' => $api_credits,
            'credits_used' => $credits_used,
            'total_credits' => $total_credits,
            'account_info' => $account_info,
            'refresh_time' => current_time('mysql')
        ];
    }

    /**
     * Récupère les dernières idées d'articles
     *
     * @since    1.0.0
     * @param    int      $limit    Nombre d'idées à récupérer
     * @return   array    Tableau contenant les dernières idées
     */
    public function get_recent_ideas($limit = 5) {
        $idee_model = new Idee();
        return $idee_model->get_all(1, $limit);
    }

    /**
     * Rendu du contenu de l'onglet
     *
     * @since    1.0.0
     */
    public function render_content() {
        // Gérer la logique de l'actualisation des crédits
        $this->handle_credits_refresh();

        // Préparer toutes les données pour la vue
        $statistics = $this->get_statistics();
        $credits_info = $this->get_api_credits_info();
        $recent_ideas = $this->get_recent_ideas(5);
        
        // Déterminer si le mode debug est activé
        $show_debug = isset($_GET['debug']) && $_GET['debug'] === '1';

        // Passer les données à la vue
        $data = array_merge(
            $statistics,
            $credits_info,
            ['recent_idees' => $recent_ideas['idees']],
            ['show_debug' => $show_debug]
        );
        
        // Rendre les données disponibles pour la vue
        extract($data);
        
        // Inclure la vue du tableau de bord
        include LEPOST_CLIENT_PLUGIN_DIR . 'src/Admin/views/tab-dashboard.php';
    }

    /**
     * Méthode pour ajouter des scripts spécifiques à l'onglet
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Pas de scripts spécifiques pour l'instant
    }

    /**
     * Méthode pour obtenir les messages de notification
     *
     * @since    1.0.0
     * @return   array    Tableau de messages de notification
     */
    public function get_notifications() {
        $notifications = [];
        
        // Vérifier si la clé API est configurée
        if (!$this->api->is_api_key_set()) {
            $notifications[] = [
                'type' => 'warning',
                'message' => sprintf(
                    __('La clé API n\'est pas configurée. Veuillez la <a href="%s">configurer</a> pour pouvoir utiliser les fonctionnalités de génération.', 'lepost-client'),
                    admin_url('admin.php?page=lepost-client-settings')
                )
            ];
        }
        
        // Vérifier s'il y a des idées d'articles
        $idee_model = new Idee();
        $result = $idee_model->get_all(1, 1);
        
        if ($result['total'] == 0) {
            $notifications[] = [
                'type' => 'info',
                'message' => sprintf(
                    __('Aucune idée d\'article n\'a été créée. <a href="%s">Commencez</a> par en créer quelques unes.', 'lepost-client'),
                    admin_url('admin.php?page=lepost-client&tab=ideas')
                )
            ];
        }
        
        return $notifications;
    }
} 