<?php

namespace YumpuPlugin\Views;

use YumpuPlugin\YumpuAPI;

class Settings
{
    /**
     * @var YumpuAPI
     */
    private $yumpuApi;

    /**
     * @var \YumpuPlugin\Settings
     */
    private $settings;

    public function __construct(YumpuAPI $api, \YumpuPlugin\Settings $settings)
    {
        $this->yumpuApi = $api;
        $this->settings = $settings;

        add_action('admin_init', [$this, 'yumpuPluginSettingsInit']);

        add_action('admin_menu', function () {
            add_submenu_page(
                "options-general.php",
                __('YUMPU plugin settings', 'yumpu-epaper-publishing'),
                __('YUMPU plugin settings', 'yumpu-epaper-publishing'),
                "manage_options",
                "yumpu-settings",
                [$this, 'yumpuPluginSettingsPage']
            );
        });
    }

    /**
     * @param $api_token
     * @return string
     */
    public function yumpuPluginSettingsSanitize($api_token)
    {
        if (!$this->yumpuApi->checkApiKey($api_token)) {
            add_settings_error(
                'prefix_messages',
                'prefix_message',
                __('Invalid API Token', 'yumpu-epaper-publishing')
            );

            return $this->settings->getApiToken();
        }

        return $api_token;
    }

    /**
     * @return void
     */
    public function yumpuPluginSettingsInit()
    {
        register_setting(
            'yumpu_plugin_settings',
            $this->settings->getApiTokenSettingName(),
            ['sanitize_callback' => [$this, 'yumpuPluginSettingsSanitize']]
        );

        add_settings_section('yumpu_plugin_settings',
            '',
            function () {
            },
            'yumpu_plugin_settings'
        );

        add_settings_field(
            'yumpu_plugin_settings',
            __('API key', 'yumpu-epaper-publishing'),
            function () {
                ?>
                <input
                        class="regular-text"
                        type="text"
                        name="<?php echo esc_attr($this->settings->getApiTokenSettingName()); ?>"
                        value="<?php echo esc_attr($this->settings->getApiToken()); ?>"
                />
                <?php
            },
            'yumpu_plugin_settings',
            'yumpu_plugin_settings'
        );
    }

    /**
     * @return void
     */
    public function yumpuPluginSettingsPage()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('yumpu_plugin_settings');
                do_settings_sections('yumpu_plugin_settings');
                submit_button(__('Save Settings', 'yumpu-epaper-publishing'));
                ?>
            </form>
        </div>
        <?php
    }
}


