<?php 

/**
 * Classe abstraite pour la logique commune des onglets
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/TabsManager
 */

namespace LePostClient\Admin\TabsManager;

/**
 * Classe abstraite pour la logique commune des onglets
 *
 * Cette classe fournit la structure de base et les fonctionnalités communes 
 * à tous les onglets de l'interface d'administration.
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin/TabsManager
 */
abstract class AbstractSettingsTab {

    /**
     * Identifiant unique de l'onglet
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $tab_id    Identifiant unique de l'onglet
     */
    protected $tab_id;

    /**
     * Titre de l'onglet
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $tab_title    Titre de l'onglet
     */
    protected $tab_title;

    /**
     * Priorité de l'onglet (pour l'ordre d'affichage)
     *
     * @since    1.0.0
     * @access   protected
     * @var      int    $priority    Priorité de l'onglet
     */
    protected $priority;

    /**
     * Constructeur de la classe
     *
     * @since    1.0.0
     * @param    string    $tab_id      Identifiant unique de l'onglet
     * @param    string    $tab_title   Titre de l'onglet
     * @param    int       $priority    Priorité de l'onglet (pour l'ordre)
     */
    public function __construct($tab_id, $tab_title, $priority = 10) {
        $this->tab_id = $tab_id;
        $this->tab_title = $tab_title;
        $this->priority = $priority;
    }

    /**
     * Obtient l'identifiant de l'onglet
     *
     * @since    1.0.0
     * @return   string    L'identifiant de l'onglet
     */
    public function get_id() {
        return $this->tab_id;
    }

    /**
     * Obtient le titre de l'onglet
     *
     * @since    1.0.0
     * @return   string    Le titre de l'onglet
     */
    public function get_title() {
        return $this->tab_title;
    }

    /**
     * Obtient la priorité de l'onglet
     *
     * @since    1.0.0
     * @return   int    La priorité de l'onglet
     */
    public function get_priority() {
        return $this->priority;
    }

    /**
     * Méthode abstraite pour le rendu du contenu de l'onglet
     *
     * @since    1.0.0
     */
    abstract public function render_content();

    /**
     * Méthode pour traiter les requêtes AJAX
     * Cette méthode peut être surchargée par les onglets qui ont besoin
     * de traiter des requêtes AJAX
     *
     * @since    1.0.0
     */
    public function process_ajax() {
        // Par défaut ne fait rien, à surcharger si besoin
    }

    /**
     * Méthode pour ajouter des scripts spécifiques à l'onglet
     * Cette méthode peut être surchargée par les onglets qui ont besoin
     * d'ajouter des scripts spécifiques
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Par défaut ne fait rien, à surcharger si besoin
    }

    /**
     * Méthode pour obtenir les messages de notification
     * Cette méthode peut être surchargée par les onglets qui ont besoin
     * d'afficher des notifications
     *
     * @since    1.0.0
     * @return   array    Tableau de messages de notification
     */
    public function get_notifications() {
        return [];
    }
}