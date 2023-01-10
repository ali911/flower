<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class McisoeHelp
{

    public function __construct( $auth_premium )
    {
        ?>
<h2 class="mcisoe_title">SUPPLIER ORDER EMAIL - Help -</h2>

<div class="mcisoe_h_container">
  <div class="mcisoe_h_card mcisoe_box_1">
    <h3><?php esc_html_e( 'How to configure the plugin', 'supplier-order-email' )?></h3>
    <ol>
      <li><?php esc_html_e( 'Create new suppliers in', 'supplier-order-email' ) . ' ';?>
        <a href="<?php echo admin_url( 'edit-tags.php?taxonomy=supplier&post_type=product' ) ?>">
          <?php esc_html_e( 'Products / Suppliers', 'supplier-order-email' )?>
        </a>
      </li>
      <li>
        <a href="<?php echo admin_url( 'edit.php?post_type=product' ) ?>">
          <?php esc_html_e( 'Select the supplier of the products', 'supplier-order-email' ) . ' ';?>
        </a>
        <?php esc_html_e( 'when editing each one in a new selection box "Suppliers" that appears.', 'supplier-order-email' )?>
      </li>
      <li>
        <?php esc_html_e( 'Set your preferences in', 'supplier-order-email' ) . '';?>
        <?php esc_html_e( 'Supplier Order Email', 'supplier-order-email' )?>
        <a href="<?php echo admin_url( 'admin.php?page=supplier-order-email' ) ?>">
          <?php esc_html_e( 'Settings page', 'supplier-order-email' ) . '';?>
        </a>
      </li>
      <li>
        <?php esc_html_e( 'When an order changes to "Processing" status, an automatic order email is sent to the supplier to send the corresponding products to the customer.', 'supplier-order-email' )?>
      </li>
    </ol>
  </div>

  <?php if ( !$auth_premium ) {?>
  <div class="mcisoe_h_card mcisoe_box_3">
    <h3><?php esc_html_e( 'Premium plugin features', 'supplier-order-email' )?></h3>
    <ul class="mcisoe_ul_premium_features">
      <?php $this->print_free_features( '✅' );?>
      <p><?php esc_html_e( 'Extra options:', 'supplier-order-email' )?></p>
      <?php $this->print_premium_features( '&#9745;' );?>
      <li>
        <a href="https://wordpress.org/support/plugin/supplier-order-email" target="_blank">
          &#9745; <?php esc_html_e( 'WordPress forum support.', 'supplier-order-email' );?>
        </a>
      </li>
      <li>
        <a href="https://mci-desarrollo.es/contactar" target="_blank">
          &#9745; <?php esc_html_e( 'Email support.', 'supplier-order-email' );?>
        </a>
      </li>
    </ul>
    <a href="https://mci-desarrollo.es/supplier-order-email-premium/?lang=en" target="_blank" class="mcisoe_btn green">
      <?php esc_html_e( 'Get 30 days free trial Pro', 'supplier-order-email' )?></a>
  </div>

  <div class="mcisoe_h_card mcisoe_box_2">
    <h3><?php esc_html_e( 'Free plugin features', 'supplier-order-email' );?></h3>
    <ul>
      <?php $this->print_free_features( '✅' );?>
      <li>
        <a href="https://wordpress.org/support/plugin/supplier-order-email" target="_blank">
          ✅ <?php esc_html_e( 'WordPress forum support.', 'supplier-order-email' );?>
        </a>
      </li>
    </ul>
    <a href="<?php echo esc_attr( MCISOE_PLUGIN_URL . 'admin/img/screenshot-1.png' ); ?>" target="_blank" title="Product edit page with new taxonomy for Suppliers">
      <img id="mcisoe_help_img" src="<?php echo esc_attr( MCISOE_PLUGIN_URL . 'admin/img/screenshot-1.png' ); ?>" width="100%" alt="Product edit page with new taxonomy for Suppliers">
    </a>
  </div>
  <?php }?>

  <div class="mcisoe_h_card mcisoe_box_4">
    <h3><?php esc_html_e( 'By clicking on the button you can access the complete documentation of the plugin.', 'supplier-order-email' );?></h3>
    <a href="https://mci-desarrollo.es/supplier-order-email-manual" target="_blank" class="mcisoe_btn_manual">
      <span class="dashicons dashicons-book"> </span> <?php esc_html_e( 'View Supplier Order Email Manual', 'supplier-order-email' );?>
    </a>
  </div>
</div>
<?php
}

    public function print_li( $text, $icon, $green = false )
    {
        echo '<li><span class="mcisoe_icon">' . $icon . ' </span>' . $text . '</li>';
    }

    public function print_free_features( $icon )
    {
        $this->print_li( 'Create unlimited suppliers and assign them to each product individually.', $icon );
        $this->print_li( 'Sending automatic emails to suppliers so that they send the products directly to the buyer.', $icon );
        $this->print_li( 'Customize email subject text.', $icon );
        $this->print_li( 'Customize the introductory text of the email.', $icon );
        $this->print_li( 'Send copy of emails to admin.', $icon );
        $this->print_li( 'Available in English and Spanish. Also texts that can be translated into any language with the "Loco Translate" plugin.', $icon );
        $this->print_li( 'Use the customer\'s billing address if the order does not have a shipping address.', $icon );
        $this->print_li( 'The prices format adapts to the one chosen in WooCommerce/Settings/General.', $icon );
        $this->print_li( 'Compatible with Import / Export of native WooCommerce products.', $icon );
    }

    public function print_premium_features( $icon )
    {
        $this->print_li( 'Select custom color for email headers.', $icon, true );
        $this->print_li( 'Show store logo instead of header text.', $icon, true );
        $this->print_li( 'Show order number in products, subject email and introductory text.', $icon, true );
        $this->print_li( 'Show order date in products, subject email and introductory text.', $icon, true );
        $this->print_li( 'Show customer email, phone and notes.', $icon, true );
        $this->print_li( 'Show sku/ean number.', $icon, true );
        $this->print_li( 'Include variable products and product custom fields.', $icon, true );
        $this->print_li( 'Include product attributes.', $icon, true );
        $this->print_li( 'Show order and product prices by supplier.', $icon, true );
        $this->print_li( 'Show shipping method and payment method.', $icon, true );
        $this->print_li( 'Show custom text by supplier.', $icon, true );
        $this->print_li( 'Compatible with native WooCommerce bulk import to select the supplier of each product.', $icon, true );
        $this->print_li( 'Button for manual sending of emails from the WooCommerce order list.', $icon, true );
        $this->print_li( 'Allows you to override the html templates of emails from the child theme for deep customization.', $icon, true );
        $this->print_li( 'Show cost price per product and total cost (if the SkyVerge “Cost of Goods” plugin has been installed).', $icon, true );
        $this->print_li( 'Filter by suppliers on the products page.', $icon, true );
    }

}