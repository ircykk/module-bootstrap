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

// Include class manually if we want to use it
require_once dirname(__FILE__).'/../../classes/bootstrap.php';

class AdminBootstrapController extends ModuleAdminController
{
    public $bootstrap = true;

    /**
     * AdminBootstrapController constructor.
     *
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->table = 'bootstrap';
        $this->className = 'Bootstrap';
        $this->lang = true;

        $this->fields_list = array(
            'id_bootstrap' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
            'name' => array(
                'title' => $this->l('Name'),
            ),
            'active' => array(
                'title' => $this->l('Active'),
                'active' => 'status',
                'type' => 'bool',
                'class' => 'fixed-width-xs',
                'align' => 'center',
                'orderby' => false,
            ),
            'date_add' => array(
                'title' => $this->l('Date add'),
                'type' => 'datetime',
                'align' => 'text-right'
            ),
            'date_upd' => array(
                'title' => $this->l('Date update'),
                'type' => 'datetime',
                'align' => 'text-right'
            ),
        );

        // Row actions
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        // Bulk actions
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?')
            )
        );

        parent::__construct();
    }

    /**
     * Init header.
     *
     * @see    AdminController::initPageHeaderToolbar()
     * @return void
     */
    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_'.$this->table] = array(
                'href' => self::$currentIndex
                    .'&add'.$this->table
                    .'&token='.$this->token,
                'desc' => $this->l('Add'),
                'icon' => 'process-icon-new'
            );
        }

        parent::initPageHeaderToolbar();
    }

    /**
     * Render add/edit form.
     *
     * @see    AdminController::renderForm()
     * @return string|bool
     * @throws Exception|SmartyException
     */
    public function renderForm()
    {
        if (!($obj = $this->loadObject(true))) {
            return;
        }

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Bootstrap object'),
                'icon' => 'icon-gear'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Name'),
                    'name' => 'name',
                    'required' => true,
                    'lang' => true,
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Text'),
                    'name' => 'text',
                    'required' => true,
                    'lang' => true,
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            )
        );

        return parent::renderForm();
    }
}
