<?php

namespace YumpuPlugin;

class Shortcode
{
    /**
     * @var YumpuAPI
     */
    private $yumpuApi;

    public function __construct(YumpuAPI $api)
    {
        $this->yumpuApi = $api;

        add_shortcode("YUMPU", [$this, 'yumpuShortcode']);
    }

    /**
     * @param array $attributes
     * @param string $content
     * @return string
     */
    public function yumpuShortcode($attributes, $content = null)
    {
        if (isset($attributes['embed_id']) && strlen($attributes['embed_id'] > 1)) {
            return $this->iframeEmbed($attributes['embed_id'], $attributes);
        }

        if (!isset($attributes['epaper_id'])) {
            return $this->errorTemplate($content, $attributes, esc_html__('Misconfigured shortcode', 'yumpu-epaper-publishing'));
        }

        try {
            $document = $this->yumpuApi->getDocument((int)$attributes['epaper_id']);

            if (isset($document->status) && $document->status === 'progress') {
                return $this->errorTemplate($content, $attributes, esc_html__('E-Paper in progress', 'yumpu-epaper-publishing'));
            }

            $content .= $document->embed_code;

            return $this->prepareEmbedView($content, $attributes);

        } catch (YumpuAPIException $e) {
            return $this->errorTemplate($content, $attributes, esc_html__('E-Paper not found', 'yumpu-epaper-publishing'));
        }
    }

    /**
     * @param string $content
     * @param array $attrs
     * @param string $message
     * @return string
     */
    private function errorTemplate($content, $attrs, $message)
    {
        $content .= '<div style="position:relative;width:' . $this->width($attrs) . 'px;height:' . $this->height($attrs) . 'px; background:#233039;margin-bottom:10px;"><p style="text-align:center;padding-top:' . (($this->height($attrs) / 2) - 30) . 'px;color:#ffffff;font-weight:normal;font-size:1.5em;">' . $message . '</p>';
        $content .= '<a class="yumpuLink" target="YUMPU" href="https://www.yumpu.com/"><img alt="YUMPU" style="width:40px;bottom:10px;right:10px;position:absolute;" src="' . plugins_url('assets/images/yumpu_logo_light.svg', plugin_dir_path(__FILE__)) . '"></a>';
        $content .= '</div>';

        return $content;
    }

    /**
     * @param string $content
     * @return string
     */
    private function prepareEmbedView($content, $attrs)
    {
        $replacements = [
            '/width:(.*?)px/i'    => "width:{$this->width($attrs)}px",
            '/height:(.*?)px/i'   => "height:{$this->height($attrs)}px",
            '/width="(.*?)px"/i'  => "width=\"{$this->width($attrs)}px\"",
            '/height="(.*?)px"/i' => "height=\"{$this->height($attrs)}px\"",
        ];

        return preg_replace(array_keys($replacements), array_values($replacements), $content);
    }

    private function iframeEmbed($embed_id, $attrs)
    {
        return sprintf(
            '<iframe width="%dpx" height="%dpx" src="https://www.yumpu.com/xx/embed/view/%s" allowfullscreen="allowfullscreen"  allowtransparency="true"></iframe>',
            $this->width($attrs),
            $this->height($attrs),
            $embed_id
        );
    }

    /**
     * @param array{width?: integer} $attrs
     * @return integer
     */
    private function width($attrs)
    {
        if (!isset($attrs['width']) || !is_numeric($attrs['width']) || $attrs['width'] < 0) {
            return 512;
        }

        return (int)$attrs['width'];
    }

    /**
     * @param array{height?: integer} $attrs
     * @return integer
     */
    private function height($attrs)
    {
        if (!isset($attrs['height']) || !is_numeric($attrs['height']) || $attrs['height'] < 0) {
            return 512;
        }

        return (int)$attrs['height'];
    }
}