<?php
/**
 * Modèle pour les articles générés
 *
 * @package    LePostClient
 * @subpackage LePostClient/ContentType
 */

namespace LePostClient\ContentType;

/**
 * Classe Article
 *
 * Cette classe gère les opérations CRUD pour les articles générés.
 *
 * @package    LePostClient
 * @subpackage LePostClient/ContentType
 */
class Article {

    /**
     * Nom de la table dans la base de données.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $table_name    Le nom de la table.
     */
    protected $table_name;

    /**
     * Initialise la classe et définit ses propriétés.
     *
     * @since    1.0.0
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'lepost_articles';
    }

    /**
     * Crée un nouvel article généré.
     *
     * @since    1.0.0
     * @param    array    $article_data    Les données de l'article à créer.
     * @return   int|false                 L'ID de l'article créé ou false en cas d'erreur.
     */
    public function create($article_data) {
        global $wpdb;
        
        error_log('LePost: Tentative de création d\'un article dans la table - ' . json_encode($article_data));
        
        $data = [
            'idee_id' => isset($article_data['idee_id']) ? $article_data['idee_id'] : null,
            'titre' => $article_data['titre'],
            'contenu' => $article_data['contenu'],
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
            'statut' => isset($article_data['statut']) ? $article_data['statut'] : 'draft',
            'post_id' => isset($article_data['post_id']) ? $article_data['post_id'] : null
        ];
        
        $result = $wpdb->insert(
            $this->table_name,
            $data,
            ['%d', '%s', '%s', '%s', '%s', '%s', '%d']
        );
        
        if ($result) {
            $insert_id = $wpdb->insert_id;
            error_log('LePost: Article créé avec succès, ID: ' . $insert_id);
            return $insert_id;
        }
        
        error_log('LePost: Échec de création d\'article dans la table - ' . $wpdb->last_error);
        return false;
    }

    /**
     * Récupère un article par son ID.
     *
     * @since    1.0.0
     * @param    int       $id    L'ID de l'article à récupérer.
     * @return   object|null      L'objet article ou null si non trouvé.
     */
    public function get_by_id($id) {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $id
        );
        
