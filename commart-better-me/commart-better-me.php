<?php
/**
 * Plugin Name: Commart Better Me
 * Plugin URI: https://example.com/
 * Description: A custom plugin to add a management menu with Contacts, Campaign, License sections, a dashboard shortcode, and several custom database tables.
 * Version: 1.1.2
 * Author: CommartEhsan2
 * Author URI: https://example.com/
 * Text Domain: commart-better-me
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $commart_db_version;
$commart_db_version = '1.0';

// Define a constant for plugin URL if not defined
if ( ! defined( 'COMMART_BETTER_ME_PLUGIN_URL' ) ) {
    define( 'COMMART_BETTER_ME_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// Define a constant for file storage directory (temporary, can be changed later)
if ( ! defined( 'COMMART_FILE_STORAGE_DIR' ) ) {
    define( 'COMMART_FILE_STORAGE_DIR', WP_CONTENT_DIR . '/uploads/filemanager' );
}

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/dashboard/lib/ajax-projects.php';
require_once plugin_dir_path(__FILE__) . 'includes/dashboard/lib/ajax-timer.php';
require_once plugin_dir_path(__FILE__) . 'includes/dashboard/lib/ajax-chat-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/dashboard/lib/ajax-functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/dashboard/lib/ajax-task.php';
require_once plugin_dir_path(__FILE__) . 'includes/dashboard/lib/ajax-filemanager.php';

// اضافه کردن فایل FTP Upload
require_once plugin_dir_path(__FILE__) . 'includes/dashboard/lib/ftp-upload.php';


/**
 * Function: Create a dedicated folder for the current user.
 * This function checks if the user is logged in and whether a folder under the defined storage path 
 * exists using the user's ID as the folder name. If not, it creates the folder.
 */
function commart_create_user_folder() {
    if ( is_user_logged_in() ) {
        $user_id = get_current_user_id();
        $user_folder = COMMART_FILE_STORAGE_DIR . '/' . $user_id;
        if ( ! file_exists( $user_folder ) ) {
            // wp_mkdir_p creates directory recursively if not available.
            wp_mkdir_p( $user_folder );
        }
    }
}
// Hook the function to 'init' so it runs on every page load when a user is logged in.
add_action( 'init', 'commart_create_user_folder' );

