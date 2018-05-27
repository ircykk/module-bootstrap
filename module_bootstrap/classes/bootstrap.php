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

class Bootstrap extends ObjectModel
{
    /**
     * Easy access to table name and primary key form outside of class
     */
    const TABLE_NAME = 'bootstrap';
    const PRIMARY_KEY = 'id_bootstrap';

    public $name;
    public $text;
    public $text2;
    public $active = 1; // Default active
    public $position = 0; // Default 0
    public $color = '#ffffff'; // Default white
    public $conf_checkbox;
    public $conf_select;
    public $date_custom;
    public $id_product;
    public $date_add;
    public $date_upd;

    public $groupBox;

    /**
     * Object definition.
     *
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => self::TABLE_NAME,
        'primary' => self::PRIMARY_KEY,
        'multilang' => true,
        'fields' => array(
            'name' => array(
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isGenericName',
                'required' => true,
            ),
            'text' => array(
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml',
            ),
            'text2' => array(
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml',
            ),
            'active' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ),
            'position' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ),
            'color' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
            ),
            'conf_checkbox' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ),
            'conf_select' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ),
            'date_custom' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ),
            'id_product' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ),
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate'
            ),
            'date_upd' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate'
            ),
        ),
    );

    /**
     * Override this method for actions on Object add
     *
     * @see ObjectModel::add()
     */
    public function add($autoDate = true, $nullValues = false)
    {
        /**
         * Auto set position on Object creation
         */
        if ($this->position <= 0) {
            $this->position = $this->getHigherPosition() + 1;
        }

        $this->cleanGroups();
        $this->addGroups($this->groupBox);

        return parent::add($autoDate, $nullValues);
    }

    /**
     * @see ObjectModel::update()
     */
    public function update($nullValues = false)
    {
        $ret = parent::update($nullValues);

        $this->cleanGroups();
        $this->addGroups($this->groupBox);

        return $ret;
    }

    /**
     * Override this method for actions on Object delete
     *
     * @see ObjectModel::delete()
     */
    public function delete()
    {
        $this->cleanGroups();

        $res = parent::delete();

        /**
         * Clean positions on Object delete
         *
         * Important : after Object delete
         */
        $this->cleanPositions();

        /**
         * Delete image
         */
        $this->deleteImage();

        return $res;
    }
    
    /**
    * Get the highest position
    *
    * @return int $position Position
    */
    public function getHigherPosition()
    {
        $sql = 'SELECT MAX(`position`)
        FROM `'._DB_PREFIX_.self::TABLE_NAME.'`';
        $position = DB::getInstance()->getValue($sql);

        return (is_numeric($position)) ? $position : -1;
    }

