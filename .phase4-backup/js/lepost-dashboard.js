/**
 * JavaScript spécifique pour le tableau de bord de LePost
 */
(function($) {
    'use strict';

    // Objet pour les fonctionnalités du tableau de bord
    const LePostDashboard = {
        /**
         * Initialisation des fonctionnalités du tableau de bord
         */
        init: function() {
            this.setupGenerateIdeasLink();
            // Suppression de setupGenerateArticleButtons() pour éviter les conflits
            // La gestion des boutons de génération d'articles est maintenant dans lepost-ideas-manager.js
        },

        /**
         * Configuration du lien de génération d'idées
         */
        setupGenerateIdeasLink: function() {
            $('#lepost-dashboard-generate-ideas').on('click', function(e) {
                e.preventDefault();
                
                // Si une modale de génération existe sur la page des idées, l'ouvrir via AJAX
                const url = $(this).attr('href');
                window.location.href = url;
            });
        }
    };

    // Initialiser les fonctionnalités au chargement du document
    $(document).ready(function() {
        LePostDashboard.init();
    });

})(jQuery); 