<?php
namespace YumpuPlugin\Views;

class DocumentList {
    private $pluginFile;

    public function __construct($pluginFile)
    {
        $this->pluginFile = $pluginFile;

        add_action('admin_menu', function () {
            add_menu_page(
                "YUMPU",
                "YUMPU",
                "edit_others_posts",
                "yumpu_document_manager",
                [$this, 'renderDocumentList'],
                'data:image/svg+xml;base64,PHN2ZyB4bWw6c3BhY2U9InByZXNlcnZlIiB3aWR0aD0iNzIuMTA1IiBoZWlnaHQ9IjY2LjgiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZmlsbD0iIzA3MDcwNyIgZD0iTTMwLjQ1MiA2Ni44Yy0zLjEgMC0zLjktMi45LTMuOS00LjRWNDQuNUwuNjUyIDYuM2MtLjgtMS4zLS45LTMtLjEtNC40LjctMS4yIDEuOC0xLjggMy4xLTEuOGgxMS42YzEuNSAwIDIuNS43IDMuOSAyLjVsMTYuOSAyNS4xIDE3LTI1LjJjMS4zLTEuOCAyLjQtMi41IDMuOC0yLjVoMTEuNmMxLjMgMCAyLjUuNyAzLjEgMS44LjggMS40LjcgMy0uMSA0LjRsLTI1LjkgMzguM3YxNy45YzAgMi44LTEuNSA0LjQtMy45IDQuNHoiLz48L3N2Zz4=',
                21
            );
        });


    }

