<?php
if ( !defined( 'ABSPATH' ) ) {exit;}

class McisoeProductListFilter
{
    public function product_list_filter_by_taxonomy()
    {

        global $typenow; // this variable stores the current custom post type
        if ( $typenow == 'product' ) {

            $taxonomy = 'supplier';

            $current_taxonomy = isset( $_GET[$taxonomy] ) ? $_GET[$taxonomy] : '';
            $taxonomy_object  = get_taxonomy( $taxonomy );
            $all_tag          = __( 'All Suppliers', 'supplier-order-email' );
            $taxonomy_terms   = get_terms( $taxonomy );

            if ( count( $taxonomy_terms ) > 0 ) {
                echo "<select name='$taxonomy' id='$taxonomy' class='postform'>";
                echo "<option value=''>" . esc_html( $all_tag ) . "</option>";

                foreach ( $taxonomy_terms as $single_term ) {
                    echo '<option value=' . $single_term->slug, $current_taxonomy == $single_term->slug ? ' selected="selected"' : '', '>' . esc_html( $single_term->name ) . '</option>';
                }
                echo "</select>";
            }
        }
    }

    public function init()
    {
        add_action( 'restrict_manage_posts', array( $this, 'product_list_filter_by_taxonomy' ), 99 );
    }

} // end class