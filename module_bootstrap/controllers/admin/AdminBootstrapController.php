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
     * To show and manage positions on HelperList we need position identifier set
     *
     * @var string
     */
    protected $position_identifier = Bootstrap::PRIMARY_KEY;

    /**
     * AdminBootstrapController constructor
     *
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->table = 'bootstrap';
        $this->className = 'Bootstrap';
        $this->lang = true;

        /**
         * Default order by postion on Objects list
         */
        $this->_defaultOrderBy = 'position';

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
            'position' => array(
                'title' => $this->l('Position'),
                'position' => 'position',
                'align' => 'center'
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
     * Init header
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
     * Render add/edit form
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

    /**
     * This method is needed to manage positions on Objects list by AJAX
     * 
     * To have this working we also need a Object::updatePosition() method
     *
     * @return void
     */
    public function ajaxProcessUpdatePositions()
    {
        $way = (int)Tools::getValue('way');
        $id = (int)Tools::getValue('id');
        $positions = Tools::getValue($this->table);

        $new_positions = array();
        foreach ($positions as $k => $v) {
            if (count(explode('_', $v)) == 4) {
                $new_positions[] = $v;
            }
        }

        $class = $this->className;

        foreach ($new_positions as $position => $value) {
            $pos = explode('_', $value);

            if (isset($pos[2]) && (int)$pos[2] === $id) {
                if ($obg = new $class((int)$pos[2])) {
                    if (isset($position) && $obg->updatePosition($way, $position)) {
                        echo 'ok position '.(int)$position.' for object '.(int)$pos[2].'\r\n';
                    } else {
                        echo '{"hasError" : true, "errors" : "Can not update the '.(int)$id.' object to position '.(int)$position.' "}';
                    }
                } else {
                    echo '{"hasError" : true, "errors" : "The ('.(int)$id.') object cannot be loaded."}';
                }

                break;
            }
        }
    }

    /**
     * PS 1.7x translation method wrapper
     */
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        return Translate::getAdminTranslation($string, $class, $addslashes, $htmlentities);
    }
}