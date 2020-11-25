<?php
/**
* 2010-2020 Webkul.
*
* NOTICE OF LICENSE
*
* All right is reserved,
* Please go through this link for complete license : https://store.webkul.com/license.html
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to https://store.webkul.com/customisation-guidelines/ for more information.
*
*  @author    Webkul IN <support@webkul.com>
*  @copyright 2010-2020 Webkul IN
*  @license   https://store.webkul.com/license.html
*/

class WkSampleProductMap extends ObjectModel
{
    public $id_product;
    public $id_product_attribute;
    public $max_cart_qty;
    /**
     * 1 = Product Actual Price
     * 2 = Deduct fix amount from product price
     * 3 = A percentage of product price
     * 4 = Custom Price
     * @var int
     */
    public $price_type;
    public $price_tax;
    public $amount;
    public $price;
    public $description;
    public $active;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'wk_sample_product',
        'primary' => 'id_sample_product',
        'fields' => array(
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'shop' => true),
            'id_product_attribute' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'shop' => true),
            'max_cart_qty' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'shop' => true),
            'price_type' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'shop' => true),
            'price_tax' =>  array('type' => self::TYPE_INT, 'validate' => 'isBool', 'shop' => true),
            'amount' => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat', 'shop' => true),
            'price' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'shop' => true),
            'button_label' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'shop' => true),
            'description' => array('type' => self::TYPE_HTML, 'shop' => true),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'shop' => true),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
        ),
    );

    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id, $idLang, $idShop);
        Shop::addTableAssociation('wk_sample_product', array('type' => 'shop', 'primary' => 'id_sample_product'));
    }

    /**
     * Get Product Sample Data
     *
     * @param  int $idProduct
     * @return array
     */
    public function getSampleProduct($idProduct)
    {
        $sample = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'wk_sample_product` wsp'
            .WkSampleCart::addSqlAssociationCustom('wk_sample_product', 'wsp', true, null, false, 'id_sample_product').'
            WHERE wsp.`id_product` = '. (int) $idProduct
        );
        if ($sample) {
            if (!$sample['active'] && Configuration::get('WK_GLOBAL_SAMPLE')) {
                return $this->getGlobalSample($idProduct);
            }
            return $sample;
        } elseif (Configuration::get('WK_GLOBAL_SAMPLE')) {
            return $this->getGlobalSample($idProduct);
        }

        return false;
    }

    private function getGlobalSample($idProduct)
    {
        $globalSampleConfig = Configuration::getMultiple(array(
            'WK_GLOBAL_SAMPLE_IN_CART',
            'WK_GLOBAL_SAMPLE_PRICE_TYPE',
            'WK_GLOBAL_SAMPLE_AMOUNT',
            'WK_GLOBAL_SAMPLE_PRICE',
            'WK_GLOBAL_SAMPLE_TAX',
            'WK_GLOBAL_SAMPLE_PERCENT',
            'WK_GLOBAL_SAMPLE_BUTTON_LABEL',
            'WK_GLOBAL_SAMPLE_DESC'
        ));
        return array(
            "id_sample_product" => "0",
            "id_product" => $idProduct,
            "id_product_attribute" => Product::getDefaultAttribute($idProduct),
            "max_cart_qty" => $globalSampleConfig['WK_GLOBAL_SAMPLE_IN_CART'],
            "price_type" => $globalSampleConfig['WK_GLOBAL_SAMPLE_PRICE_TYPE'],
            "price_tax" => $globalSampleConfig['WK_GLOBAL_SAMPLE_TAX'],
            "amount" => ($globalSampleConfig['WK_GLOBAL_SAMPLE_PRICE_TYPE'] == '3') ?
            $globalSampleConfig['WK_GLOBAL_SAMPLE_PERCENT']: $globalSampleConfig['WK_GLOBAL_SAMPLE_AMOUNT'],
            "price" => $globalSampleConfig['WK_GLOBAL_SAMPLE_PRICE'],
            "button_label" => $globalSampleConfig['WK_GLOBAL_SAMPLE_BUTTON_LABEL'],
            "description" => $globalSampleConfig['WK_GLOBAL_SAMPLE_DESC'],
            "active" => "1",
            "date_add" => date('Y-m-d H:i:s'),
            "date_upd" => date('Y-m-d H:i:s')
        );
    }
}
