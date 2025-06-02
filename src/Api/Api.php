<?php
/**
 * Classe de gestion des communications avec l'API LePost
 *
 * @package    LePostClient
 * @subpackage LePostClient/Api
 */

namespace LePostClient\Api;

// Protection directe
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe API
 * Gère les communications avec l'API LePost pour la génération d'idées et d'articles
 *
 * @package    LePostClient
 * @subpackage LePostClient/Api
 */
class Api {

    /**
     * Le seul type de publication supporté par ce plugin pour la génération.
     * @var string
     */
    private const SUPPORTED_PUBLICATION_TYPE = 'article';

    /**
     * URL de base de l'API
     *
     * @var string
     */
    private $api_url;

    /**
     * Clé API
     *
     * @var string
     */
    private $api_key;

    /**
     * Timeout pour les requêtes à l'API
     *
     * @var int
     */
    private $timeout;

    /**
     * SSL verify
     *
     * @var bool
     */
    private $sslverify;

    /**
     * Constructeur
     */
    public function __construct() {
        $this->api_url = get_option('lepost_client_api_url', LEPOST_API_BASE_URL);
        $this->api_key = get_option('lepost_client_api_key', '');
        $this->timeout = get_option('lepost_client_api_timeout', 90);
        // Forcer la vérification SSL pour des raisons de sécurité
        $this->sslverify = true; 

        
    }

    /**
     * Vérifie si la clé API est configurée
     *
     * @return bool
     */
    public function is_api_key_set() {
        return !empty($this->api_key);
    }

    /**
     * Définit la clé API
     *
     * @param string $api_key Clé API à définir
     * @return void
     */
    public function set_api_key($api_key) {
        $this->api_key = sanitize_text_field($api_key);
        update_option('lepost_client_api_key', $this->api_key);
    }

    /**
     * Récupère la clé API
     *
     * @return string
     */
    public function get_api_key() {
        return $this->api_key;
    }

    /**
     * Vérifie la connexion à l'API
     *
     * @return array Résultat du test avec statut et message
     */
    public function verify_connection() {
        if (!$this->is_api_key_set()) {
            return [
                'success' => false,
                'message' => __('La clé API n\'est pas configurée.', 'lepost-client')
            ];
        }

        // Use enhanced verify-api-key endpoint with basic verification
        $url = rtrim($this->api_url, '/') . '/wp-json/le-post/v1/verify-api-key';
        
        $args = [
            'method'  => 'POST',
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
            'body'    => json_encode(['api_key' => $this->api_key]),
            'timeout' => 30,
            'sslverify' => $this->sslverify
        ];

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => $response->get_error_message()
            ];
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        // Handle new API response format
        if ($response_code !== 200) {
            return [
                'success' => false,
                'message' => isset($body['message']) 
                    ? esc_html($body['message'])
                    : __('Une erreur s\'est produite lors du test de connexion.', 'lepost-client')
            ];
        }

