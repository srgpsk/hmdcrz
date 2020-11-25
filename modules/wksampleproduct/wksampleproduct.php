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
 * @author    Webkul IN <support@webkul.com>
 * @copyright 2010-2020 Webkul IN
 * @license   https://store.webkul.com/license.html
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once "classes/WkSampleProductMap.php";
require_once "classes/WkSampleCart.php";

class WkSampleProduct extends Module
{
    public $html = '';

    public function __construct()
    {
        $this->name = 'wksampleproduct';
        $this->tab = 'front_office_features';
        $this->version = '1.1.0';
        $this->author = 'Webkul';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        $this->secure_key = Tools::encrypt($this->name);
        parent::__construct();
        $this->displayName = $this->l('Sample Product');
        $this->description = $this->l('Allow customer to buy sample product');
        $this->confirmUninstall = $this->l('Are you sure?');
//        $this->registerHook('displayProductListFunctionalButtonsBottom');

    }

    public function getContent()
    {
        if (Tools::isSubmit('submit' . $this->name)) {
            $this->postValidation();
            if (!count($this->context->controller->errors)) {
                $this->postProcess();
            }
        } else {
            $this->html .= '<br />';
        }
        $this->context->controller->addJS(_MODULE_DIR_ . $this->name . '/views/js/wksampleproductconfig.js');
        $this->html .= $this->renderForm();
        return $this->html;
    }

    /**
     * Validate post data
     */
    protected function postValidation()
    {
        if (Tools::isSubmit('submitGlobalSample')) {
            $maxSample = Tools::getValue('WK_GLOBAL_SAMPLE');
            if (Tools::getValue('WK_GLOBAL_SAMPLE')) {
                $priceType = Tools::getValue('WK_GLOBAL_SAMPLE_PRICE_TYPE');
                $sampleAmount = Tools::getValue('WK_GLOBAL_SAMPLE_AMOUNT');
                $samplePrice = Tools::getValue('WK_GLOBAL_SAMPLE_PRICE');
                $samplePercent = Tools::getValue('WK_GLOBAL_SAMPLE_PERCENT');
                $sampleBtn = Tools::getValue('WK_GLOBAL_SAMPLE_BUTTON_LABEL');
                $maxInCart = Tools::getValue('WK_GLOBAL_SAMPLE_IN_CART');
                if ($maxInCart && !Validate::isUnsignedInt($maxInCart)) {
                    $this->context->controller->errors[] =
                        $this->l('Maximum global sample quantity in one cart should be a number');
                }
                if ($priceType == 2) {
                    if (!$sampleAmount || !Tools::strlen($sampleAmount) || ((float)$sampleAmount == 0)) {
                        $this->context->controller->errors[] =
                            $this->l('Please enter global sample deduction amount.');
                    } elseif (!Validate::isUnsignedFloat($sampleAmount)) {
                        $this->context->controller->errors[] =
                            $this->l('Global sample deduction amount should be a number.');
                    }
                } elseif ($priceType == 3) {
                    if (!$samplePercent || !Tools::strlen($samplePercent) || ((float)$samplePercent == 0)) {
                        $this->context->controller->errors[] =
                            $this->l('Please enter global sample deduction percent.');
                    } elseif (!Validate::isUnsignedFloat($samplePercent)) {
                        $this->context->controller->errors[] =
                            $this->l('Global sample deduction percent should be a number.');
                    }
                } elseif ($priceType == 4) {
                    if (!$samplePrice || !Tools::strlen($samplePrice) || ((int)$samplePrice == 0)) {
                        $this->context->controller->errors[] =
                            $this->l('Please enter global sample custom price.');
                    } elseif (!Validate::isUnsignedFloat($samplePrice)) {
                        $this->context->controller->errors[] =
                            $this->l('Global sample custom price should be a number.');
                    }
                }
                if (!$sampleBtn || !Tools::strlen($sampleBtn)) {
                    $this->context->controller->errors[] =
                        $this->l('Please enter global sample button title.');
                }
            }
        } else {
            $maxSample = Tools::getValue('WK_MAX_SAMPLE_IN_CART');
            if ($maxSample && !Validate::isUnsignedInt($maxSample)) {
                $this->context->controller->errors[] =
                    $this->l('Maximum Sample Product in one cart should be a number');
            }
            $sampleBtnBgColor = Tools::getValue('WK_SAMPLE_BUTTON_BG_COLOR');
            if (!$sampleBtnBgColor || !Tools::strlen(trim($sampleBtnBgColor))) {
                $this->context->controller->errors[] = $this->l('Please select sample button background color.');
            } elseif (!Validate::isColor($sampleBtnBgColor)) {
                $this->context->controller->errors[] = $this->l('Please enter correct sample button background color.');
            }
            $sampleBtnTextColor = Tools::getValue('WK_SAMPLE_BUTTON_TEXT_COLOR');
            if (!$sampleBtnTextColor || !Tools::strlen(trim($sampleBtnTextColor))) {
                $this->context->controller->errors[] = $this->l('Please select sample button title color.');
            } elseif (!Validate::isColor($sampleBtnTextColor)) {
                $this->context->controller->errors[] = $this->l('Please enter correct sample button title color.');
            }
        }
    }


