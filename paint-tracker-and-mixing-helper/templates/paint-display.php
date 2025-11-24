<?php
/**
 * Frontend table template for Paint Tracker and Mixing Helper.
 *
 * Expects:
 * - $pct_paints      : array of [ 'name', 'number', 'hex', 'links' ]
 * - $pct_range_title : string (range name, e.g. "Vallejo Model Color")
 * - $pct_mixing_page_url : string (URL of page with [shade-helper])
 */

if ( ! isset( $pct_paints ) || ! is_array( $pct_paints ) || empty( $pct_paints ) ) {
    return;
}
?>

<div class="pct-table-container">
    <?php if ( ! empty( $pct_range_title ) ) : ?>
        <div class="pct-range-title">
            <?php echo esc_html( $pct_range_title ); ?>
        </div>
    <?php endif; ?>

    <div class="pct-table-wrapper">
        <table class="pct-table">
            <thead>
                <tr>
                    <th class="pct-swatch-header" aria-hidden="true"></th>
                    <th><?php esc_html_e( 'Colour', 'pct' ); ?></th>
                    <th><?php esc_html_e( 'Number', 'pct' ); ?></th>
                    <th class="pct-models-header"><?php esc_html_e( 'Models', 'pct' ); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ( $pct_paints as $paint ) :
                $name   = isset( $paint['name'] )   ? $paint['name']   : '';
                $number = isset( $paint['number'] ) ? $paint['number'] : '';
                $hex    = isset( $paint['hex'] )    ? $paint['hex']    : '';
                $links  = isset( $paint['links'] )  && is_array( $paint['links'] )
                    ? $paint['links']
                    : [];
                ?>
                <tr>
                    <td class="pct-swatch-cell">
                        <?php if ( $hex ) : ?>
                            <?php
                            // Build shade helper URL if one is configured.
                            $shade_url = '';
                            if ( ! empty( $pct_mixing_page_url ) ) {
                                // Drop any leading '#' so it doesn't become a fragment.
                                $param_hex = ltrim( $hex, '#' );
                    
                                // Let WordPress build the query string properly.
                                $shade_url = add_query_arg(
                                    'pct_shade_hex',
                                    $param_hex,
                                    $pct_mixing_page_url
                                );
                            }
                            ?>
                    
                            <?php if ( $shade_url ) : ?>
                                <a href="<?php echo esc_url( $shade_url ); ?>" class="pct-swatch-link">
                                    <span class="pct-swatch" style="background-color: <?php echo esc_attr( $hex ); ?>"></span>
                                </a>
                            <?php else : ?>
                                <span class="pct-swatch" style="background-color: <?php echo esc_attr( $hex ); ?>"></span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td class="pct-name-cell">
                        <span class="pct-name"><?php echo esc_html( $name ); ?></span>
                    </td>
                    <td class="pct-number">
                        <?php echo esc_html( $number ); ?>
                    </td>
                    <td class="pct-models">
                        <?php if ( ! empty( $links ) ) : ?>
                            <?php
                            $total_links = count( $links );
                            $shown       = 0;

                            foreach ( $links as $i => $link ) {
                                $url    = isset( $link['url'] )   ? $link['url']   : '';
                                $ltitle = isset( $link['title'] ) ? $link['title'] : '';

                                if ( ! $url ) {
                                    continue;
                                }

                                if ( '' === $ltitle ) {
                                    $ltitle = ( $total_links > 1 )
                                        ? sprintf( __( 'View %d', 'pct' ), $i + 1 )
                                        : __( 'View', 'pct' );
                                }

                                $shown++;
                                ?>
                                <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener">
                                    <?php echo esc_html( $ltitle ); ?>
                                </a>
                                <?php if ( $shown < $total_links ) : ?>
                                    <br>
                                <?php endif; ?>
                            <?php } ?>
                        <?php else : ?>
                            &mdash;
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
