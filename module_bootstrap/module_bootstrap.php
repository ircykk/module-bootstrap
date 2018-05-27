<?php
/**
 * PrestaShop bootstrap module containing most of features:
 *
 * - Install, uninstall, upgrade
 * - Configure, admin tabs, CRUD
 * - Front & back office hooks
 * - Forms (HelperForm)
 * - Templates
 * - Override classes, controllers and views
 * - and more...
 *
 * PHP version 5.3, PrestaShop 1.6-1.7x
 *
 * LICENSE: MIT https://api.github.com/licenses/mit
 *
 * @category PrestaShop
 * @package  PrestaShop
 * @author   Ireneusz Kierkowski <ircykk@gmail.com>
 * @license  https://api.github.com/licenses/mit  MIT
 * @link     https://github.com/ircykk/module-bootstrap
 * @see      https://github.com/PrestaShop
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__.'/classes/bootstrap.php';

class Module_Bootstrap extends Module
{
    protected $config_form = false;

    /**
     * Module_Bootstrap constructor.
     */
    public function __construct()
    {
        $this->name = 'module_bootstrap';
        $this->tab = 'administration';
        $this->author = 'ircykk'; // Feel free to paste here your name :)
        $this->need_instance = 0;

        /**
         * Current version of module.
         *
         * @see /upgrade/upgrade-x.x.x.php
         */
        $this->version = '1.0.0';

        /**
         * Set to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Module bootstrap.');
        $this->description = $this->l(
            'This is a bootstrap module for PrestaShop 1.6-1.7x'
        );

        $this->confirmUninstall = $this->l('Are you sure?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Install method, install module, tabs, hooks, create DB tables etc...
     *
     * @see /upgrade/upgrade-x.x.x.php
     *
     * @return bool
     * @throws Exception
     */
    public function install()
    {
        /**
         * Create config (optional).
         */
        Configuration::updateValue('MODULE_BOOTSTRAP_BOOL', false);
        Configuration::updateValue('MODULE_BOOTSTRAP_TEXT', '');

        /**
         * Create DB
         */
        Bootstrap::installDb();

        /**
         * Add admin tab.
         *
         * @see Administration > Tabs
         */
        $this->installModuleTab(
            'AdminBootstrap',
            array((int)Configuration::get('PS_LANG_DEFAULT') => 'Bootstrap'),
            'AdminParentModulesSf' // Modules parent tab
        );

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayLeftColumn');
    }

    /**
     * Uninstall module.
     *
     * @return bool
     */
    public function uninstall()
    {
        /**
         * Delete config.
         */
        Configuration::deleteByName('MODULE_BOOTSTRAP_TEST');

        $sql = array();

        $sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'bootstrap`';
        $sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'bootstrap_lang`';

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        /**
         * Remove admin tab.
         */
        $this->uninstallModuleTab('Bootstrap');

        return parent::uninstall();
    }

    /**
     * Add new admin tab.
     *
     * @param string $tabClass          Tab class name
     * @param array  $tabName           Tab names list
     * @param int    $parentTabClass    Id parent tab
     * @param int    $active            Status
     *
     * @return bool|int
     */
    public function installModuleTab($tabClass, $tabName, $parentTabClass, $active = 1)
    {
        $tab = new Tab();
        $tab->name = $tabName;
        $tab->class_name = $tabClass;
        $tab->module = $this->name;
        $tab->id_parent = Tab::getIdFromClassName($parentTabClass);
        $tab->active = $active;

        if (!$tab->add()) {
            return false;
        }

        return $tab->id;
    }


    /**
     * Remove admin tab.
     *
     * @param string $tabClass Tab class name
     *
     * @return bool
     */
    public function uninstallModuleTab($tabClass)
    {
        $idTab = Tab::getIdFromClassName($tabClass);

        if ($idTab) {
            $tab = new Tab($idTab);
            return $tab->delete();
        }

        return false;
    }

    /**
     * Set module config form.
     *
     * @return string
     * @throws Exception|SmartyException
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (Tools::isSubmit('submitBootstrapModule')) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch(
            $this->local_path.'views/templates/admin/configure.tpl'
        );

        return $output.$this->renderForm();
    }

    /**
     * Create config form for module.
     *
     * @see    Helper::generateForm()
     * @return string
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang
            = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitBootstrapModule';
        $helper->currentIndex
            = $this->context->link->getAdminLink(
                'AdminModules',
                false
            )
            .'&configure='.$this->name
            .'&tab_module='.$this->tab
            .'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     *
     * @return array
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('On / off switch'),
                        'name' => 'MODULE_BOOTSTRAP_BOOL',
                        'is_bool' => true,
                        'desc' => $this->l('Demo enabled / disabled switch.'),
                        'values' => array(
                            array(
                                'id' => 'bool_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'bool_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-gear"></i>',
                        'desc' => $this->l('Enter some text.'),
                        'name' => 'MODULE_BOOTSTRAP_TEXT',
                        'label' => $this->l('Text'),
                    ),
                ),

                // @todo Add more fields.

                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set and return values for the inputs.
     *
     * @return array
     */
    protected function getConfigFormValues()
    {
        return array(
            'MODULE_BOOTSTRAP_BOOL'
                => Configuration::get('MODULE_BOOTSTRAP_BOOL'),
            'MODULE_BOOTSTRAP_TEXT'
                => Configuration::get('MODULE_BOOTSTRAP_TEXT'),
        );
    }

    /**
     * Save config.
     *
     * @return void
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Run hookBackOfficeHeader for CSS & JS in admin header.
     *
     * @return void
     */
    public function hookBackOfficeHeader()
    {
        $this->context->controller->addJS($this->_path.'views/js/back.js');
        $this->context->controller->addCSS($this->_path.'views/css/back.css');
    }

    /**
     * Run hookHeader for CSS & JS in FO header.
     *
     * @return void
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    /**
     * Run hookDisplayLeftColumn for display template in FO left column.
     *
     * @return void
     */
    public function hookDisplayLeftColumn()
    {
        // @todo
    }
}