function commart_better_me_install() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Table names with WordPress prefix
    $targets_table            = $wpdb->prefix . 'commart_better_me_targets';
    $plans_table              = $wpdb->prefix . 'commart_better_me_plans';
    $steps_table              = $wpdb->prefix . 'commart_better_me_steps';
    $step_reports_table       = $wpdb->prefix . 'commart_better_me_step_reports';
    $employers_table          = $wpdb->prefix . 'commart_better_me_employers';
    $employer_brands_table    = $wpdb->prefix . 'commart_better_me_employer_brands';
    $projects_table           = $wpdb->prefix . 'commart_better_me_projects';
    $tasks_table              = $wpdb->prefix . 'commart_better_me_tasks';
   
    $myfile_table             = $wpdb->prefix . 'commart_better_me_myfile';
    $employer_profiles_table  = $wpdb->prefix . 'commart_better_me_employer_profiles';
    
   

    // SQL for other tables
    $sql_targets = "CREATE TABLE $targets_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        start_date DATE NOT NULL,
        deadline DATE NOT NULL,
        type ENUM('short', 'medium', 'long') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;";

    $sql_plans = "CREATE TABLE $plans_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        target_id BIGINT(20) UNSIGNED NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        description TEXT NOT NULL,
        budget DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (target_id) REFERENCES $targets_table(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;";

    $sql_steps = "CREATE TABLE $steps_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        plan_id BIGINT(20) UNSIGNED NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        title VARCHAR(255) NOT NULL,
        timer_start DATETIME DEFAULT NULL,
        deadline DATE NOT NULL,
        status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
        elapsed_time INT DEFAULT 0,
        report TEXT,
        container_status ENUM('play', 'pause') DEFAULT 'pause',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (plan_id) REFERENCES $plans_table(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;";

    $sql_step_reports = "CREATE TABLE $step_reports_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        step_id BIGINT(20) UNSIGNED NOT NULL,
        description TEXT NOT NULL,
        cost DECIMAL(10,2) NOT NULL,
        attached_file BIGINT(20) UNSIGNED DEFAULT NULL,
        reported_by BIGINT(20) UNSIGNED NOT NULL,
        reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (step_id) REFERENCES $steps_table(id) ON DELETE CASCADE,
        FOREIGN KEY (attached_file) REFERENCES $files_table(id) ON DELETE SET NULL,
        FOREIGN KEY (reported_by) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;";

    $sql_employers = "CREATE TABLE $employers_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        employer_username VARCHAR(255) NOT NULL,
        employer_name VARCHAR(255) NOT NULL,
        company_name VARCHAR(255) NOT NULL,
        activity_field VARCHAR(255) NOT NULL,
        brands TEXT,
        business_mobile VARCHAR(20) NOT NULL,
        site VARCHAR(255),
        email VARCHAR(255) NOT NULL,
        created_by BIGINT(20) UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (created_by) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;";

    $sql_employer_brands = "CREATE TABLE $employer_brands_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        employer_id BIGINT(20) UNSIGNED NOT NULL,
        brand_name VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (employer_id) REFERENCES $employers_table(id) ON DELETE CASCADE
    ) $charset_collate;";

    $sql_projects = "CREATE TABLE $projects_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        projects_id VARCHAR(36) NOT NULL UNIQUE,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        projects_title VARCHAR(255) NOT NULL,
        brand VARCHAR(255) NOT NULL,
        start_date DATE NOT NULL,
        deadline DATE NOT NULL,
        status ENUM('in_progress', 'stopped', 'done') NOT NULL,
        description TEXT NOT NULL,
        project_amount DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;";

    $sql_tasks = "CREATE TABLE $tasks_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        projects_id BIGINT(20) UNSIGNED NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        tasks_title VARCHAR(255) NOT NULL,
        tasks_timer_start DATETIME DEFAULT NULL,
        tasks_deadline DATE NOT NULL,
        tasks_status ENUM('pending', 'in_progress', 'paused', 'completed') DEFAULT 'pending',
        tasks_elapsed_time INT DEFAULT 0,
        tasks_report TEXT,
        tasks_container_status ENUM('play', 'pause', 'completed') DEFAULT 'pause',
        tasks_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (projects_id) REFERENCES $projects_table(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;";

    $sql_employer_profiles = "CREATE TABLE $employer_profiles_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        employer_username VARCHAR(255) NOT NULL,
        employer_name VARCHAR(255) NOT NULL,
        company_name VARCHAR(255) NOT NULL,
        activity_field VARCHAR(255) NOT NULL,
        brands TEXT,
        business_mobile VARCHAR(20) NOT NULL,
        site VARCHAR(255),
        email VARCHAR(255) NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        created_by BIGINT(20) UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_filemanager);
    dbDelta($sql_myfile);
    dbDelta($sql_targets);
    dbDelta($sql_plans);
    dbDelta($sql_steps);
    dbDelta($sql_step_reports);
    dbDelta($sql_employers);
    dbDelta($sql_employer_brands);
    dbDelta($sql_projects);
    dbDelta($sql_tasks);
    dbDelta($sql_employer_profiles);
}
register_activation_hook(__FILE__, 'commart_better_me_install');

/**
 * Function to insert user details into the custom table upon login.
 */
function commart_better_me_store_user_details( $user_login, $user ) {
    global $wpdb;
    $table_users = $wpdb->prefix . 'commart_better_me';
    // Check if a record for this user already exists.
    $existing = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM $table_users WHERE user_id = %d", $user->ID));
    if ( $existing == 0 ) {
        $profile_image = get_avatar_url($user->ID);
        $wpdb->insert(
            $table_users,
            array(
                'user_id'       => $user->ID,
                'user_login'    => $user->user_login,
                'email'         => $user->user_email,
                'profile_image' => $profile_image,
            ),
            array('%d', '%s', '%s', '%s')
        );
    }
}
add_action('wp_login', 'commart_better_me_store_user_details', 10, 2);

/**
 * Enqueue scripts and localize our AJAX settings with nonces.
 */
function commart_better_me_enqueue_assets( $hook ) {
    $plugin_pages = [ 'toplevel_page_commart-better-me', 'commart-contacts', 'commart-campaign', 'commart-license' ];
    if ( in_array($hook, $plugin_pages) ) {
        wp_enqueue_style(
            'commart-better-me-styles',
            plugins_url('includes/css/styles.css', __FILE__)
        );
         
        wp_enqueue_style(
            'commart-chat-style',
            plugins_url('includes/css/chatstyle.css', __FILE__)
        );
        wp_enqueue_script(
            'commart-better-me-scripts',
            plugins_url('includes/js/scripts.js', __FILE__),
            ['jquery'],
            null,
            true
        );
        wp_enqueue_script(
            'commart-timer-script',
            plugins_url('includes/dashboard/lib/timer-script.js', __FILE__),
            ['jquery'],
            null,
            true
        );
        wp_enqueue_script(
            'commart-task-timer-script',
            plugins_url('includes/dashboard/lib/task-timer-script.js', __FILE__),
            ['jquery'],
            null,
            true
        );

        wp_enqueue_script(
            'commart-task-script',
            plugins_url('includes/dashboard/lib/task-script.js', __FILE__),
            array('jquery'),
            null,
            true
        );
         wp_enqueue_script(
            'commart-timer-script',
            plugins_url('includes/dashboard/lib/upload-fix.js', __FILE__),
            ['jquery'],
            null,
            true
        );
        
        wp_enqueue_script(
            'commart-timer-script',
            plugins_url('includes/dashboard/lib/filemanager-script.js', __FILE__),
            ['jquery'],
            null,
            true
        );
        
        
        wp_localize_script( 'commart-task-script', 'commartTask', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('commart_task_nonce')
        ) );
    }
}
add_action('admin_enqueue_scripts', 'commart_better_me_enqueue_assets');

