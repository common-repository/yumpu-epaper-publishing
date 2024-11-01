<?php

namespace YumpuPlugin;

class YumpuAPI {
    const API_URI = "https://api.yumpu.com/2.0/";

    /**
     * @var string
     */
    private $apiToken;

    /**
     * @param string $apiToken
     */
    public function __construct($apiToken) {
        $this->apiToken = $apiToken;
    }

    /**
     * @param array{tmp_name: string} $file
     * @param string $title
     * @param string $description
     * @return void
     * @throws YumpuAPIException
     */
    public function uploadDocument($file, $title, $description) {
        $data = [
            'file' => $file['tmp_name'],
            'title' => $title,
            'description' => $description
        ];

        $result = $this->remotePostFile("document/file.json", $data);

        if (!is_object($result) || $result->state != "success") {
            throw new YumpuAPIException(esc_html($result->state));
        }
    }

    /**
     * @param integer $offset
     * @param integer $limit
     * @param 'desc'|'asc' $sort
     * @return array
     */
    public function getDocuments($offset = 0, $limit = 0, $sort = 'desc')
    {
        $get_query_params = [
            'offset' => $offset,
            'limit' => $limit,
            'sort' => $sort,
            'return_fields' => 'id,create_date,update_date,url,short_url,image_small,image_medium,image_big,language,title,description,tags,embed_code,settings',
        ];

        $result = $this->remote_get('documents.json', $get_query_params);

        if (!is_object($result) || $result->state != "success") {
            return [];
        }

        return $result->documents;
    }

    /**
     * @param integer $id
     * @return object
     * @throws YumpuAPIException
     */
    public function getDocument($id)
    {
        $result = $this->remote_get('document.json', ['id' => $id]);
        if (!is_object($result) || $result->state !== 'success' || !isset($result->document[0])) {
            throw new YumpuAPIException('API-Error');
        }

        return $result->document[0];
    }

    /**
     * @param string $path
     * @param array $data
     * @return mixed
     */
    private function remotePostFile($path, $data) {
        global $wp_filesystem;
        require_once ( ABSPATH . '/wp-admin/includes/file.php' );
        WP_Filesystem();

        $boundary= '--------------------------'.microtime(true);
        $content =  "--".$boundary."\r\n".
            "Content-Disposition: form-data; name=\"file\"; filename=\"test.pdf\"\r\n".
            "Content-Type: application/zip\r\n\r\n".
            $wp_filesystem->get_contents($data['file'])."\r\n";
        $content .= "--".$boundary."\r\n".
            "Content-Disposition: form-data; name=\"title\"\r\n\r\n".
            $data['title'] ."\r\n";
        $content .= "--".$boundary."\r\n".
            "Content-Disposition: form-data; name=\"description\"\r\n\r\n".
            $data['description'] ."\r\n";

        $options = [
            'body'    => $content,
            'timeout'     => 4,
            'redirection' => 3,
            'headers'   => [
                'X-ACCESS-TOKEN' => $this->apiToken,
                'Content-Type'=> "multipart/form-data; boundary=$boundary"
            ],
        ];

        $response = wp_remote_post( self::API_URI . $path, $options);

        return json_decode(wp_remote_retrieve_body($response));
    }

    /**
     * @param string $path
     * @param array $get_query_params
     * @return mixed
     */
    private function remote_get($path, $get_query_params = []) {
        $options = [
            'timeout'     => 4,
            'redirection' => 3,
            'headers'   => [
                'X-ACCESS-TOKEN' => $this->apiToken,
                'content-type' => 'application/json',
            ],
        ];

        $url = self::API_URI . $path . '?' . http_build_query($get_query_params);
        $response = wp_remote_get($url, $options);

        return json_decode(wp_remote_retrieve_body($response));
    }

    /**
     * @param string $api_key
     * @return bool
     */
    public function checkApiKey($api_key) {
        $options = [
            'timeout'     => 4,
            'redirection' => 3,
            'headers'   => [
                'X-ACCESS-TOKEN' => $api_key,
                'content-type' => 'application/json',
            ],
        ];

        $response = wp_remote_get('https://api.yumpu.com/2.0/user.json', $options);

        $response_body = json_decode(wp_remote_retrieve_body($response));

        return is_object($response_body) && $response_body->state === 'success';
    }
}
