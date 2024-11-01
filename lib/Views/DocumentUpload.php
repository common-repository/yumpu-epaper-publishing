<?php
namespace YumpuPlugin\Views;

class DocumentUpload {

    public function __construct()
    {
        add_action('admin_menu', function () {
            add_submenu_page(
                null,
                esc_html__('Upload document', 'yumpu-epaper-publishing'),
                'null',
                'edit_others_posts',
                'yumpu_upload_document',
                [$this, 'uploadDocument']
            );
        });
    }

    public function uploadDocument() {
        $wp_rest_nonce = wp_create_nonce('wp_rest');

        wp_localize_script('wp-api', 'wpApiSettings', [
            'root' => esc_url_raw(rest_url()),
            'nonce' => $wp_rest_nonce,
        ]);
        wp_enqueue_script('wp-api');
        wp_enqueue_script('jquery');
        ?>
        <div class="wrap">
            <h2><?php esc_html_e('Upload document', 'yumpu-epaper-publishing'); ?></h2>
            <form action="<?php echo esc_attr(rest_url( 'yumpu/v2/upload_document')); ?>" method="post" enctype="multipart/form-data" onsubmit="return uploadFrom(this)">
                <input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo esc_attr($wp_rest_nonce); ?>" />
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><label for="documentFile"><?php esc_html_e('PDF document', 'yumpu-epaper-publishing') ?></label></th>
                            <td><input id="documentFile" type="file" name="document" accept="application/pdf" class="regular-text"/></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="documentTitle"><?php esc_html_e('Title', 'yumpu-epaper-publishing') ?></label></th>
                            <td><input type="text" name="title" id="documentTitle" class="regular-text"/></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="documentDescription"><?php esc_html_e('Description', 'yumpu-epaper-publishing') ?></label></th>
                            <td><textarea name="description" class="regular-text" id="documentDescription"></textarea></td>
                        </tr>
                    </tbody>
                </table>
                <?php submit_button(__('Upload document', 'yumpu-epaper-publishing')); ?>
            </form>
        </div>
        <script>
            function uploadFrom (form) {
                jQuery.ajax({
                    url: form.action,
                    data: new FormData(form),
                    cache: false,
                    processData: false,
                    contentType: false,
                    type: 'POST',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
                    },
                    success: function() {
                        alert("<?php esc_html_e('Document uploaded successfully.', 'yumpu-epaper-publishing'); ?>");
                        setTimeout(function () {
                            window.location.replace("<?php menu_page_url('yumpu_document_manager') ?>");
                        }, 500);
                    },
                    error: function (xhr, error) {
                        console.error(error);
                    }
                });
                return false;
            }
        </script>
        <?php
    }
}