# LePost Client - Developer Guide

## Architecture Overview

LePost Client 2.0 features a completely redesigned architecture focused on simplicity, maintainability, and WordPress standards compliance. This guide provides comprehensive documentation for developers working with or extending the plugin.

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [File Structure](#file-structure)
3. [Core Components](#core-components)
4. [Database Schema](#database-schema)
5. [API Integration](#api-integration)
6. [Development Setup](#development-setup)
7. [Code Standards](#code-standards)
8. [Testing](#testing)
9. [Extending the Plugin](#extending-the-plugin)

---

## File Structure

```
LePost-Client/
├── lepost-client.php              # Main plugin file
├── src/
│   ├── Core/
│   │   ├── Plugin.php             # Main plugin class
│   │   └── Loader.php             # Hook loader
│   ├── Admin/
│   │   ├── SimpleAdmin.php        # Main admin controller
│   │   ├── Pages/                 # Page controllers
│   │   │   ├── AbstractPage.php   # Base page controller
│   │   │   ├── DashboardPage.php  # Dashboard controller
│   │   │   ├── IdeasPage.php      # Ideas management
│   │   │   ├── SettingsPage.php   # Settings controller
│   │   │   └── GenerateArticlePage.php # Article generation
│   │   ├── Tables/                # WordPress list tables
│   │   │   └── IdeasListTable.php # Ideas list table
│   │   └── templates/             # View templates
│   │       ├── dashboard/
│   │       ├── ideas/
│   │       ├── settings/
│   │       └── generate/
│   ├── Api/
│   │   └── Api.php                # API communication
│   ├── ContentType/
│   │   ├── Idee.php              # Ideas model
│   │   └── Article.php           # Articles model
│   └── Common/
│       ├── Activator.php         # Plugin activation
│       ├── Deactivator.php       # Plugin deactivation
│       └── I18n.php              # Internationalization
├── assets/
│   ├── css/
│   │   └── lepost-admin-simple.css  # Unified admin styles
│   └── js/
│       └── lepost-admin-simple.js   # Unified admin scripts
└── docs/
    ├── USER_GUIDE.md             # User documentation
    ├── DEVELOPER_GUIDE.md        # This file
    └── CHANGELOG.md              # Version history
```

---

## Core Components

### 1. Plugin Class (`src/Core/Plugin.php`)

The main plugin class handles initialization and dependency management.

```php
class Plugin {
    private $loader;
    private $plugin_name;
    private $version;

    public function __construct() {
        $this->version = LEPOST_CLIENT_VERSION;
        $this->plugin_name = 'lepost-client';
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
    }
}
```

**Key Responsibilities:**
- Load all required dependencies
- Initialize internationalization
- Set up admin and public hooks
- Manage plugin lifecycle

### 2. Simple Admin (`src/Admin/SimpleAdmin.php`)

The simplified admin controller replaces the complex tab-based system.

```php
class SimpleAdmin {
    private $plugin_name;
    private $version;
    private $pages = [];

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->init_pages();
    }

    public function init_pages() {
        $this->pages = [
            'dashboard' => new DashboardPage($this->plugin_name, $this->version),
            'ideas' => new IdeasPage($this->plugin_name, $this->version),
            'settings' => new SettingsPage($this->plugin_name, $this->version),
            'generate' => new GenerateArticlePage($this->plugin_name, $this->version)
        ];
    }
}
```

**Features:**
- Standard WordPress admin menu structure
- Page-based navigation (no complex tabs)
- PSR-4 autoloading compatible
- Modular page controllers

### 3. Abstract Page Controller (`src/Admin/Pages/AbstractPage.php`)

Base class for all admin pages providing common functionality.

```php
abstract class AbstractPage {
    protected $plugin_name;
    protected $version;
    protected $page_slug;
    protected $capability = 'manage_options';

    abstract public function render();
    abstract public function handle_form_submission();

    protected function add_admin_notice($message, $type = 'success') {
        // Standard WordPress admin notice handling
    }

    protected function verify_nonce($action) {
        // Security verification
    }
}
```

**Provides:**
- Standardized form handling
- Nonce verification
- Admin notice management
- Common security checks

### 4. Models

#### Ideas Model (`src/ContentType/Idee.php`)

```php
class Idee {
    public static function get_all($args = []) {
        // Retrieve ideas with pagination and filtering
    }

    public static function create($data) {
        // Create new idea with validation
    }

    public static function update($id, $data) {
        // Update existing idea
    }

    public static function delete($id) {
        // Soft delete idea
    }

    public static function search($query, $args = []) {
        // Advanced search functionality
    }
}
```

#### Articles Model (`src/ContentType/Article.php`)

```php
class Article {
    public static function generate_from_idea($idea_id, $settings = []) {
        // Generate article using LePost API
    }

    public static function create_wp_post($article_data, $settings = []) {
        // Create WordPress post from generated article
    }

    public static function get_recent($limit = 5) {
        // Get recently generated articles
    }
}
```

---

## Database Schema

### Ideas Table (`wp_lepost_idees`)

```sql
CREATE TABLE wp_lepost_idees (
    id int(11) NOT NULL AUTO_INCREMENT,
    titre varchar(255) NOT NULL,
    description text,
    mots_cles text,
    categorie varchar(100),
    statut varchar(50) DEFAULT 'draft',
    date_creation datetime DEFAULT CURRENT_TIMESTAMP,
    date_modification datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    article_genere tinyint(1) DEFAULT 0,
    article_id int(11) DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_statut (statut),
    KEY idx_categorie (categorie),
    KEY idx_date_creation (date_creation)
);
```

### Articles Table (`wp_lepost_articles`)

```sql
CREATE TABLE wp_lepost_articles (
    id int(11) NOT NULL AUTO_INCREMENT,
    idee_id int(11) NOT NULL,
    titre varchar(255) NOT NULL,
    contenu longtext NOT NULL,
    post_id int(11) DEFAULT NULL,
    statut varchar(50) DEFAULT 'draft',
    date_creation datetime DEFAULT CURRENT_TIMESTAMP,
    metadonnees text,
    PRIMARY KEY (id),
    KEY idx_idee_id (idee_id),
    KEY idx_post_id (post_id),
    KEY idx_statut (statut),
    FOREIGN KEY (idee_id) REFERENCES wp_lepost_idees(id) ON DELETE CASCADE
);
```

---

## API Integration

### API Client (`src/Api/Api.php`)

The API client handles all communication with the LePost service.

```php
class Api {
    private $api_key;
    private $base_url = 'https://api.lepost.ai/v1/';

    public function __construct($api_key = null) {
        $this->api_key = $api_key ?: get_option('lepost_api_key');
    }

    public function test_connection() {
        // Test API connectivity
    }

    public function generate_article($idea_data, $settings = []) {
        // Generate article from idea
    }

    public function get_credits() {
        // Get remaining API credits
    }

    private function make_request($endpoint, $data = [], $method = 'POST') {
        // HTTP request handling with error management
    }
}
```

### Error Handling

```php
class ApiException extends Exception {
    private $response_code;
    private $response_body;

    public function __construct($message, $code = 0, $response_body = null) {
        parent::__construct($message, $code);
        $this->response_code = $code;
        $this->response_body = $response_body;
    }
}
```

---

## Development Setup

### Prerequisites

- PHP 7.4+
- WordPress 5.0+
- Composer (for dependencies)
- Node.js (for asset building)

### Local Development

1. **Clone Repository**
   ```bash
   git clone https://github.com/your-org/lepost-client.git
   cd lepost-client
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Development Environment**
   ```bash
   # Start WordPress development environment
   wp-env start
   
   # Watch for asset changes
   npm run watch
   ```

4. **Database Setup**
   ```bash
   # Run database migrations
   wp lepost migrate
   
   # Seed test data
   wp lepost seed
   ```

### Build Process

```bash
# Development build
npm run dev

# Production build
npm run build

# CSS compilation
npm run css

# JavaScript compilation
npm run js
```

---

## Code Standards

### PHP Standards

Follow WordPress Coding Standards and PSR-4 autoloading.

```php
<?php
/**
 * Class description
 *
 * @package    LePostClient
 * @subpackage LePostClient/Admin
 * @since      2.0.0
 */

namespace LePostClient\Admin\Pages;

class ExamplePage extends AbstractPage {
    
    /**
     * Method description
     *
     * @since  2.0.0
     * @param  array $args Method parameters
     * @return mixed       Return value description
     */
    public function example_method($args = []) {
        // Implementation
    }
}
```

### JavaScript Standards

Use ES6+ with WordPress conventions.

```javascript
/**
 * Example module
 *
 * @package LePostClient
 * @since   2.0.0
 */

const ExampleModule = {
    
    /**
     * Initialize module
     */
    init: function() {
        this.setupEventListeners();
    },
    
    /**
     * Setup event listeners
     */
    setupEventListeners: function() {
        // Implementation
    }
};
```

### CSS Standards

Use BEM methodology with WordPress admin compatibility.

```css
/* Block */
.lepost-admin {
    /* Block styles */
}

/* Element */
.lepost-admin__header {
    /* Element styles */
}

/* Modifier */
.lepost-admin--large {
    /* Modifier styles */
}
```

---

## Testing

### PHP Unit Tests

```php
class TestIdeasModel extends WP_UnitTestCase {
    
    public function test_create_idea() {
        $idea_data = [
            'titre' => 'Test Idea',
            'description' => 'Test description',
            'mots_cles' => 'test, idea',
            'categorie' => 'technology'
        ];
        
        $idea_id = Idee::create($idea_data);
        
        $this->assertNotEmpty($idea_id);
        $this->assertIsInt($idea_id);
    }
    
    public function test_get_idea() {
        $idea = Idee::get_by_id(1);
        
        $this->assertNotNull($idea);
        $this->assertEquals('Test Idea', $idea->titre);
    }
}
```

### JavaScript Tests

```javascript
describe('LePost Admin', function() {
    
    beforeEach(function() {
        // Setup
    });
    
    it('should initialize correctly', function() {
        expect(LePostAdmin.init).toBeDefined();
        LePostAdmin.init();
        expect(LePostAdmin.state.isInitialized).toBe(true);
    });
    
    it('should handle API testing', function() {
        // Mock API response
        spyOn($, 'ajax').and.callFake(function(options) {
            options.success({success: true, data: {credits: 100}});
        });
        
        LePostAdmin.setupAPITesting();
        $('#test-api-connection').click();
        
        expect($('#api-connection-status')).toContain('Connection successful');
    });
});
```

### Running Tests

```bash
# PHP tests
vendor/bin/phpunit

# JavaScript tests
npm test

# All tests
npm run test:all

# Coverage report
npm run test:coverage
```

---

## Extending the Plugin

### Adding New Page Controllers

1. **Create Page Controller**
   ```php
   namespace LePostClient\Admin\Pages;
   
   class CustomPage extends AbstractPage {
       protected $page_slug = 'lepost-custom';
       
       public function render() {
           // Page rendering logic
       }
       
       public function handle_form_submission() {
           // Form handling logic
       }
   }
   ```

2. **Register in SimpleAdmin**
   ```php
   // In SimpleAdmin::init_pages()
   $this->pages['custom'] = new CustomPage($this->plugin_name, $this->version);
   ```

3. **Add Menu Item**
   ```php
   // In SimpleAdmin::add_menu_pages()
   add_submenu_page(
       'lepost-dashboard',
       __('Custom Page', 'lepost-client'),
       __('Custom', 'lepost-client'),
       'manage_options',
       'lepost-custom',
       [$this->pages['custom'], 'render']
   );
   ```

### Creating Custom Templates

```php
// In your page controller
private function render_template($template, $data = []) {
    $template_path = LEPOST_CLIENT_PLUGIN_DIR . 'src/Admin/templates/' . $template . '.php';
    
    if (file_exists($template_path)) {
        extract($data);
        include $template_path;
    }
}
```

### Adding API Endpoints

```php
class CustomApiEndpoint {
    
    public function __construct() {
        add_action('wp_ajax_lepost_custom_action', [$this, 'handle_request']);
    }
    
    public function handle_request() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'lepost_custom_nonce')) {
            wp_die('Security check failed');
        }
        
        // Process request
        $result = $this->process_custom_action($_POST);
        
        // Return response
        wp_send_json_success($result);
    }
}
```

### Custom Hooks and Filters

The plugin provides various hooks for customization:

```php
// Action hooks
do_action('lepost_before_idea_save', $idea_data);
do_action('lepost_after_article_generation', $article_id, $idea_id);
do_action('lepost_settings_updated', $settings);

// Filter hooks
$idea_data = apply_filters('lepost_idea_data_before_save', $idea_data);
$article_content = apply_filters('lepost_article_content', $content, $idea_id);
$generation_settings = apply_filters('lepost_generation_settings', $settings);
```

### Usage Examples

```php
// Hook into idea creation
add_action('lepost_after_idea_save', function($idea_id, $idea_data) {
    // Send notification email
    wp_mail(
        get_option('admin_email'),
        'New Idea Created',
        'A new idea has been created: ' . $idea_data['titre']
    );
}, 10, 2);

// Modify article content before saving
add_filter('lepost_article_content', function($content, $idea_id) {
    // Add custom footer to all articles
    $footer = "\n\n---\n*This article was generated using LePost AI.*";
    return $content . $footer;
}, 10, 2);

// Custom generation settings
add_filter('lepost_generation_settings', function($settings) {
    // Force specific settings for certain users
    if (current_user_can('editor')) {
        $settings['creativity_level'] = 'conservative';
    }
    return $settings;
});
```

---

## Performance Optimization

### Database Optimization

```php
// Use prepared statements
$wpdb->prepare("SELECT * FROM {$wpdb->prefix}lepost_idees WHERE statut = %s", $status);

// Implement caching
$cache_key = 'lepost_ideas_' . md5(serialize($args));
$results = wp_cache_get($cache_key, 'lepost');

if (false === $results) {
    $results = $this->query_database($args);
    wp_cache_set($cache_key, $results, 'lepost', HOUR_IN_SECONDS);
}
```

### Asset Optimization

```javascript
// Lazy loading
const LazyLoader = {
    init: function() {
        this.observeElements();
    },
    
    observeElements: function() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.loadElement(entry.target);
                }
            });
        });
        
        document.querySelectorAll('[data-lazy]').forEach(el => {
            observer.observe(el);
        });
    }
};
```

### Memory Management

```php
// Batch processing for large datasets
class BatchProcessor {
    
