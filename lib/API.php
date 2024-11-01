<?php

namespace YumpuPlugin;

use WP_REST_Server;
use WP_Error;
use WP_HTTP_Response;

class API {
    /**
     * @var YumpuAPI
     */
    private $yumpuApi;
    private $dateFormat;

    public function __construct(YumpuAPI $api)
    {
        $this->yumpuApi = $api;
        $this->dateFormat = get_option('date_format');

        add_action( 'rest_api_init', [$this, 'register_api_routes']);
    }

    /**
     * @return WP_Error|WP_HTTP_Response
     */
    public function uploadDocument()
    {
        try {
            if ( ! wp_verify_nonce($_POST['_wpnonce'], 'wp_rest') ) {
                return new WP_Error('YUMPU API Error',  __( 'Security check', 'yumpu-epaper-publishing'), ['status' => 500]);
            }
            $this->yumpuApi->uploadDocument($_FILES['document'], $_POST['title'], $_POST['description']);
            return rest_ensure_response(['status' => 'ok']);
        } catch (YumpuAPIException $e) {
            return new WP_Error('YUMPU API Error', $e->getMessage(), ['status' => 500]);
        }
    }

    /**
     * @return WP_Error|WP_HTTP_Response
     */
    public function getDocuments() {
        $documents = $this->yumpuApi->getDocuments(0, 100);

        $data = [];
        foreach($documents as $document) {
            $data[] = $this->formatDocumentToTableData($document);
        }

        return rest_ensure_response(['documents' => $data]);
    }

    /**
     * @return void
     */
    public function register_api_routes() {
        register_rest_route('yumpu/v2', 'documents', [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [$this, 'getDocuments'],
                'permission_callback' => [$this, 'checkEditorAccess'],
        ]);

        register_rest_route('yumpu/v2', 'document/(?P<id>\d+)',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [$this, 'getDocument'],
                'permission_callback' => [$this, 'checkEditorAccess'],
                'args' => [
                    'id' => [
                        'required' => true,
                    ],
                ],
            ]
        );

        register_rest_route('yumpu/v2', 'upload_document',
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'uploadDocument'],
                'permission_callback' => [$this, 'checkEditorAccess'],
            ]
        );
    }

    /**
     * @return true|WP_Error
     */
    public function checkEditorAccess()
    {
        if(!current_user_can('edit_posts')) {
            return new WP_Error('rest_forbidden', __('Access denied.', 'yumpu-epaper-publishing'), ['status' => 401]);
        }

        return true;
    }

    /**
     * @param object $document
     * @return array
     */
    private function formatDocumentToTableData($document) {
        return [
            'Cover' => $document->image->small,
            'Title' => $document->title,
            'Shortcode' => '[YUMPU epaper_id=&quot;' . $document->id . '&quot; width=&quot;512&quot; height=&quot;384&quot;]',
            'Visibility' => $document->settings->privacy_mode,
            'Created' => date_i18n($this->dateFormat, strtotime($document->create_date)),
            'DocumentId' => $document->id,
            'url' => $document->url,
            'language' => $document->language,
        ];
    }
}