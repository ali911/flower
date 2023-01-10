<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
?>

<?php if ( MCISOE_REAL_ENVIRONMENT == false ): ?>
<div class="dev_mode">
  <p>Development mode is active</p>
</div>
<?php endif;?>

<h2 class="mcisoe_title">SUPPLIER ORDER EMAIL - Settings -</h2>

<!-- Header -->
<div class="mcisoe_header">
  <?php if ( !$options->auth_premium ): ?>
  <p class="mcisoe_description"><?php esc_html_e( 'Do you want the Premium version? ', 'supplier-order-email' );?>
    <a href="https://mci-desarrollo.es/supplier-order-email-premium/?lang=en" target="_blank">
      <?php esc_html_e( 'Get a 30-day free trial here.', 'supplier-order-email' );?></a>
  </p>
  <?php endif;?>

  <p class="mcisoe_description"><?php esc_html_e( 'Do you need changes in the plugin? Send us an email to', 'supplier-order-email' );?>
    <a href="mailto:soporte@mci-desarrollo.es">soporte@mci-desarrollo.es</a>
    <?php esc_html_e( 'and we will send you a quote.', 'supplier-order-email' );?>
  </p>

  <?php if ( !$options->auth_premium ): ?>
  <p><?php esc_html_e( 'Thanks for using our plugin. ', 'supplier-order-email' );?>
    <a href='https://wordpress.org/support/plugin/supplier-order-email/reviews/#new-post' target="_blank" rel="nofollow">
      <?php esc_html_e( 'You will be collaborating to maintain it if you value  ', 'supplier-order-email' );?><span class="stars">★ ★ ★ ★ ★</span>
    </a>
  </p>
  <?php endif;?>
</div>

