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
    public $active = 1; // Default active
    public $position = 0; // Default 0
    public $date_add;
    public $date_upd;

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
                'required' => true,
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

        return parent::add($autoDate, $nullValues);
    }

    /**
     * Override this method for actions on Object delete
     *
     * @see ObjectModel::delete()
     */
    public function delete()
    {
        $res = parent::delete();

        /**
         * Clean positions on Object delete
         * 
         * Important : after Object delete
         */
        $this->cleanPositions();

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
            ) && Db::getInstance()->execute(
                'UPDATE `'._DB_PREFIX_.self::TABLE_NAME.'`
                SET `position` = '.(int)$position.'
                WHERE `'.self::PRIMARY_KEY.'`='.(int)$moveItem[self::PRIMARY_KEY]
            ));
    }
}