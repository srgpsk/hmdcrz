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

class WkSampleProductSampleSpecificPriceModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        // Getting Data
        if (Tools::getValue('ajax') && (Tools::getValue('action') == 'checkSampleQuantityInCart')) {
            $this->displayAjaxCheckSampleQuantityInCart();
        } else {
            // $ipa = Tools::getValue('id_attr');
            $idProduct = Tools::getValue('id_product');
            $idAttr = Tools::getValue('id_attr');
            $sampleCartObj = new WkSampleCart();
            $sampleCartObj->checkMaximumInCart($idProduct, $idAttr, $this->context);
            $this->context->cookie->sampleProductId = $idProduct;
            $this->context->cookie->sampleProductIdAttr = $idAttr;
            die(json_encode(array(
                'status' => 'ok',
                'msg' => $this->module->l('success'),
            )));
        }
    }

    public function displayAjaxCheckSampleQuantityInCart()
    {
        $idProduct = Tools::getValue('idProduct');
        $idAttr = Tools::getValue('idAttr');
        $objSampleProductMap = new WkSampleProductMap();
        $sample = $objSampleProductMap->getSampleProduct($idProduct);
        $cartQuantity = $this->getProductQuantityInCart($idProduct, $idAttr);
        $objSampleCart = new WkSampleCart();
        $sampleCart = $objSampleCart->getSampleCartProduct($this->context->cart->id, $idProduct, $idAttr);
        $standardAdded = false;
        $sampleAdded = false;
        if ($sampleCart) {
            //sample is added to cart
            $sampleAdded = true;
        } else {
            //sample is not added to cart
            if ($cartQuantity > 0) {
                $standardAdded = true;
            }
        }

        $cartExactQuantity = $this->getProductQuantityInCart($idProduct, $idAttr);
        $result = array(
            'standardInCart' => $standardAdded,
            'sampleAdded' => $sampleAdded,
            'sampleTemplate' => $this->module->displaySampleButton(new Product($idProduct), $idAttr),
            'allowedQty' => ($sample['max_cart_qty'] - $cartExactQuantity)
        );
        die(json_encode($result));
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
}