    /**
     * Save form data
     */
    protected function postProcess()
    {
        if (Tools::isSubmit('submitGlobalSample')) {
            Configuration::updateValue('WK_GLOBAL_SAMPLE', Tools::getValue('WK_GLOBAL_SAMPLE'));
            if (Tools::getValue('WK_GLOBAL_SAMPLE')) {
                Configuration::updateValue(
                    'WK_GLOBAL_SAMPLE_IN_CART',
                    (int)Tools::getValue('WK_GLOBAL_SAMPLE_IN_CART')
                );
                Configuration::updateValue(
                    'WK_GLOBAL_SAMPLE_PRICE_TYPE',
                    Tools::getValue('WK_GLOBAL_SAMPLE_PRICE_TYPE')
                );
                Configuration::updateValue(
                    'WK_GLOBAL_SAMPLE_BUTTON_LABEL',
                    Tools::getValue('WK_GLOBAL_SAMPLE_BUTTON_LABEL')
                );
                Configuration::updateValue('WK_GLOBAL_SAMPLE_AMOUNT', Tools::getValue('WK_GLOBAL_SAMPLE_AMOUNT'));
                Configuration::updateValue('WK_GLOBAL_SAMPLE_PRICE', Tools::getValue('WK_GLOBAL_SAMPLE_PRICE'));
                Configuration::updateValue('WK_GLOBAL_SAMPLE_TAX', Tools::getValue('WK_GLOBAL_SAMPLE_TAX'));
                Configuration::updateValue('WK_GLOBAL_SAMPLE_PERCENT', Tools::getValue('WK_GLOBAL_SAMPLE_PERCENT'));
                Configuration::updateValue('WK_GLOBAL_SAMPLE_DESC', Tools::getValue('WK_GLOBAL_SAMPLE_DESC'), true);
            }
        } else {
            Configuration::updateValue('WK_MAX_SAMPLE_IN_CART', Tools::getValue('WK_MAX_SAMPLE_IN_CART'));
            Configuration::updateValue('WK_SAMPLE_STOCK_UPDATE', Tools::getValue('WK_SAMPLE_STOCK_UPDATE'));
            Configuration::updateValue('WK_SAMPLE_LOGGED_ONLY', Tools::getValue('WK_SAMPLE_LOGGED_ONLY'));
            Configuration::updateValue('WK_SAMPLE_QUANTITY_SPIN', Tools::getValue('WK_SAMPLE_QUANTITY_SPIN'));
            Configuration::updateValue('WK_SAMPLE_BUTTON_TEXT_COLOR', Tools::getValue('WK_SAMPLE_BUTTON_TEXT_COLOR'));
            Configuration::updateValue('WK_SAMPLE_BUTTON_BG_COLOR', Tools::getValue('WK_SAMPLE_BUTTON_BG_COLOR'));
            /*if ($groupBox = Tools::getValue('groupBox')) {
                Configuration::updateValue('WK_SAMPLE_GROUP', json_encode($groupBox));
            } else {
                Configuration::updateValue('WK_SAMPLE_GROUP', json_encode(array()));
            }*/
        }

        $this->context->controller->confirmations[] = $this->l('Successfully saved.');
    }

