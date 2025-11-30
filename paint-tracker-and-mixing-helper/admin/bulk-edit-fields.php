<?php
/**
 * Bulk Edit fields template for Paint Colours.
 *
 * Variables available:
 * - $column_name
 * - $post_type
 */
?>
<fieldset class="inline-edit-col-right">
    <div class="inline-edit-group">
        <label class="alignleft">
            <span class="title"><?php esc_html_e( 'On Shelf?', 'paint-tracker-and-mixing-helper' ); ?></span>
            <span class="input-text-wrap">
                <select name="pct_bulk_on_shelf">
                    <option value=""><?php esc_html_e( '— No change —', 'paint-tracker-and-mixing-helper' ); ?></option>
                    <option value="1"><?php esc_html_e( 'On shelf', 'paint-tracker-and-mixing-helper' ); ?></option>
                    <option value="0"><?php esc_html_e( 'Not on shelf', 'paint-tracker-and-mixing-helper' ); ?></option>
                </select>
            </span>
        </label>
    </div>

    <div class="inline-edit-group">
        <label class="alignleft">
            <span class="title"><?php esc_html_e( 'Shade helper', 'paint-tracker-and-mixing-helper' ); ?></span>
            <span class="input-text-wrap">
                <select name="pct_bulk_exclude_shade">
                    <option value=""><?php esc_html_e( '— No change —', 'paint-tracker-and-mixing-helper' ); ?></option>
                    <option value="1"><?php esc_html_e( 'Exclude from shading', 'paint-tracker-and-mixing-helper' ); ?></option>
                    <option value="0"><?php esc_html_e( 'Include in shading', 'paint-tracker-and-mixing-helper' ); ?></option>
                </select>
            </span>
        </label>
    </div>

    <div class="inline-edit-group">
        <label class="alignleft">
            <span class="title"><?php esc_html_e( 'Base type', 'paint-tracker-and-mixing-helper' ); ?></span>
            <span class="input-text-wrap">
                <select name="pct_bulk_base_type">
                    <option value=""><?php esc_html_e( '— No change —', 'paint-tracker-and-mixing-helper' ); ?></option>
                    <option value="acrylic"><?php esc_html_e( 'Set to Acrylic', 'paint-tracker-and-mixing-helper' ); ?></option>
                    <option value="enamel"><?php esc_html_e( 'Set to Enamel', 'paint-tracker-and-mixing-helper' ); ?></option>
                    <option value="oil"><?php esc_html_e( 'Set to Oil', 'paint-tracker-and-mixing-helper' ); ?></option>
                    <option value="lacquer"><?php esc_html_e( 'Set to Lacquer', 'paint-tracker-and-mixing-helper' ); ?></option>
                </select>
            </span>
        </label>
    </div>
</fieldset>
<?php
wp_nonce_field( 'pct_bulk_edit', 'pct_bulk_edit_nonce' );