        return $wpdb->get_row($query);
    }

    /**
     * Récupère tous les articles générés.
     *
     * @since    1.0.0
     * @param    array    $args    Arguments pour le filtrage des résultats.
     * @return   array             Liste des articles générés.
     */
    public function get_all($args = []) {
        global $wpdb;
        
        $defaults = [
            'orderby' => 'created_at',
            'order' => 'DESC',
            'limit' => 0,
            'offset' => 0,
            'idee_id' => 0,
            'statut' => '',
            'has_post' => null
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $query = "SELECT a.*, i.titre as idee_titre 
                  FROM {$this->table_name} a 
                  LEFT JOIN {$wpdb->prefix}lepost_idees i ON a.idee_id = i.id";
        
        $where_clauses = [];
        
        // Filtrage par idée
        if ($args['idee_id'] > 0) {
            $where_clauses[] = $wpdb->prepare("a.idee_id = %d", $args['idee_id']);
        }
        
        // Filtrage par statut
        if (!empty($args['statut'])) {
            $where_clauses[] = $wpdb->prepare("a.statut = %s", $args['statut']);
        }
        
        // Filtrage par existence de post WordPress
        if ($args['has_post'] !== null) {
            if ($args['has_post']) {
                $where_clauses[] = "a.post_id IS NOT NULL AND a.post_id > 0";
            } else {
                $where_clauses[] = "a.post_id IS NULL OR a.post_id = 0";
            }
        }
        
        // Ajout des clauses WHERE
        if (!empty($where_clauses)) {
            $query .= " WHERE " . implode(" AND ", $where_clauses);
        }
        
        // Tri
        $query .= " ORDER BY a.{$args['orderby']} {$args['order']}";
        
        // Pagination
        if ($args['limit'] > 0) {
            $query .= $wpdb->prepare(" LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
        }
        
        return $wpdb->get_results($query);
    }

    /**
     * Met à jour un article généré.
     *
     * @since    1.0.0
     * @param    array    $article_data    Les données de l'article à mettre à jour.
     * @return   bool                     True si la mise à jour a réussi, false sinon.
     */
    public function update($article_data) {
        global $wpdb;
        
        $id = isset($article_data['id']) ? $article_data['id'] : 0;
        
        if (empty($id)) {
            error_log('LePost: Tentative de mise à jour d\'article sans ID');
            return false;
        }
        
        error_log('LePost: Tentative de mise à jour d\'article ID: ' . $id . ' - ' . json_encode($article_data));
        
        $data = [
            'updated_at' => current_time('mysql')
        ];
        
        $formats = ['%s'];
        
        if (isset($article_data['titre'])) {
            $data['titre'] = $article_data['titre'];
            $formats[] = '%s';
        }
        
        if (isset($article_data['contenu'])) {
            $data['contenu'] = $article_data['contenu'];
            $formats[] = '%s';
        }
        
        if (isset($article_data['statut'])) {
            $data['statut'] = $article_data['statut'];
            $formats[] = '%s';
        }
        
        if (isset($article_data['post_id'])) {
            $data['post_id'] = $article_data['post_id'];
            $formats[] = '%d';
        }
        
        $result = $wpdb->update(
            $this->table_name,
            $data,
            ['id' => $id],
            $formats,
            ['%d']
        );
        
        if ($result === false) {
            error_log('LePost: Échec de mise à jour d\'article ID: ' . $id . ' - ' . $wpdb->last_error);
            return false;
        }
        
        error_log('LePost: Article mis à jour avec succès, ID: ' . $id);
        return true;
    }

    /**
     * Supprime un article généré.
     *
     * @since    1.0.0
     * @param    int     $id    L'ID de l'article à supprimer.
     * @return   bool          True si la suppression a réussi, false sinon.
     */
    public function delete($id) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $this->table_name,
            ['id' => $id],
            ['%d']
        );
        
        return $result !== false;
    }

    /**
     * Compte le nombre total d'articles, éventuellement filtrés par statut ou idée.
     *
     * @since    1.0.0
     * @param    array    $args    Arguments pour le filtrage.
     * @return   int              Le nombre total d'articles.
     */
    public function count_all($args = []) {
        global $wpdb;
        
        $defaults = [
            'idee_id' => 0,
            'statut' => '',
            'has_post' => null
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $query = "SELECT COUNT(*) FROM {$this->table_name}";
        
        $where_clauses = [];
        
        if ($args['idee_id'] > 0) {
            $where_clauses[] = $wpdb->prepare("idee_id = %d", $args['idee_id']);
        }
        
        if (!empty($args['statut'])) {
            $where_clauses[] = $wpdb->prepare("statut = %s", $args['statut']);
        }
        
        if ($args['has_post'] !== null) {
            if ($args['has_post']) {
                $where_clauses[] = "post_id IS NOT NULL AND post_id > 0";
            } else {
                $where_clauses[] = "post_id IS NULL OR post_id = 0";
            }
        }
        
        if (!empty($where_clauses)) {
            $query .= " WHERE " . implode(" AND ", $where_clauses);
        }
        
        return (int) $wpdb->get_var($query);
    }

    /**
     * Récupère un article de la table custom par son idee_id.
     * Retourne le plus récent si plusieurs existent pour une même idée (ce qui ne devrait pas arriver idéalement).
     *
     * @since    1.0.1
     * @param    int       $idee_id    L'ID de l'idée.
     * @return   object|null           L'objet article (custom table) ou null si non trouvé.
     */
    public function get_by_idee_id(int $idee_id) {
        global $wpdb;
        
        if ($idee_id <= 0) {
            return null;
        }
        
        $query = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE idee_id = %d ORDER BY id DESC LIMIT 1",
            $idee_id
        );
        
        return $wpdb->get_row($query);
    }

    /**
     * Crée un article WordPress à partir d'un article généré.
     *
     * @since    1.0.0
     * @param    int       $article_id    L'ID de l'article à publier.
     * @param    array     $args          Arguments supplémentaires pour la création de l'article.
     * @return   int|false                L'ID de l'article WordPress ou false en cas d'erreur.
     */
    public function publish_to_wordpress($article_id, $args = []) {
        $article = $this->get_by_id($article_id);
        
        if (!$article) {
            return false;
        }
        
        $defaults = [
            'post_status' => 'draft',
            'post_author' => get_current_user_id(),
            'post_category' => [get_option('default_category')]
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $post_args = [
            'post_title' => $article->titre,
            'post_content' => $article->contenu,
            'post_status' => $args['post_status'],
            'post_author' => $args['post_author'],
            'post_category' => $args['post_category'],
            'post_type' => 'post'
        ];
        
        $post_id = wp_insert_post($post_args);
        
        if ($post_id && !is_wp_error($post_id)) {
            // Mettre à jour l'enregistrement avec l'ID du post WordPress
            $this->update([
                'id' => $article_id,
                'post_id' => $post_id,
                'statut' => 'published'
            ]);
            
            return $post_id;
        }
        
        return false;
    }

    /**
     * Crée un article WordPress à partir des données de l'article généré.
     *
     * @since    1.0.0
     * @param    array    $article_data    Données de l'article (titre, contenu).
     * @param    array    $settings        Réglages du plugin (default_status, default_category).
     * @return   int|false                 ID de l'article WordPress ou false en cas d'erreur.
     */
    public static function createWpPost(array $article_data, array $settings): int|false {
        // Validation des paramètres requis
        if (empty($article_data['titre'])) {
            error_log('LePost: [ERREUR] Tentative de création de post WP sans titre.');
            return false;
        }
        if (empty($article_data['contenu'])) {
            error_log('LePost: [ERREUR] Tentative de création de post WP sans contenu.');
            return false;
        }
        
        $post_args = [
            'post_title'   => $article_data['titre'],
            'post_content' => wp_kses_post($article_data['contenu']), // Utilise wp_kses_post pour la sécurité
            'post_status'  => isset($settings['default_status']) ? $settings['default_status'] : 'draft',
            'post_author'  => get_current_user_id(), // Assigne à l'utilisateur courant
            'post_type'    => 'post',
        ];
        
        // Validation de l'auteur (au cas où get_current_user_id() retournerait 0)
        if ($post_args['post_author'] <= 0) {
            $post_args['post_author'] = 1; // Fallback sur l'admin ID 1
            error_log('LePost: [AVERTISSEMENT] Auteur invalide pour la création de post WP. Utilisation de l\'ID utilisateur 1.');
        }
        
        // Validation du statut
        $valid_statuses = ['draft', 'publish', 'pending', 'private'];
        if (!in_array($post_args['post_status'], $valid_statuses, true)) {
            error_log('LePost: [AVERTISSEMENT] Statut de post WP invalide : ' . $post_args['post_status'] . '. Utilisation de "draft".');
            $post_args['post_status'] = 'draft';
        }
        
        // Configuration de la catégorie
        if (isset($settings['default_category']) && (int) $settings['default_category'] > 0) {
            $category_id = (int) $settings['default_category'];
            // Vérification de l'existence de la catégorie
            if (term_exists($category_id, 'category')) {
                $post_args['post_category'] = [$category_id];
            } else {
                error_log('LePost: [AVERTISSEMENT] La catégorie ID ' . $category_id . ' n\'existe pas. Le post sera créé sans catégorie spécifique.');
            }
        }
        
        $post_id = wp_insert_post($post_args, true); // Le deuxième paramètre à true active WP_Error en cas d'échec

        if (is_wp_error($post_id)) {
            error_log('LePost: [ERREUR CRITIQUE] Erreur lors de la création du post WordPress - ' . $post_id->get_error_message());
            error_log('LePost: [ERREUR CRITIQUE] Code d\'erreur - ' . $post_id->get_error_code());
            error_log('LePost: [ERREUR CRITIQUE] Données de l\'article: ' . print_r($article_data, true));
            error_log('LePost: [ERREUR CRITIQUE] Arguments du post: ' . print_r($post_args, true));
            return false;
        }
        
        // $post_id est un entier en cas de succès
        if ($post_id && is_int($post_id)) {
            // Ajouter une méta-donnée pour identifier l'article comme généré
            $meta_result = update_post_meta($post_id, '_lepost_generated_article', true);
            if (false === $meta_result) {
                error_log('LePost: [AVERTISSEMENT] Impossible d\'ajouter la métadonnée _lepost_generated_article pour le post ID ' . $post_id);
            }
            
            // Vérifier que le post existe bien (mesure de sécurité supplémentaire)
            $post = get_post($post_id);
            if (!$post) {
                error_log('LePost: [ERREUR] Le post semble avoir été créé avec ID ' . $post_id . ' mais get_post() ne le trouve pas.');
                // Il est possible que ce soit une condition de course ou une erreur très rare.
                // Envisager une action ici, mais pour l'instant, on retourne $post_id car wp_insert_post a réussi.
            }
        } else {
            // wp_insert_post n'a pas retourné d'erreur WP_Error, mais n'a pas non plus retourné un ID de post valide.
            // Cela peut arriver si un hook annule l'insertion (retourne 0 ou false).
            error_log('LePost: [ERREUR CRITIQUE] wp_insert_post a retourné une valeur inattendue (' . print_r($post_id, true) . ') sans objet WP_Error. Post non créé.');
            error_log('LePost: [ERREUR CRITIQUE] Données de l\'article: ' . print_r($article_data, true));
            error_log('LePost: [ERREUR CRITIQUE] Arguments du post: ' . print_r($post_args, true));
            return false; // Explicitement retourner false si $post_id n'est pas un entier > 0
        }
        
        return (int) $post_id; // Assurer que c'est un entier
    }
} 