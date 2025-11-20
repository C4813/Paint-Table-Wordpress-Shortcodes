<?php
/**
 * Frontend template for [mixing_helper].
 *
 * Expects:
 * - $pct_ranges : array of WP_Term (paint ranges)
 * - $pct_paints : array of paints:
 *                 [ 'id', 'name', 'number', 'hex', 'range_id' ]
 */

if ( ! isset( $pct_ranges, $pct_paints ) || empty( $pct_ranges ) || empty( $pct_paints ) ) {
    return;
}

/**
 * Helper to print all paint options for a column.
 *
 * @param array  $paints
 * @param string $side   'left' or 'right' (for CSS classes/ids)
 */
function pct_render_mix_paint_options( $paints, $side ) {
    foreach ( $paints as $paint ) {
        $name     = isset( $paint['name'] ) ? $paint['name'] : '';
        $number   = isset( $paint['number'] ) ? $paint['number'] : '';
        $hex      = isset( $paint['hex'] ) ? $paint['hex'] : '';
        $range_id = isset( $paint['range_id'] ) ? (int) $paint['range_id'] : 0;

        if ( '' === $name || '' === $hex || ! $range_id ) {
            continue;
        }

        $label = $name;
        if ( '' !== $number ) {
            $label .= ' (' . $number . ')';
        }

        // Choose text colour for contrast (simple luminance check)
        $text_color = '#000000';
        $hex_clean  = ltrim( $hex, '#' );
        if ( strlen( $hex_clean ) === 6 ) {
            $r = hexdec( substr( $hex_clean, 0, 2 ) );
            $g = hexdec( substr( $hex_clean, 2, 2 ) );
            $b = hexdec( substr( $hex_clean, 4, 2 ) );
            $luminance = ( 0.299 * $r + 0.587 * $g + 0.114 * $b ) / 255;
            $text_color = ( $luminance < 0.5 ) ? '#f9fafb' : '#111827';
        }

        $style = sprintf(
            'background-color:%1$s;color:%2$s;',
            esc_attr( $hex ),
            esc_attr( $text_color )
        );
        ?>
        <div class="pct-mix-option"
             data-hex="<?php echo esc_attr( $hex ); ?>"
             data-label="<?php echo esc_attr( $label ); ?>"
             data-range="<?php echo esc_attr( $range_id ); ?>"
             style="<?php echo $style; ?>">
            <span class="pct-mix-option-swatch"></span>
            <span class="pct-mix-option-label"><?php echo esc_html( $label ); ?></span>
        </div>
        <?php
    }
}
?>

