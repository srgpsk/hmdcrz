<?php
/**
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
class Cart extends CartCore
{
    /*
    * module: wksampleproduct
    * date: 2020-10-11 18:23:17
    * version: 1.1.0
    */
    public function updateQty(
        $quantity,
        $id_product,
        $id_product_attribute = null,
        $id_customization = false,
        $operator = 'up',
        $id_address_delivery = 0,
        Shop $shop = null,
        $auto_add_cart_rule = true
    ) {
        if (Module::isInstalled('wksampleproduct') && Module::isEnabled('wksampleproduct')) {
            if (!$shop) {
                $shop = Context::getContext()->shop;
            }
            if (Context::getContext()->customer->id) {
                if ($id_address_delivery == 0 && (int)$this->id_address_delivery) {
                    $id_address_delivery = $this->id_address_delivery;
                } elseif ($id_address_delivery == 0) {
                    $id_address_delivery = (int)Address::getFirstCustomerAddressId(
                        (int)Context::getContext()->customer->id
                    );
                } elseif (!Customer::customerHasAddress(Context::getContext()->customer->id, $id_address_delivery)) {
                    $id_address_delivery = 0;
                }
            }
            $quantity = (int)$quantity;
            $id_product = (int)$id_product;
            $id_product_attribute = (int)$id_product_attribute;
            $product = new Product($id_product, false, Configuration::get('PS_LANG_DEFAULT'), $shop->id);
            if ($id_product_attribute) {
                $combination = new Combination((int)$id_product_attribute);
                if ($combination->id_product != $id_product) {
                    return false;
                }
            }
            
            if (!empty($id_product_attribute)) {
                $minimal_quantity = (int)Attribute::getAttributeMinimalQty($id_product_attribute);
            } else {
                $minimal_quantity = (int)$product->minimal_quantity;
            }
            if (!Validate::isLoadedObject($product)) {
                die(Tools::displayError());
            }
            if (isset(self::$_nbProducts[$this->id])) {
                unset(self::$_nbProducts[$this->id]);
            }
            if (isset(self::$_totalWeight[$this->id])) {
                unset(self::$_totalWeight[$this->id]);
            }
            Hook::exec('actionBeforeCartUpdateQty', array(
                'cart' => $this,
                'product' => $product,
                'id_product_attribute' => $id_product_attribute,
                'id_customization' => $id_customization,
                'quantity' => $quantity,
                'operator' => $operator,
                'id_address_delivery' => $id_address_delivery,
                'shop' => $shop,
                'auto_add_cart_rule' => $auto_add_cart_rule,
            ));
            if ((int)$quantity <= 0) {
                return $this->deleteProduct(
                    $id_product,
                    $id_product_attribute,
                    (int)$id_customization,
                    0,
                    $auto_add_cart_rule
                );
            } elseif (!$product->available_for_order
            || (Configuration::get('PS_CATALOG_MODE')&& !defined('_PS_ADMIN_DIR_'))) {
                return false;
            } else {
                
                $result = $this->containsProduct(
                    $id_product,
                    $id_product_attribute,
                    (int)$id_customization,
                    (int)$id_address_delivery
                );
                $objSampleCart = new WkSampleCart();
                $sampleProduct = $objSampleCart->getSampleCartProduct($this->id, $id_product, $id_product_attribute);
                
                if ($result) {
                    if ($operator == 'up') {
                        $sql = 'SELECT stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity
                                FROM '._DB_PREFIX_.'product p
                                '.Product::sqlStock('p', $id_product_attribute, true, $shop).'
                                WHERE p.id_product = '.$id_product;
                        $result2 = Db::getInstance()->getRow($sql);
                        $product_qty = (int)$result2['quantity'];
                        if (Pack::isPack($id_product)) {
                            $product_qty = Pack::getQuantity($id_product, $id_product_attribute);
                        }
                        $new_qty = (int)$result['quantity'] + (int)$quantity;
                        $qty = '+ '.(int)$quantity;
                        if (!Product::isAvailableWhenOutOfStock((int)$result2['out_of_stock'])) {
                            if ($new_qty > $product_qty) {
                                return false;
                            }
                        }
                    } elseif ($operator == 'down') {
                        $qty = '- '.(int)$quantity;
                        $new_qty = (int)$result['quantity'] - (int)$quantity;
                        if ($new_qty < $minimal_quantity && $minimal_quantity > 1) {
                            if (!$sampleProduct) {
                                return -1;
                            }
                        }
                    } else {
                        return false;
                    }
                    
                    if ($new_qty <= 0) {
                        return $this->deleteProduct(
                            (int)$id_product,
                            (int)$id_product_attribute,
                            (int)$id_customization,
                            0,
                            $auto_add_cart_rule
                        );
                    } elseif ($new_qty < $minimal_quantity) {
                        if (!$sampleProduct) {
                            return -1;
                        } else {
                            Db::getInstance()->execute(
                                'UPDATE `'._DB_PREFIX_.'cart_product`
                                SET `quantity` = `quantity` '.$qty.', `date_add` = NOW()
                                WHERE `id_product` = '.(int)$id_product.
                                (!empty($id_product_attribute) ?
                                ' AND `id_product_attribute` = '.(int)$id_product_attribute : '').'
                                AND `id_cart` = '.(int)$this->id.
                                (Configuration::get('PS_ALLOW_MULTISHIPPING') && $this->isMultiAddressDelivery() ?
                                ' AND `id_address_delivery` = '.(int)$id_address_delivery : '').'
                                LIMIT 1'
                            );
                        }
                    } else {
                        Db::getInstance()->execute(
                            'UPDATE `'._DB_PREFIX_.'cart_product`
						    SET `quantity` = `quantity` '.$qty.', `date_add` = NOW()
						    WHERE `id_product` = '.(int)$id_product.
                            (!empty($id_product_attribute) ?
                            ' AND `id_product_attribute` = '.(int)$id_product_attribute : '').'
						    AND `id_cart` = '.(int)$this->id.
                            (Configuration::get('PS_ALLOW_MULTISHIPPING') && $this->isMultiAddressDelivery() ?
                            ' AND `id_address_delivery` = '.(int)$id_address_delivery : '').'
						    LIMIT 1'
                        );
                    }
                } elseif ($operator == 'up') {
                    
                    $sql = 'SELECT stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity
                            FROM '._DB_PREFIX_.'product p
                            '.Product::sqlStock('p', $id_product_attribute, true, $shop).'
                            WHERE p.id_product = '.$id_product;
                    $result2 = Db::getInstance()->getRow($sql);
                    if (Pack::isPack($id_product)) {
                        $result2['quantity'] = Pack::getQuantity($id_product, $id_product_attribute);
                    }
                    if (!Product::isAvailableWhenOutOfStock((int)$result2['out_of_stock'])) {
                        if ((int)$quantity > $result2['quantity']) {
                            return false;
                        }
                    }
                    if ((int)$quantity < $minimal_quantity) {
                        if (!$sampleProduct) {
                            return -1;
                        }
                    }
                    $result_add = Db::getInstance()->insert('cart_product', array(
                        'id_product' =>            (int)$id_product,
                        'id_product_attribute' =>    (int)$id_product_attribute,
                        'id_cart' =>                (int)$this->id,
                        'id_address_delivery' =>    (int)$id_address_delivery,
                        'id_shop' =>                $shop->id,
                        'quantity' =>                (int)$quantity,
                        'date_add' =>                date('Y-m-d H:i:s')
                    ));
                    if (!$result_add) {
                        return false;
                    }
                }
            }
            $this->_products = $this->getProducts(true);
            $this->update();
            $context = Context::getContext()->cloneContext();
            $context->cart = $this;
            Cache::clean('getContextualValue_*');
            if ($auto_add_cart_rule) {
                CartRule::autoAddToCart($context);
            }
            if ($product->customizable) {
                return $this->_updateCustomizationQuantity(
                    (int)$quantity,
                    (int)$id_customization,
                    (int)$id_product,
                    (int)$id_product_attribute,
                    (int)$id_address_delivery,
                    $operator
                );
            } else {
                return true;
            }
        } else {
            parent::updateQty(
                $quantity,
                $id_product,
                $id_product_attribute,
                $id_customization,
                $operator,
                $id_address_delivery,
                $shop,
                $auto_add_cart_rule
            );
        }
    }
    /*
    * module: wksampleproduct
    * date: 2020-10-11 18:23:17
    * version: 1.1.0
    */
    public function checkQuantities($return_product = false)
    {
        if (Module::isEnabled('wksampleproduct')) {
            if (Configuration::get('PS_CATALOG_MODE') && !defined('_PS_ADMIN_DIR_')) {
                return false;
            }
            foreach ($this->getProducts() as $product) {
                if (!$this->allow_seperated_package
                    && !$product['allow_oosp']
                    && StockAvailable::dependsOnStock($product['id_product'])
                    && $product['advanced_stock_management']
                    && (bool)Context::getContext()->customer->isLogged()
                    && ($delivery = $this->getDeliveryOption())
                    && !empty($delivery)
                ) {
                    $product['stock_quantity'] = StockManager::getStockByCarrier(
                        (int)$product['id_product'],
                        (int)$product['id_product_attribute'],
                        $delivery
                    );
                }
                if (!$product['active'] || !$product['available_for_order']
                    || (!$product['allow_oosp'] && $product['stock_quantity'] < $product['cart_quantity'])
                ) {
                    return $return_product ? $product : false;
                }
                require_once _PS_MODULE_DIR_.'wksampleproduct/classes/WkSampleProductMap.php';
                require_once _PS_MODULE_DIR_.'wksampleproduct/classes/WkSampleCart.php';
                $objSampleCart = new WkSampleCart();
                $sampleCart = $objSampleCart->getSampleCartProduct(
                    $this->id,
                    $product['id_product'],
                    $product['id_product_attribute']
                );
                if ($sampleCart) {
                    $objSampleProductMap = new WkSampleProductMap();
                    $sample = $objSampleProductMap->getSampleProduct($product['id_product']);
                    if ($sample && $sample['active']) {
                        if (($sample['max_cart_qty'] > 0) && ($product['cart_quantity'] > $sample['max_cart_qty'])) {
                            return $return_product ? $product : false;
                        }
                    } else {
                        return $return_product ? $product : false;
                    }
                }
            }
            return true;
        } else {
            return parent::checkQuantities($return_product);
        }
    }
}