/**
 * Main plugin class.
 */
class CommartBetterMe {

    public function __construct() {
        add_action('admin_menu', [ $this, 'add_admin_menu' ]);
        add_shortcode('commart-better-me', [ $this, 'shortcode_dashboard' ]);
    }

    public function add_admin_menu() {
        add_menu_page(
            __( 'Better Me', 'commart-better-me' ),
            __( 'Better Me', 'commart-better-me' ),
            'manage_options',
            'commart-better-me',
            [ $this, 'load_dashboard_page' ],
            'dashicons-admin-generic',
            6
        );
        add_submenu_page(
            'commart-better-me',
            __( 'Contacts', 'commart-better-me' ),
            __( 'Contacts', 'commart-better-me' ),
            'manage_options',
            'commart-contacts',
            [ $this, 'load_contacts_page' ]
        );
        add_submenu_page(
            'commart-better-me',
            __( 'Campaign', 'commart-better-me' ),
            __( 'Campaign', 'commart-better-me' ),
            'manage_options',
            'commart-campaign',
            [ $this, 'load_campaign_page' ]
        );
        add_submenu_page(
            'commart-better-me',
            __( 'License', 'commart-better-me' ),
            __( 'License', 'commart-better-me' ),
            'manage_options',
            'commart-license',
            [ $this, 'load_license_page' ]
        );
    }

    public function load_dashboard_page() {
        include plugin_dir_path(__FILE__) . 'includes/dashboard/commart-dashboard.php';
    }

    public function load_contacts_page() {
        include plugin_dir_path(__FILE__) . 'includes/commart-contacts.php';
    }

    public function load_campaign_page() {
        include plugin_dir_path(__FILE__) . 'includes/commart-campaign.php';
    }

    public function load_license_page() {
        include plugin_dir_path(__FILE__) . 'includes/logfile.php';
    }

    public function shortcode_dashboard() {
        wp_enqueue_script(
            'commart-timer-script',
            plugins_url('includes/dashboard/lib/timer-script.js', __FILE__),
            ['jquery'],
            null,
            true
        );
        wp_enqueue_script(
            'commart-task-timer-script',
            plugins_url('includes/dashboard/lib/task-timer-script.js', __FILE__),
            ['jquery'],
            null,
            true
        );
        wp_enqueue_script(
            'commart-task-script',
            plugins_url('includes/dashboard/lib/task-script.js', __FILE__),
            ['jquery'],
            null,
            true
        );
        wp_enqueue_style(
            'commart-dashboard-style',
            COMMART_BETTER_ME_PLUGIN_URL . 'includes/css/dashboard.css'
        );
        wp_enqueue_style(
    'commart-filemanager-style',
    COMMART_BETTER_ME_PLUGIN_URL . 'includes/css/filemanager.css'
);
        wp_enqueue_style(
            'commart-chat-style',
            COMMART_BETTER_ME_PLUGIN_URL . 'includes/css/chatstyle.css'
        );
        if ( ! is_user_logged_in() ) {
            $login_register_file = plugin_dir_path(__FILE__) . 'includes/commart-login-register.php';
            if ( file_exists($login_register_file) ) {
                ob_start();
                include $login_register_file;
                return ob_get_clean();
            } else {
                return '<p>' . esc_html__('Login/Register file not found.', 'commart-better-me') . '</p>';
            }
        }
        $dashboard_file = plugin_dir_path(__FILE__) . 'includes/dashboard/commart-dashboard.php';
        if ( file_exists($dashboard_file) ) {
            ob_start();
            include $dashboard_file;
            return ob_get_clean();
        } else {
            return '<p>' . esc_html__('Dashboard file not found.', 'commart-better-me') . '</p>';
        }
    }
}

new CommartBetterMe();
?>