    public function process_ideas_batch($batch_size = 100) {
        $offset = 0;
        
        do {
            $ideas = Idee::get_all([
                'limit' => $batch_size,
                'offset' => $offset
            ]);
            
            foreach ($ideas as $idea) {
                $this->process_idea($idea);
            }
            
            $offset += $batch_size;
            
            // Clear memory
            unset($ideas);
            
        } while (count($ideas) === $batch_size);
    }
}
```

---

## Security Best Practices

### Input Validation

```php
// Sanitize and validate input
public function sanitize_idea_data($data) {
    return [
        'titre' => sanitize_text_field($data['titre'] ?? ''),
        'description' => wp_kses_post($data['description'] ?? ''),
        'mots_cles' => sanitize_text_field($data['mots_cles'] ?? ''),
        'categorie' => sanitize_key($data['categorie'] ?? '')
    ];
}

// Validate required fields
public function validate_idea_data($data) {
    $errors = [];
    
    if (empty($data['titre'])) {
        $errors[] = __('Title is required', 'lepost-client');
    }
    
    if (strlen($data['titre']) > 255) {
        $errors[] = __('Title is too long', 'lepost-client');
    }
    
    return $errors;
}
```

### Capability Checks

```php
// Check user capabilities
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have permission to access this page.', 'lepost-client'));
}

