<?php
/**
 * Frontend template for the [mixing-helper] shortcode.
 *
 * Expects:
 * - $pct_mixing_paints      : array of [ 'name', 'number', 'hex', 'label', 'text_color' ]
 * - $pct_mixing_placeholder : string (e.g. "Select a paint")
 */

if ( ! isset( $pct_mixing_paints ) || ! is_array( $pct_mixing_paints ) || empty( $pct_mixing_paints ) ) {
    return;
}

$placeholder = isset( $pct_mixing_placeholder ) ? $pct_mixing_placeholder : __( 'Select a paint', 'pct' );
?>

<div class="pct-mix-container">
    <div class="pct-mix-row">
        <div class="pct-mix-column pct-mix-left">
            <p class="pct-mix-field">
                <label>
                    <?php esc_html_e( 'Paint 1', 'pct' ); ?><br>
                    <div class="pct-mix-dropdown pct-mix-dropdown-left" data-hex="">
                        <button type="button" class="pct-mix-trigger">
                            <span class="pct-mix-trigger-swatch"></span>
                            <span class="pct-mix-trigger-label"><?php echo esc_html( $placeholder ); ?></span>
                            <span class="pct-mix-trigger-caret" aria-hidden="true">▾</span>
                        </button>
                        <div class="pct-mix-list" hidden>
                            <?php foreach ( $pct_mixing_paints as $paint ) : ?>
                                <?php
                                $hex        = isset( $paint['hex'] )        ? $paint['hex']        : '';
                                $label      = isset( $paint['label'] )      ? $paint['label']      : '';
                                $text_color = isset( $paint['text_color'] ) ? $paint['text_color'] : '#000000';
                                ?>
                                <div class="pct-mix-option"
                                     data-hex="<?php echo esc_attr( $hex ); ?>"
                                     data-label="<?php echo esc_attr( $label ); ?>"
                                     style="background-color: <?php echo esc_attr( $hex ); ?>; color: <?php echo esc_attr( $text_color ); ?>;">
                                    <span class="pct-mix-option-swatch"></span>
                                    <span class="pct-mix-option-label"><?php echo esc_html( $label ); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" class="pct-mix-value" value="">
                    </div>
                </label>
            </p>
            <p class="pct-mix-field">
                <label>
                    <?php esc_html_e( 'Parts', 'pct' ); ?><br>
                    <input type="number"
                           class="pct-mix-parts pct-mix-parts-left"
                           min="1"
                           step="1"
                           inputmode="numeric"
                           pattern="\d+"
                           value="1">
                </label>
            </p>
        </div>

        <div class="pct-mix-column pct-mix-right">
            <p class="pct-mix-field">
                <label>
                    <?php esc_html_e( 'Paint 2', 'pct' ); ?><br>
                    <div class="pct-mix-dropdown pct-mix-dropdown-right" data-hex="">
                        <button type="button" class="pct-mix-trigger">
                            <span class="pct-mix-trigger-swatch"></span>
                            <span class="pct-mix-trigger-label"><?php echo esc_html( $placeholder ); ?></span>
                            <span class="pct-mix-trigger-caret" aria-hidden="true">▾</span>
                        </button>
                        <div class="pct-mix-list" hidden>
                            <?php foreach ( $pct_mixing_paints as $paint ) : ?>
                                <?php
                                $hex        = isset( $paint['hex'] )        ? $paint['hex']        : '';
                                $label      = isset( $paint['label'] )      ? $paint['label']      : '';
                                $text_color = isset( $paint['text_color'] ) ? $paint['text_color'] : '#000000';
                                ?>
                                <div class="pct-mix-option"
                                     data-hex="<?php echo esc_attr( $hex ); ?>"
                                     data-label="<?php echo esc_attr( $label ); ?>"
                                     style="background-color: <?php echo esc_attr( $hex ); ?>; color: <?php echo esc_attr( $text_color ); ?>;">
                                    <span class="pct-mix-option-swatch"></span>
                                    <span class="pct-mix-option-label"><?php echo esc_html( $label ); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" class="pct-mix-value" value="">
                    </div>
                </label>
            </p>
            <p class="pct-mix-field">
                <label>
                    <?php esc_html_e( 'Parts', 'pct' ); ?><br>
                    <input type="number"
                           class="pct-mix-parts pct-mix-parts-right"
                           min="1"
                           step="1"
                           inputmode="numeric"
                           pattern="\d+"
                           value="1">
                </label>
            </p>
        </div>
    </div>

    <div class="pct-mix-result-row">
        <span class="pct-mix-result-label">
            <?php esc_html_e( 'Result:', 'pct' ); ?>
        </span>
        <span class="pct-mix-result-hex">—</span>
        <span class="pct-mix-result-swatch" aria-hidden="true"></span>
    </div>
</div>