        // Check new API response structure
        if (isset($body['success']) && $body['success'] === true && 
            isset($body['data']['is_valid']) && $body['data']['is_valid'] === true) {
            
            // Store account information if available
            if (isset($body['data']['user_id'])) {
                update_option('lepost_client_user_id', $body['data']['user_id']);
            }
            if (isset($body['data']['key_type'])) {
                update_option('lepost_client_key_type', $body['data']['key_type']);
            }
            if (isset($body['data']['account_status'])) {
                update_option('lepost_client_account_status', $body['data']['account_status']);
            }
            
            return [
                'success' => true,
                'message' => __('Connexion réussie à l\'API LePost.', 'lepost-client'),
                'data' => $body['data']
            ];
        } else {
            return [
                'success' => false,
                'message' => isset($body['message']) 
                    ? esc_html($body['message'])
                    : __('Clé API invalide.', 'lepost-client')
            ];
        }
    }

    /**
     * Génère des idées d'articles à partir d'un thème
     *
     * @param string $theme   Thème pour lequel générer des idées
     * @param int    $count   Nombre d'idées à générer (défaut: 5)
     * @return array|WP_Error Tableau d'idées ou objet d'erreur
     */
    public function generate_ideas($theme, $count = 5) {
        error_log('LePost API: Début de génération d\'idées pour le thème "' . $theme . '" et count demandé: ' . $count);
        
        if (empty($this->api_key)) {
            error_log('LePost API: Erreur - Clé API non configurée');
            return new \WP_Error(
                'no_api_key',
                __('Veuillez configurer votre clé API LePost dans les paramètres.', 'lepost-client')
            );
        }
        
        // Vérifier que le thème n'est pas vide
        if (empty($theme)) {
            error_log('LePost API: Erreur - Thème vide');
            return new \WP_Error(
                'empty_theme',
                __('Le thème ne peut pas être vide.', 'lepost-client')
            );
        }
        
        // Limite le nombre d'idées entre 1 et 10
        $count = max(1, min(10, intval($count)));
        
        // Construire l'URL
        $url = rtrim($this->api_url, '/') . '/wp-json/le-post/v1/generate-ideas';
        error_log('LePost API: Requête à ' . $url);
        
        // Données à envoyer
        $data = [
            'api_key' => $this->api_key,
            'theme' => $theme,
            'count' => $count
        ];
        error_log('LePost API: Données envoyées pour générer des idées : ' . json_encode($data));
        
        // Arguments de la requête
        $args = [
            'method'      => 'POST',
            'timeout'     => $this->timeout,
            'sslverify'   => $this->sslverify,
            'headers'     => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
            'body'        => json_encode($data)
        ];
        
        error_log('LePost API: Envoi de la requête pour les idées avec timeout ' . $this->timeout . 's');

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            error_log('LePost API: [ERREUR] Erreur de communication: ' . $response->get_error_message());
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        error_log('LePost API: [INFO] Code de réponse: ' . $response_code);
        error_log('LePost API: [DEBUG] Taille de la réponse: ' . strlen($body) . ' caractères');
        
        // Décodage de la réponse
        $decoded_body = json_decode($body, true);

        if ($response_code >= 400 || !$decoded_body) {
            error_log('LePost API: [ERREUR] Réponse d\'erreur de l\'API - Code: ' . $response_code);
            $api_error_message = __('Une erreur inconnue de l\'API s\'est produite.', 'lepost-client');
            if ($decoded_body && isset($decoded_body['message'])) {
                error_log('LePost API: [ERREUR] Message d\'erreur: ' . $decoded_body['message']);
                // Le message de l'API est loggué mais ne doit pas être affiché directement sans échappement.
                $api_error_message = esc_html($decoded_body['message']);
            } else {
                error_log('LePost API: [ERREUR] Réponse brute: ' . $body); // Attention: le body brut peut contenir des infos sensibles.
            }
            
            // Note: WP_Error stocke les données. Si get_error_message() est utilisé pour afficher à l'utilisateur,
            // le code appelant doit s'assurer de l'échappement.
            return new \WP_Error(
                'api_error',
                sprintf(
                    __('Erreur API (code %d): %s', 'lepost-client'),
                    $response_code,
                    $api_error_message
                )
            );
        }

        $ideas = [];

        // Handle the new consolidated API response format first
        if (is_array($decoded_body)) {
            // Format nouveau: Consolidated API format with success and data wrapper
            if (isset($decoded_body['success']) && $decoded_body['success'] === true && 
                isset($decoded_body['data']) && is_array($decoded_body['data'])) {
                
                error_log('LePost API: [INFO] Nouveau format consolidé détecté');
                
                // Extract ideas from the data wrapper
                if (isset($decoded_body['data']['ideas']) && is_array($decoded_body['data']['ideas'])) {
                    $ideas = $decoded_body['data']['ideas'];
                } else {
                    error_log('LePost API: [ERREUR] Pas d\'idées trouvées dans la réponse consolidée');
                    return new \WP_Error(
                        'api_invalid_response',
                        __('Aucune idée trouvée dans la réponse de l\'API.', 'lepost-client'),
                        $body
                    );
                }
            }
            // Traiter les données selon leur structure (anciens formats)
            // Format possible 1: Un tableau d'idées, chaque élément contenant un tableau d'idées
            else if (isset($decoded_body[0]) && is_array($decoded_body[0])) {
                // Extraire et aplatir tous les résultats
                foreach ($decoded_body as $result_set) {
                    if (is_array($result_set) && isset($result_set[0]) && is_array($result_set[0])) {
                        // Si chaque élément est lui-même un tableau (résultat multiple par appel)
                        $ideas = array_merge($ideas, $result_set);
                    } else {
                        // Si l'élément est directement une idée
                        $ideas[] = $result_set;
                    }
                }
            } 
            // Format possible 2: Un tableau direct d'idées
            else if (isset($decoded_body[0])) {
                $ideas = $decoded_body;
            }
            // Format possible 3: Ancien format avec une clé 'ideas'
            else if (isset($decoded_body['ideas']) && is_array($decoded_body['ideas'])) {
                $ideas = $decoded_body['ideas'];
            }
            // Aucun résultat exploitable
            else {
                error_log('LePost API: [ERREUR] Format de réponse non reconnu pour la génération d\'idées: ' . $body);
                return new \WP_Error(
                    'api_invalid_response',
                    __('Format de réponse inattendu lors de la génération des idées.', 'lepost-client'),
                    $body
                );
            }
        }

        // Normalisation des idées
        $normalized_ideas = [];
        foreach ($ideas as $idea) {
            if (is_array($idea)) {
                // Extraire le titre et la description selon les formats possibles
                $title = isset($idea['title']) ? $idea['title'] : 
                         (isset($idea['titre']) ? $idea['titre'] : null);
                         
                $explanation = isset($idea['explanation']) ? $idea['explanation'] : 
                               (isset($idea['description']) ? $idea['description'] : null);
                
                if ($title) {
                    $normalized_ideas[] = [
                        'title' => $title,
                        'explanation' => $explanation
                    ];
                }
            }
        }

        return [
            'success' => true,
            'ideas' => $normalized_ideas,
            'message' => sprintf(
                __('%d idées d\'articles ont été générées avec succès.', 'lepost-client'),
                count($normalized_ideas)
            )
        ];
    }

    /**
     * Génère un article à partir d'une idée
     *
     * @param array $idea_data  Données de l'idée
     * @return array|WP_Error   Article généré ou objet d'erreur
     */
    public function generate_article($idea_data) {
        error_log('LePost API: Début de génération d\'article pour l\'idée: ' . json_encode($idea_data));
        
        if (empty($this->api_key)) {
            error_log('LePost API: Erreur - Clé API non configurée');
            return new \WP_Error(
                'no_api_key',
                __('Veuillez configurer votre clé API LePost dans les paramètres.', 'lepost-client')
            );
        }
        
        // Vérifier que les données de l'idée sont valides - accepter les deux formats (ancien et nouveau)
        if (empty($idea_data)) {
            error_log('LePost API: Erreur - Données d\'idée vides');
            return new \WP_Error(
                'invalid_idea_data',
                __('Les données de l\'idée sont vides.', 'lepost-client')
            );
        }
        
        // Permettre l'utilisation des deux formats (compatibilité)
        $title = null;
        $description = null;
        
        // Format ancien (titre/description)
        if (isset($idea_data['titre'])) {
            $title = $idea_data['titre'];
        }
        if (isset($idea_data['description'])) {
            $description = $idea_data['description'];
        }
        
        // Format nouveau (title/description) - prend la préséance
        if (isset($idea_data['title'])) {
            $title = $idea_data['title'];
        }
        
        // Vérification finale des données obligatoires
        if (empty($title) || empty($description)) {
            error_log('LePost API: Erreur - Titre ou description manquant');
            return new \WP_Error(
                'invalid_idea_data',
                __('Les données de l\'idée sont invalides ou incomplètes (titre ou description manquant).', 'lepost-client')
            );
        }
        
        // Récupérer les paramètres de contenu
        $content_settings = get_option('lepost_content_settings', []);
        
        // Construire l'URL
        $url = rtrim($this->api_url, '/') . '/wp-json/le-post/v1/generate-content';
        error_log('LePost API: Requête à ' . $url);
        
        // Préparation des données
        $data = [
            'api_key' => $this->api_key,
            'subject' => $title,
            'subject_explanation' => $description,
            // IMPORTANT : Ce plugin est conçu UNIQUEMENT pour générer le type de contenu défini
            // dans la constante self::SUPPORTED_PUBLICATION_TYPE ('article').
            // Ne pas modifier cette valeur sans une compréhension approfondie des implications.
            'publication_type' => [self::SUPPORTED_PUBLICATION_TYPE] 
        ];
        
        // Ajouter les informations de l'entreprise si définies
        if (!empty($content_settings['company_info'])) {
            $data['company_info'] = $content_settings['company_info'];
        }
        
        // Ajouter le style d'écriture si défini
        if (!empty($content_settings['writing_style'])) {
            $data['writing_style'] = $content_settings['writing_style'];
        }
        
        // Arguments de la requête
        $args = [
            'method'      => 'POST',
            'timeout'     => $this->timeout,
            'sslverify'   => $this->sslverify,
            'headers'     => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
            'body'        => json_encode($data)
        ];
        
        error_log('LePost API: Envoi de la requête pour l\'article avec timeout ' . $this->timeout . 's');
        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            error_log('LePost API: [ERREUR] Erreur de communication: ' . $response->get_error_message());
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        error_log('LePost API: [INFO] Code de réponse: ' . $response_code);
        error_log('LePost API: [DEBUG] Taille de la réponse: ' . strlen($body) . ' caractères');
        
        // Décodage de la réponse
        $decoded_body = json_decode($body, true);

        if ($response_code >= 400 || !$decoded_body) {
            error_log('LePost API: [ERREUR] Réponse d\'erreur de l\'API - Code: ' . $response_code);
            $api_error_message = __('Une erreur inconnue de l\'API s\'est produite.', 'lepost-client');
            if ($decoded_body && isset($decoded_body['message'])) {
                error_log('LePost API: [ERREUR] Message d\'erreur: ' . $decoded_body['message']);
                // Le message de l'API est loggué mais ne doit pas être affiché directement sans échappement.
                $api_error_message = esc_html($decoded_body['message']);
            } else {
                error_log('LePost API: [ERREUR] Réponse brute: ' . $body); // Attention: le body brut peut contenir des infos sensibles.
            }
            
            // Note: WP_Error stocke les données. Si get_error_message() est utilisé pour afficher à l'utilisateur,
            // le code appelant doit s'assurer de l'échappement.
            return new \WP_Error(
                'api_error',
                sprintf(
                    __('Erreur API (code %d): %s', 'lepost-client'),
                    $response_code,
                    $api_error_message
                )
            );
        }

        // Vérification de la structure de la réponse
        if (isset($decoded_body['article']) && isset($decoded_body['article']['content'])) {
            error_log('LePost API: [SUCCÈS] Article généré avec succès');
            error_log('LePost API: [DEBUG] Titre: ' . ($decoded_body['article']['title'] ?? 'Non spécifié'));
            error_log('LePost API: [DEBUG] Taille du contenu: ' . strlen($decoded_body['article']['content']) . ' caractères');
            
            return [
                'success' => true,
                'article' => $decoded_body['article'],
                'message' => __('L\'article a été généré avec succès.', 'lepost-client')
            ];
        }

        error_log('LePost API: [ERREUR] Format de réponse inattendu');
        error_log('LePost API: [DEBUG] Réponse: ' . $body);
        
        return [
            'success' => false,
            'message' => __('Format de réponse inattendu de l\'API.', 'lepost-client'),
            'raw_response' => $body
        ];
    }

    /**
     * Récupère les informations du compte
     *
     * @param bool $force_refresh   Force le rafraîchissement des données
     * @return array                Informations du compte
     */
    public function get_account_info($force_refresh = true) {
        if (!$force_refresh) {
            // Check for cached data
            $cached_info = get_transient('lepost_client_account_info');
            if ($cached_info !== false) {
                return array_merge($cached_info, ['cached' => true]);
            }
        }
        
        if (empty($this->api_key)) {
            return [
                'success' => false,
                'message' => __('Clé API non configurée', 'lepost-client'),
                'credits' => 0
            ];
        }

        // Use the enhanced verify-api-key endpoint with credits information
        $url = rtrim($this->api_url, '/') . '/wp-json/le-post/v1/verify-api-key';
        
        $args = [
            'method'  => 'POST',
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ],
            'body'    => json_encode([
                'api_key' => $this->api_key,
                'include' => ['credits', 'usage'] // Request credits and usage information
            ]),
            'timeout' => $this->timeout,
            'sslverify' => $this->sslverify
        ];

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => $response->get_error_message(),
                'credits' => 0
            ];
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $raw_response = wp_remote_retrieve_body($response);

        error_log('LePost API Enhanced: Response Code: ' . $response_code);
        error_log('LePost API Enhanced: Raw Response: ' . $raw_response);

        if ($response_code !== 200) {
            return [
                'success' => false,
                'message' => sprintf(__('Erreur API (code %d)', 'lepost-client'), $response_code),
                'credits' => 0,
                'debug' => ['response_code' => $response_code, 'raw_response' => $raw_response]
            ];
        }

        // Handle the enhanced API response format
        if (isset($body['success']) && $body['success'] === true && 
            isset($body['data']['is_valid']) && $body['data']['is_valid'] === true) {
            
            // Extract credits from the enhanced response
            $credits = 0;
            if (isset($body['data']['credits']['credits_remaining'])) {
                $credits = (int) $body['data']['credits']['credits_remaining'];
            }

            // Cache the result for 5 minutes to reduce API calls
            $result = [
                'success' => true,
                'credits' => $credits,
                'account' => $body['data'],
                'message' => __('Informations du compte récupérées avec succès.', 'lepost-client'),
                'cached' => false,
                'endpoint' => 'verify-api-key-enhanced'
            ];

            set_transient('lepost_client_account_info', $result, 300); // 5 minutes cache
            
            error_log('LePost API Enhanced: Successfully retrieved account info with ' . $credits . ' credits');
            
            return $result;
        }

        return [
            'success' => false,
            'message' => $body['message'] ?? __('Impossible de récupérer les informations du compte', 'lepost-client'),
            'credits' => 0,
            'debug' => ['response_code' => $response_code, 'raw_response' => $raw_response]
        ];
    }

    /**
     * Get just credits quickly (with caching)
     *
     * @return int Number of available credits
     */
    public function get_credits() {
        $account_info = $this->get_account_info(false); // Use cache if available
        return isset($account_info['credits']) ? (int) $account_info['credits'] : 0;
    }

    /**
     * Make a generic API request
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $endpoint API endpoint (without base URL)
     * @param array $data Data to send with the request
     * @return array|WP_Error Response data or error
     */
    private function make_request($method, $endpoint, $data = []) {
        $url = rtrim($this->api_url, '/') . '/wp-json/le-post/v1/' . ltrim($endpoint, '/');

        $args = [
            'method'  => $method,
            'headers' => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ],
            'timeout' => $this->timeout,
            'sslverify' => $this->sslverify
        ];

        if (!empty($data)) {
            $data['api_key'] = $this->api_key;
            $args['body'] = json_encode($data);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            $error_code = $response->get_error_code();
            $error_message = $response->get_error_message();
            
            // Personnalisation des messages d'erreur cURL pour les rendre plus compréhensibles
            if (strpos($error_message, 'cURL error 6: Could not resolve host') !== false) {
                return new \WP_Error(
                    'api_connection_error',
                    __('Impossible de se connecter au serveur API. Veuillez vérifier votre connexion internet et l\'URL de l\'API.', 'lepost-client')
                );
            } elseif (strpos($error_message, 'cURL error') !== false) {
                return new \WP_Error(
                    'api_connection_error',
                    __('Erreur de connexion à l\'API LePost. Détails techniques: ', 'lepost-client') . $error_message
                );
            }
            
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($response_code >= 400) {
            return new \WP_Error(
                'api_error',
                sprintf(
                    __('Erreur API (code %d): %s', 'lepost-client'),
                    $response_code,
                    $body
                )
            );
        }

        $decoded_body = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new \WP_Error(
                'api_invalid_json',
                __('Réponse API invalide: impossible de décoder le JSON.', 'lepost-client')
            );
        }

        return $decoded_body;
    }

} 