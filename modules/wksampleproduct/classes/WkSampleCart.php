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

class WkSampleCart extends ObjectModel
{
    public $id_cart;
    public $id_order;
    public $id_product;
    public $id_product_attribute;
    public $id_specific_price;
    public $sample;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'wk_sample_cart',
        'primary' => 'id_sample_cart',
        'fields' => array(
            'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'shop' => true),
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'shop' => true),
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'shop' => true),
            'id_product_attribute' => array('type' => self::TYPE_INT, 'shop' => true),
            'id_specific_price' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'shop' => true),
            'sample' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'shop' => true),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
        ),
    );

    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id, $idLang, $idShop);
        Shop::addTableAssociation('wk_sample_cart', array('type' => 'shop', 'primary' => 'id_sample_cart'));
    }

    public function deleteSampleCart($idCart, $idProduct, $idAttr)
    {
        $sampleCarts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT wsa.`id_sample_cart` FROM `'._DB_PREFIX_.'wk_sample_cart` wsa'
            .WkSampleCart::addSqlAssociationCustom('wk_sample_cart', 'wsa').' WHERE wsa.`id_cart` = '
            .(int)$idCart.' AND wsa.`id_product` = '.(int)$idProduct.' AND wsa.`id_product_attribute`='.(int) $idAttr
            .' GROUP BY wsa.`id_sample_cart`'
        );
        $success = true;
        if (!empty($sampleCarts)) {
            foreach ($sampleCarts as $sampleCart) {
                $objSampleCart = new WkSampleCart($sampleCart['id_sample_cart']);
                $success &= $objSampleCart->delete();
            }
        }
        return $success;
    }

    /**
     * Delete Speciic Price from PrestaShop
     *
     * @param  int $idCart
     * @param  int $idProduct
     * @return bool
     */
    public function deleteSampleSpecificPrice($idCart, $idProduct, $idAttr)
    {
        $sampleCarts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT wsa.`id_specific_price` FROM `'._DB_PREFIX_.'wk_sample_cart` wsa'
            .WkSampleCart::addSqlAssociationCustom('wk_sample_cart', 'wsa').' WHERE wsa.`id_cart` = '
            .(int)$idCart.' AND wsa.`id_product` = '.(int)$idProduct.' AND wsa.`id_product_attribute`='.(int) $idAttr
            .' GROUP BY wsa.`id_sample_cart`'
        );
        $success = true;
        if (!empty($sampleCarts)) {
            foreach ($sampleCarts as $sampleCart) {
                $specificPrice = new SpecificPrice($sampleCart['id_specific_price']);
                if (Validate::isLoadedObject($specificPrice)) {
                    $success &= $specificPrice->delete();
                }
            }
        }
        return $success;
    }

    public function getSampleOrderProduct($idOrder, $idProduct, $idAttr)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'wk_sample_cart` wsa'
            .WkSampleCart::addSqlAssociationCustom('wk_sample_cart', 'wsa').' WHERE wsa.`id_order` = '
            .(int)$idOrder.' AND wsa.`id_product` = '.(int)$idProduct.' AND wsa.`id_product_attribute`='.(int) $idAttr
            .' GROUP BY wsa.`id_sample_cart`'
        );
    }

    public function getSampleCartProduct($idCart, $idProduct, $idAttr = false)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'wk_sample_cart` wsa'
        .WkSampleCart::addSqlAssociationCustom('wk_sample_cart', 'wsa').' WHERE wsa.`id_cart` = '
        .(int)$idCart.' AND wsa.`id_product` = '.(int)$idProduct;
        if ($idAttr) {
            $sql .= ' AND wsa.`id_product_attribute`='.(int) $idAttr;
        }
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
    }

    public function getOtherProductInCart($idCart, $idProduct, $idAttr)
    {
        $cart = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'wk_sample_cart` wsa'
            .WkSampleCart::addSqlAssociationCustom('wk_sample_cart', 'wsa').' WHERE wsa.`id_cart` = '
            .(int)$idCart.' AND wsa.`id_product` != '.(int) $idProduct.' OR wsa.`id_product_attribute`!='.(int) $idAttr
            .' GROUP BY wsa.`id_sample_cart`'
        );

        if ($cart) {
            return $cart;
        }

        return false;
    }

    public function updateCartOrder($idCart, $idProduct, $idOrder, $idAttr)
    {

        $sampleCarts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT wsa.`id_sample_cart` FROM `'._DB_PREFIX_.'wk_sample_cart` wsa'
            .WkSampleCart::addSqlAssociationCustom('wk_sample_cart', 'wsa').' WHERE wsa.`id_cart` = '
            .(int)$idCart.' AND wsa.`id_product` = '.(int)$idProduct.' AND wsa.`id_product_attribute`='.(int) $idAttr
            .' GROUP BY wsa.`id_sample_cart`'
        );
        $success = true;
        if (!empty($sampleCarts)) {
            foreach ($sampleCarts as $sampleCart) {
                $sampleCartObj = new WkSampleCart($sampleCart['id_sample_cart']);
                $sampleCartObj->id_order = (int) $idOrder;
                $success &= $sampleCartObj->save();
            }
        }
        return $success;
    }

    /**
     * Get Sample Cart
     *
     * @param  int $idCart
     * @return array
     */
    public function getSampleCart($idCart)
    {
        $cart = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'wk_sample_cart` wsa'
            .WkSampleCart::addSqlAssociationCustom('wk_sample_cart', 'wsa')
            .' WHERE wsa.`id_cart` = '. (int) $idCart.' GROUP BY wsa.`id_sample_cart`'
        );

        if ($cart) {
            return $cart;
        }

        return false;
    }

    public function checkProductQtyInCart($cart, $idProduct, $updateQty = 0, $idAttr = 0)
    {
        $objSampleProductMap = new WkSampleProductMap();
        if ($sample = $objSampleProductMap->getSampleProduct($idProduct)) {
            if ($this->getSampleCart($cart->id)) {
                foreach ($cart->getProducts() as $prod) {
                    if (($prod['id_product'] == $idProduct) && ($prod['id_product_attribute'] == $idAttr)) {
                        if (($sample['max_cart_qty'] != 0)
                            && (($prod['cart_quantity'] + $updateQty) > $sample['max_cart_qty'])
                        ) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    public function validateSampleCart($ipa, $idProduct, $updateQty = 0)
    {
        $this->context = Context::getContext();
        $this->module = new WkSampleProduct();
        $objSampleProductMap = new WkSampleProductMap();
        $sample = $objSampleProductMap->getSampleProduct($idProduct);
        if (!$sample) {
            die(json_encode(array(
                'hasError' => true,
                'errors' => array(Tools::displayError($this->module->l('This product is not the sample product.'))),
            )));
        }


        // Check if this standard product already in the cart
        $this->checkStandardProductInCart($idProduct, $ipa);

        // Check maximum product in cart ristriction
        $this->checkMaximumInCart($idProduct, $ipa, $this->context);
        // Check maximum quantity in cart ristriction
        if ($this->checkProductQtyInCart($this->context->cart, $idProduct, $updateQty, $ipa)) {
            die(json_encode(array(
                'hasError' => true,
                'errors' => array(
                    Tools::displayError($this->module->l('Maximum quantity for this product to buy exceeded.'))
                ),
            )));
        }

        $product = new Product($idProduct);
        if (isset($this->context->customer->id_default_group)) {
            $taxMethod = Group::getPriceDisplayMethod((int) $this->context->customer->id_default_group);
        } else {
            $taxMethod = Group::getDefaultPriceDisplayMethod();
        }
        //Add specific price discount
        $productPriceSpecific = Product::getPriceStatic($idProduct, !$taxMethod);
        $productPriceStandard = $product->price;
        if (!$taxMethod) {
            $productPriceStandard = $this->addTaxToAmount(
                $productPriceStandard,
                $product->id_tax_rules_group,
                $this->context
            );
        }
        $specificAmount = Tools::ps_round($productPriceStandard, 2) - Tools::ps_round($productPriceSpecific, 2);
        if ($sample['price_type'] == 2) { //Deduct fix amount from product price
            $reduction_type = 'amount';
            $price = '-1.000000';
            if ($taxMethod == $sample['price_tax']) {
                if ($taxMethod) {
                    //price is excluded but sample is included (remove tax)
                    $sampleAmountTaxExcl = $this->removeTaxes(
                        $sample['amount'],
                        $product->id_tax_rules_group,
                        $this->context
                    );
                    $reduction = (float)Tools::ps_round((float)$sampleAmountTaxExcl, 6) + (float) $specificAmount;
                } else {
                    //price is included but sample is excluded (add tax)
                    $sampleAmountTaxExcl = $sample['amount'];
                    $sampleAmountTaxIncl = $this->getTaxIncludedReduction(
                        $product->id_tax_rules_group,
                        $sampleAmountTaxExcl,
                        true
                    );
                    $reduction = (float)Tools::ps_round((float)$sampleAmountTaxIncl, 6) + (float) $specificAmount;
                }
            } else {
                $reduction = (float)Tools::ps_round($sample['amount'], 6) + (float) $specificAmount;
            }
            $sample['price_tax'] = (int) !$taxMethod;
        } elseif ($sample['price_type'] == 3) { //A percentage of product price
            $reduction_type = 'percentage';
            $price = '-1.000000';
            $sample['price_tax'] = 1;
            $productPriceStandard = $product->price;
            if (!$taxMethod) {
                $productPriceStandard = $this->addTaxToAmount(
                    $productPriceStandard,
                    $product->id_tax_rules_group,
                    $this->context
                );
            }
            $sampleSpecificAmount = (($productPriceStandard - $specificAmount) * (float)$sample['amount'])/100;
            $totalSpecificAmount = (float) $sampleSpecificAmount + (float) $specificAmount;
            $reductionPercent = $totalSpecificAmount / $productPriceStandard;
            $reduction = (float)Tools::ps_round($reductionPercent, 6);
        } elseif ($sample['price_type'] == 4) { //Custom Price
            $reduction_type = 'amount';
            $price = '-1.000000';
            $reduction = '0.00';
            $price = (float)$sample['price'];
            if ($sample['price_tax']) {
                $samplePrice = $this->getTaxIncludedReduction(
                    $product->id_tax_rules_group,
                    $price
                );
                $price = Tools::ps_round((float)$samplePrice, 6);
            }
        } elseif ($sample['price_type'] == 5) {
            $reduction_type = 'amount';
            $price = '-1.000000';
            $product = new Product($idProduct);
            $reduction = $product->getPriceWithoutReduct();
        } else { // price is original as product price
            $this->createSampleCart($idProduct, $ipa);
            return;
        }

        $specificPrice = new SpecificPrice();
        $specificPrice->id_product = (int) $idProduct;
        $specificPrice->id_shop = 0;
        $specificPrice->id_cart = $this->context->cart->id;
        $specificPrice->id_currency = 0;
        $specificPrice->id_country = 0;
        $specificPrice->id_group = 0;
        $specificPrice->price = (float) $price;
        $specificPrice->from_quantity = 1;
        $specificPrice->reduction_type = $reduction_type;
        $specificPrice->reduction_tax = $sample['price_tax'];
        $specificPrice->id_customer = (int) $this->context->customer->id;
        $specificPrice->reduction = (float) $reduction;
        $specificPrice->from = '0000-00-00 00:00:00';
        $specificPrice->to = '0000-00-00 00:00:00';
        $specificPrice->id_product_attribute = (int) $ipa;
        $specificPrice->save();
        if ($specificPrice->save()) {
            $this->createSampleCart($idProduct, $ipa, $specificPrice->id);
            return;
        }
    }

    public function getTaxIncludedReduction(
        $idTaxRulesGroup,
        $samplePrice,
        $addTax = false
    ) {
        $context = Context::getContext();
        $address = $this->getAddressFromContext($context);
        if ($idTaxRulesGroup) {
            //tax included amount deduction
            $taxRule = new TaxRulesTaxManager($address, $idTaxRulesGroup);
            $taxCalculator = $taxRule->getTaxCalculator();
            $taxRate = $taxCalculator->getTotalRate();
            if ($addTax) {
                $samplePrice = (float) $samplePrice + (((float)$samplePrice*(float)$taxRate)/100);
            } else {
                $samplePrice = ((float)$samplePrice*100)/((float)$taxRate + 100);
            }
        }
        return (float)$samplePrice;
    }

    public function removeTaxes($amount, $id_tax_rules_group, $context = false)
    {
        if (!$context) {
            $context = Context::getContext();
        }
        $address = $this->getAddressFromContext($context);
        $taxRule = new TaxRulesTaxManager($address, $id_tax_rules_group);
        $taxCalculator = $taxRule->getTaxCalculator();
        $taxRate = $taxCalculator->getTotalRate();
        return ($amount*100)/(100+(float)$taxRate);
    }

    public function getAddressFromContext($context)
    {
        if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_delivery') {
            $cartIdAddress = $context->cart->id_address_delivery;
        } else {
            $cartIdAddress = $context->cart->id_address_invoice;
        }
        if ($cartIdAddress) {
            return Address::initialize($cartIdAddress, true);
        } elseif ($context->customer->isLogged()) {
            $addresses = $context->customer->getAddresses($context->language->id);
            if (!empty($addresses)) {
                $id_address = $addresses['0']['id_address'];
            } else {
                $id_address = 0;
            }
        } else {
            $id_address = 0;
        }
        return Address::initialize($id_address, true);
    }

    public function addTaxToAmount($amount, $idTaxRulesGroup, $context = false)
    {
        if (!$context) {
            $context = Context::getContext();
        }

        $address = $this->getAddressFromContext($context);
        $taxRule = new TaxRulesTaxManager($address, $idTaxRulesGroup);
        $taxCalculator = $taxRule->getTaxCalculator();
        return $taxCalculator->addTaxes($amount);
    }

    public function checkMaximumInCart($idProduct, $idAttr, $context = false)
    {
        if (!$context) {
            $context = Context::getContext();
        }
        $module = Module::getInstanceByName('wksampleproduct');
        $maxInCart = Configuration::get('WK_MAX_SAMPLE_IN_CART');
        $sampleProduct = $this->getOtherProductInCart($context->cart->id, $idProduct, $idAttr);
        if ($maxInCart) { //If not max set, then unlimited, no check needed
            if ($sampleProduct && (count($sampleProduct) >= $maxInCart)) {
                die(json_encode(array(
                    'hasError' => true,
                    'msg' => array(
                        sprintf(
                            $module->l('You can not add more than %d sample product(s) in one cart.').
                            ' '.$module->l('Please checkout current cart first.'),
                            $maxInCart
                        )
                    ),
                )));
            }
        }
    }

    public function checkStandardProductInCart($idProduct, $idAttr)
    {
        $idCart = $this->context->cart->id;
        $products = $this->context->cart->getProducts();
        $objSampleCart = new WkSampleCart();
        if ($products && !$objSampleCart->getSampleCartProduct($idCart, $idProduct, $idAttr)) {
            foreach ($products as $product) {
                if (($product['id_product'] == $idProduct) && ($product['id_product_attribute'] == $idAttr)) {
                    Tools::redirect($this->context->link->getProductLink($idProduct));
                    die(json_encode(array(
                        'hasError' => true,
                        'errors' =>
                        array(
                            Tools::displayError(
                                $this->module->l('This product already in cart. Please checkout your cart first.')
                            )
                        ),
                    )));
                }
            }
        }
    }

    public function createSampleCart($idProduct, $idAttr, $idSpecificPrice = false)
    {
        if (!$this->getSampleCartProduct($this->context->cart->id, $idProduct, $idAttr)) {
            $sampleCart = new WkSampleCart();
            $sampleCart->id_cart = $this->context->cart->id;
            $sampleCart->id_product_attribute = (int) $idAttr;
            $sampleCart->id_product = $idProduct;
            if ($idSpecificPrice) {
                $sampleCart->id_specific_price = $idSpecificPrice;
            } else {
                $sampleCart->id_specific_price = 0;
            }
            $sampleCart->sample = 1;
            $sampleCart->save();
        }
    }

    public static function addSqlAssociationCustom(
        $table,
        $alias,
        $inner_join = true,
        $on = null,
        $force_not_default = false,
        $identifier = 'id_sample_cart'
    ) {
        $table_alias = $table . '_shop';
        if (strpos($table, '.') !== false) {
            list($table_alias, $table) = explode('.', $table);
        }

        $asso_table = Shop::getAssoTable($table);
        if ($asso_table === false || $asso_table['type'] != 'shop') {
            return;
        }
        $sql = (($inner_join) ? ' INNER' : ' LEFT') . ' JOIN ' . _DB_PREFIX_ . $table . '_shop ' . $table_alias . '
        ON (' . $table_alias . '.' . $identifier . ' = ' . $alias . '.' . $identifier;
        if ((int) Shop::getContextShopID()) {
            $sql .= ' AND ' . $table_alias . '.id_shop = ' . (int) Shop::getContextShopID();
        } elseif (Shop::checkIdShopDefault($table) && !$force_not_default) {
            $sql .= ' AND ' . $table_alias . '.id_shop = ' . $alias . '.id_shop_default';
        } else {
            $sql .= ' AND ' . $table_alias . '.id_shop IN (' . implode(', ', Shop::getContextListShopID()) . ')';
        }
        $sql .= (($on) ? ' AND ' . $on : '') . ')';

        return $sql;
    }
}
