/**
 * LePost Client - Simplified Admin JavaScript
 *
 * Provides essential admin functionality with minimal complexity.
 * Focuses on progressive enhancement rather than JavaScript dependencies.
 * Optimized for performance and accessibility.
 *
 * @package    LePostClient
 * @since      2.0.0
 * @version    2.1.0
 */

(function($) {
    'use strict';

    /**
     * Main LePost Admin object
     */
    const LePostAdmin = {
        
        // Configuration
        config: {
            animationDuration: 300,
            debounceDelay: 2000,
            autoSaveMinLength: 50,
            tooltipDelay: 500
        },

        // State management
        state: {
            isInitialized: false,
            activeTooltip: null,
            progressOverlay: null
        },

        /**
         * Initialize admin functionality
         */
        init: function() {
            if (this.state.isInitialized) {
                return;
            }

            try {
                this.setupFormEnhancements();
                this.setupAPITesting();
                this.setupConfirmations();
                this.setupProgressIndicators();
                this.setupTooltips();
                this.setupAccessibility();
                this.setupPerformanceOptimizations();
                
                this.state.isInitialized = true;
                this.log('LePost Admin initialized successfully');
            } catch (error) {
                this.handleError('Initialization failed', error);
            }
        },

        /**
         * Setup form enhancements
         */
        setupFormEnhancements: function() {
            // Form validation indicators with improved UX
            $('form[data-validate]').on('submit', function(e) {
                const $form = $(this);
                const $submitBtn = $form.find('input[type="submit"], button[type="submit"]');
                
                // Prevent double submission
                if ($submitBtn.prop('disabled')) {
                    e.preventDefault();
                    return false;
                }
                
                // Show loading state
                $submitBtn.prop('disabled', true);
                $submitBtn.addClass('lepost-loading');
                
                // Add spinner if not already present
                if (!$submitBtn.find('.spinner').length) {
                    $submitBtn.append(' <span class="spinner is-active" style="float: none; margin: 0 0 0 5px;"></span>');
                }

                // Add fade-in animation to form
                $form.addClass('lepost-fade-in');
            });

            // Enhanced auto-save with better feedback
            $('textarea[data-autosave]').on('input', this.debounce(function() {
                const $textarea = $(this);
                const content = $textarea.val();
                const fieldName = $textarea.attr('name');
                
                if (content.length > LePostAdmin.config.autoSaveMinLength) {
                    try {
                        localStorage.setItem('lepost_draft_' + fieldName, content);
                        localStorage.setItem('lepost_draft_' + fieldName + '_timestamp', Date.now());
                        
                        // Show saved indicator with animation
                        $textarea.siblings('.autosave-indicator').remove();
                        const $indicator = $('<span class="autosave-indicator lepost-fade-in" style="color: var(--lepost-success-color); font-size: 12px; margin-left: 10px;">✓ Draft saved</span>');
                        $textarea.after($indicator);
                        
                        setTimeout(() => {
                            $indicator.fadeOut(LePostAdmin.config.animationDuration);
                        }, 3000);
                    } catch (error) {
                        LePostAdmin.handleError('Auto-save failed', error);
                    }
                }
            }, this.config.debounceDelay));

            // Load draft content with timestamp check
            $('textarea[data-autosave]').each(function() {
                const $textarea = $(this);
                const fieldName = $textarea.attr('name');
                const savedContent = localStorage.getItem('lepost_draft_' + fieldName);
                const timestamp = localStorage.getItem('lepost_draft_' + fieldName + '_timestamp');
                
                if (savedContent && !$textarea.val()) {
                    // Check if draft is not too old (24 hours)
                    const isRecent = timestamp && (Date.now() - parseInt(timestamp)) < 24 * 60 * 60 * 1000;
                    
                    if (isRecent) {
                        $textarea.val(savedContent);
                        const $indicator = $('<span class="draft-loaded lepost-slide-up" style="color: var(--lepost-primary-color); font-size: 12px; margin-left: 10px;">📄 Draft restored</span>');
                        $textarea.after($indicator);
                        
                        setTimeout(() => {
                            $indicator.fadeOut(LePostAdmin.config.animationDuration);
                        }, 5000);
                    }
                }
            });

            // Enhanced form field focus effects
            $('.lepost-admin input, .lepost-admin textarea, .lepost-admin select').on('focus', function() {
                $(this).closest('.lepost-form-row, .card').addClass('lepost-focused');
            }).on('blur', function() {
                $(this).closest('.lepost-form-row, .card').removeClass('lepost-focused');
            });
        },

        /**
         * Setup API connection testing with enhanced feedback
         */
        setupAPITesting: function() {
            $('#test-api-connection').on('click', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const $statusArea = $('#api-connection-status');
                const apiKey = $('#api_key').val();
                
                if (!apiKey || apiKey.trim().length === 0) {
                    LePostAdmin.showNotice('Please enter an API key first.', 'warning');
                    $('#api_key').focus();
                    return;
                }
                
                // Show enhanced loading state
                $button.prop('disabled', true).text('Testing Connection...');
                $statusArea.html('<div class="lepost-status-testing lepost-fade-in"><span class="spinner is-active"></span> Testing connection...</div>');
                
                // Make AJAX request with timeout
                $.ajax({
                    url: lepost_admin_ajax.ajax_url,
                    type: 'POST',
                    timeout: 30000, // 30 second timeout
                    data: {
                        action: 'lepost_test_api_connection',
                        api_key: apiKey.trim(),
                        nonce: lepost_admin_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            const creditsInfo = response.data.credits ? 
                                `<p><strong>Available credits:</strong> ${response.data.credits}</p>` : '';
                            
                            $statusArea.html(`
                                <div class="lepost-status-success lepost-slide-up">
                                    <span class="dashicons dashicons-yes-alt"></span>
                                    <div>
                                        <p><strong>Connection successful!</strong></p>
                                        ${creditsInfo}
                                        <p><small>API endpoint is responding correctly.</small></p>
                                    </div>
                                </div>
                            `);
                            
                            LePostAdmin.showNotice('API connection test successful!', 'success');
                        } else {
                            const errorMessage = response.data?.message || 'Unknown error occurred';
                            $statusArea.html(`
                                <div class="lepost-status-error lepost-slide-up">
                                    <span class="dashicons dashicons-warning"></span>
                                    <div>
                                        <p><strong>Connection failed:</strong></p>
                                        <p>${errorMessage}</p>
                                        <p><small>Please check your API key and try again.</small></p>
                                    </div>
                                </div>
                            `);
                            
                            LePostAdmin.showNotice('API connection test failed: ' + errorMessage, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        let errorMessage = 'Request failed. Please check your connection and try again.';
                        
                        if (status === 'timeout') {
                            errorMessage = 'Request timed out. The server may be busy.';
                        } else if (xhr.status === 0) {
                            errorMessage = 'Network error. Please check your internet connection.';
                        }
                        
                        $statusArea.html(`
                            <div class="lepost-status-error lepost-slide-up">
                                <span class="dashicons dashicons-dismiss"></span>
                                <div>
                                    <p><strong>Request failed</strong></p>
                                    <p>${errorMessage}</p>
                                    <p><small>Error details: ${error}</small></p>
                                </div>
                            </div>
                        `);
                        
                        LePostAdmin.showNotice(errorMessage, 'error');
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('Test Connection');
                    }
                });
            });
        },

        /**
         * Setup confirmation dialogs with enhanced UX
         */
        setupConfirmations: function() {
            // Delete confirmations with custom styling
            $('a[data-confirm], button[data-confirm]').on('click', function(e) {
                const $element = $(this);
                const message = $element.data('confirm') || lepost_admin_ajax.strings.confirm_delete;
                const isDestructive = $element.hasClass('delete') || $element.data('action') === 'delete';
                
                // Create custom confirmation dialog
                const confirmed = LePostAdmin.showConfirmDialog(message, {
                    type: isDestructive ? 'danger' : 'info',
                    confirmText: isDestructive ? 'Delete' : 'Confirm',
                    cancelText: 'Cancel'
                });
                
                if (!confirmed) {
                    e.preventDefault();
                    return false;
                }
                
                // Add loading state to the element
                $element.addClass('lepost-loading');
            });

            // Bulk action confirmations with count display
            $('form[data-bulk-confirm]').on('submit', function(e) {
                const $form = $(this);
                const $checkedItems = $form.find('input[type="checkbox"]:checked');
                const checkedCount = $checkedItems.length;
                
                if (checkedCount === 0) {
                    LePostAdmin.showNotice('Please select at least one item.', 'warning');
                    e.preventDefault();
                    return false;
                }
                
                const action = $form.find('select[name="action"]').val();
                if (action === 'delete') {
                    const message = `Are you sure you want to delete ${checkedCount} item${checkedCount > 1 ? 's' : ''}? This action cannot be undone.`;
                    if (!confirm(message)) {
                        e.preventDefault();
                        return false;
                    }
                }
                
                // Show progress for bulk operations
                LePostAdmin.showProgress(`Processing ${checkedCount} item${checkedCount > 1 ? 's' : ''}...`);
            });
        },

        /**
         * Setup progress indicators with better UX
         */
        setupProgressIndicators: function() {
            // Show progress for long-running operations
            $('form[data-progress]').on('submit', function() {
                const $form = $(this);
                const message = $form.data('progress') || 'Processing...';
                LePostAdmin.showProgress(message);
            });

            // Auto-hide progress on page unload
            $(window).on('beforeunload', function() {
                LePostAdmin.hideProgress();
            });
        },

        /**
         * Setup enhanced tooltips
         */
        setupTooltips: function() {
            let tooltipTimeout;
            
            $('[data-tooltip]').on('mouseenter', function() {
                const $element = $(this);
                
                tooltipTimeout = setTimeout(() => {
                    LePostAdmin.showTooltip($element);
                }, LePostAdmin.config.tooltipDelay);
                
            }).on('mouseleave', function() {
                clearTimeout(tooltipTimeout);
                LePostAdmin.hideTooltip();
            });
        },

        /**
         * Setup accessibility enhancements
         */
        setupAccessibility: function() {
            // Keyboard navigation for custom elements
            $('.lepost-dashboard-card').attr('tabindex', '0').on('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    const $link = $(this).find('a').first();
                    if ($link.length) {
                        $link[0].click();
                    }
                }
            });

            // ARIA labels for dynamic content
            $('[data-count]').each(function() {
                const $element = $(this);
                const count = $element.text();
                $element.attr('aria-label', `${$element.data('label') || 'Count'}: ${count}`);
            });

            // Focus management for modals and overlays
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    LePostAdmin.hideProgress();
                    LePostAdmin.hideTooltip();
                }
            });
        },

        /**
         * Setup performance optimizations
         */
        setupPerformanceOptimizations: function() {
            // Lazy load images if any
            $('img[data-src]').each(function() {
                const $img = $(this);
                if (LePostAdmin.isElementInViewport($img[0])) {
                    $img.attr('src', $img.data('src')).removeAttr('data-src');
                }
            });

            // Throttled scroll handler for performance
            let scrollTimeout;
            $(window).on('scroll', function() {
                if (scrollTimeout) {
                    clearTimeout(scrollTimeout);
                }
                
                scrollTimeout = setTimeout(() => {
                    // Handle scroll-based functionality
                    LePostAdmin.handleScroll();
                }, 100);
            });
        },

        /**
         * Show custom confirmation dialog
         */
        showConfirmDialog: function(message, options = {}) {
            const defaults = {
                type: 'info',
                confirmText: 'Confirm',
                cancelText: 'Cancel'
            };
            
            const settings = Object.assign(defaults, options);
            
            // For now, use native confirm - can be enhanced with custom modal later
            return confirm(message);
        },

        /**
         * Show tooltip
         */
        showTooltip: function($element) {
            this.hideTooltip(); // Hide any existing tooltip
            
            const text = $element.data('tooltip');
            if (!text) return;
            
            const $tooltip = $(`
                <div class="lepost-tooltip lepost-fade-in" style="
                    position: absolute;
                    background: var(--lepost-text-color);
                    color: var(--lepost-card-background);
                    padding: 8px 12px;
                    border-radius: var(--lepost-border-radius);
                    font-size: 12px;
                    z-index: 10000;
                    pointer-events: none;
                    white-space: nowrap;
                    box-shadow: var(--lepost-box-shadow);
                ">${text}</div>
            `);
            
            $('body').append($tooltip);
            
            const offset = $element.offset();
            const elementWidth = $element.outerWidth();
            const elementHeight = $element.outerHeight();
            const tooltipWidth = $tooltip.outerWidth();
            const tooltipHeight = $tooltip.outerHeight();
            
            // Position tooltip above element, centered
            let top = offset.top - tooltipHeight - 8;
            let left = offset.left + (elementWidth / 2) - (tooltipWidth / 2);
            
            // Adjust if tooltip would go off screen
            if (left < 10) left = 10;
            if (left + tooltipWidth > $(window).width() - 10) {
                left = $(window).width() - tooltipWidth - 10;
            }
            if (top < 10) {
                top = offset.top + elementHeight + 8; // Show below instead
            }
            
            $tooltip.css({ top: top, left: left });
            this.state.activeTooltip = $tooltip;
        },

        /**
         * Hide tooltip
         */
        hideTooltip: function() {
            if (this.state.activeTooltip) {
                this.state.activeTooltip.remove();
                this.state.activeTooltip = null;
            }
        },

        /**
         * Show progress overlay
         */
        showProgress: function(message = 'Processing...') {
            this.hideProgress(); // Hide any existing progress
            
            const $overlay = $(`
                <div class="lepost-progress-overlay lepost-fade-in">
                    <div class="lepost-progress-content">
                        <span class="spinner is-active" style="float: none; margin-bottom: 15px;"></span>
                        <h3>${message}</h3>
                        <p>Please wait while we process your request...</p>
                    </div>
                </div>
            `);
            
            $('body').append($overlay);
            this.state.progressOverlay = $overlay;
            
            // Prevent body scroll
            $('body').addClass('lepost-no-scroll');
        },

        /**
         * Hide progress overlay
         */
        hideProgress: function() {
            if (this.state.progressOverlay) {
                this.state.progressOverlay.fadeOut(this.config.animationDuration, function() {
                    $(this).remove();
                });
                this.state.progressOverlay = null;
                $('body').removeClass('lepost-no-scroll');
            }
        },

        /**
         * Handle scroll events
         */
        handleScroll: function() {
            // Lazy load images
            $('img[data-src]').each(function() {
                const $img = $(this);
                if (LePostAdmin.isElementInViewport($img[0])) {
                    $img.attr('src', $img.data('src')).removeAttr('data-src');
                }
            });
        },

        /**
         * Check if element is in viewport
         */
        isElementInViewport: function(element) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        },

        /**
         * Debounce function to limit function calls
         */
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func.apply(this, args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        /**
         * Show admin notice with enhanced styling
         */
        showNotice: function(message, type = 'info', dismissible = true) {
            const icons = {
                success: 'yes-alt',
                error: 'dismiss',
                warning: 'warning',
                info: 'info'
            };
            
            const icon = icons[type] || icons.info;
            
            const $notice = $(`
                <div class="notice notice-${type} lepost-fade-in ${dismissible ? 'is-dismissible' : ''}" style="display: none;">
                    <p>
                        <span class="dashicons dashicons-${icon}" style="margin-right: 5px;"></span>
                        ${message}
                    </p>
                    ${dismissible ? '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss</span></button>' : ''}
                </div>
            `);
            
            $('.wrap .wp-header-end').after($notice);
            $notice.slideDown(this.config.animationDuration);
            
            if (dismissible) {
                $notice.find('.notice-dismiss').on('click', function() {
                    $notice.slideUp(LePostAdmin.config.animationDuration, function() {
                        $(this).remove();
                    });
                });
            }
            
            // Auto-dismiss success notices
            if (type === 'success') {
                setTimeout(() => {
                    $notice.slideUp(LePostAdmin.config.animationDuration, function() {
                        $(this).remove();
                    });
                }, 5000);
            }
        },

        /**
         * Log messages (only in debug mode)
         */
        log: function(message, data = null) {
            if (window.console && typeof console.log === 'function') {
                if (data) {
                    console.log('[LePost Admin]', message, data);
                } else {
                    console.log('[LePost Admin]', message);
                }
            }
        },

        /**
         * Handle errors gracefully
         */
        handleError: function(message, error = null) {
            this.log('Error: ' + message, error);
            
            if (error && error.stack) {
                this.log('Stack trace:', error.stack);
            }
            
            // Show user-friendly error message
            this.showNotice('An error occurred: ' + message, 'error');
        }
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        LePostAdmin.init();
    });

    /**
     * Make LePostAdmin available globally
     */
    window.LePostAdmin = LePostAdmin;

})(jQuery); 