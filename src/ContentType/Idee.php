<?php
/**
 * Classe de gestion des idées d'articles
 *
 * @package    LePostClient
 * @subpackage LePostClient/ContentType
 */

namespace LePostClient\ContentType;

// Protection directe
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe Idee
 * Gère les opérations CRUD (Create, Read, Update, Delete) sur la table des idées d'articles
 *
 * @package    LePostClient
 * @subpackage LePostClient/ContentType
 */
class Idee {

    /**
     * Nom de la table en base de données
     *
     * @var string
     */
    private $table_name;

    /**
     * Constructeur
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'lepost_idees';
    }

    /**
     * Crée la table des idées lors de l'activation du plugin
     *
     * @return void
     */
    public function create_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $this->table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            titre text NOT NULL,
            description longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Récupère toutes les idées avec pagination
     *
     * @param int    $page   Numéro de page pour la pagination
     * @param int    $per_page   Nombre d'éléments par page
     * @return array    Tableau contenant les idées et le nombre total
     */
    public function get_all($page = 1, $per_page = 10) {
        global $wpdb;

        $offset = ($page - 1) * $per_page;
        $total = $this->count_all();

        $query = "SELECT * FROM $this->table_name ORDER BY created_at DESC LIMIT %d, %d";
        $idees = $wpdb->get_results($wpdb->prepare($query, $offset, $per_page));

        return [
            'idees' => $idees,
            'total' => $total
        ];
    }

    /**
     * Récupère une idée par son ID
     *
     * @param int $id    ID de l'idée à récupérer
     * @return object|null    Objet représentant l'idée ou null si non trouvée
     */
    public function get_by_id($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $this->table_name WHERE id = %d", $id));
    }

    /**
     * Enregistre une nouvelle idée ou met à jour une idée existante
     *
     * @param array $data   Données de l'idée à enregistrer
     * @return int|false    ID de l'idée enregistrée ou false en cas d'erreur
     */
    public function save($data) {
        global $wpdb;
        // error_log('LEPOST DEBUG: IdeeModel save - Données reçues pour sauvegarde: ' . print_r($data, true));

        if (empty($data['titre'])) {
            // error_log('LEPOST DEBUG: IdeeModel save - Titre manquant, sauvegarde annulée.');
            return false;
        }

        $sanitized_data = [
            'titre' => sanitize_text_field($data['titre']),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
        ];
        // error_log('LEPOST DEBUG: IdeeModel save - Données nettoyées: ' . print_r($sanitized_data, true));

        if (!empty($data['id'])) {
            // error_log('LEPOST DEBUG: IdeeModel save - Tentative de MISE À JOUR de l\'idée ID: ' . $data['id']);
            $result = $wpdb->update(
                $this->table_name,
                $sanitized_data,
                ['id' => (int) $data['id']],
                ['%s', '%s'],
                ['%d']
            );
            // error_log('LEPOST DEBUG: IdeeModel save - Résultat de la MISE À JOUR: ' . print_r($result, true));

            if ($result !== false) {
                // error_log('LEPOST DEBUG: IdeeModel save - MISE À JOUR réussie pour ID: ' . $data['id']);
                return (int) $data['id'];
            }
            // error_log('LEPOST DEBUG: IdeeModel save - Échec de la MISE À JOUR pour ID: ' . $data['id']);
            return false;
        }

        // error_log('LEPOST DEBUG: IdeeModel save - Tentative de CRÉATION d\'une nouvelle idée.');
        $result = $wpdb->insert(
            $this->table_name,
            $sanitized_data,
            ['%s', '%s']
        );
        // error_log('LEPOST DEBUG: IdeeModel save - Résultat de la CRÉATION: ' . print_r($result, true));

        if ($result) {
            $insert_id = $wpdb->insert_id;
            // error_log('LEPOST DEBUG: IdeeModel save - CRÉATION réussie, nouvel ID: ' . $insert_id);
            return $insert_id;
        }
        // error_log('LEPOST DEBUG: IdeeModel save - Échec de la CRÉATION.');
        return false;
    }

    /**
     * Supprime une idée par son ID
     *
     * @param int $id    ID de l'idée à supprimer
     * @return bool      true si la suppression a réussi, false sinon
     */
    public function delete($id) {
        global $wpdb;
        return $wpdb->delete($this->table_name, ['id' => (int) $id], ['%d']) !== false;
    }

    /**
     * Compte le nombre total d'idées
     *
     * @return int Nombre total d'idées
     */
    public function count_all() {
        global $wpdb;
        $query = "SELECT COUNT(*) FROM $this->table_name";
        $total = $wpdb->get_var($query);
        return (int) $total;
    }

