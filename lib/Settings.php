<?php

namespace YumpuPlugin;

class Settings
{
    /**
     * @return string
     */
    public function getApiTokenSettingName()
    {
        return 'YUMPU_API_ACCESS_TOKEN';
    }

    /**
     * @return string
     */
    public function getApiToken()
    {
        return (string)get_option($this->getApiTokenSettingName(), '');
    }
}
