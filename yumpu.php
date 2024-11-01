<?php
/**
 * Plugin Name: YUMPU E-Paper publishing
 * Description:  YUMPU is a free PDF to E-Paper site. The Service allows you to upload a PDF and embed it as an E-Paper via shortcode.
 * Author: Yumpu.com
 * Author URI: https://www.yumpu.com
 * Requires at least: 4.6
 * Version: 3.0.7
 * Requires PHP: 5.6
 * License: GPLv3 or later
 * Text Domain: yumpu-epaper-publishing
 * Domain Path: /languages
 */

Class WP_Yumpu {
    static private $instance = null;

    public function __construct() {
        spl_autoload_register(static function ($class) {
            $file = str_replace(['YumpuPlugin\\', '/', '\\'], DIRECTORY_SEPARATOR, __DIR__ . '/lib/' . $class) . '.php';
            if (file_exists($file)) {
                require($file);
            }
        });

        add_action('plugins_loaded', function() {
            $settings = new YumpuPlugin\Settings();
            $yumpuApi = new YumpuPlugin\YumpuAPI($settings->getApiToken());

            new YumpuPlugin\Views\Settings($yumpuApi, $settings);
            new YumpuPlugin\Views\DocumentUpload();
            new YumpuPlugin\Views\DocumentList(__FILE__);
            new YumpuPlugin\API($yumpuApi);
            new YumpuPlugin\Shortcode($yumpuApi);

            register_activation_hook( __FILE__, [$this, 'plugin_activate']);
            register_deactivation_hook(__FILE__, [$this, 'plugin_deactivate']);

            if (!get_option("YUMPU_API_ACCESS_TOKEN", null)) {
                add_action('admin_notices',
                    function() {
                        ?>
                        <div class="updated">
                            <p>
                                <?php esc_html_e( 'Get your YUMPU API Key from your account and add it', 'yumpu-epaper-publishing'); ?> <a href="<?php menu_page_url('yumpu-settings'); ?>"><?php esc_html_e( 'here', 'yumpu-epaper-publishing'); ?></a>.
                                <?php esc_html_e( 'You can get more information', 'yumpu-epaper-publishing'); ?> <a href="https://helpcenter.yumpu.com/en/articles/1101967-api-token"><?php esc_html_e( 'here', 'yumpu-epaper-publishing'); ?></a>.
                            </p>
                        </div>
                        <?php
                    }
                );
            }
        });
    }

    public function plugin_activate()
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    }

    public function plugin_deactivate() {}

    /**
     * @return WP_Yumpu
     */
    static public function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}

WP_Yumpu::getInstance();