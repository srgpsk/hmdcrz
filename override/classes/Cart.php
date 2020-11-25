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
    protected $sampleCarrierReference = 109;
    protected $sampleCarrierId;
    public $sampleModuleEnabled;

    /**
     * Overriden to provide order splitting for orders that contain samples.
     */
    public function __construct($id = null, $id_lang = null)
    {
        parent::__construct($id, $id_lang);

        $this->sampleCarrierId = Carrier::getCarrierByReference($this->sampleCarrierReference)->id;
        $this->sampleModuleEnabled = Module::isEnabled('wksampleproduct');
    }

    /**
     *
     * Overriden to provide order splitting for orders that contain samples.
     * module: wksampleproduct
     *
     * Get products grouped by package and by addresses to be sent individualy (one package = one shipping cost).
     *
     * @return array array(
     *                   0 => array( // First address
     *                       0 => array(  // First package
     *                           'product_list' => array(...),
     *                           'carrier_list' => array(...),
     *                           'id_warehouse' => array(...),
     *                       ),
     *                   ),
     *               );
     * @todo Add avaibility check
     */
    public function getPackageList($flush = false)
    {
        static $cache = array();
        $cache_key = (int)$this->id . '_' . (int)$this->id_address_delivery;
        if (isset($cache[$cache_key]) && $cache[$cache_key] !== false && !$flush) {
            return $cache[$cache_key];
        }

        $product_list = $this->getProducts($flush);
        // Step 1 : Get product informations (warehouse_list and carrier_list), count warehouse
        // Determine the best warehouse to determine the packages
        // For that we count the number of time we can use a warehouse for a specific delivery address
        $warehouse_count_by_address = array();

        $stock_management_active = Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT');

        foreach ($product_list as &$product) {
            if ((int)$product['id_address_delivery'] == 0) {
                $product['id_address_delivery'] = (int)$this->id_address_delivery;
            }

            if (!isset($warehouse_count_by_address[$product['id_address_delivery']])) {
                $warehouse_count_by_address[$product['id_address_delivery']] = array();
            }

            $product['warehouse_list'] = array();

            if ($stock_management_active &&
                (int)$product['advanced_stock_management'] == 1) {
                $warehouse_list = Warehouse::getProductWarehouseList($product['id_product'], $product['id_product_attribute'], $this->id_shop);
                if (count($warehouse_list) == 0) {
                    $warehouse_list = Warehouse::getProductWarehouseList($product['id_product'], $product['id_product_attribute']);
                }
                // Does the product is in stock ?
                // If yes, get only warehouse where the product is in stock

                $warehouse_in_stock = array();
                $manager = StockManagerFactory::getManager();

                foreach ($warehouse_list as $key => $warehouse) {
                    $product_real_quantities = $manager->getProductRealQuantities(
                        $product['id_product'],
                        $product['id_product_attribute'],
                        array($warehouse['id_warehouse']),
                        true
                    );

                    if ($product_real_quantities > 0 || Pack::isPack((int)$product['id_product'])) {
                        $warehouse_in_stock[] = $warehouse;
                    }
                }

                if (!empty($warehouse_in_stock)) {
                    $warehouse_list = $warehouse_in_stock;
                    $product['in_stock'] = true;
                } else {
                    $product['in_stock'] = false;
                }
            } else {
                //simulate default warehouse
                $warehouse_list = array(0 => array('id_warehouse' => 0));
                $product['in_stock'] = StockAvailable::getQuantityAvailableByProduct($product['id_product'], $product['id_product_attribute']) > 0;
            }

            foreach ($warehouse_list as $warehouse) {
                $product['warehouse_list'][$warehouse['id_warehouse']] = $warehouse['id_warehouse'];
                if (!isset($warehouse_count_by_address[$product['id_address_delivery']][$warehouse['id_warehouse']])) {
                    $warehouse_count_by_address[$product['id_address_delivery']][$warehouse['id_warehouse']] = 0;
                }

                $warehouse_count_by_address[$product['id_address_delivery']][$warehouse['id_warehouse']]++;
            }
        }
        unset($product);

        arsort($warehouse_count_by_address);

        // Step 2 : Group product by warehouse
        $grouped_by_warehouse = array();

        foreach ($product_list as &$product) {
            if (!isset($grouped_by_warehouse[$product['id_address_delivery']])) {
                $grouped_by_warehouse[$product['id_address_delivery']] = array(
                    'in_stock' => array(),
                    'out_of_stock' => array(),
                );
            }

            $product['carrier_list'] = array();
            $id_warehouse = 0;
            foreach ($warehouse_count_by_address[$product['id_address_delivery']] as $id_war => $val) {
                if (array_key_exists((int)$id_war, $product['warehouse_list'])) {
                    $product['carrier_list'] = Tools::array_replace($product['carrier_list'], Carrier::getAvailableCarrierList(new Product($product['id_product']), $id_war, $product['id_address_delivery'], null, $this));
                    if (!$id_warehouse) {
                        $id_warehouse = (int)$id_war;
                    }
                }
            }

            if (!isset($grouped_by_warehouse[$product['id_address_delivery']]['in_stock'][$id_warehouse])) {
                $grouped_by_warehouse[$product['id_address_delivery']]['in_stock'][$id_warehouse] = array();
                $grouped_by_warehouse[$product['id_address_delivery']]['out_of_stock'][$id_warehouse] = array();
            }

            if (!$this->allow_seperated_package) {
                $key = 'in_stock';
            } else {
                $key = $product['in_stock'] ? 'in_stock' : 'out_of_stock';
                $product_quantity_in_stock = StockAvailable::getQuantityAvailableByProduct($product['id_product'], $product['id_product_attribute']);
                if ($product['in_stock'] && $product['cart_quantity'] > $product_quantity_in_stock) {
                    $out_stock_part = $product['cart_quantity'] - $product_quantity_in_stock;
                    $product_bis = $product;
                    $product_bis['cart_quantity'] = $out_stock_part;
                    $product_bis['in_stock'] = 0;
                    $product['cart_quantity'] -= $out_stock_part;
                    $grouped_by_warehouse[$product['id_address_delivery']]['out_of_stock'][$id_warehouse][] = $product_bis;
                }
            }

            if (empty($product['carrier_list'])) {
                $product['carrier_list'] = array(0 => 0);
            }

            $grouped_by_warehouse[$product['id_address_delivery']][$key][$id_warehouse][] = $product;
        }
        unset($product);

        /* wksample */
        if ($this->sampleModuleEnabled) {
            require_once _PS_MODULE_DIR_ . 'wksampleproduct/classes/WkSampleProductMap.php';
            require_once _PS_MODULE_DIR_ . 'wksampleproduct/classes/WkSampleCart.php';
            $objSampleCart = new WkSampleCart();
            $objSampleProductMap = new WkSampleProductMap();
        }
        $cartHasNormalProducts = false;


        // Step 3 : grouped product from grouped_by_warehouse by available carriers
        $grouped_by_carriers = array();
        foreach ($grouped_by_warehouse as $id_address_delivery => $products_in_stock_list) {
            if (!isset($grouped_by_carriers[$id_address_delivery])) {
                $grouped_by_carriers[$id_address_delivery] = array(
                    'in_stock' => array(),
                    'out_of_stock' => array(),
                );
            }
            foreach ($products_in_stock_list as $key => $warehouse_list) {
                if (!isset($grouped_by_carriers[$id_address_delivery][$key])) {
                    $grouped_by_carriers[$id_address_delivery][$key] = array();
                }
                foreach ($warehouse_list as $id_warehouse => $product_list) {
                    if (!isset($grouped_by_carriers[$id_address_delivery][$key][$id_warehouse])) {
                        $grouped_by_carriers[$id_address_delivery][$key][$id_warehouse] = array();
                    }

// wksample
//                    $cartHasNormalProducts = false;
                    foreach ($product_list as $product) {
                        $sampleCart = $objSampleCart->getSampleCartProduct(
                            $this->id,
                            $product['id_product'],
                            $product['id_product_attribute']
                        );
                        $sample = $objSampleProductMap->getSampleProduct($product['id_product']);
                        if ($sampleCart && $this->sampleCarrierId && $sample && $sample['active']) { // TODO clean up this mess :/
                            continue;
                        }

                        unset($product['carrier_list'][$this->sampleCarrierId]);
                        $normalCarriers = $product['carrier_list'];
                        $cartHasNormalProducts = true;
                        break;
                    }

                    foreach ($product_list as $product) {
// wksample
                        if ($this->sampleModuleEnabled) {
                            $sampleCart = $objSampleCart->getSampleCartProduct(
                                $this->id,
                                $product['id_product'],
                                $product['id_product_attribute']
                            );
                            if ($sampleCart && $this->sampleCarrierId) {
                                $sample = $objSampleProductMap->getSampleProduct($product['id_product']);
                                if ($sample && $sample['active']) {
                                    /**
                                     * If there is only one product - we need to show sample Free shipping carrier to have at least one carrier that could be selected
                                     * If there are many products + sample(s) - we set id_carrier to X to split cart into packages and then detect sample package in PaymentModule::validateOrder
                                     * X = 1000000, just a big number. -1 won't work due to validation rules, 0 - could be confused with 0s set by Presta in other places in code
                                     */
//                                    $product['carrier_list'] = $cartHasNormalProducts ?  [$this->getSampleFakeCarrierId() => $this->getSampleFakeCarrierId()] : [$this->sampleCarrierId => (string)$this->sampleCarrierId];
                                    $product['carrier_list'] = [$this->getSampleFakeCarrierId() => $this->getSampleFakeCarrierId()];
                                }
                            } else {
                                // delete sample carrier from other packages
                                unset($product['carrier_list'][$this->sampleCarrierId]);
                            }
                        }
                        $package_carriers_key = implode(',', $product['carrier_list']);

                        if (!isset($grouped_by_carriers[$id_address_delivery][$key][$id_warehouse][$package_carriers_key])) {
                            $grouped_by_carriers[$id_address_delivery][$key][$id_warehouse][$package_carriers_key] = array(
                                'product_list' => array(),
                                'carrier_list' => $product['carrier_list'],
                                'warehouse_list' => $product['warehouse_list'],
                            );
                        }

                        $grouped_by_carriers[$id_address_delivery][$key][$id_warehouse][$package_carriers_key]['product_list'][] = $product;
                    }
                }
            }
        }
//        Tools::dieObject($grouped_by_carriers);
        $package_list = array();
        // Step 4 : merge product from grouped_by_carriers into $package to minimize the number of package
        foreach ($grouped_by_carriers as $id_address_delivery => $products_in_stock_list) {
            if (!isset($package_list[$id_address_delivery])) {
                $package_list[$id_address_delivery] = array(
                    'in_stock' => array(),
                    'out_of_stock' => array(),
                );
            }

            foreach ($products_in_stock_list as $key => $warehouse_list) {
                if (!isset($package_list[$id_address_delivery][$key])) {
                    $package_list[$id_address_delivery][$key] = array();
                }
                // Count occurance of each carriers to minimize the number of packages
                $carrier_count = array();
                foreach ($warehouse_list as $id_warehouse => $products_grouped_by_carriers) {
                    foreach ($products_grouped_by_carriers as $data) {
                        foreach ($data['carrier_list'] as $id_carrier) {
                            if (!isset($carrier_count[$id_carrier])) {
                                $carrier_count[$id_carrier] = 0;
                            }
                            $carrier_count[$id_carrier]++;
                        }
                    }
                }
                arsort($carrier_count);
                foreach ($warehouse_list as $id_warehouse => $products_grouped_by_carriers) {
                    if (!isset($package_list[$id_address_delivery][$key][$id_warehouse])) {
                        $package_list[$id_address_delivery][$key][$id_warehouse] = array();
                    }
                    foreach ($products_grouped_by_carriers as $data) {
                        foreach ($carrier_count as $id_carrier => $rate) {
                            if (array_key_exists($id_carrier, $data['carrier_list'])) {
                                if (!isset($package_list[$id_address_delivery][$key][$id_warehouse][$id_carrier])) {
                                    $package_list[$id_address_delivery][$key][$id_warehouse][$id_carrier] = array(
                                        'carrier_list' => $data['carrier_list'],
                                        'warehouse_list' => $data['warehouse_list'],
                                        'product_list' => array(),
                                    );
                                }
                                $package_list[$id_address_delivery][$key][$id_warehouse][$id_carrier]['carrier_list'] =
                                    array_intersect($package_list[$id_address_delivery][$key][$id_warehouse][$id_carrier]['carrier_list'], $data['carrier_list']);
                                $package_list[$id_address_delivery][$key][$id_warehouse][$id_carrier]['product_list'] =
                                    array_merge($package_list[$id_address_delivery][$key][$id_warehouse][$id_carrier]['product_list'], $data['product_list']);

                                break;
                            }
                        }
                    }
                }
            }
        }

        // Step 5 : Reduce depth of $package_list
        $final_package_list = array();
        foreach ($package_list as $id_address_delivery => $products_in_stock_list) {
            if (!isset($final_package_list[$id_address_delivery])) {
                $final_package_list[$id_address_delivery] = array();
            }

            foreach ($products_in_stock_list as $key => $warehouse_list) {
                foreach ($warehouse_list as $id_warehouse => $products_grouped_by_carriers) {
                    foreach ($products_grouped_by_carriers as $data) {

                        // wksample
                        // at this point cart is already split to packages so we need to set carriers that we show to user
                        // we replace fake carrier we used to split packages with "normal" carriers we want to show on front
                        $carrier_list =  $data['carrier_list'];
                        $sample_package = false;
                        if(isset($data['carrier_list'][$this->getSampleFakeCarrierId()])) {
                            $sample_package = true;
                            $carrier_list = $cartHasNormalProducts ? $normalCarriers : [$this->sampleCarrierId => $this->sampleCarrierId];
                            foreach ($data['product_list'] as &$product) {
                                $product['carrier_list'] = $carrier_list;
                            }
                        }

                        $final_package_list[$id_address_delivery][] = array(
                            'sample_package' => $sample_package,
                            'product_list' => $data['product_list'],
                            'carrier_list' => $carrier_list,
                            'warehouse_list' => $data['warehouse_list'],
                            'id_warehouse' => $id_warehouse,
                        );
                    }
                }
            }
        }
        $cache[$cache_key] = $final_package_list;
//        Tools::dieObject([$cartHasNormalProducts, $normalCarriers, $final_package_list]);
        return $final_package_list;
    }

    /*
    * module: wksampleproduct
    * date: 2020-10-11 18:23:17
    * version: 1.1.0
    */
    public function updateQty(
        $quantity, $id_product, $id_product_attribute = null, $id_customization = false,
        $operator = 'up', $id_address_delivery = 0, $cart_price = 0, Shop $shop = null, $auto_add_cart_rule = true
    )
    {
        if (Module::isInstalled('wksampleproduct') && $this->sampleModuleEnabled) {
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
                || (Configuration::get('PS_CATALOG_MODE') && !defined('_PS_ADMIN_DIR_'))) {
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
                                FROM ' . _DB_PREFIX_ . 'product p
                                ' . Product::sqlStock('p', $id_product_attribute, true, $shop) . '
                                WHERE p.id_product = ' . $id_product;
                        $result2 = Db::getInstance()->getRow($sql);
                        $product_qty = (int)$result2['quantity'];
                        if (Pack::isPack($id_product)) {
                            $product_qty = Pack::getQuantity($id_product, $id_product_attribute);
                        }
                        $new_qty = (int)$result['quantity'] + (int)$quantity;
                        $qty = '+ ' . (int)$quantity;
                        if (!Product::isAvailableWhenOutOfStock((int)$result2['out_of_stock'])) {
                            if ($new_qty > $product_qty) {
                                return false;
                            }
                        }
                    } elseif ($operator == 'down') {
                        $qty = '- ' . (int)$quantity;
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
                                'UPDATE `' . _DB_PREFIX_ . 'cart_product`
                                SET `quantity` = `quantity` ' . $qty . ', `date_add` = NOW()
                                WHERE `id_product` = ' . (int)$id_product .
                                (!empty($id_product_attribute) ?
                                    ' AND `id_product_attribute` = ' . (int)$id_product_attribute : '') . '
                                AND `id_cart` = ' . (int)$this->id .
                                (Configuration::get('PS_ALLOW_MULTISHIPPING') && $this->isMultiAddressDelivery() ?
                                    ' AND `id_address_delivery` = ' . (int)$id_address_delivery : '') . '
                                LIMIT 1'
                            );
                        }
                    } else {
                        Db::getInstance()->execute(
                            'UPDATE `' . _DB_PREFIX_ . 'cart_product`
						    SET `quantity` = `quantity` ' . $qty . ', `date_add` = NOW()
						    WHERE `id_product` = ' . (int)$id_product .
                            (!empty($id_product_attribute) ?
                                ' AND `id_product_attribute` = ' . (int)$id_product_attribute : '') . '
						    AND `id_cart` = ' . (int)$this->id .
                            (Configuration::get('PS_ALLOW_MULTISHIPPING') && $this->isMultiAddressDelivery() ?
                                ' AND `id_address_delivery` = ' . (int)$id_address_delivery : '') . '
						    LIMIT 1'
                        );
                    }
                } elseif ($operator == 'up') {

                    $sql = 'SELECT stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity
                            FROM ' . _DB_PREFIX_ . 'product p
                            ' . Product::sqlStock('p', $id_product_attribute, true, $shop) . '
                            WHERE p.id_product = ' . $id_product;
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
                        'id_product' => (int)$id_product,
                        'id_product_attribute' => (int)$id_product_attribute,
                        'id_cart' => (int)$this->id,
                        'id_address_delivery' => (int)$id_address_delivery,
                        'id_shop' => $shop->id,
                        'quantity' => (int)$quantity,
                        'date_add' => date('Y-m-d H:i:s')
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
        if ($this->sampleModuleEnabled) {
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
                require_once _PS_MODULE_DIR_ . 'wksampleproduct/classes/WkSampleProductMap.php';
                require_once _PS_MODULE_DIR_ . 'wksampleproduct/classes/WkSampleCart.php';
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

    /**
     * @return int
     */
    public function getSampleCarrierId()
    {
        return $this->sampleCarrierId;
    }

    public function getSampleFakeCarrierId()
    {
        return 1000000;
//        return 0;
    }
}