    /**
     * Met à jour une idée existante
     *
     * @param array $data   Données de l'idée à mettre à jour
     * @return bool         true si la mise à jour a réussi, false sinon
     */
    public function update($data) {
        global $wpdb;
        
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        
        if (empty($id)) {
            error_log('LePost: Tentative de mise à jour d\'idée sans ID');
            return false;
        }
        
        error_log('LePost: Tentative de mise à jour d\'idée ID: ' . $id . ' - ' . json_encode($data));
        
        $update_data = [
            'updated_at' => current_time('mysql')
        ];
        
        $formats = ['%s'];
        
        // Mettre à jour le titre si fourni
        if (isset($data['titre'])) {
            $update_data['titre'] = sanitize_text_field($data['titre']);
            $formats[] = '%s';
        }
        
        // Mettre à jour la description si fournie
        if (isset($data['description'])) {
            $update_data['description'] = sanitize_textarea_field($data['description']);
            $formats[] = '%s';
        }
        
        $result = $wpdb->update(
            $this->table_name,
            $update_data,
            ['id' => $id],
            $formats,
            ['%d']
        );
        
        if ($result === false) {
            error_log('LePost: Échec de mise à jour d\'idée ID: ' . $id . ' - ' . $wpdb->last_error);
            return false;
        }
        
        error_log('LePost: Idée mise à jour avec succès, ID: ' . $id);
        return true;
    }

    /**
     * Vérifie si une idée avec le titre donné existe déjà.
     *
     * @since    1.0.0
     * @param    string    $titre    Le titre de l'idée à vérifier.
     * @return   int|false           L'ID de l'idée si elle existe, sinon false.
     */
    public static function existsByTitle(string $titre) {
        global $wpdb;
        
        // Assurez-vous que $wpdb est disponible. Dans certains contextes, il faut le passer en argument
        // ou utiliser une méthode pour l'obtenir s'il n'est pas global dans ce scope.
        if (!isset($wpdb)) {
            // Logique pour obtenir $wpdb si nécessaire, par exemple :
            // require_once ABSPATH . 'wp-includes/wp-db.php';
            // if (file_exists(ABSPATH . 'wp-admin/includes/upgrade.php')) {
            // require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            // }
            // Cette initialisation peut être complexe selon le contexte d'appel.
            // Idéalement, la classe Idee aurait $wpdb injecté ou accessible via un service locator.
            // Pour l'instant, on se base sur sa disponibilité globale.
        }

        $table_name = $wpdb->prefix . 'lepost_idees'; // Assurez-vous que ce nom de table est correct
        $query = $wpdb->prepare("SELECT id FROM {$table_name} WHERE titre = %s LIMIT 1", $titre);
        $result = $wpdb->get_var($query);
        
        return $result ? (int) $result : false;
    }

    /**
     * Crée plusieurs idées à partir des données de l'API.
     * Vérifie l'existence avant d'insérer.
     *
     * @since 1.0.1
     * @param array $apiIdeasData Tableau d'idées provenant de l'API (chaque idée avec 'title' et 'explanation').
     * @param Idee $ideeModelInstance Instance de la classe Idee pour utiliser la méthode save().
     * @return array Tableau avec 'saved_count' et 'skipped_count'.
     */
    public static function createManyFromApiData(array $apiIdeasData, Idee $ideeModelInstance): array {
        $saved_count = 0;
        $skipped_count = 0;

        foreach ($apiIdeasData as $idee_data_from_api) {
            $titre = isset($idee_data_from_api['title']) ? sanitize_text_field($idee_data_from_api['title']) : '';
            $description = isset($idee_data_from_api['explanation']) ? sanitize_textarea_field($idee_data_from_api['explanation']) : '';

            if (empty($titre)) {
                $skipped_count++;
                continue;
            }

            if (self::existsByTitle($titre)) {
                $skipped_count++;
                continue;
            }

            $idee_to_save = [
                'titre'       => $titre,
                'description' => $description,
            ];

            if ($ideeModelInstance->save($idee_to_save)) {
                $saved_count++;
            } else {
                // Log l'erreur si save() échoue, même si existsByTitle a passé
                error_log("LePost Client: Échec de la sauvegarde de l'idée générée par API avec titre: " . $titre);
                $skipped_count++;
            }
        }
        return ['saved_count' => $saved_count, 'skipped_count' => $skipped_count];
    }

    /**
     * Crée plusieurs idées à partir de lignes de données CSV.
     * Vérifie l'existence avant d'insérer.
     *
     * @since 1.0.1
     * @param array $csvRows Tableau de lignes CSV (chaque ligne est un tableau avec titre et description).
     * @param Idee $ideeModelInstance Instance de la classe Idee pour utiliser la méthode save().
     * @return array Tableau avec 'imported_count' et 'skipped_count'.
     */
    public static function createManyFromCsv(array $csvRows, Idee $ideeModelInstance): array {
        $imported_count = 0;
        $skipped_count = 0;

        foreach ($csvRows as $row) {
            if (count($row) < 2) { // S'assurer qu'il y a au moins titre et description
                $skipped_count++;
                continue;
            }

            $titre = sanitize_text_field(trim($row[0]));
            $description = sanitize_textarea_field(trim($row[1]));

            if (empty($titre)) { // Le titre est requis
                $skipped_count++;
                continue;
            }

            if (self::existsByTitle($titre)) {
                $skipped_count++;
                continue;
            }

            $idee_to_save = [
                'titre'       => $titre,
                'description' => $description,
            ];

            if ($ideeModelInstance->save($idee_to_save)) {
                $imported_count++;
            } else {
                error_log("LePost Client: Échec de la sauvegarde de l'idée importée par CSV avec titre: " . $titre);
                $skipped_count++;
            }
        }
        return ['imported_count' => $imported_count, 'skipped_count' => $skipped_count];
    }
} 