    public function renderDocumentList() {
        wp_localize_script('wp-api', 'wpApiSettings', [
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
        wp_enqueue_script('wp-api');
        wp_enqueue_style('datatables-css', plugins_url('assets/css/datatables.min.css', $this->pluginFile), [], '2.0.8');
        wp_enqueue_style('datatables-additional-css', plugins_url('assets/css/datatables-additional.css', $this->pluginFile), [], '2.0.8');
        wp_enqueue_script('datatables-js', plugins_url('assets/js/datatables.min.js',  $this->pluginFile), ['jquery'], '2.0.8', ['in_footer' => true]);
        ?>
        <div class="wrap">
            <h2><?php esc_html_e( 'E-Paper powered by', 'yumpu-epaper-publishing'); ?> <a href="https://www.yumpu.com" target="_blank">Yumpu.com</a></h2>
            <a class="button-primary" href="<?php menu_page_url('yumpu_upload_document') ?>"><?php esc_html_e('Add New', 'yumpu-epaper-publishing'); ?></a>
            <div class="wrap">
                <table id="yumpuDocumentList" class="display">
                    <thead>
                        <tr>
                            <th></th>
                            <th><?php esc_html_e('Title', 'yumpu-epaper-publishing'); ?></th>
                            <th><?php esc_html_e('Shortcode', 'yumpu-epaper-publishing'); ?></th>
                            <th><?php esc_html_e('Visibility', 'yumpu-epaper-publishing'); ?></th>
                            <th><?php esc_html_e('Created', 'yumpu-epaper-publishing'); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <script>
            $ = jQuery.noConflict();
            $(document).ready( function () {
                $('#yumpuDocumentList').DataTable({
                    ajax: {
                        url: wpApiSettings.root + 'yumpu/v2/documents',
                        dataSrc: "documents",
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        beforeSend: function ( xhr ) {
                            xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
                        },
                        error: function (xhr, error) {}
                    },
                    sorting: [[5, "desc"]],
                    autoWidth: false,
                    retrieve: true,
                    lengthMenu: [
                        [10, 25, 50, -1],
                        [10, 25, 50, "All"]
                    ],
                    language: {
                        lengthMenu: "_MENU_ <?php esc_html_e( 'records per page', 'yumpu-epaper-publishing'); ?>",
                        loadingRecords: '<div style="text-align:center;"><img width="150" alt="" src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMDAgMjAwIj48Y2lyY2xlIGZpbGw9IiM0RjRGNEYiIHN0cm9rZT0iIzRGNEY0RiIgc3Ryb2tlLXdpZHRoPSIxNSIgcj0iMTUiIGN4PSI0MCIgY3k9IjEwMCI+PGFuaW1hdGUgYXR0cmlidXRlTmFtZT0ib3BhY2l0eSIgY2FsY01vZGU9InNwbGluZSIgZHVyPSIyIiB2YWx1ZXM9IjE7MDsxOyIga2V5U3BsaW5lcz0iLjUgMCAuNSAxOy41IDAgLjUgMSIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiIGJlZ2luPSItLjQiPjwvYW5pbWF0ZT48L2NpcmNsZT48Y2lyY2xlIGZpbGw9IiM0RjRGNEYiIHN0cm9rZT0iIzRGNEY0RiIgc3Ryb2tlLXdpZHRoPSIxNSIgcj0iMTUiIGN4PSIxMDAiIGN5PSIxMDAiPjxhbmltYXRlIGF0dHJpYnV0ZU5hbWU9Im9wYWNpdHkiIGNhbGNNb2RlPSJzcGxpbmUiIGR1cj0iMiIgdmFsdWVzPSIxOzA7MTsiIGtleVNwbGluZXM9Ii41IDAgLjUgMTsuNSAwIC41IDEiIHJlcGVhdENvdW50PSJpbmRlZmluaXRlIiBiZWdpbj0iLS4yIj48L2FuaW1hdGU+PC9jaXJjbGU+PGNpcmNsZSBmaWxsPSIjNEY0RjRGIiBzdHJva2U9IiM0RjRGNEYiIHN0cm9rZS13aWR0aD0iMTUiIHI9IjE1IiBjeD0iMTYwIiBjeT0iMTAwIj48YW5pbWF0ZSBhdHRyaWJ1dGVOYW1lPSJvcGFjaXR5IiBjYWxjTW9kZT0ic3BsaW5lIiBkdXI9IjIiIHZhbHVlcz0iMTswOzE7IiBrZXlTcGxpbmVzPSIuNSAwIC41IDE7LjUgMCAuNSAxIiByZXBlYXRDb3VudD0iaW5kZWZpbml0ZSIgYmVnaW49IjAiPjwvYW5pbWF0ZT48L2NpcmNsZT48L3N2Zz4="/></div>',
                        search: "<?php esc_html_e('Search', 'yumpu-epaper-publishing'); ?>",
                        zeroRecords: '<div style="text-align: center"><?php esc_html_e('No E-Paper to show.', 'yumpu-epaper-publishing'); ?></div>'
                    },
                    columns: [
                        { data: "Cover" },
                        { data: "Title" },
                        { data: "Shortcode" },
                        { data: "Visibility" },
                        { data: "Created" },
                        { data: "DocumentId" }
                    ],
                    rowCallback: function (nRow, aData) {
                        $('td:eq(0)', nRow).html('<a href="' + aData['url'] + '" target="_blank"><img src="' + aData['Cover'] + '" alt="' + aData['Title'] + '" height="42" width="32"></a>').css('text-align', 'center');
                        $('td:eq(1)', nRow).html(aData['Title']);
                        $('td:eq(2)', nRow).html('<div class="input-group"><input type="text" style="width: 400px; background-color:white" class="form-control" value="' + aData['Shortcode'] + '" readonly"></div>');
                        $('td:eq(3)', nRow).html(aData['Visibility']).css('text-align', 'center');
                        $('td:eq(4)', nRow).html(aData['Created']).css('text-align', 'center');
                        $('td:eq(5)', nRow).html('<a class="button-primary" href="https://www.yumpu.com/' + aData['language'] + '/account/magazines/edit/' + aData['DocumentId'] + '" title="<?php esc_html_e( 'Edit', 'yumpu-epaper-publishing'); ?> - ' + aData['Title'] + '" target="_blank"><?php esc_html_e( 'Edit', 'yumpu-epaper-publishing'); ?></a>').css('text-align', 'center');
                        return nRow;
                    }
                });
            });
        </script>
        <?php
    }
}