<div id="mcisoe_content">

  <!-- Suppliers -->
  <div id="mcisoe_suppliers">
    <form action="" method="post">
      <table class="mcisoe_table">
        <thead>
          <tr>
            <th><?php esc_html_e( 'SUPPLIER', 'supplier-order-email' );?></th>
            <th><?php esc_html_e( 'EMAIL', 'supplier-order-email' );?></th>
            <?php if ( $options->auth_premium ): ?>
            <th><?php esc_html_e( 'SUPPLIER CUSTOM TEXT', 'supplier-order-email' );?></th>
            <?php endif;?>
          </tr>
        </thead>
        <tbody>
          <?php if ( $options->suppliers ): ?>
          <?php foreach ( $options->suppliers as $supplier ): ?>
          <tr>
            <td><?php echo esc_html( $supplier['name'] ) ?></td>
            <td><?php echo esc_html( $supplier['email'] ) ?></td>
            <?php if ( $options->auth_premium ): ?>
            <td><?php echo esc_textarea( $supplier['supplier_custom_text'] ) ?></td>
            <?php endif;?>
          </tr>
          <?php endforeach;?>
          <?php else: ?>
          <td class="mcisoe_not_suppliers">
            <?php esc_html_e( 'There are no suppliers created yet. Create new suppliers in', 'supplier-order-email' )?>
            <?php echo " "; ?>
            <a href="<?php echo admin_url( 'edit-tags.php?taxonomy=supplier&post_type=product' ) ?>">
              <?php esc_html_e( 'Products / Suppliers', 'supplier-order-email' )?>
          </td>
          <?php endif;?>
        </tbody>
      </table>

      <?php if ( $options->suppliers ): ?>
      <?php esc_html_e( 'To add new Suppliers, edit or delete them', 'supplier-order-email' )?>
      <?php echo " "; ?>
      <a href="<?php echo admin_url( 'edit-tags.php?taxonomy=supplier&post_type=product' ) ?>">
        <?php esc_html_e( 'Go to Suppliers', 'supplier-order-email' )?>
        <?php endif;?>
      </a>
  </div>

  <!-- Options -->
  <div id="mcisoe_options">

    <!-- Email subject -->
    <div class="mcisoe_input">
      <label for="subject"><?php esc_html_e( 'Email subject', 'supplier-order-email' )?></label>
      <input type="text" name="subject" id="subject" value="<?php echo esc_html( $options->email_subject ); ?>">
      <p class="mci_annotation">
        <?php esc_html_e( 'Optional tags: {order_number} {order_date} {supplier_custom_text}', 'supplier-order-email' );?>
        <?php McisoeHelpers::mcisoe_premium_text( $options->auth_premium );?>
      </p>
    </div>

    <!-- Introductory text -->
    <div class="mcisoe_input">
      <label for="email_intro"><?php esc_html_e( 'Email introductory text', 'supplier-order-email' )?></label>
      <textarea name="email_intro" id="email_intro" rows="4" columns="3" maxlength="390"><?php echo esc_textarea( $options->email_intro ); ?></textarea>
      <p class="mci_annotation">
        <?php esc_html_e( 'Optional tags: {order_number} {order_date} {supplier_custom_text}', 'supplier-order-email' );?>
        <?php McisoeHelpers::mcisoe_premium_text( $options->auth_premium );?>
      </p>
    </div>

    <!-- GENERAL OPTIONS -->
    <h3><?php esc_html_e( 'General options', 'supplier-order-email' )?></h3>

    <!-- Select admin email -->
    <div class="mcisoe_checkbox">
      <input type="checkbox" name="select_email_admin" id="select_email_admin" <?php if ( $options->select_email_admin == 1 ) {echo "checked";}?>>
      <label for="select_email_admin"><?php esc_html_e( 'Send copy of emails to admin when send emails to suppliers', 'supplier-order-email' );?></label>
    </div>

    <!-- Replace address -->
    <div class="mcisoe_checkbox">
      <input type="checkbox" name="replace_address" id="replace_address" <?php if ( $options->replace_address == 1 ) {echo "checked";}?>>
      <label for="replace_address"><?php esc_html_e( 'Use the customer´s billing address if the order does not have a shipping address', 'supplier-order-email' );?></label>
    </div>

    <!-- Delete all data -->
    <div class="mcisoe_checkbox">
      <input type="checkbox" name="delete_all_data" id="delete_all_data" <?php if ( $options->delete_all_data == 1 ) {echo "checked";}?>>
      <label for="delete_all_data"><?php esc_html_e( 'Delete all data when uninstall the plugin', 'supplier-order-email' );?></label>
    </div>

    <!-- ORDER OPTIONS IN EMAILS -->
    <h3 <?php if ( !$options->auth_premium ) {echo 'class="option_disabled"';}?>><?php esc_html_e( 'Order options in emails', 'supplier-order-email' );
