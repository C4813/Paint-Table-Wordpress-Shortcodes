<?php
/**
 * Front-end table template for Paint Table Shortcodes.
 *
 * Expects:
 *  - $pct_paints: array of paints
 *  - $pct_range_title: string (range name, e.g. "Vallejo Model Color")
 */

if ( ! isset( $pct_paints ) || ! is_array( $pct_paints ) || empty( $pct_paints ) ) {
    return;
}
?>

<div class="pct-table-wrapper">
    <?php if ( ! empty( $pct_range_title ) ) : ?>
        <h2 class="pct-table-title"><?php echo esc_html( $pct_range_title ); ?></h2>
    <?php endif; ?>

    <table class="pct-table">
        <thead>
        <tr>
            <th class="pct-col-swatch" scope="col">&nbsp;</th>
            <th scope="col"><?php esc_html_e( 'Colour', 'pct' ); ?></th>
            <th scope="col"><?php esc_html_e( 'Number', 'pct' ); ?></th>
            <th scope="col"><?php esc_html_e( 'Models', 'pct' ); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ( $pct_paints as $paint ) : ?>
            <?php
            $name   = isset( $paint['name'] ) ? $paint['name'] : '';
            $number = isset( $paint['number'] ) ? $paint['number'] : '';
            $hex    = isset( $paint['hex'] ) ? $paint['hex'] : '';
            $links  = isset( $paint['links'] ) && is_array( $paint['links'] ) ? $paint['links'] : [];
            ?>
            <tr>
                <td class="pct-cell-swatch">
                    <?php if ( $hex ) : ?>
                        <span class="pct-swatch" style="background-color: <?php echo esc_attr( $hex ); ?>"></span>
                    <?php endif; ?>
                </td>
                <td class="pct-cell-name">
                    <span class="pct-name"><?php echo esc_html( $name ); ?></span>
                </td>
                <td class="pct-cell-number">
                    <?php echo esc_html( $number ); ?>
                </td>
                <td class="pct-cell-links">
                    <?php if ( ! empty( $links ) ) : ?>
                        <?php foreach ( $links as $i => $link ) :
                            $url    = isset( $link['url'] ) ? $link['url'] : '';
                            $ltitle = isset( $link['title'] ) ? $link['title'] : '';
                            if ( ! $url ) {
                                continue;
                            }

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
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
