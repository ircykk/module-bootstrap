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
require_once __DIR__.'/../../classes/bootstrap.php';

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
         * Important for image upload
         */
        $this->fieldImageSettings = array(
            'name' => 'img',
            'dir' => '../modules/module_bootstrap/uploads'
        );

        /**
         * Default order by position on Objects list
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
     * Set extra CSS and JS.
     *
     * @see AdminController::setMedia()
     */
    public function setMedia()
    {
        /**
         * Load (bit old) autocomplete jQuery plugin
         */
        $this->context->controller->addJqueryPlugin(array('autocomplete'));

        $this->context->controller->addJS($this->path.'/views/js/back.js');

        return parent::setMedia();
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

        /**
         * Image upload
         */
        $image = __DIR__.'/../../uploads/'.$obj->id.'.jpg';
        $image_url = ImageManager::thumbnail(
            $image,
            $this->table.'_'.(int)$obj->id.'.'.$this->imageType,
            150,
            $this->imageType,
            true,
            true
        );
        $image_size = file_exists($image) ? filesize($image) / 1000 : false;

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
                'upload_tab' => $this->l('Upload'),
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
                    'type' => 'textarea',
                    'label' => $this->l('Text with editor'),
                    'name' => 'text2',
                    'lang' => true,
                    'autoload_rte' => true,
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
                    'type' => 'text',
                    'label' => $this->l('ID product with auto-complete'),
                    'name' => 'product_name',
                    'tab' => 'main_tab',
                    'desc' => $this->l('Start typing...'),
                    'class' => 'ac_input_class',
                ),
                array(
                    'type' => 'html',
                    'label' => $this->l('Products with auto-complete'),
                    'name' => 'products',
                    'html_content' => $this->context->smarty->assign(
                            array('products' => $this->getSelectedProducts())
                        )->fetch(
                            __DIR__.'/../../views/templates/admin/bootstrap/products_autocomplete.tpl' // @todo
                    ),
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
                array(
                    'type' => 'file',
                    'label' => $this->l('Image'),
                    'name' => 'img',
                    'display_image' => true,
                    'image' => $image_url ? $image_url : false,
                    'size' => $image_size,
                    'delete_url' => self::$currentIndex.'&'.$this->identifier.'='.$obj->id
                        .'&token='.$this->token.'&deleteImage=1',
                    'tab' => 'upload_tab',
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

            /**
             * Products
             */
            if (($products = Tools::getValue('products'))) {
                $_POST['products'] = implode('|', $products);
            }
        }

        return parent::postProcess();
    }

    /**
     * Get product data for auto-complete
     *
     * @return array|false
     * @throws Exception
     */
    public function getSelectedProducts()
    {
        if (!empty($this->object->products)) {
            $productsIds = explode('|', $this->object->products);
            $sql = 'SELECT p.`id_product`, p.`reference`, pl.`name`
				FROM `' . _DB_PREFIX_ . 'product` p
				' . Shop::addSqlAssociation('product', 'p') . '
				LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl 
				    ON (p.`id_product` = pl.`id_product` ' . Shop::addSqlRestrictionOnLang('pl') . ')
				WHERE pl.`id_lang` = ' . (int)$this->context->language->id . '
				AND p.`id_product` IN (' . implode(',', $productsIds) . ')
				ORDER BY FIELD(p.`id_product`, '.implode(',', $productsIds).')';

            return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        }

        return array();
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
                    echo '{"hasError" : true, "errors" : "The ('.$id.') object cannot be loaded."}';
                }

                break;
            }
        }
    }

    /**
     * Get products list by name for ajax auto-complete
     *
     * @throws PrestaShopDatabaseException
     */
    public function ajaxProcessGetAutocompleteProducts()
    {
        $query = Tools::getValue('q', false);
        if (!$query or $query === '' || strlen($query) < 2) {
            die();
        }

        $sql = 'SELECT p.`id_product`, p.`reference`, pl.`name`
		FROM `'._DB_PREFIX_.'product` p
		'.Shop::addSqlAssociation('product', 'p').'
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.id_product = p.id_product 
		    AND pl.id_lang = '.(int)$this->context->language->id.
            Shop::addSqlRestrictionOnLang('pl').'
        )
        WHERE pl.`name` LIKE "%'.pSQL($query).'%" 
        OR p.`reference` LIKE "%'.pSQL($query).'%" 
        OR p.`id_product` LIKE "%'.pSQL($query).'%"
		GROUP BY p.id_product';

        $products = Db::getInstance()->executeS($sql);

        $results = array();
        foreach ($products as $product) {
            $results[] = '['.$product['reference'].'] '.$product['name'].'|'.$product['id_product'];
        }

        /**
         * Result:
         *
         * Product|ID
         * Next Product|ID
         * ...
         */
        die(implode(PHP_EOL, $results));
    }

    /**
     * PS 1.7x translation method wrapper
     */
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        return Translate::getAdminTranslation($string, $class, $addslashes, $htmlentities);
    }
}