McisoeHelpers::mcisoe_premium_text( $options->auth_premium );?></h3>

    <!-- // PREMIUM OPTIONS /////////////////////////////////////////// -->
    <!-- Show header color -->
    <div class="mcisoe_input_color">
      <input type="color" name="header_color" id="header_color" value="<?php echo esc_html( $options->header_color ); ?>" <?php if ( !$options->auth_premium ) {echo 'disabled';}?>>
      <label for="header_color" <?php if ( !$options->auth_premium ) {echo 'class="option_disabled"';}?>><?php esc_html_e( 'Headers color', 'supplier-order-email' );?>
    </div>

    <!-- Replace email header with store logo -->
    <div class="mcisoe_checkbox">
      <input type="checkbox" name="store_logo" id="store_logo" <?php if ( $options->auth_premium ) {if ( $options->store_logo ) {echo 'checked';}} else {echo 'disabled';}?>>
      <label for="store_logo" <?php if ( !$options->auth_premium ) {echo 'class="option_disabled"';}?>><?php esc_html_e( 'Show store logo instead of header text', 'supplier-order-email' );?>
      </label>
    </div>

    <!-- Show order number -->
    <div class="mcisoe_checkbox">
      <input type="checkbox" name="show_order_number" id="show_order_number" <?php if ( $options->auth_premium ) {if ( $options->show_order_number ) {echo 'checked';}} else {echo 'disabled';}?>>
      <label for="show_order_number" <?php if ( !$options->auth_premium ) {echo 'class="option_disabled"';}?>><?php esc_html_e( 'Show order number', 'supplier-order-email' );?>
      </label>
    </div>

    <!-- Show customer email -->
    <div class="mcisoe_checkbox">
      <input type="checkbox" name="show_customer_email" id="show_customer_email" <?php if ( $options->auth_premium ) {if ( $options->show_customer_email ) {echo 'checked';}} else {echo 'disabled';}?>>
      <label for="show_customer_email" <?php if ( !$options->auth_premium ) {echo 'class="option_disabled"';}?>><?php esc_html_e( 'Show customer email', 'supplier-order-email' );?>
      </label>
    </div>

    <!-- Show customer phone -->
    <div class="mcisoe_checkbox">
      <input type="checkbox" name="show_customer_phone" id="show_customer_phone" <?php if ( $options->auth_premium ) {if ( $options->show_customer_phone ) {echo 'checked';}} else {echo 'disabled';}?>>
      <label for="show_customer_phone" <?php if ( !$options->auth_premium ) {echo 'class="option_disabled"';}?>><?php esc_html_e( 'Show customer phone', 'supplier-order-email' );?>
      </label>
    </div>

    <!-- Show notes -->
    <div class="mcisoe_checkbox">
      <input type="checkbox" name="show_notes" id="show_notes" <?php if ( $options->auth_premium ) {if ( $options->show_notes ) {echo 'checked';}} else {echo 'disabled';}?>>
      <label for="show_notes" <?php if ( !$options->auth_premium ) {echo 'class="option_disabled"';}?>><?php esc_html_e( 'Show customer notes', 'supplier-order-email' );?>
      </label>
    </div>

    <!-- Show order total -->
    <div class="mcisoe_checkbox">
      <input type="checkbox" name="show_order_total" id="show_order_total" <?php if ( $options->auth_premium ) {if ( $options->show_order_total ) {echo 'checked';}} else {echo 'disabled';}?>>
      <label for="show_order_total" <?php if ( !$options->auth_premium ) {echo 'class="option_disabled"';}?>><?php esc_html_e( 'Show total price', 'supplier-order-email' );?>
      </label>
    </div>

    <!-- Show payment method -->
    <div class="mcisoe_checkbox">
      <input type="checkbox" name="show_payment_method" id="show_payment_method" <?php if ( $options->auth_premium ) {if ( $options->show_payment_method ) {echo 'checked';}} else {echo 'disabled';}?>>
      <label for="show_payment_method" <?php if ( !$options->auth_premium ) {echo 'class="option_disabled"';}?>><?php esc_html_e( 'Show payment method', 'supplier-order-email' );?>
      </label>
    </div>

    <!-- Show shipping method -->
    <div class="mcisoe_checkbox">
      <input type="checkbox" name="show_shipping_method" id="show_shipping_method" <?php if ( $options->auth_premium ) {if ( $options->show_shipping_method ) {echo 'checked';}} else {echo 'disabled';}?>>
      <label for="show_shipping_method" <?php if ( !$options->auth_premium ) {echo 'class="option_disabled"';}?>><?php esc_html_e( 'Show shipping method', 'supplier-order-email' );?>
      </label>
    </div>

    <!-- Show cost total if Plugin is active -->
    <?php if ( is_plugin_active( 'woocommerce-cost-of-goods/woocommerce-cost-of-goods.php' ) ) {?>
    <div class="mcisoe_checkbox">
      <input type="checkbox" name="show_cost_total" id="show_cost_total" <?php if ( $options->auth_premium ) {if ( $options->show_cost_total ) {echo 'checked';}} else {echo 'disabled';}?>>
      <label for="show_cost_total" <?php if ( !$options->auth_premium ) {echo 'class="option_disabled"';}?>><?php esc_html_e( 'Show cost total', 'supplier-order-email' );?>
      </label>
    </div>
    <?php }?>

    <!-- Hide customer -->
    <div class="mcisoe_checkbox">
      <input type="checkbox" name="hide_customer" id="hide_customer" <?php if ( $options->auth_premium ) {if ( $options->hide_customer ) {echo 'checked';}} else {echo 'disabled';}?>>
      <label for="hide_customer" <?php if ( !$options->auth_premium ) {echo 'class="option_disabled"';}?>><?php esc_html_e( 'Hide all customer data', 'supplier-order-email' );?>
      </label>
    </div>

    <!-- PRODUCT OPTIONS IN EMAILS -->
    <h3 <?php if ( !$options->auth_premium ) {echo 'class="option_disabled"';}?>><?php esc_html_e( 'Show Product List fields', 'supplier-order-email' );
