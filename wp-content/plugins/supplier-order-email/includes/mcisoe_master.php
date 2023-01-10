<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class McisoeMaster
{

    private $mcisoe_admin;
    private $mcisoe_public;
    private $mcisoe_check_premium;
    private $mcisoe_load_textdomain;
    private $mcisoe_helpers;

    private function load_dependencies()
    {

        require_once MCISOE_PLUGIN_DIR . 'public/mcisoe_public.php';
        require_once MCISOE_PLUGIN_DIR . 'admin/mcisoe_admin.php';
        require_once MCISOE_PLUGIN_DIR . 'includes/check_premium/mcisoe_check_premium.php';
        require_once MCISOE_PLUGIN_DIR . 'helpers/mcisoe_helpers.php';
        require_once MCISOE_PLUGIN_DIR . 'includes/mcisoe_load_textdomain.php';
    }

    private function load_instances()
    {
        $this->mcisoe_load_textdomain = new Mcisoe_load_textdomain();
        $this->mcisoe_load_textdomain->init();

        $this->mcisoe_check_premium = new McisoeCheckPremium();
        $this->mcisoe_check_premium->init();

        $this->mcisoe_public = new McisoePublic();
        $this->mcisoe_public->init();

        $this->mcisoe_admin = new McisoeAdmin();
        $this->mcisoe_admin->init();

        $this->mcisoe_helpers = new McisoeHelpers();

    }

    public function init()
    {

        $this->load_dependencies();
        $this->load_instances();

    }

}