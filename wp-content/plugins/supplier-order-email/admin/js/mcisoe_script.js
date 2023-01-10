'use strict';
/////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////
// MCISOE - Main JavaScript file  ///////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
// Disable the button if values are not valid in supplier add & edit pages
////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////
mcisoe_disable_button_add();

function mcisoe_disable_button_add() {

  // If the url contains the parameter taxonomy=supplier
  if (window.location.href.indexOf('edit-tags.php?taxonomy=supplier') > -1) {

    let form = document.getElementById('addtag');
    let supplier_email = document.getElementById('mcisoe_supplier_email');
    let supplier_name = document.getElementById('tag-name');
    let submit_button = document.getElementById('submit');
    submit_button.disabled = true;

    // Add event listener when someone input of the form changes the value
    form.addEventListener('input', function () {

      // If the input is empty, disable the button
      if (!mcisoe_validate_email(supplier_email.value) || supplier_name.value == '') {
        submit_button.disabled = true;
      } else {
        submit_button.disabled = false;
      }
    }
    );
  }
}

mcisoe_disable_button_edit();

function mcisoe_disable_button_edit() {

  // If the url contains the parameter taxonomy=supplier
  if (window.location.href.indexOf('term.php?taxonomy=supplier') > -1) {

    let form = document.getElementById('edittag');
    let supplier_name = document.getElementById('name');
    let supplier_email = document.getElementById('mcisoe_supplier_email');
    let submit_button = document.getElementsByClassName('button')[1];

    // Add event listener when someone input of the form changes the value
    form.addEventListener('input', function () {

      // If the input is empty, disable the button
      if (!mcisoe_validate_email(supplier_email.value) || supplier_name.value == '') {
        submit_button.disabled = true;
      } else {
        submit_button.disabled = false;
      }
    }
    );
  }
}
///////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////
// Do not allow selecting more than one supplier on the product edit page. (if Yoast Seo is not active)
/////////////////////////////////////////////////////////////////////////////////////////////

mcisoe_show_limit_supplier_product_page();

function mcisoe_show_limit_supplier_product_page() {

  // If is page edit product & has the parameter action=edit
  if (window.location.href.indexOf('post.php?post=') > -1 && window.location.href.indexOf('action=edit') > -1) {

    // If plugin Yoast SEO is not active
    if (document.getElementById('yoast_wpseo_meta-robots-noindex') == null) {

      count_suppliers_and_uncheck();

      // Add event listener when someone input of the page changes the value
      window.addEventListener('input', function () {

        count_suppliers_and_uncheck();

      });
    }
  }
}

function count_suppliers_and_uncheck() {
  // Check if more than one supplier is selected
  let supplierchecklist = document.getElementById('supplierchecklist');

  // Check if input supplier is checked
  let supplierchecklist_input = supplierchecklist.getElementsByTagName('input');

  // Count the number of checked inputs
  let count = 0;
  for (let i = 0; i < supplierchecklist_input.length; i++) {
    if (supplierchecklist_input[i].checked) {
      count++;
    }
  }

  // If one supplier is selected disable rest of inputs
  if (count == 1) {
    for (let i = 0; i < supplierchecklist_input.length; i++) {
      if (!supplierchecklist_input[i].checked) {
        supplierchecklist_input[i].disabled = true;
      }
    }
  } else {
    for (let i = 0; i < supplierchecklist_input.length; i++) {
      supplierchecklist_input[i].disabled = false;
    }
  }
}
///////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////
// Function to validate email
/////////////////////////////////////////////////////////////////////////////////////////////
function mcisoe_validate_email(email) {
  let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return re.test(String(email).toLowerCase());
}
///////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////
// Show message confirmation in supplier-order-email plugin settings page
/////////////////////////////////////////////////////////////////////////////////////////////
mcisoe_deactivate_confirm();

function mcisoe_deactivate_confirm() {

  if (window.location.href.indexOf('admin.php?page=supplier-order-email') > -1) {

    // Listen button click event and show a confirmation message for delete registration
    let mcisoe_deactivate_btn = document.getElementById('mcisoe_deactivate');
    let mcisoe_deactivate_confirm = 'Are you sure you want to deactivate the plugin pro? You will need the license code to activate it again.';
    if (mcisoe_deactivate_btn) {
      mcisoe_confirm(mcisoe_deactivate_btn, mcisoe_deactivate_confirm);
    }
  }
}

function mcisoe_confirm(button, message) {

  button.addEventListener('click', function (event) {
    if (confirm(message)) {
      return true;
    } else {
      event.preventDefault();
    }
  }
  );
}
///////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////
// Show old field registration
/////////////////////////////////////////////////////////////////////////////////////////////
mcisoe_show_old_field_registration();

function mcisoe_show_old_field_registration() {
  if (window.location.href.indexOf('admin.php?page=supplier-order-email') > -1) {
    // Show email field register for old registrations
    let mci_pay_email = document.getElementById('mci_pay_email');
    mci_pay_email.style.display = 'none';
    let mcisoe_show_email_field = document.getElementById('mcisoe_show_email_field');

    mcisoe_show_email_field.addEventListener('click', function () {
      // If mci_pay_email element is hide, show mci_pay_email element
      if (mci_pay_email.style.display == 'none') {

        mci_pay_email.style.display = 'block';
      }
    });
  }
}
///////////////////////////////////////////////