McisoeHelpers::mcisoe_premium_text( $options->auth_premium );?></h3>

    <!-- Show short description -->
    <div class="mcisoe_checkbox">
      <input type="checkbox" name="show_shortdesc" id="show_shortdesc" <?php if ( $options->auth_premium ) {if ( $options->show_shortdesc ) {echo 'checked';}} else {echo 'disabled';}?>>
      <label for="show_shortdesc" <?php if ( !$options->auth_premium ) {echo 'class="option_disabled"';}?>><?php esc_html_e( 'Short description', 'supplier-order-email' );?>
      </label>
    </div>

    <!-- Show price items -->
    <div class="mcisoe_checkbox">
      <input type="checkbox" name="show_price_items" id="show_price_items" <?php if ( $options->auth_premium ) {if ( $options->show_price_items ) {echo 'checked';}} else {echo 'disabled';}?>>
      <label for="show_price_items" <?php if ( !$options->auth_premium ) {echo 'class="option_disabled"';}?>><?php esc_html_e( 'Price', 'supplier-order-email' );?>
      </label>
    </div>

    <!-- Show product cost prices if Plugin is active -->
    <?php if ( is_plugin_active( 'woocommerce-cost-of-goods/woocommerce-cost-of-goods.php' ) ) {?>
    <div class="mcisoe_checkbox">
      <input type="checkbox" name="show_cost_prices" id="show_cost_prices" <?php if ( $options->auth_premium ) {if ( $options->show_cost_prices ) {echo 'checked';}} else {echo 'disabled';}?>>
      <label for="show_cost_prices" <?php if ( !$options->auth_premium ) {echo 'class="option_disabled"';}?>><?php esc_html_e( 'Cost price', 'supplier-order-email' );?>
      </label>
    </div>
    <?php }?>

    <!-- Show ean -->
    <div class="mcisoe_checkbox">
      <input type="checkbox" name="show_ean" id="show_ean" <?php if ( $options->auth_premium ) {if ( $options->show_ean ) {echo 'checked';}} else {echo 'disabled';}?>>
      <label for="show_ean" <?php if ( !$options->auth_premium ) {echo 'class="option_disabled"';}?>><?php esc_html_e( 'EAN number - Compatible with the plugin "Product GTIN (EAN, UPC, ISBN) for WooCommerce"', 'supplier-order-email' );?>
      </label>
    </div>

    <!-- Show product attributes -->
    <div class="mcisoe_checkbox">
      <input type="checkbox" name="show_product_attributes" id="show_product_attributes" <?php if ( $options->auth_premium ) {if ( $options->show_product_attributes ) {echo 'checked';}} else {echo 'disabled';}?>>
      <label for="show_product_attributes" <?php if ( !$options->auth_premium ) {echo 'class="option_disabled"';}?>><?php esc_html_e( 'Attributes', 'supplier-order-email' );?>
      </label>
    </div>

    <!-- Show product meta data -->
    <div class="mcisoe_checkbox">
      <input type="checkbox" name="show_product_meta" id="show_product_meta" <?php if ( $options->auth_premium ) {if ( $options->show_product_meta ) {echo 'checked';}} else {echo 'disabled';}?>>
      <label for="show_product_meta" <?php if ( !$options->auth_premium ) {echo 'class="option_disabled"';}?>><?php esc_html_e( 'Variations and meta custom fields', 'supplier-order-email' );?>
      </label>
    </div>

    <!-- // END PREMIUM OPTIONS /////////////////////////////////////////// -->

    <?php wp_nonce_field( 'mcisoe_nonce_field', 'mcisoe_nonce_field' );?>

    <input class="mcisoe_btn" type="submit" name="submit" value="<?php esc_html_e( 'Save', 'supplier-order-email' );?>">

    <hr>
    <div id="mcisoe_login" <?php if ( $options->auth_premium ) {echo ' class="background_green"';}?>>
      <?php if ( !$options->auth_premium ): ?>

      <div class="mcisoe_input" id="mci_pay_email">
        <label for="email"><?php esc_html_e( 'Premium registration email:', 'supplier-order-email' );?></label>
        <input type="email" name="mci_pay_email" class="premium-password" value="<?php echo esc_attr( $options->pay_email ); ?>">
      </div>

      <div class="mcisoe_input">
        <label for="code_key"><?php esc_html_e( 'License key', 'supplier-order-email' );?></label>
        <input type="password" name="mci_code_key" id="code_key" minlength="20" class="premium-password">
      </div>

      <input class="mcisoe_btn" type="submit" name="submit_mcisoe_activate" value="<?php esc_html_e( 'Activate premium', 'supplier-order-email' );?>">

      <a href="https://mci-desarrollo.es/supplier-order-email-premium/?lang=en" target="_blank" class="mcisoe_btn green">
        <?php esc_html_e( 'Get 30 days free trial Pro', 'supplier-order-email' );?></a>

      <a id="mcisoe_show_email_field"><?php esc_html_e( 'Show email field for old registrations', 'supplier-order-email' )?></a>

      <?php else: ?>
      <b class="mcisoe_success bold"><?php esc_html_e( '&#10687; PREMIUM VERSION IS ACTIVE', 'supplier-order-email' );?></b>
      <?php if ( $options->auth_lemon == '1' ): ?>
      <p class="success secondary_text deactivate_text"><?php esc_html_e( 'If you are no longer going to use the Premium options of the plugin in this WooCommerce installation, you can deactivate licenses to reduce the limit of your premium plan so that you can use it on other websites. You can always reactivate it with your License Key.', 'supplier-order-email' );?>
        <input class="mcisoe_btn" type="submit" name="mcisoe_deactivate" id="mcisoe_deactivate" value="<?php esc_html_e( 'Deactivate premium license on this website', 'supplier-order-email' );?>">
      </p>
      <?php endif;?>
      <?php endif;?>
    </div>
    <hr>

    </form>

    <div class="mcisoe_footer">
      <div class="instructions_mcisoe">
        <ol>
          <li><?php esc_html_e( 'Create new suppliers in: ', 'supplier-order-email' )?><a href="<?php echo admin_url( 'edit-tags.php?taxonomy=supplier&post_type=product' ) ?>">
              <?php esc_html_e( 'Products / Suppliers', 'supplier-order-email' )?>
            </a></li>
          <li><?php esc_html_e( 'Select the supplier of the products in a new selection box that appears when editing each product.', 'supplier-order-email' )?></li>
          <li>
            <?php esc_html_e( 'When an order changes to "Processing" status, an automatic order email is sent to the supplier to send the corresponding products to the customer.', 'supplier-order-email' )?>
          </li>
        </ol>
        <p>
          <a href="https://mci-desarrollo.es/supplier-order-email-manual" target="_blank">
            <?php esc_html_e( 'View Supplier Order Email Manual', 'supplier-order-email' );?>
          </a>
        </p>
        <?php if ( $options->auth_premium ): ?>
        <p>
          <a href='https://wordpress.org/support/plugin/supplier-order-email/reviews/#new-post' target="_blank" rel="nofollow">
            <?php esc_html_e( 'Rate our plugin', 'supplier-order-email' );?><span class="stars"> ★ ★ ★ ★ ★</span>
          </a>
        </p>
        <?php endif;?>

      </div>
    </div>

  </div>

  <?php