    /**
     * Reorder positions after delete some Objects
     *
     * @return bool
     * @throws Exception
     */
    public function cleanPositions()
    {
        $return = true;
        $result = Db::getInstance()->executeS('
        SELECT `'.self::PRIMARY_KEY.'`
        FROM `'._DB_PREFIX_.self::TABLE_NAME.'`
        ORDER BY `position`');
        $count = count($result);
        for ($i = 0; $i < $count; $i++) {
            $return &= Db::getInstance()->execute('
            UPDATE `'._DB_PREFIX_.self::TABLE_NAME.'`
            SET `position` = '.(int)$i.'
            WHERE `'.self::PRIMARY_KEY.'`='.(int)$result[$i][self::PRIMARY_KEY]);
        }

        return $return;
    }

    /**
     * Update Object position
     *
     * @param $direction
     * @param $position
     * @return bool
     * @throws Exception
     */
    public function updatePosition($direction, $position)
    {
        if (!$res = Db::getInstance()->executeS(
            'SELECT `position`, `'.self::PRIMARY_KEY.'`
			FROM `'._DB_PREFIX_.self::TABLE_NAME.'` 
			WHERE `'.self::PRIMARY_KEY.'` = '.(int)Tools::getValue('id', 1).'
			ORDER BY `position` ASC'
        )) {
            return false;
        }

        foreach ($res as $item) {
            if ((int) $item[self::PRIMARY_KEY] == (int) $this->id) {
                $moveItem = $item;
            }
        }

        if (!isset($moveItem) || !isset($position)) {
            return false;
        }

        // < and > statements rather than BETWEEN operator
        // since BETWEEN is treated differently according to databases
        return (Db::getInstance()->execute(
            'UPDATE `'._DB_PREFIX_.self::TABLE_NAME.'`
			SET `position`= `position` '.($direction ? '- 1' : '+ 1').'
			WHERE `position`
			'.($direction
                    ? '> '.(int) $moveItem['position'].' AND `position` <= '.(int) $position
                    : '< '.(int) $moveItem['position'].' AND `position` >= '.(int) $position)
        )
            && Db::getInstance()->execute(
                'UPDATE `'._DB_PREFIX_.self::TABLE_NAME.'`
                SET `position` = '.(int)$position.'
                WHERE `'.self::PRIMARY_KEY.'`='.(int)$moveItem[self::PRIMARY_KEY]
            ));
    }

    /**
     * Clean groups
     *
     * @return bool
     */
    public function cleanGroups()
    {
        return Db::getInstance()->delete(self::TABLE_NAME.'_group', self::PRIMARY_KEY.' = '.(int)$this->id);
    }

    /**
     * Add groups
     *
     * @param $groups
     * @throws Exception
     */
    public function addGroups($groups)
    {
        if (count($groups)) {
            foreach ($groups as $group) {
                if ($group !== false) {
                    Db::getInstance()->insert(
                        self::TABLE_NAME.'_group',
                        array(self::PRIMARY_KEY => (int)$this->id, 'id_group' => (int)$group)
                    );
                }
            }
        }
    }

    /**
     * Get groups
     *
     * @return array|null
     * @throws Exception
     */
    public function getGroups()
    {
        $cacheId = self::TABLE_NAME.'::getGroups_'.(int)$this->id;
        if (!Cache::isStored($cacheId)) {
            $sql = new DbQuery();
            $sql->select('id_group');
            $sql->from(self::TABLE_NAME.'_group');
            $sql->where(self::PRIMARY_KEY.' = '.(int)$this->id);
            $result = Db::getInstance()->executeS($sql);
            $groups = array();
            foreach ($result as $group) {
                $groups[] = $group['id_group'];
            }
            Cache::store($cacheId, $groups);
            return $groups;
        }
        return Cache::retrieve($cacheId);
    }

    /**
     * Delete image
     *
     * @see AdminController::processDeleteImage()
     *
     * @param bool $force_delete
     * @return bool
     */
    public function deleteImage($force_delete = false)
    {
        return @unlink(__DIR__.'/../uploads/'.$this->id.'.jpg');
    }

    /**
     * Install DB tables
     *
     * @return bool
     */
    public static function installDb()
    {
        $sql = array();

        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::TABLE_NAME.'` (
            `'.self::PRIMARY_KEY.'` int(10) NOT NULL AUTO_INCREMENT,
            `active` int(1),
            `position` int(10) default 0,
            `color` varchar(255),
            `conf_checkbox` int(10),
            `conf_select` int(10),
            `date_custom` datetime,
            `id_product` int(10),
            `date_add` datetime,
            `date_upd` datetime,
            PRIMARY KEY  (`id_bootstrap`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        // Lang table for multi language fields
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::TABLE_NAME.'_lang` (
            `'.self::PRIMARY_KEY.'` int(10) NOT NULL,
            `id_lang` int(10) NOT NULL,
            `name` varchar(255),
            `text` text,
            `text2` text,
            PRIMARY KEY  (`id_bootstrap`, `id_lang`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        // Group table for group association
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_.self::TABLE_NAME.'_group` (
            `'.self::PRIMARY_KEY.'` int(10) NOT NULL,
            `id_group` int(10) UNSIGNED NOT NULL,
            PRIMARY KEY (' . self::PRIMARY_KEY . ', `id_group`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Drop DB tables
     *
     * @return bool
     */
    public static function uninstallDb()
    {
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.self::TABLE_NAME.'`;') &&
            Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.self::TABLE_NAME.'_lang`;') &&
            Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.self::TABLE_NAME.'_group`;');
    }
}