// Role-based access
public function user_can_generate_articles() {
    return current_user_can('edit_posts') || current_user_can('manage_options');
}
```

### SQL Injection Prevention

```php
// Always use prepared statements
$wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}lepost_idees WHERE titre LIKE %s AND categorie = %s",
    '%' . $wpdb->esc_like($search_term) . '%',
    $category
);
```

---

## Debugging and Logging

### Debug Mode

```php
// Enable debug mode
define('LEPOST_DEBUG', true);

// Debug logging
if (defined('LEPOST_DEBUG') && LEPOST_DEBUG) {
    error_log('LePost Debug: ' . $message);
}

// Debug information
public function get_debug_info() {
    return [
        'plugin_version' => LEPOST_CLIENT_VERSION,
        'wp_version' => get_bloginfo('version'),
        'php_version' => PHP_VERSION,
        'api_status' => $this->test_api_connection(),
        'ideas_count' => Idee::count_all(),
        'articles_count' => Article::count_all()
    ];
}
```

### Error Handling

```php
try {
    $result = $this->api->generate_article($idea_data);
} catch (ApiException $e) {
    $this->log_error('API Error: ' . $e->getMessage(), [
        'idea_id' => $idea_id,
        'response_code' => $e->getCode(),
        'response_body' => $e->getResponseBody()
    ]);
    
    $this->add_admin_notice(
        __('Article generation failed. Please try again later.', 'lepost-client'),
        'error'
    );
}
```

---

## Deployment

### Build Process

```bash
# Create production build
npm run build:production

# Generate translation files
wp i18n make-pot . languages/lepost-client.pot

# Create distribution package
npm run package
```

### Version Management

```php
// Update version in main plugin file
// Version: 2.1.0

// Update version constant
define('LEPOST_CLIENT_VERSION', '2.1.0');

// Database migrations
public function maybe_upgrade() {
    $current_version = get_option('lepost_client_version', '0.0.0');
    
    if (version_compare($current_version, LEPOST_CLIENT_VERSION, '<')) {
        $this->run_upgrades($current_version);
        update_option('lepost_client_version', LEPOST_CLIENT_VERSION);
    }
}
```

### Quality Assurance

```bash
# Code quality checks
composer run phpcs
composer run phpmd
composer run phpstan

# Security scanning
composer run security-check

# Performance testing
npm run test:performance
```

---

## Conclusion

This developer guide provides comprehensive documentation for working with LePost Client 2.0. The simplified architecture makes the codebase more maintainable while providing extensive customization options.

For additional support or questions, please refer to:
- GitHub repository for issues and contributions
- Plugin documentation for user guides
- WordPress.org plugin page for community support

The simplified architecture ensures that future development and maintenance will be more efficient while maintaining high code quality standards. 