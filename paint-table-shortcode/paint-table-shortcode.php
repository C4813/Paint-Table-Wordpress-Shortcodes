<?php
/**
 * Plugin Name: Paint Table Shortcodes
 * Description: Adds the shortcode [paint_table] to display paint colour tables, configured in the admin panel.
 * Version: 0.1.0
 * Author: C4813
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PCT_Paint_Table_Plugin {

    const CPT = 'paint_color';
    const TAX = 'paint_range';

    public function __construct() {
        add_action( 'init',                    [ $this, 'register_types' ] );
        add_action( 'add_meta_boxes',          [ $this, 'add_meta_boxes' ] );
        add_action( 'save_post_' . self::CPT,  [ $this, 'save_paint_meta' ], 10, 2 );
        add_shortcode( 'paint_table',          [ $this, 'shortcode_paint_table' ] );
        add_action( 'wp_enqueue_scripts',      [ $this, 'enqueue_frontend_assets' ] );

        // Admin: CSV import page
        add_action( 'admin_menu',              [ $this, 'register_import_page' ] );

        // Admin: list table columns & sorting
        add_filter( 'manage_edit-' . self::CPT . '_columns',          [ $this, 'admin_columns' ] );
        add_action( 'manage_' . self::CPT . '_posts_custom_column',   [ $this, 'admin_columns_content' ], 10, 2 );
        add_filter( 'manage_edit-' . self::CPT . '_sortable_columns', [ $this, 'admin_sortable_columns' ] );
        add_action( 'pre_get_posts',                                   [ $this, 'admin_default_sort_by_number' ] );
    }

    /**
     * Register custom post type and taxonomy.
     */
    public function register_types() {

        // Custom post type for individual paints
        register_post_type(
            self::CPT,
            [
                'labels' => [
                    'name'          => __( 'Paint Colours', 'pct' ),
                    'singular_name' => __( 'Paint Colour', 'pct' ),
                    'add_new_item'  => __( 'Add New Paint Colour', 'pct' ),
                    'edit_item'     => __( 'Edit Paint Colour', 'pct' ),
                ],
                'public'       => false,
                'show_ui'      => true,
                'show_in_menu' => true,
                'menu_icon'    => 'dashicons-art',
                'supports'     => [ 'title' ],
            ]
        );

        // Taxonomy for ranges (Vallejo Model Color, etc.)
        register_taxonomy(
            self::TAX,
            self::CPT,
            [
                'labels' => [
                    'name'          => __( 'Paint Ranges', 'pct' ),
                    'singular_name' => __( 'Paint Range', 'pct' ),
                ],
                'public'       => false,
                'show_ui'      => true,
                'hierarchical' => false,
            ]
        );
    }

    /**
     * Add custom columns to Paint Colours admin list.
     */
    public function admin_columns( $columns ) {
        $new = [];

        foreach ( $columns as $key => $label ) {
            $new[ $key ] = $label;

            // Insert our "Number" column right after the title
            if ( 'title' === $key ) {
                $new['pct_number'] = __( 'Number', 'pct' );
            }
        }

        return $new;
    }

    /**
     * Render content for custom columns.
     */
    public function admin_columns_content( $column, $post_id ) {
        if ( 'pct_number' === $column ) {
            $number = get_post_meta( $post_id, '_pct_number', true );
            echo esc_html( $number );
        }
    }

    /**
     * Make columns sortable.
     */
    public function admin_sortable_columns( $columns ) {
        // When user clicks the "Number" header, orderby=pct_number
        $columns['pct_number'] = 'pct_number';
        return $columns;
    }

    /**
     * Default sort by paint number in admin, and support clicking the Number column.
     */
    public function admin_default_sort_by_number( $query ) {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }

        $post_type = $query->get( 'post_type' );
        if ( self::CPT !== $post_type ) {
            return;
        }

        $orderby = $query->get( 'orderby' );

        // If user clicked the "Number" header
        if ( 'pct_number' === $orderby ) {
            $query->set( 'meta_key', '_pct_number' );
            $query->set( 'orderby', 'meta_value' ); // or 'meta_value_num' if you change numbers to pure numeric
            return;
        }

        // Default ordering (no specific orderby set yet)
        if ( empty( $orderby ) ) {
            $query->set( 'meta_key', '_pct_number' );
            $query->set( 'orderby', 'meta_value' );
            $query->set( 'order', 'ASC' );
        }
    }

    /**
     * Add meta box for paint details.
     */
    public function add_meta_boxes() {
        add_meta_box(
            'pct_paint_details',
            __( 'Paint details', 'pct' ),
            [ $this, 'render_paint_meta_box' ],
            self::CPT,
            'normal',
            'default'
        );
    }

    /**
     * Render the meta box HTML (includes multiple links with "add" button).
     */
    public function render_paint_meta_box( $post ) {
        wp_nonce_field( 'pct_save_paint_meta', 'pct_paint_meta_nonce' );

        $number = get_post_meta( $post->ID, '_pct_number', true );
        $hex    = get_post_meta( $post->ID, '_pct_hex', true );

        // New: multiple links stored as array of ['title' => ..., 'url' => ...]
        $links = get_post_meta( $post->ID, '_pct_links', true );

        // Backwards compatibility
        if ( empty( $links ) ) {
            // If we have the old single-link meta, convert it
            $legacy_url = get_post_meta( $post->ID, '_pct_link', true );
            if ( $legacy_url ) {
                $links = [
                    [
                        'title' => '',
                        'url'   => $legacy_url,
                    ],
                ];
            } else {
                $links = [];
            }
        } elseif ( is_array( $links ) && ! empty( $links ) ) {
            // If it's a simple array of URLs (old format), convert each one
            $first = reset( $links );
            if ( is_string( $first ) ) {
                $converted = [];
                foreach ( $links as $url ) {
                    $converted[] = [
                        'title' => '',
                        'url'   => $url,
                    ];
                }
                $links = $converted;
            }
        }

        ?>
        <p>
            <label for="pct_number"><strong><?php esc_html_e( 'Paint number', 'pct' ); ?></strong> (e.g. 70.800)</label><br>
            <input type="text" id="pct_number" name="pct_number" value="<?php echo esc_attr( $number ); ?>" class="regular-text">
        </p>

        <p>
            <label for="pct_hex"><strong><?php esc_html_e( 'Hex colour', 'pct' ); ?></strong> (e.g. #2f353a)</label><br>
            <input type="text" id="pct_hex" name="pct_hex" value="<?php echo esc_attr( $hex ); ?>" class="regular-text">
        </p>

        <p><strong><?php esc_html_e( 'Linked posts / URLs', 'pct' ); ?></strong></p>

        <div id="pct-links-wrapper">
            <?php
            if ( empty( $links ) ) {
                $links = [
                    [
                        'title' => '',
                        'url'   => '',
                    ],
                ];
            }

            foreach ( $links as $link ) :
                $ltitle = isset( $link['title'] ) ? $link['title'] : '';
                $lurl   = isset( $link['url'] ) ? $link['url'] : '';
                ?>
                <div class="pct-link-row" style="margin-bottom:8px; padding:6px 8px; border:1px solid #ccd0d4; border-radius:4px; background:#f8f9fa;">
                    <p style="margin:4px 0;">
                        <label>
                            <?php esc_html_e( 'Link title', 'pct' ); ?><br>
                            <input type="text"
                                   name="pct_links_title[]"
                                   value="<?php echo esc_attr( $ltitle ); ?>"
                                   class="regular-text"
                                   placeholder="<?php esc_attr_e( 'e.g. Tutorial, Review, Example Build', 'pct' ); ?>">
                        </label>
                    </p>
                    <p style="margin:4px 0;">
                        <label>
                            <?php esc_html_e( 'Link URL', 'pct' ); ?><br>
                            <input type="url"
                                   name="pct_links_url[]"
                                   value="<?php echo esc_attr( $lurl ); ?>"
                                   class="regular-text"
                                   placeholder="https://example.com/my-article">
                        </label>
                    </p>
                    <p style="margin:4px 0;">
                        <button type="button" class="button pct-remove-link">
                            <?php esc_html_e( 'Remove link', 'pct' ); ?>
                        </button>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>

        <p>
            <button type="button" class="button button-secondary" id="pct-add-link">
                <?php esc_html_e( 'Add another link', 'pct' ); ?>
            </button>
        </p>

        <script>
            (function($){
                $(function(){
                    var $wrapper = $('#pct-links-wrapper');

                    $('#pct-add-link').on('click', function(e){
                        e.preventDefault();
                        var $row = $('<div class="pct-link-row" style="margin-bottom:8px; padding:6px 8px; border:1px solid #ccd0d4; border-radius:4px; background:#f8f9fa;">'
                            + '<p style="margin:4px 0;">'
                            + '<label><?php echo esc_js( __( 'Link title', 'pct' ) ); ?><br>'
                            + '<input type="text" name="pct_links_title[]" value="" class="regular-text" placeholder="<?php echo esc_js( __( 'e.g. Tutorial, Review, Example Build', 'pct' ) ); ?>">'
                            + '</label></p>'
                            + '<p style="margin:4px 0;">'
                            + '<label><?php echo esc_js( __( 'Link URL', 'pct' ) ); ?><br>'
                            + '<input type="url" name="pct_links_url[]" value="" class="regular-text" placeholder="https://example.com/my-article">'
                            + '</label></p>'
                            + '<p style="margin:4px 0;">'
                            + '<button type="button" class="button pct-remove-link"><?php echo esc_js( __( 'Remove link', 'pct' ) ); ?></button>'
                            + '</p>'
                            + '</div>');
                        $wrapper.append($row);
                    });

                    $wrapper.on('click', '.pct-remove-link', function(e){
                        e.preventDefault();
                        var rows = $wrapper.find('.pct-link-row');
                        if (rows.length > 1) {
                            $(this).closest('.pct-link-row').remove();
                        } else {
                            // Keep at least one row and just clear it
                            $(this).closest('.pct-link-row').find('input').val('');
                        }
                    });
                });
            })(jQuery);
        </script>
        <?php
    }

    /**
     * Save meta box fields (including multiple links).
     */
    public function save_paint_meta( $post_id, $post ) {

        if ( ! isset( $_POST['pct_paint_meta_nonce'] ) ||
             ! wp_verify_nonce( $_POST['pct_paint_meta_nonce'], 'pct_save_paint_meta' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $number = isset( $_POST['pct_number'] ) ? sanitize_text_field( wp_unslash( $_POST['pct_number'] ) ) : '';
        $hex    = isset( $_POST['pct_hex'] ) ? sanitize_text_field( wp_unslash( $_POST['pct_hex'] ) ) : '';

        update_post_meta( $post_id, '_pct_number', $number );
        update_post_meta( $post_id, '_pct_hex', $hex );

        // Multiple links with titles
        $titles = isset( $_POST['pct_links_title'] ) && is_array( $_POST['pct_links_title'] )
            ? array_map( 'wp_unslash', $_POST['pct_links_title'] )
            : [];

        $urls   = isset( $_POST['pct_links_url'] ) && is_array( $_POST['pct_links_url'] )
            ? array_map( 'wp_unslash', $_POST['pct_links_url'] )
            : [];

        $links = [];
        $count = max( count( $titles ), count( $urls ) );

        for ( $i = 0; $i < $count; $i++ ) {
            $title = isset( $titles[ $i ] ) ? trim( $titles[ $i ] ) : '';
            $url   = isset( $urls[ $i ] ) ? trim( $urls[ $i ] )   : '';

            if ( '' === $url ) {
                continue; // skip empty rows
            }

            $links[] = [
                'title' => sanitize_text_field( $title ),
                'url'   => esc_url_raw( $url ),
            ];
        }

        if ( ! empty( $links ) ) {
            update_post_meta( $post_id, '_pct_links', $links );
            // keep first URL as legacy single-link meta
            update_post_meta( $post_id, '_pct_link', $links[0]['url'] );
        } else {
            delete_post_meta( $post_id, '_pct_links' );
            delete_post_meta( $post_id, '_pct_link' );
        }
    }

    /**
     * Simple modern table styling injected via inline CSS.
     */
    public function enqueue_frontend_assets() {
        wp_register_style( 'pct_paint_table', false );
        wp_enqueue_style( 'pct_paint_table' );

        $css = '
        .pct-table-wrapper {
            overflow-x: auto;
            margin: 1.5rem 0;
        }
        table.pct-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        table.pct-table thead {
            background: #111827;
            color: #f9fafb;
        }
        table.pct-table th,
        table.pct-table td {
            padding: 0.75rem 0.9rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        table.pct-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        table.pct-table tbody tr:hover {
            background: #e5f2ff;
        }
        .pct-swatch {
            display: inline-block;
            width: 1.8rem;
            height: 1.8rem;
            border-radius: 999px;
            border: 1px solid rgba(0,0,0,.12);
            box-shadow: 0 0 0 1px rgba(255,255,255,.45) inset;
            margin-right: 0.6rem;
            vertical-align: middle;
        }
        .pct-name {
            font-weight: 500;
        }
        .pct-number {
            font-family: Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 0.85em;
        }
        .pct-link a {
            text-decoration: none;
            border-bottom: 1px solid rgba(59,130,246,.4);
        }
        .pct-link a:hover {
            border-bottom-color: rgba(59,130,246,.9);
        }
        @media (max-width: 640px) {
            table.pct-table th:nth-child(3),
            table.pct-table td:nth-child(3) {
                display: none;
            }
        }';

        wp_add_inline_style( 'pct_paint_table', $css );
    }

    /**
     * Shortcode handler: [paint_table range="vallejo-model-color" limit="-1" orderby="meta_number|title"]
     */
    public function shortcode_paint_table( $atts ) {
        $atts = shortcode_atts(
            [
                'range'   => 'vallejo-model-color', // taxonomy slug
                'limit'   => -1,
                'orderby' => 'meta_number',         // or "title"
            ],
            $atts,
            'paint_table'
        );

        $meta_key = '_pct_number';
        $orderby  = ( $atts['orderby'] === 'title' ) ? 'title' : 'meta_value';

        $args = [
            'post_type'      => self::CPT,
            'posts_per_page' => intval( $atts['limit'] ),
            'post_status'    => 'publish',
            'orderby'        => $orderby,
            'order'          => 'ASC',
        ];

        if ( $orderby === 'meta_value' ) {
            $args['meta_key'] = $meta_key;
        }

        if ( ! empty( $atts['range'] ) ) {
            $args['tax_query'] = [
                [
                    'taxonomy' => self::TAX,
                    'field'    => 'slug',
                    'terms'    => sanitize_title( $atts['range'] ),
                ],
            ];
        }

        $q = new WP_Query( $args );

        if ( ! $q->have_posts() ) {
            return '<p>No paints found.</p>';
        }

        ob_start();
        ?>
        <div class="pct-table-wrapper">
            <table class="pct-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Colour', 'pct' ); ?></th>
                        <th><?php esc_html_e( 'Number', 'pct' ); ?></th>
                        <th><?php esc_html_e( 'Range', 'pct' ); ?></th>
                        <th><?php esc_html_e( 'Guides', 'pct' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                while ( $q->have_posts() ) :
                    $q->the_post();
                    $id     = get_the_ID();
                    $name   = get_the_title();
                    $number = get_post_meta( $id, '_pct_number', true );
                    $hex    = get_post_meta( $id, '_pct_hex', true );

                    // Multiple links with titles (backwards compatible)
                    $links = get_post_meta( $id, '_pct_links', true );

                    // If no structured links, fall back to legacy single URL
                    if ( empty( $links ) ) {
                        $single = get_post_meta( $id, '_pct_link', true );
                        if ( $single ) {
                            $links = [
                                [
                                    'title' => '',
                                    'url'   => $single,
                                ],
                            ];
                        } else {
                            $links = [];
                        }
                    } elseif ( is_array( $links ) ) {
                        // If it’s an array of plain URLs, normalise
                        $first = reset( $links );
                        if ( is_string( $first ) ) {
                            $normalised = [];
                            foreach ( $links as $url ) {
                                $normalised[] = [
                                    'title' => '',
                                    'url'   => $url,
                                ];
                            }
                            $links = $normalised;
                        }
                    }

                    $terms       = get_the_terms( $id, self::TAX );
                    $range_label = ( $terms && ! is_wp_error( $terms ) ) ? esc_html( $terms[0]->name ) : '';
                    ?>
                    <tr>
                        <td>
                            <?php if ( $hex ) : ?>
                                <span class="pct-swatch" style="background-color: <?php echo esc_attr( $hex ); ?>"></span>
                            <?php endif; ?>
                            <span class="pct-name"><?php echo esc_html( $name ); ?></span>
                        </td>
                        <td class="pct-number"><?php echo esc_html( $number ); ?></td>
                        <td><?php echo $range_label; ?></td>
                        <td class="pct-link">
                            <?php if ( ! empty( $links ) ) : ?>
                                <?php foreach ( $links as $i => $link ) :
                                    $url    = isset( $link['url'] ) ? $link['url'] : '';
                                    $ltitle = isset( $link['title'] ) ? $link['title'] : '';
                                    if ( ! $url ) {
                                        continue;
                                    }

                                    // Fallback title if none set
                                    if ( '' === $ltitle ) {
                                        $ltitle = ( count( $links ) > 1 )
                                            ? sprintf( __( 'View %d', 'pct' ), $i + 1 )
                                            : __( 'View', 'pct' );
                                    }
                                    ?>
                                    <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener">
                                        <?php echo esc_html( $ltitle ); ?>
                                    </a>
                                    <?php if ( $i < count( $links ) - 1 ) : ?>
                                        <br>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else : ?>
                                &mdash;
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
                endwhile;
                wp_reset_postdata();
                ?>
                </tbody>
            </table>
        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Register "Import from CSV" submenu.
     */
    public function register_import_page() {
        add_submenu_page(
            'edit.php?post_type=' . self::CPT,
            __( 'Import Paints from CSV', 'pct' ),
            __( 'Import from CSV', 'pct' ),
            'manage_options',
            'pct-import-paints',
            [ $this, 'render_import_page' ]
        );
    }

    /**
     * Render the CSV import page + handle form submission.
     *
     * This assumes a simple 3-column CSV:
     *  - Column 1: Paint name
     *  - Column 2: Paint number
     *  - Column 3: Hex colour (e.g. #2f353a)
     */
    public function render_import_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'pct' ) );
        }

        $message = '';
        $errors  = [];

        if ( isset( $_POST['pct_import_submit'] ) ) {
            check_admin_referer( 'pct_import_paints', 'pct_import_nonce' );

            $range_id = isset( $_POST['pct_range'] ) ? intval( $_POST['pct_range'] ) : 0;
            if ( ! $range_id ) {
                $errors[] = __( 'Please choose a paint range.', 'pct' );
            }

            if ( empty( $_FILES['pct_csv']['tmp_name'] ) ) {
                $errors[] = __( 'Please upload a CSV file.', 'pct' );
            }

            if ( empty( $errors ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';

                $uploaded_file = wp_handle_upload(
                    $_FILES['pct_csv'],
                    [ 'test_form' => false ]
                );

                if ( isset( $uploaded_file['error'] ) ) {
                    $errors[] = $uploaded_file['error'];
                } else {
                    $file_path = $uploaded_file['file'];

                    $created = 0;
                    $handle  = fopen( $file_path, 'r' );

                    if ( $handle ) {
                        $row = 0;
                        while ( ( $data = fgetcsv( $handle, 0, ',' ) ) !== false ) {
                            $row++;

                            // Skip header row
                            if ( 1 === $row ) {
                                continue;
                            }

                            // Expecting at least 3 columns
                            if ( count( $data ) < 3 ) {
                                continue;
                            }

                            $name   = trim( $data[0] );
                            $number = trim( $data[1] );
                            $hex    = trim( $data[2] );

                            if ( '' === $name ) {
                                continue;
                            }

                            $post_id = wp_insert_post(
                                [
                                    'post_type'   => self::CPT,
                                    'post_status' => 'publish',
                                    'post_title'  => $name,
                                ]
                            );

                            if ( ! is_wp_error( $post_id ) && $post_id ) {
                                update_post_meta( $post_id, '_pct_number', $number );
                                update_post_meta( $post_id, '_pct_hex', $hex );
                                wp_set_object_terms( $post_id, $range_id, self::TAX );
                                $created++;
                            }
                        }
                        fclose( $handle );

                        $message = sprintf(
                            /* translators: 1: count of paints imported */
                            _n( 'Imported %d paint.', 'Imported %d paints.', $created, 'pct' ),
                            $created
                        );
                    } else {
                        $errors[] = __( 'Unable to open the uploaded CSV file.', 'pct' );
                    }
                }
            }
        }

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Import Paints from CSV', 'pct' ); ?></h1>

            <?php if ( ! empty( $errors ) ) : ?>
                <div class="notice notice-error">
                    <p><?php echo implode( '<br>', array_map( 'esc_html', $errors ) ); ?></p>
                </div>
            <?php endif; ?>

            <?php if ( $message ) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html( $message ); ?></p>
                </div>
            <?php endif; ?>

            <p>
                <?php esc_html_e( 'Upload a CSV file to automatically create paints in a specific range.', 'pct' ); ?>
            </p>
            <p>
                <?php esc_html_e( 'Expected format (per row): name, number, hex colour.', 'pct' ); ?>
            </p>

            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field( 'pct_import_paints', 'pct_import_nonce' ); ?>

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="pct_range"><?php esc_html_e( 'Paint range', 'pct' ); ?></label>
                        </th>
                        <td>
                            <?php
                            wp_dropdown_categories(
                                [
                                    'taxonomy'         => self::TAX,
                                    'name'             => 'pct_range',
                                    'id'               => 'pct_range',
                                    'hide_empty'       => false,
                                    'show_option_none' => __( 'Select a range', 'pct' ),
                                ]
                            );
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="pct_csv"><?php esc_html_e( 'CSV file', 'pct' ); ?></label>
                        </th>
                        <td>
                            <input type="file" name="pct_csv" id="pct_csv" accept=".csv">
                        </td>
                    </tr>
                </table>

                <?php submit_button( __( 'Import paints', 'pct' ), 'primary', 'pct_import_submit' ); ?>
            </form>
        </div>
        <?php
    }
}

new PCT_Paint_Table_Plugin();