    public function renderForm()
    {
        $fieldsForm = array();
        //$groups = Group::getGroups($this->context->language->id, true);
        $fieldsForm[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Basic Configuration'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Maximum Sample Product in one cart'),
                    'name' => 'WK_MAX_SAMPLE_IN_CART',
                    'col' => '2',
                    'desc' => $this->l('Leave empty or fill zero if no limitation'),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Stock update on Sample Product order'),
                    'name' => 'WK_SAMPLE_STOCK_UPDATE',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Only logged in customer can order'),
                    'name' => 'WK_SAMPLE_LOGGED_ONLY',
                    'desc' => $this->l('If No, guest can also order'),
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Show sample quantity selector'),
                    'name' => 'WK_SAMPLE_QUANTITY_SPIN',
                    'hint' =>
                        $this->l('Select if you want users to select sample quantity or add 1 on each button click.'),
                    'desc' => $this->l('If disabled, 1 sample will be added to cart on each sample button click.'),
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'color',
                    'label' => $this->l('Sample button background color.'),
                    'name' => 'WK_SAMPLE_BUTTON_BG_COLOR'
                ),
                array(
                    'type' => 'color',
                    'label' => $this->l('Sample button title color.'),
                    'name' => 'WK_SAMPLE_BUTTON_TEXT_COLOR'
                ),
                // @Todo in future
                /*array(
                    'type' => 'group',
                    'label' => $this->l('Group access'),
                    'values' => $groups,
                    'name' => 'groupBox',
                    'col' => '6',
                    'hint' => $this->l('Select all the groups that you would like to apply for order sample product')
                ),*/
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );
        $fieldsForm[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('Global sample'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Global sample'),
                    'desc' => $this->l('All products will be offered with a sample with below settings.'),
                    'name' => 'WK_GLOBAL_SAMPLE',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Maximum quantity of sample product in one cart'),
                    'form_group_class' => 'wk_global_sample_block',
                    'name' => 'WK_GLOBAL_SAMPLE_IN_CART',
                    'col' => '2',
                    'desc' => $this->l('Leave empty or fill zero if no limitation'),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Price type'),
                    'name' => 'WK_GLOBAL_SAMPLE_PRICE_TYPE',
                    'form_group_class' => 'wk_global_sample_block wk_price_type_wrap',
                    'is_bool' => true,
                    'options' => array(
                        'id' => 'id_option',
                        'name' => 'name',
                        'query' => array(
                            array(
                                'id_option' => 1,
                                'name' => $this->l('Product Standard Price')
                            ),
                            array(
                                'id_option' => 2,
                                'name' => $this->l('Deduct fix amount from product price')
                            ),
                            array(
                                'id_option' => 3,
                                'name' => $this->l('A percentage of product price')
                            ),
                            array(
                                'id_option' => 4,
                                'name' => $this->l('Custom Price')
                            ),
                            array(
                                'id_option' => 5,
                                'name' => $this->l('Free Sample')
                            )
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Amount'),
                    'prefix' => $this->context->currency->sign,
                    'name' => 'WK_GLOBAL_SAMPLE_AMOUNT',
                    'form_group_class' => 'wk_global_sample_block wk_price_type_amount',
                    'col' => '2',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Sample Price'),
                    'prefix' => $this->context->currency->sign,
                    'name' => 'WK_GLOBAL_SAMPLE_PRICE',
                    'form_group_class' => 'wk_global_sample_block wk_price_type_customprice',
                    'col' => '2',
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Tax'),
                    'name' => 'WK_GLOBAL_SAMPLE_TAX',
                    'form_group_class' => 'wk_global_sample_block wk_price_type_tax',
                    'is_bool' => true,
                    'options' => array(
                        'id' => 'id_option',
                        'name' => 'name',
                        'query' => array(
                            array(
                                'id_option' => 0,
                                'name' => $this->l('Tax excluded')
                            ),
                            array(
                                'id_option' => 1,
                                'name' => $this->l('Tax Included')
                            )
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Percentage'),
                    'prefix' => '%',
                    'name' => 'WK_GLOBAL_SAMPLE_PERCENT',
                    'form_group_class' => 'wk_global_sample_block wk_price_type_percent',
                    'col' => '2',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Sample button title'),
                    'name' => 'WK_GLOBAL_SAMPLE_BUTTON_LABEL',
                    'form_group_class' => 'wk_global_sample_block',
                    'col' => '2',
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Sample description'),
                    'form_group_class' => 'wk_global_sample_block',
                    'name' => 'WK_GLOBAL_SAMPLE_DESC',
                    'autoload_rte' => true
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'submitGlobalSample'
            ),
        );
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit' . $this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm($fieldsForm);
    }

    public function getConfigFormValues()
    {
        $keys = array(
            'WK_MAX_SAMPLE_IN_CART',
            'WK_SAMPLE_STOCK_UPDATE',
            'WK_SAMPLE_LOGGED_ONLY',
            'WK_SAMPLE_QUANTITY_SPIN',
            'WK_SAMPLE_BUTTON_BG_COLOR',
            'WK_SAMPLE_BUTTON_TEXT_COLOR',
            'WK_GLOBAL_SAMPLE',
            'WK_GLOBAL_SAMPLE_IN_CART',
            'WK_GLOBAL_SAMPLE_PRICE_TYPE',
            'WK_GLOBAL_SAMPLE_AMOUNT',
            'WK_GLOBAL_SAMPLE_PRICE',
            'WK_GLOBAL_SAMPLE_TAX',
            'WK_GLOBAL_SAMPLE_PERCENT',
            'WK_GLOBAL_SAMPLE_BUTTON_LABEL',
            'WK_GLOBAL_SAMPLE_DESC'
        );
        $formValues = array();
        foreach ($keys as $key) {
            $formValues[$key] = Configuration::get($key);
        }
        return $formValues;
    }

    public function hookDisplayRightColumnProduct($params)
    {
        $idProduct = Tools::getValue('id_product');
        $product = new Product($idProduct);
        if (Configuration::get('WK_SAMPLE_LOGGED_ONLY')) {
            if (isset($this->context->customer->id)) {
                return $this->displaySampleButton($product);
            }
        } else {
            return $this->displaySampleButton($product);
        }
    }

    public function hookDisplayProductDeliveryTime($params)
    {
        return $this->hookDisplayRightColumnProduct($params);
    }

    public function hookDisplayProductListFunctionalButtonsBottom_($params) {
        $idProduct = $params['product']['id_product'];
        $product = new Product($idProduct);


      /*      $objSampleCart = new WkSampleCart();
            $sampleCart = $objSampleCart->getSampleCartProduct(
                $this->context->cart->id,
                $idProduct,
                (int)Product::getDefaultAttribute($idProduct)
            );
            $prodQty = $this->getProductQuantityInCart($idProduct, (int)Product::getDefaultAttribute($idProduct));
            $objSampleProductMap = new WkSampleProductMap();
            $sample = $objSampleProductMap->getSampleProduct($idProduct);
            Media::addJsDef(
                array(
                    'maxSampleQty' => $sample['max_cart_qty'],
                    'productAvailableQuantity' => Product::getQuantity(
                        $idProduct,
                        Product::getDefaultAttribute($idProduct)
                    ),
                    'addToCartEnabled' => $this->isAvailableWhenOutOfStock(
                        $idProduct,
                        (int)Product::getDefaultAttribute($idProduct)
                    ),
                    'allowedQuantity' => $sample['max_cart_qty'] - $prodQty,
                    'idPsProduct' => $idProduct,
                    'wk_sp_token' => Tools::getToken(false)
                )
            );
            if ($sampleCart) {
                Media::addJsDef(array('sampleInCart' => 1));
            }*/


        if (Configuration::get('WK_SAMPLE_LOGGED_ONLY')) {
            if (isset($this->context->customer->id)) {
                return $this->displaySampleButton(product);
            }
        } else {
            return $this->displaySampleButton($product);
        }
    }

    private function getTaxIncludedSampleAmount($sample, $idTaxRulesGroup, $psProductPrice)
    {
        $sampleAmount = ($sample['price_type'] == 2) ? $sample['amount'] : (($psProductPrice * $sample['amount']) / 100);
        if (isset($this->context->customer->id_default_group)) {
            $taxMethod = Group::getPriceDisplayMethod((int)$this->context->customer->id_default_group);
        } else {
            $taxMethod = Group::getDefaultPriceDisplayMethod();
        }
        if (($sample['price_tax'] == $taxMethod) && $idTaxRulesGroup) {
            //tax included amount deduction
            $sampleCartObj = new WkSampleCart();
            if ($sample['price_type'] == 2) {
                if ($taxMethod) {
                    //excluded price display && sampleAmount is included (remove tax from sample)
                    $sampleAmount = $sampleCartObj->removeTaxes($sample['amount'], $idTaxRulesGroup, $this->context);
                } else {
                    //included price display && sampleAmount is excluded (add tax to sample)
                    $sampleAmount = $sampleCartObj->addTaxToAmount($sample['amount'], $idTaxRulesGroup, $this->context);
                }
            }
        }
        return $sampleAmount;
    }

    public function getSamplePrice($sample)
    {
        $product = new Product($sample['id_product']);
        if (isset($this->context->customer->id_default_group)) {
            $taxMethod = Group::getPriceDisplayMethod((int)$this->context->customer->id_default_group);
        } else {
            $taxMethod = Group::getDefaultPriceDisplayMethod();
        }
        //Add specific price discount
        $productPrice = Product::getPriceStatic($sample['id_product'], !$taxMethod);
        $amountToDeduct = $this->getTaxIncludedSampleAmount($sample, $product->id_tax_rules_group, $productPrice);
        $samplePrice = '';
        if (($sample['price_type'] == 2)
            || ($sample['price_type'] == 3)) {
            $samplePrice = $productPrice - $amountToDeduct;
        } elseif ($sample['price_type'] == 4) {
            if ($sample['price_tax'] && $taxMethod) {
                $sampleCartObj = new WkSampleCart();
                $samplePrice = $sampleCartObj->removeTaxes(
                    $sample['price'],
                    $product->id_tax_rules_group,
                    $this->context
                );
            } else {
                $samplePrice = $sample['price'];
            }
        } elseif ($sample['price_type'] == 5) {
            $samplePrice = 0;//$this->l('Free');
        }
        return $samplePrice;
    }

    private function getProductQuantityInCart($idProduct, $idAttr = false)
    {
        $allProducts = $this->context->cart->getProducts();
        $totalQuantity = 0;
        foreach ($allProducts as $cartProduct) {
            if ($cartProduct['id_product'] == $idProduct) {
                if (!$idAttr || ($cartProduct['id_product_attribute'] == $idAttr)) {
                    $totalQuantity += $cartProduct['cart_quantity'];
                }
            }
        }
        return $totalQuantity;
    }

    private function isAvailableWhenOutOfStock($idProduct, $idAttr)
    {
        $objProduct = new Product($idProduct, false, $this->context->language->id);
        $shouldShowButton = $objProduct->available_for_order;
        if ($shouldShowButton) {
            $defaultComb = Product::getDefaultAttribute($objProduct->id);
            $cartQuantity = (float)$this->getProductQuantityInCart($idProduct, $idAttr);
            $quantity = Product::getQuantity($objProduct->id, $defaultComb);
            $quantity -= $cartQuantity;
            switch ($objProduct->out_of_stock) {
                case 0:
                    $shouldShowButton = ($quantity > 0);
                    break;
                case 1:
                    $shouldShowButton = true;
                    break;
                default:
                    $shouldShowButton = ($quantity > 0) || (Configuration::get('PS_ORDER_OUT_OF_STOCK') == 1);
                    break;
            }
        }

        return $shouldShowButton;
    }

    public function displaySampleButton($product, $idAttr = false)
    {
        if (!$idAttr) {
            $idAttr = Product::getDefaultAttribute($product->id);
        }
        $objSampleProductMap = new WkSampleProductMap();
        $sample = $objSampleProductMap->getSampleProduct($product->id);
        if ($sample && $sample['active']) {
            if (isset($this->context->customer->id_default_group)) {
                $taxMethod = Group::getPriceDisplayMethod((int)$this->context->customer->id_default_group);
            } else {
                $taxMethod = Group::getDefaultPriceDisplayMethod();
            }
            $samplePrice = $this->getSamplePrice($sample);
            if ($samplePrice > 0) {
                $this->context->smarty->assign('samplePrice', Tools::displayPrice($samplePrice));
            }
            $objSampleCart = new WkSampleCart();
            $sampleCart = $objSampleCart->getSampleCartProduct($this->context->cart->id, $product->id, $idAttr);
            $cartQuantity = $this->getProductQuantityInCart($product->id);
            $standardAdded = false;
            $sampleAdded = false;
            if ($sampleCart) {
                $sampleAdded = true;
                if ($sample['price_type'] == 1) {
                    $productPrice = Product::getPriceStatic($product->id, true);
                    $this->context->smarty->assign('samplePrice', Tools::displayPrice($productPrice));
                }
                //sample is added to cart
                $cartExactQuantity = $this->getProductQuantityInCart($product->id, $idAttr);
                if (($sample['max_cart_qty'] > 0) && ($cartExactQuantity >= $sample['max_cart_qty'])) {
                    $this->context->smarty->assign('maxSampleAdded', 1);
                }
            } else {
                //sample is not added to cart
                if ($cartQuantity > 0) {
                    $standardAdded = true;
                    $this->context->smarty->assign('maxSampleAdded', 1);
                }
            }
            $this->context->smarty->assign(array(
                'sample' => $sample,
                'idProduct' => $product->id,
                'standardAdded' => $standardAdded,
                'sampleAdded' => $sampleAdded,
                'displayQuantitySpin' => Configuration::get('WK_SAMPLE_QUANTITY_SPIN'),
                'sampleBtnTextColor' => Configuration::get('WK_SAMPLE_BUTTON_TEXT_COLOR'),
                'sampleBtnBgColor' => Configuration::get('WK_SAMPLE_BUTTON_BG_COLOR'),
                'addToCartEnabled' => $this->isAvailableWhenOutOfStock($product->id, $idAttr),
                'isTaxExclDisplay' => $taxMethod,
                'idCustomer' => $this->context->customer->id,
                'cartPageURL' => $this->context->link->getPageLink(
                    'cart',
                    null,
                    null,
                    array(
                        'add' => 1,
                        'id_product' => $product->id,
                        'ipa' => 0,
                        'token' => Tools::getToken(false)
                    )
                ),
            ));
            return $this->display(__FILE__, 'productadditionalinfo.tpl');
        }
    }

    /**
     * Display sample coulmn in Order render list
     *
     * @param Array $params row data
     */
    public function hookActionAdminOrdersListingFieldsModifier($params)
    {
        if (isset($params['select'])) {
            $params['select'] .= ', coalesce(wsc.sample, 0) as sample ';
        }
        if (isset($params['join'])) {
            ' ON (apo.`id_order` = a.`id_order`)';
            $params['join'] .= ' LEFT JOIN ' . _DB_PREFIX_ . 'wk_sample_cart wsc ON (a.id_order = wsc.id_order)';
        }
        if (isset($params['join'])) {
            $params['group_by'] .= ' GROUP BY a.`id_order`';
        }
        $params['fields']['sample'] = array(
            'title' => $this->l('Contains Sample'),
            'type' => 'bool',
            'align' => 'text-center',
            'orderby' => false,
            'search' => true,
            'havingFilter' => true,
        );
    }

    public function hookActionObjectOrderDetailAddAfter($params)
    {
        if ($params && isset($params['object']->id_order)) {
            $paramOrder = new Order($params['object']->id_order);
            $cart = new Cart($paramOrder->id_cart);
            $allOrders = Order::getByReference($paramOrder->reference)->getResults();
            $objSampleCart = new WkSampleCart();
            $objSampleProductMap = new WkSampleProductMap();
            foreach ($allOrders as $eachOrder) {
                $products = $eachOrder->getProducts();
                foreach ($products as $product) {
                    $sample = $objSampleProductMap->getSampleProduct($product['product_id']);
                    $sampleCart = $objSampleCart->getSampleCartProduct(
                        $cart->id,
                        $product['product_id'],
                        $product['product_attribute_id']
                    );
                    if ($sample && $sampleCart) {
                        if (!Configuration::get('WK_SAMPLE_STOCK_UPDATE')) {
                            StockAvailable::updateQuantity(
                                $product['product_id'],
                                $product['product_attribute_id'],
                                $product['product_quantity']
                            );
                        }
                        // update cart order
                        $objSampleCart->updateCartOrder(
                            $cart->id,
                            $product['product_id'],
                            $eachOrder->id,
                            $product['product_attribute_id']
                        );
                        // delete specific price
                        $objSampleCart->deleteSampleSpecificPrice(
                            $cart->id,
                            $product['product_id'],
                            $product['product_attribute_id']
                        );
                    }
                }
            }
        }
    }

    /**
     * Display Extra Information on Product Edit
     *
     * @param array $params this product details
     * @return tpl
     */
    public function hookDisplayAdminProductsExtra($params)
    {
        $idProduct = Tools::getValue('id_product');
        if ($idProduct) {
            if (Module::isInstalled('marketplace') && WkMpSellerProduct::getSellerProductByPsIdProduct($idProduct)) {
                $this->context->controller->warnings[] =
                    $this->l('This is a marketplace product. You can create the sample in Marketplace products page.');
            } else {
                $objSampleProductMap = new WkSampleProductMap();
                $sample = $objSampleProductMap->getSampleProduct($idProduct);
                $product = new Product($idProduct);
                if ($sample) {
                    $this->context->smarty->assign(array(
                        'sample' => $sample,
                    ));
                }
                $fileDir = _PS_MODULE_DIR_ . $this->name . '/views/samples';
                $files = glob($fileDir . "/sample_" . Tools::getValue('id_product') . "*");
                if (count($files) > 0) {
                    $sampleFileArr = explode('/', $files[0]);
                    $sampleFileName = end($sampleFileArr);
                    $this->context->smarty->assign('sampleFileName', $sampleFileName);
                }
                $product_download = new ProductDownload();
                $id_product_download = $product_download->getIdFromIdProduct(
                    $this->context->controller->getFieldValue($product, 'id')
                );
                if ($id_product_download) {
                    $product_download = new ProductDownload($id_product_download);
                }
                $this->context->smarty->assign(array(
                    'sign' => $this->context->currency->sign,
                    'isVirtual' => $product->is_virtual,
                    'idProduct' => $idProduct,
                    'isExists' => (count($files)),
                    'shouldUpload' => $product_download->id
                        && $product_download->filename &&
                        $product_download->display_filename,
                    'sampleJSUrl' => _MODULE_DIR_ . 'wksampleproduct/views/js/wksampleproducttab.js',
                    'productPrice' => Tools::displayPrice($product->getPrice(false)),
                ));
                return $this->display(__FILE__, 'adminproduct.tpl');
            }
        } else {
            $this->context->controller->warnings[] =
                $this->l('You must save this product before adding sample product');
        }
    }

    //Custom hook
    public function hookActionSampleProductDownloadBefore(&$params)
    {
        $info = $params[0];
        $objSampleCart = new WkSampleCart();
        $sampleOrder = $objSampleCart->getSampleOrderProduct(
            $info['id_order'],
            $info['id_product'],
            $info['id_product_attribute']
        );
        if ($sampleOrder) {
            $filePath = _PS_MODULE_DIR_ . $this->name . '/views/samples/';
            $params[2] = 'sample_' . $info['id_product'];
            $files = glob($filePath . $params[2] . "*");
            if (count($files) > 0) {
                $fileNameParts = explode('/', $files[0]);
                $fileToDownload = end($fileNameParts);
                $params[1] = $filePath . $fileToDownload;
                $params[2] = $fileToDownload;
            } else {
                $params[2] = false;
                $params[1] = $this->l('This sample does not have a file.');
            }
        }
    }

    public function hookActionAdminControllerSetMedia()
    {
        if ($this->context->controller->controller_name == 'AdminProducts') {
            $maxUploadSize = (Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') <
                Configuration::get('PS_LIMIT_UPLOAD_FILE_VALUE')) ?
                Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') : Configuration::get('PS_LIMIT_UPLOAD_FILE_VALUE');
            Media::addJsDef(
                array(
                    'sampleMaxSizeError' => $this->l('File is too large.'),
                    'sampleAttachmentMaxSize' => $maxUploadSize
                )
            );
        }
    }

    /**
     * Save product extra information
     *
     * @param array $params this product details
     */
    public function hookActionProductSave($params)
    {
        if ($params['id_product']) {
            $maxCartQty = Tools::getValue('max_cart_qty');
            $priceType = Tools::getValue('wk_sample_price_type');
            $priceTax = Tools::getValue('wk_sample_price_tax');
            $price = Tools::getValue('wk_sample_price');
            $sampleAmount = Tools::getValue('sample_amount');
            $btnLabel = Tools::getValue('sample_btn_label');
            $desc = Tools::getValue('wk_sample_desc');
            $status = Tools::getValue('sample_active');
            $hasVirtualFile = Tools::getValue('sample_file_active');
            $psProduct = $params['product'];
            if ($maxCartQty && !Validate::isUnsignedInt($maxCartQty)) {
                $this->context->controller->errors[] = $this->l('Maximum quantity in cart should be a positive number');
            }

            if ($priceType == 2 || $priceType == 3) {
                if ($sampleAmount && !Validate::isUnsignedFloat($sampleAmount)) {
                    $this->context->controller->errors[] = $this->l('Amount should be positive');
                } else {
                    $productPrice = $psProduct->getPriceStatic($params['id_product'], true);
                    $sampleAmountWithTax = $sampleAmount;
                    if ($priceType == 3) {
                        $sampleAmountPercent = ($productPrice * $sampleAmount) / 100;
                        if ($sampleAmountPercent > $productPrice) {
                            $this->context->controller->errors[] =
                                $this->l('Amount should be less than product price');
                        }
                    } else {
                        //Amount reduction
                        if ($priceTax == 1) {
                            if ($sampleAmount > $productPrice) {
                                $this->context->controller->errors[] =
                                    $this->l('Amount should be less than product price');
                            }
                        } else {
                            $taxRules = TaxRule::getTaxRulesByGroupId(
                                $this->context->language->id,
                                $psProduct->id_tax_rules_group
                            );
                            foreach ($taxRules as $taxArr) {
                                //If any taxincluded amount is greater than product price
                                $taxRate = $taxArr['rate'];
                                $sampleAmountWithTax = $sampleAmount + (($sampleAmount * $taxRate) / 100);
                                if ($sampleAmountWithTax > $productPrice) {
                                    $this->context->controller->errors[] =
                                        $this->l('Amount should be less than product price');
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            if ($priceType == 4) {
                if ($price && !Validate::isPrice($price)) {
                    $this->context->controller->errors[] = $this->l('Price is not valid');
                }
            }

            if ($btnLabel && !Validate::isGenericName($btnLabel)) {
                $this->context->controller->errors[] = $this->l('Button label is not valid');
            }

            if (Tools::strlen($desc) && $this->checkScriptInHtml($desc)) {
                $this->context->controller->errors[] = $this->l('Please enter valid description');
            }

            if ($status && $psProduct->is_virtual && $hasVirtualFile) {
                $fileDir = _PS_MODULE_DIR_ . $this->name . '/views/samples';
                $files = glob($fileDir . "/sample_" . Tools::getValue('id_product') . "*");
                if (count($files) == 0) {
                    if (Tools::getValue('deleteSampleFile') == $psProduct->id) {
                        $this->context->controller->errors[] = $this->l('Sample file does not exist.');
                    } else {
                        if (isset($_FILES['uploaded_sample_file'])) {
                            $sampleFile = $_FILES['uploaded_sample_file'];
                            if (($sampleFile['error'] == 4) || ($sampleFile['size'] == 0)) {
                                $this->context->controller->errors[] =
                                    $this->l('Sample file is required for virtual product sample.');
                            } elseif ($sampleFile['size'] / 1000000 >= Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE')) {
                                $this->context->controller->errors[] = $this->l('Sample file is too large.');
                            }
                        } else {
                            $this->context->controller->errors[] =
                                $this->l('Sample file is required for virtual product sample.');
                        }
                    }
                }
            }
            if (empty($this->context->controller->errors)) {
                $objSampleProductMap = new WkSampleProductMap();
                $sampleProduct = $objSampleProductMap->getSampleProduct($params['id_product']);
                if ($sampleProduct && $sampleProduct['id_sample_product']) {
                    $sample = new WkSampleProductMap($sampleProduct['id_sample_product']);
                } else {
                    $sample = new WkSampleProductMap();
                }

                $sample->id_product = $params['id_product'];
                $sample->id_product_attribute = $psProduct->cache_default_attribute;
                $sample->max_cart_qty = $maxCartQty;
                $sample->price_type = $priceType;
                $sample->price_tax = ($priceType == 3) ? 1 : $priceTax;
                $sample->amount = $sampleAmount;
                $sample->price = $price;
                $sample->button_label = $btnLabel;
                $sample->description = $desc;
                $sample->active = $status;
                $sample->save();
                if ($sample->save() && $psProduct->is_virtual) {
                    $sampleFile = $_FILES['uploaded_sample_file'];
                    $fileDir = _PS_MODULE_DIR_ . $this->name . '/views/samples';
                    $files = glob($fileDir . "/sample_" . Tools::getValue('id_product') . "*");
                    if ($hasVirtualFile) {
                        if (Tools::getValue('deleteSampleFile') == $psProduct->id) {
                            //delete file
                            foreach ($files as $file) {
                                unlink($file);
                            }
                        } elseif (($sampleFile['error'] == 0)
                            && ($sampleFile['size'] > 0)) {
                            if (!file_exists($fileDir)) {
                                @mkdir($fileDir . '/', 0777, true);
                            }
                            if (!file_exists($fileDir . '/index.php')) {
                                @copy(
                                    _PS_MODULE_DIR_ . $this->name . '/index.php',
                                    $fileDir . '/index.php'
                                );
                            }

                            $name = $sampleFile['name'];
                            $nameParts = explode('.', $name);
                            if (count($nameParts) > 1) {
                                $ext = end($nameParts);
                            } else {
                                $ext = '';
                            }

                            $files = glob($fileDir . "/sample_" . Tools::getValue('id_product') . "*");
                            foreach ($files as $file) {
                                unlink($file);
                            }

                            $helper = new HelperUploader('uploaded_sample_file');
                            $file = $helper->setPostMaxSize(Tools::getOctets(ini_get('upload_max_filesize')))
                                ->setSavePath($fileDir . '/')
                                ->upload($sampleFile, 'sample_' . Tools::getValue('id_product') . '.' . $ext);
                            if (Tools::strlen($file['error']) && $file['error'] != 0) {
                                $this->context->controller->errors[] =
                                    $file['error'];
                            }
                        }
                    } else {
                        foreach ($files as $file) {
                            unlink($file);
                        }
                    }
                }
            }
        }
        $this->context->controller->display_tab = 'ModuleWksampleproduct';
    }

    public function removeScriptFromHtml($html)
    {
        $dom = new DOMDocument();
        $dom->loadHTML(htmlspecialchars_decode($html));
        $script = $dom->getElementsByTagName('script');
        $remove = array();
        foreach ($script as $item) {
            $remove[] = $item;
        }
        foreach ($remove as $item) {
            $item->parentNode->removeChild($item);
        }
        return $dom->saveHTML();
    }

    public function checkScriptInHtml($html)
    {
        $dom = new DOMDocument();
        $dom->loadHTML(htmlspecialchars_decode($html));
        $script = $dom->getElementsByTagName('script');
        return $script->length;
    }

    public function hookDisplayHeader()
    {
        Media::addJsDef(
            array(
                'loginreq' => $this->l('To buy preorder product you need to login first.'),
                'sampleSpecificPriceURL' => $this->context->link->getModuleLink(
                    'wksampleproduct',
                    'samplespecificprice'
                ),
                'sampleCartPage' => $this->context->link->getModuleLink(
                    'wksampleproduct',
                    'samplespecificprice'
                ),
            )
        );
    }

    public function modifyTables()
    {
        $sql1 = 'ALTER TABLE ps_wk_sample_cart ADD COLUMN `id_product_attribute` int(10) unsigned NOT NULL';
        return Db::getInstance()->execute($sql1);
    }

    public function hookActionFrontControllerSetMedia($params)
    {
        if ($idProduct = Tools::getValue('id_product')) {
            $objSampleCart = new WkSampleCart();
            $sampleCart = $objSampleCart->getSampleCartProduct(
                $this->context->cart->id,
                $idProduct,
                (int)Product::getDefaultAttribute($idProduct)
            );
            $prodQty = $this->getProductQuantityInCart($idProduct, (int)Product::getDefaultAttribute($idProduct));
            $objSampleProductMap = new WkSampleProductMap();
            $sample = $objSampleProductMap->getSampleProduct($idProduct);
            Media::addJsDef(
                array(
                    'maxSampleQty' => $sample['max_cart_qty'],
                    'productAvailableQuantity' => Product::getQuantity(
                        $idProduct,
                        Product::getDefaultAttribute($idProduct)
                    ),
                    'addToCartEnabled' => $this->isAvailableWhenOutOfStock(
                        $idProduct,
                        (int)Product::getDefaultAttribute($idProduct)
                    ),
                    'allowedQuantity' => $sample['max_cart_qty'] - $prodQty,
                    'idPsProduct' => $idProduct,
                    'wk_sp_token' => Tools::getToken(false)
                )
            );
            if ($sampleCart) {
                Media::addJsDef(array('sampleInCart' => 1));
            }
        }
        $this->context->controller->addCSS(
            $this->_path . '/views/css/wksampleproduct.css'
        );
        $this->context->controller->addJS(
            $this->_path . '/views/js/wksampleproduct.js'
        );
    }

    public function hookDisplayCartExtraProductActions($params)
    {
        $idProduct = $params['product']['id_product'];
        $objSampleCart = new WkSampleCart();
        $sampleCart = $objSampleCart->getSampleCartProduct(
            $this->context->cart->id,
            $idProduct,
            $params['product']['id_product_attribute']
        );
        if ($sampleCart) {
            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . $this->name . '/views/templates/hook/cartsamplenotifier.tpl'
            );
        }
    }


    /**
     * Delete Sample from our map if delete
     *
     * @param array $params
     */

    public function hookActionAfterDeleteProductInCart($params)
    {
        $objSampleCart = new WkSampleCart();
        $objSampleCart->deleteSampleSpecificPrice(
            $params['id_cart'],
            $params['id_product'],
            $params['id_product_attribute']
        );
        $objSampleCart->deleteSampleCart($params['id_cart'], $params['id_product'], $params['id_product_attribute']);
    }

    /**
     * Prevent to add main product if sample already in cart
     * Prevent to update quantity of sample if ristricted
     *
     * @param array $params
     */
    public function hookActionBeforeCartUpdateQty($params)
    {
        $cart = $params['cart'];
        $product = $params['product'];
        $objSampleCart = new WkSampleCart();
        if (isset($params['operator'])) {
            // Do not run if operator is down, means quantity is decreasing
            // @todo:: prevent to add if sample is already in cart
            // WkSampleCart::getSampleCartProduct()
            if ($params['operator'] == 'up') {
                $isSampleProduct = $objSampleCart->getSampleCartProduct(
                    $cart->id,
                    $product->id,
                    $params['id_product_attribute']
                );
                if (isset($this->context->cookie->sampleProductId)
                    && isset($this->context->cookie->sampleProductIdAttr)
                    && ($this->context->cookie->sampleProductId == $product->id)
                    && ($this->context->cookie->sampleProductIdAttr == (int)$params['id_product_attribute'])
                    && !$isSampleProduct
                ) {
                    unset($this->context->cookie->sampleProductId);
                    unset($this->context->cookie->sampleProductIdAttr);
                    $sampleCart = new WkSampleCart();
                    $sampleCart->validateSampleCart($params['id_product_attribute'], $product->id, $params['quantity']);
                }
                if ($isSampleProduct) {
                    if ($objSampleCart->checkProductQtyInCart(
                        $cart,
                        $product->id,
                        $params['quantity'],
                        $params['id_product_attribute']
                    )) {
                        die(Tools::jsonEncode(array(
                            'hasError' => true,
                            'errors' => array(
                                $this->l('Max quantity exceeded for this sample product in cart')
                            ),
                        )));
                    }
                }
            } elseif ($params['operator'] == 'down') {
                $quantity = $this->getProductQuantityInCart($product->id, $params['id_product_attribute']);
                $reduceQuantity = $params['quantity'];
                if ($quantity == $reduceQuantity) {
                    $objSampleCart->deleteSampleSpecificPrice(
                        $cart->id,
                        $product->id,
                        $params['id_product_attribute']
                    );
                    $objSampleCart->deleteSampleCart($cart->id, $product->id, $params['id_product_attribute']);
                }
            }
        } else {
            // just for security if in case operator is not found
            if ($objSampleCart->checkProductQtyInCart($cart, $product->id, 0, $params['id_product_attribute'])) {
                die(Tools::jsonEncode(array(
                    'hasError' => true,
                    'errors' => array($this->l('Max quantity exceeded for this sample product in cart')),
                )));
            }
        }
    }

    public function hookDisplayAdminOrder($params)
    {
        $order = new Order($params['id_order']);
        $products = $order->getProducts();
        $sample = array();
        $objSampleCart = new WkSampleCart();
        foreach ($products as $product) {
            $sampleOrder = $objSampleCart->getSampleOrderProduct(
                $params['id_order'],
                $product['product_id'],
                $product['product_attribute_id']
            );
            if ($sampleOrder) {
                $sample[] = $product;
            }
        }

        if (!empty($sample)) {
            foreach ($sample as &$product) {
                $product['sample_price'] = Tools::displayPrice($product['total_price_tax_incl']);
            }
            $this->context->smarty->assign(array(
                'sample' => $sample,
                'sampleCount' => count($sample),
            ));
            return $this->display(__FILE__, 'displayadminorder.tpl');
        }
    }

    public function registerModuleHook()
    {
        return $this->registerHook(array(
            'displayAdminProductsExtra',
//            'displayRightColumnProduct',
            'displayProductDeliveryTime',
            'displayProductListFunctionalButtonsBottom',
            'actionProductSave',
            'actionBeforeCartUpdateQty',
            'actionCartSave',
            'actionFrontControllerSetMedia',
            'displayHeader',
            'actionAfterDeleteProductInCart',
            'displayOrderConfirmation',
            'actionAdminOrdersListingFieldsModifier',
            'displayAdminOrder',
            'actionSampleProductDownloadBefore',
            'displayCartExtraProductActions',
            'actionAdminControllerSetMedia',
            'actionObjectOrderDetailAddAfter'
        ));
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        if (!parent::install()
            || !$this->registerModuleHook()
            || !$this->createTable()
            || !$this->defaultConfig()
        ) {
            return false;
        }

        return true;
    }

    public function defaultConfig()
    {
        $configs = array(
            'WK_SAMPLE_BUTTON_BG_COLOR' => '#428BCA',
            'WK_SAMPLE_QUANTITY_SPIN' => 1,
            'WK_SAMPLE_BUTTON_TEXT_COLOR' => '#FFFFFF'
        );
        foreach ($configs as $key => $value) {
            if (!Configuration::updateValue($key, $value)) {
                return false;
            }
        }
        return true;
    }

    public function deleteConfigKey()
    {
        $keys = array(
            'WK_MAX_SAMPLE_IN_CART',
            'WK_SAMPLE_STOCK_UPDATE',
            'WK_SAMPLE_LOGGED_ONLY',
            'WK_SAMPLE_QUANTITY_SPIN',
            'WK_SAMPLE_BUTTON_BG_COLOR',
            'WK_SAMPLE_BUTTON_TEXT_COLOR'
        );

        foreach ($keys as $key) {
            if (!Configuration::deleteByName($key)) {
                return false;
            }
        }

        $sampleVirtualFiles = glob(_PS_MODULE_DIR_ . $this->name . '/views/samples/sample_*');
        foreach ($sampleVirtualFiles as $sample) {
            if (!unlink($sample)) {
                return false;
            }
        }
        return true;
    }

    protected function deleteTable()
    {
        return Db::getInstance()->execute('
            DROP TABLE IF EXISTS
            `' . _DB_PREFIX_ . 'wk_sample_product`,
            `' . _DB_PREFIX_ . 'wk_sample_product_shop`,
            `' . _DB_PREFIX_ . 'wk_sample_cart`,
            `' . _DB_PREFIX_ . 'wk_sample_cart_shop`');
    }

    public function uninstall()
    {
        if (!parent::uninstall()
            || !$this->deleteTable()
            || !$this->deleteConfigKey()) {
            return false;
        }
        return true;
    }

    protected function createTable()
    {
        $success = true;
        $db = Db::getInstance();
        $queries = $this->getDbTableQueries();

        foreach ($queries as $query) {
            $success &= $db->execute($query);
        }

        return $success;
    }

    private function getDbTableQueries()
    {
        return array(
            "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "wk_sample_product` (
                `id_sample_product` int(10) unsigned NOT NULL auto_increment,
                `id_product` int(10) unsigned NOT NULL,
                `id_product_attribute` int(10) unsigned NOT NULL,
                `max_cart_qty` int(10) unsigned NOT NULL,
                `price_type` int(10) unsigned NOT NULL,
                `price_tax` int(10) unsigned NOT NULL,
                `amount` decimal(17,2) unsigned NOT NULL,
                `price` decimal(17,2) NOT NULL,
                `button_label` varchar(32) NOT NULL,
                `description` TEXT,
                `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
            PRIMARY KEY  (`id_sample_product`)
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "wk_sample_product_shop` (
                `id_sample_product` int(10) unsigned NOT NULL,
                `id_shop` int(10) unsigned NOT NULL,
                `id_product` int(10) unsigned NOT NULL,
                `id_product_attribute` int(10) unsigned NOT NULL,
                `max_cart_qty` int(10) unsigned NOT NULL,
                `price_type` int(10) unsigned NOT NULL,
                `price_tax` int(10) unsigned NOT NULL,
                `amount` decimal(17,2) unsigned NOT NULL,
                `price` decimal(17,2) NOT NULL,
                `button_label` varchar(32) NOT NULL,
                `description` TEXT,
                `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY  (`id_sample_product`, `id_shop`)
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "wk_sample_cart` (
                `id_sample_cart` int(10) unsigned NOT NULL auto_increment,
                `id_cart` int(10) unsigned NOT NULL,
                `id_order` int(10) unsigned NOT NULL,
                `id_product` int(10) unsigned NOT NULL,
                `id_product_attribute` int(10) unsigned NOT NULL,
                `id_specific_price` int(10) unsigned NOT NULL,
                `sample` tinyint(1) unsigned NOT NULL DEFAULT '1',
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
            PRIMARY KEY  (`id_sample_cart`)
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "wk_sample_cart_shop` (
                `id_sample_cart` int(10) unsigned NOT NULL auto_increment,
                `id_shop` int(10) unsigned NOT NULL,
                `id_cart` int(10) unsigned NOT NULL,
                `id_order` int(10) unsigned NOT NULL,
                `id_product` int(10) unsigned NOT NULL,
                `id_product_attribute` int(10) unsigned NOT NULL,
                `id_specific_price` int(10) unsigned NOT NULL,
                `sample` tinyint(1) unsigned NOT NULL DEFAULT '1',
            PRIMARY KEY  (`id_sample_cart`, `id_shop`)
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8",
            "ALTER TABLE `" . _DB_PREFIX_ . "wk_sample_product` ADD UNIQUE `id_product, id_product_attribute` (`id_product`, `id_product_attribute`)",
            "ALTER TABLE `" . _DB_PREFIX_ ."wk_sample_product_shop` ADD UNIQUE `id_product, id_product_attribute` (`id_product`, `id_product_attribute`)",
        );
    }
}
