<?php
/**
 * Frontend template for [shade-helper].
 *
 * Expects:
 * - $pct_ranges            : array of WP_Term (paint ranges)
 * - $pct_paints            : array of paints: [ 'id', 'name', 'number', 'hex', 'range_id' ]
 * - $pct_default_shade_hex : string (optional hex from URL)
 * - $pct_default_shade_id  : int    (optional paint ID from URL)
 */

if ( ! isset( $pct_ranges, $pct_paints ) || empty( $pct_ranges ) || empty( $pct_paints ) ) {
    return;
}

$default_shade_hex = isset( $pct_default_shade_hex ) ? $pct_default_shade_hex : '';
$default_shade_id  = isset( $pct_default_shade_id ) ? (int) $pct_default_shade_id : 0;

/**
 * Build a parent → children map for ranges, preserving the order
 * coming from get_terms() (which is already ordered by term_order).
 */
$pct_ranges_by_parent = [];
foreach ( $pct_ranges as $range ) {
    $parent_id = (int) $range->parent;

    if ( ! isset( $pct_ranges_by_parent[ $parent_id ] ) ) {
        $pct_ranges_by_parent[ $parent_id ] = [];
    }

    $pct_ranges_by_parent[ $parent_id ][] = $range;
}

/**
 * Recursive renderer for hierarchical range options.
 *
 * Guarded with function_exists so it can coexist with the version
 * defined in mixing-helper.php.
 */
if ( ! function_exists( 'pct_render_range_options_hierarchical' ) ) {
    function pct_render_range_options_hierarchical( $parent_id, $map, $depth = 0 ) {
        if ( empty( $map[ $parent_id ] ) ) {
            return;
        }

        foreach ( $map[ $parent_id ] as $term ) {
            $indent = str_repeat( '— ', max( 0, (int) $depth ) );
            ?>
            <div class="pct-mix-range-option"
                 data-range="<?php echo esc_attr( $term->term_id ); ?>">
                <span class="pct-mix-option-label">
                    <?php echo esc_html( $indent . $term->name ); ?>
                </span>
            </div>
            <?php
            pct_render_range_options_hierarchical( (int) $term->term_id, $map, $depth + 1 );
        }
    }
}
?>

<!-- ========== SHADE RANGE HELPER (SEPARATE TOOL) ========== -->
<div class="pct-shade-container"
     data-default-shade-hex="<?php echo esc_attr( $default_shade_hex ); ?>"
     data-default-shade-id="<?php echo esc_attr( $default_shade_id ); ?>">
    <div class="pct-shade-helper">
        <div class="pct-shade-header">
            <?php esc_html_e( 'Shade helper', 'paint-tracker-and-mixing-helper' ); ?>
        </div>

        <div class="pct-shade-controls">
            <div class="pct-mix-column pct-mix-column-shade">
                <!-- Range dropdown -->
                <div class="pct-mix-field">
                    <label>
                        <?php esc_html_e( 'Range', 'paint-tracker-and-mixing-helper' ); ?><br>
                        <div class="pct-mix-range-dropdown pct-mix-range-dropdown-shade">
                            <button type="button" class="pct-mix-trigger">
                                <span class="pct-mix-trigger-label">
                                    <?php esc_html_e( 'All', 'paint-tracker-and-mixing-helper' ); ?>
                                </span>
                                <span class="pct-mix-trigger-caret">&#9662;</span>
                            </button>
                            <input type="hidden" class="pct-mix-range-value" value="">
                            <div class="pct-mix-list" hidden>
                                <div class="pct-mix-range-option" data-range="">
                                    <span class="pct-mix-option-label">
                                        <?php esc_html_e( 'All', 'paint-tracker-and-mixing-helper' ); ?>
                                    </span>
                                </div>
                                <?php
                                // Top-level parents (parent_id = 0).
                                pct_render_range_options_hierarchical( 0, $pct_ranges_by_parent );
                                ?>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Paint dropdown -->
                <div class="pct-mix-field">
                    <label>
                        <?php esc_html_e( 'Paint', 'paint-tracker-and-mixing-helper' ); ?><br>
                        <div class="pct-mix-dropdown pct-mix-dropdown-shade">
                            <button type="button" class="pct-mix-trigger">
                                <span class="pct-mix-trigger-swatch"></span>
                                <span class="pct-mix-trigger-label">
                                    <?php esc_html_e( 'Select a paint', 'paint-tracker-and-mixing-helper' ); ?>
                                </span>
                                <span class="pct-mix-trigger-caret">&#9662;</span>
                            </button>
                            <input type="hidden" class="pct-mix-value" value="">
                            <div class="pct-mix-list" hidden>
                                <?php PCT_Paint_Table_Plugin::render_mix_paint_options( $pct_paints ); ?>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="pct-shade-ladders">
                <div class="pct-shade-ladder pct-shade-ladder--strict">
                    <div class="pct-shade-scale pct-shade-scale--strict"
                         aria-live="polite"
                         data-hue-mode="strict">
                        <p class="pct-shade-empty">
                            <?php esc_html_e( 'Select a paint to see lighter and darker mixes.', 'paint-tracker-and-mixing-helper' ); ?>
                        </p>
                    </div>
                </div>

                <div class="pct-shade-ladder pct-shade-ladder--relaxed">
                    <div class="pct-shade-scale pct-shade-scale--relaxed"
                         aria-live="polite"
                         data-hue-mode="relaxed">
                        <p class="pct-shade-empty">
                            <?php esc_html_e( 'Select a paint to see lighter and darker mixes.', 'paint-tracker-and-mixing-helper' ); ?>
                        </p>
                    </div>
                </div>
            </div><!-- /.pct-shade-ladders -->
        </div>
    </div>
</div><!-- /.pct-shade-container -->
