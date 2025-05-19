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
            this.setupGenerateArticleButtons();
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
        },

        /**
         * Configuration des boutons de génération d'articles
         */
        setupGenerateArticleButtons: function() {
            $('.lepost-generate-article').on('click', function(e) {
                e.preventDefault();
                
                const ideeId = $(this).data('id');
                if (!ideeId) return;
                
                const confirmation = confirm(lepost_dashboard.i18n.confirm_generate_article);
                if (!confirmation) return;
                
                // Rediriger vers la page des idées avec le paramètre d'action
                window.location.href = lepost_dashboard.urls.ideas_page + '&generate=' + ideeId;
            });
        }
    };

    // Initialiser les fonctionnalités au chargement du document
    $(document).ready(function() {
        LePostDashboard.init();
    });

})(jQuery); 