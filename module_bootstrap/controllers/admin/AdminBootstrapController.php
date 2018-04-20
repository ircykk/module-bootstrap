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

    protected $checkbox;

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

        $this->checkbox = array(
            array('id' => 1, 'name' => 'Name 1'),
            array('id' => 2, 'name' => 'Name 2'),
            array('id' => 4, 'name' => 'Name 3'),
        );

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
     * @throws Exception
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
            /**
             * Split form into tabs
             */
            'tabs' => array(
                'main_tab' => $this->l('Main tab'),
                'asso_tab' => $this->l('Association'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Name'),
                    'name' => 'name',
                    'required' => true,
                    'lang' => true,
                    'tab' => 'main_tab',
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Text'),
                    'name' => 'text',
                    'lang' => true,
                    'tab' => 'main_tab',
                ),
                array(
                    'type' => 'color',
                    'label' => $this->l('Color'),
                    'name' => 'color',
                    'tab' => 'main_tab',
                ),
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Checkbox (flag)'),
                    'name' => 'conf_checkbox',
                    'values' => array(
                        'query' => $this->checkbox,
                        'id' => 'id',
                        'name' => 'name',
                    ),
                    'col' => '2',
                    'tab' => 'main_tab',
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Select'),
                    'name' => 'conf_select',
                    'options' => array(
                        'query' => array(
                            array('id' => 1, 'name' => 'Name 1'),
                            array('id' => 2, 'name' => 'Name 2'),
                            array('id' => 3, 'name' => 'Name 3'),
                        ),
                        'id' => 'id',
                        'name' => 'name',
                    ),
                    'col' => '2',
                    'tab' => 'main_tab',
                ),
                array(
                    'type' => 'date',
                    'label' => $this->l('Date'),
                    'name' => 'date_custom',
                    'tab' => 'main_tab',
                ),
                array(
                    'type' => 'group',
                    'label' => $this->l('Group access'),
                    'name' => 'groupBox',
                    'values' => Group::getGroups(Context::getContext()->language->id),
                    'tab' => 'asso_tab',
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            )
        );

        /**
         * Set groups access
         */
        $groupsIds = $obj->getGroups();
        $groups = Group::getGroups($this->context->language->id);
        $preselected = array(
            Configuration::get('PS_UNIDENTIFIED_GROUP'),
            Configuration::get('PS_GUEST_GROUP'),
            Configuration::get('PS_CUSTOMER_GROUP')
        );
        foreach ($groups as $group) {
            $this->fields_value['groupBox_'.$group['id_group']] = Tools::getValue(
                'groupBox_'.$group['id_group'],
                (in_array($group['id_group'], $groupsIds)
                    || (empty($groupsIds) && in_array($group['id_group'], $preselected)))
            );
        }

        /**
         * Set checkbox
         */
        foreach ($this->checkbox as $checkbox) {
            $this->fields_value['conf_checkbox_'.$checkbox['id']] = Tools::getValue(
                'conf_checkbox_'.$checkbox['id'],
                (($checkbox['id'] & $obj->conf_checkbox))
            );
        }

        return parent::renderForm();
    }

    /**
     * @see AdminController::postProcess()
     */
    public function postProcess()
    {
        /**
         * Set checkbox
         */
        if (Tools::getValue('submitAdd'.Bootstrap::TABLE_NAME)) {
            $list = array();
            foreach ($this->checkbox as $checkbox) {
                if (Tools::getIsset('conf_checkbox_'.$checkbox['id'])) {
                    $list[] += $checkbox['id'];
                }
            }
            $_POST['conf_checkbox'] = array_sum($list);
        }

        return parent::postProcess();
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