<div class="pct-mix-container">
    <div class="pct-mix-row">
        <!-- Left column -->
        <div class="pct-mix-column pct-mix-column-left">
            <div class="pct-mix-field">
                <label><strong><?php esc_html_e( 'Paint 1', 'pct' ); ?></strong></label>
            </div>

            <!-- Range dropdown (custom) -->
            <div class="pct-mix-field">
                <label>
                    <?php esc_html_e( 'Range', 'pct' ); ?><br>
                    <div class="pct-mix-range-dropdown pct-mix-range-dropdown-left">
                        <button type="button" class="pct-mix-trigger">
                            <span class="pct-mix-trigger-label">
                                <?php esc_html_e( 'All', 'pct' ); ?>
                            </span>
                            <span class="pct-mix-trigger-caret">&#9662;</span>
                        </button>
                        <input type="hidden" class="pct-mix-range-value" value="">
                        <div class="pct-mix-list" hidden>
                            <div class="pct-mix-range-option" data-range="">
                                <span class="pct-mix-option-label">
                                    <?php esc_html_e( 'All', 'pct' ); ?>
                                </span>
                            </div>
                            <?php foreach ( $pct_ranges as $range ) : ?>
                                <div class="pct-mix-range-option"
                                     data-range="<?php echo esc_attr( $range->term_id ); ?>">
                                    <span class="pct-mix-option-label">
                                        <?php echo esc_html( $range->name ); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </label>
            </div>

            <!-- Paint dropdown (custom) -->
            <div class="pct-mix-field">
                <label>
                    <?php esc_html_e( 'Paint', 'pct' ); ?><br>
                    <div class="pct-mix-dropdown pct-mix-dropdown-left">
                        <button type="button" class="pct-mix-trigger">
                            <span class="pct-mix-trigger-swatch"></span>
                            <span class="pct-mix-trigger-label">
                                <?php esc_html_e( 'Select a paint', 'pct' ); ?>
                            </span>
                            <span class="pct-mix-trigger-caret">&#9662;</span>
                        </button>
                        <input type="hidden" class="pct-mix-value" value="">
                        <div class="pct-mix-list" hidden>
                            <?php pct_render_mix_paint_options( $pct_paints, 'left' ); ?>
                        </div>
                    </div>
                </label>
            </div>

            <div class="pct-mix-field">
                <label>
                    <?php esc_html_e( 'Parts', 'pct' ); ?><br>
                    <input type="number"
                           class="pct-mix-parts pct-mix-parts-left"
                           min="1"
                           step="1"
                           value="1">
                </label>
            </div>
        </div>

        <!-- Right column -->
        <div class="pct-mix-column pct-mix-column-right">
            <div class="pct-mix-field">
                <label><strong><?php esc_html_e( 'Paint 2', 'pct' ); ?></strong></label>
            </div>

            <!-- Range dropdown (custom) -->
            <div class="pct-mix-field">
                <label>
                    <?php esc_html_e( 'Range', 'pct' ); ?><br>
                    <div class="pct-mix-range-dropdown pct-mix-range-dropdown-right">
                        <button type="button" class="pct-mix-trigger">
                            <span class="pct-mix-trigger-label">
                                <?php esc_html_e( 'All', 'pct' ); ?>
                            </span>
                            <span class="pct-mix-trigger-caret">&#9662;</span>
                        </button>
                        <input type="hidden" class="pct-mix-range-value" value="">
                        <div class="pct-mix-list" hidden>
                            <div class="pct-mix-range-option" data-range="">
                                <span class="pct-mix-option-label">
                                    <?php esc_html_e( 'All', 'pct' ); ?>
                                </span>
                            </div>
                            <?php foreach ( $pct_ranges as $range ) : ?>
                                <div class="pct-mix-range-option"
                                     data-range="<?php echo esc_attr( $range->term_id ); ?>">
                                    <span class="pct-mix-option-label">
                                        <?php echo esc_html( $range->name ); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </label>
            </div>

            <!-- Paint dropdown (custom) -->
            <div class="pct-mix-field">
                <label>
                    <?php esc_html_e( 'Paint', 'pct' ); ?><br>
                    <div class="pct-mix-dropdown pct-mix-dropdown-right">
                        <button type="button" class="pct-mix-trigger">
                            <span class="pct-mix-trigger-swatch"></span>
                            <span class="pct-mix-trigger-label">
                                <?php esc_html_e( 'Select a paint', 'pct' ); ?>
                            </span>
                            <span class="pct-mix-trigger-caret">&#9662;</span>
                        </button>
                        <input type="hidden" class="pct-mix-value" value="">
                        <div class="pct-mix-list" hidden>
                            <?php pct_render_mix_paint_options( $pct_paints, 'right' ); ?>
                        </div>
                    </div>
                </label>
            </div>

            <div class="pct-mix-field">
                <label>
                    <?php esc_html_e( 'Parts', 'pct' ); ?><br>
                    <input type="number"
                           class="pct-mix-parts pct-mix-parts-right"
                           min="1"
                           step="1"
                           value="1">
                </label>
            </div>
        </div>
    </div>

    <div class="pct-mix-result-block">
        <div class="pct-mix-result-label">
            <?php esc_html_e( 'Result:', 'pct' ); ?>
        </div>
        <div class="pct-mix-result-hex">#FFFFFF</div>
        <div class="pct-mix-result-swatch" style="background-color:#FFFFFF;"></div>
    </div>
</div>
