<?php
/**
 * Enregistre tous les hooks du plugin.
 *
 * @package    LePostClient
 * @subpackage LePostClient/Core
 */

namespace LePostClient\Core;

/**
 * Classe Loader
 *
 * Cette classe maintient et enregistre tous les hooks utilisés dans le plugin.
 *
 * @package    LePostClient
 * @subpackage LePostClient/Core
 */
class Loader {

    /**
     * Le tableau des actions enregistrées avec WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $actions    Les actions enregistrées avec WordPress pour être exécutées lors du chargement.
     */
    protected $actions;

    /**
     * Le tableau des filtres enregistrés avec WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $filters    Les filtres enregistrés avec WordPress pour être exécutés lors du chargement.
     */
    protected $filters;

    /**
     * Initialise les collections utilisées pour maintenir les actions et les filtres.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->actions = array();
        $this->filters = array();
    }

    /**
     * Ajoute une nouvelle action au tableau des actions.
     *
     * @since    1.0.0
     * @param    string               $hook             Le nom du hook WordPress auquel l'action est attachée.
     * @param    object               $component        Une référence à l'instance de l'objet sur lequel l'action est définie.
     * @param    string               $callback         Le nom de la fonction définie sur $component.
     * @param    int                  $priority         Facultatif. La priorité à laquelle l'action est exécutée. Par défaut 10.
     * @param    int                  $accepted_args    Facultatif. Le nombre d'arguments que l'action accepte. Par défaut 1.
     */
    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * Ajoute un nouveau filtre au tableau des filtres.
     *
     * @since    1.0.0
     * @param    string               $hook             Le nom du hook WordPress auquel le filtre est attaché.
     * @param    object               $component        Une référence à l'instance de l'objet sur lequel le filtre est défini.
     * @param    string               $callback         Le nom de la fonction définie sur $component.
     * @param    int                  $priority         Facultatif. La priorité à laquelle le filtre est exécuté. Par défaut 10.
     * @param    int                  $accepted_args    Facultatif. Le nombre d'arguments que le filtre accepte. Par défaut 1.
     */
    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * Méthode utilitaire qui est utilisée pour enregistrer les actions et les filtres dans un tableau unique.
     *
     * @since    1.0.0
     * @access   private
     * @param    array                $hooks            Le tableau dans lequel les hooks doivent être enregistrés.
     * @param    string               $hook             Le nom du hook WordPress.
     * @param    object               $component        Une référence à l'instance de l'objet.
     * @param    string               $callback         Le nom de la fonction.
     * @param    int                  $priority         La priorité à laquelle le hook doit être exécuté.
     * @param    int                  $accepted_args    Le nombre d'arguments que le hook accepte.
     *
     * @return   array                                  Le tableau des hooks collectés avec le hook ajouté.
     */
    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        );

        return $hooks;
    }

    /**
     * Enregistre les filtres et les actions avec WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        foreach ($this->filters as $hook) {
            add_filter($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }

        foreach ($this->actions as $hook) {
            add_action($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
        }
    }
} 