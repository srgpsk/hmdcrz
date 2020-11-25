<?php


class AdminImportController extends AdminImportControllerCore
{
    /**
     * Provides Samples import functionality
     */
    private const SAMPLE_PREFIX = 'wks_';
    private $prefixed_sample_fields;
    private $prefixed_sample_fields_defaults;

    public function __construct()
    {
        parent::__construct();

        if((int)Tools::getValue('entity') === $this->entities[$this->l('Products')]) {
            $sample_fields = array(
                'max_cart_qty' => array('label' => $this->l('WKS Max Cart Qty'), 'help' => 'Default is 5, Integer, WkSampleProduct field'),
                'price_type' => array('label' => $this->l('WKS Price Type'), 'help' => 'By default is set to = 5 for Free Sample, can be ignored in import, integer, WkSampleProduct field'),
//                'price_tax' =>  array('label' => $this->l(''), 'help' => 'isBool'),
//                'amount' => array('label' => $this->l(''), 'help' => 'isUnsignedFloat'),
//                'price' => array('label' => $this->l(''), 'help' => 'isPrice'),
                'button_label' => array('label' => $this->l('WKS Button Label'), 'help' => 'Default is \'Buy Sample\', leave empty for default text, string, WkSampleProduct field'),
                'description' => array('label' => $this->l('WKS Description'), 'help' => 'Leave empty for no text, string, WkSampleProduct field'),
                'active' => array('label' => $this->l('WKS Active'), 'help' => '0 = No, 1 = Yes, integer, WkSampleProduct field'),
            );
            $this->prefixed_sample_fields = $this->addSamplePrefix($sample_fields);
            $this->available_fields += $this->prefixed_sample_fields;

            $sample_fields_defaults = array(
                'max_cart_qty' => 5,
                'price_type' => 5,
                'price_tax' => 0,
                'amount' => 0.00,
                'price' => 0,
                'button_label' => 'Buy Sample'
            );
            $this->prefixed_sample_fields_defaults = $this->addSamplePrefix($sample_fields_defaults);
            self::$default_values += $this->prefixed_sample_fields_defaults;
        }
    }

    /**
     * @param array $fields
     * @return array
     */
    private function addSamplePrefix(Array $fields): array
    {
        $prefixed_fields = [];
        foreach ($fields as $key => $field) {
            $prefixed_fields[self::SAMPLE_PREFIX . $key] = $field;
        }

        return $prefixed_fields;
    }

    /**
     * @param array $prefixed_fields
     * @return array
     */
    private function removeSamplePrefix(Array $prefixed_fields): array
    {
        $fields = [];
        foreach ($prefixed_fields as $key => $field) {
            $fields[str_replace(self::SAMPLE_PREFIX, '', $key)] = $field;
        }

        return $fields;
    }
    /**
     * Provides Samples import functionality
     * For changes look at the CSV file loop closing } custom import added before that
     */
    public function productImport()
    {
        if (!defined('PS_MASS_PRODUCT_CREATION')) {
            define('PS_MASS_PRODUCT_CREATION', true);
        }

        $this->receiveTab();
        $handle = $this->openCsvFile();
        $default_language_id = (int)Configuration::get('PS_LANG_DEFAULT');
        $id_lang = Language::getIdByIso(Tools::getValue('iso_lang'));
        if (!Validate::isUnsignedId($id_lang)) {
            $id_lang = $default_language_id;
        }
        AdminImportController::setLocale();
        $shop_ids = Shop::getCompleteListOfShopsID();

        $convert = Tools::getValue('convert');
        $force_ids = Tools::getValue('forceIDs');
        $match_ref = Tools::getValue('match_ref');
        $regenerate = Tools::getValue('regenerate');
        $shop_is_feature_active = Shop::isFeatureActive();
        Module::setBatchMode(true);

        for ($current_line = 0; $line = fgetcsv($handle, MAX_LINE_SIZE, $this->separator); $current_line++) {
            if ($convert) {
                $line = $this->utf8EncodeArray($line);
            }
            $info = AdminImportController::getMaskedRow($line);

            if ($force_ids && isset($info['id']) && (int)$info['id']) {
                $product = new Product((int)$info['id']);
            } elseif ($match_ref && array_key_exists('reference', $info)) {
                $datas = Db::getInstance()->getRow('
                        SELECT p.`id_product`
                        FROM `'._DB_PREFIX_.'product` p
                        '.Shop::addSqlAssociation('product', 'p').'
                        WHERE p.`reference` = "'.pSQL($info['reference']).'"
                    ', false);
                if (isset($datas['id_product']) && $datas['id_product']) {
                    $product = new Product((int)$datas['id_product']);
                } else {
                    $product = new Product();
                }
            } elseif (array_key_exists('id', $info) && (int)$info['id'] && Product::existsInDatabase((int)$info['id'], 'product')) {
                $product = new Product((int)$info['id']);
            } else {
                $product = new Product();
            }

            $update_advanced_stock_management_value = false;
            if (isset($product->id) && $product->id && Product::existsInDatabase((int)$product->id, 'product')) {
                $product->loadStockData();
                $update_advanced_stock_management_value = true;
                $category_data = Product::getProductCategories((int)$product->id);

                if (is_array($category_data)) {
                    foreach ($category_data as $tmp) {
                        if (!isset($product->category) || !$product->category || is_array($product->category)) {
                            $product->category[] = $tmp;
                        }
                    }
                }
            }

            AdminImportController::setEntityDefaultValues($product);
            AdminImportController::arrayWalk($info, array('AdminImportController', 'fillInfo'), $product);

            if (!$shop_is_feature_active) {
                $product->shop = (int)Configuration::get('PS_SHOP_DEFAULT');
            } elseif (!isset($product->shop) || empty($product->shop)) {
                $product->shop = implode($this->multiple_value_separator, Shop::getContextListShopID());
            }

            if (!$shop_is_feature_active) {
                $product->id_shop_default = (int)Configuration::get('PS_SHOP_DEFAULT');
            } else {
                $product->id_shop_default = (int)Context::getContext()->shop->id;
            }

            // link product to shops
            $product->id_shop_list = array();
            foreach (explode($this->multiple_value_separator, $product->shop) as $shop) {
                if (!empty($shop) && !is_numeric($shop)) {
                    $product->id_shop_list[] = Shop::getIdByName($shop);
                } elseif (!empty($shop)) {
                    $product->id_shop_list[] = $shop;
                }
            }

            if ((int)$product->id_tax_rules_group != 0) {
                if (Validate::isLoadedObject(new TaxRulesGroup($product->id_tax_rules_group))) {
                    $address = $this->context->shop->getAddress();
                    $tax_manager = TaxManagerFactory::getManager($address, $product->id_tax_rules_group);
                    $product_tax_calculator = $tax_manager->getTaxCalculator();
                    $product->tax_rate = $product_tax_calculator->getTotalRate();
                } else {
                    $this->addProductWarning(
                        'id_tax_rules_group',
                        $product->id_tax_rules_group,
                        Tools::displayError('Invalid tax rule group ID. You first need to create a group with this ID.')
                    );
                }
            }
            if (isset($product->manufacturer) && is_numeric($product->manufacturer) && Manufacturer::manufacturerExists((int)$product->manufacturer)) {
                $product->id_manufacturer = (int)$product->manufacturer;
            } elseif (isset($product->manufacturer) && is_string($product->manufacturer) && !empty($product->manufacturer)) {
                if ($manufacturer = Manufacturer::getIdByName($product->manufacturer)) {
                    $product->id_manufacturer = (int)$manufacturer;
                } else {
                    $manufacturer = new Manufacturer();
                    $manufacturer->name = $product->manufacturer;
                    $manufacturer->active = true;

                    if (($field_error = $manufacturer->validateFields(UNFRIENDLY_ERROR, true)) === true &&
                        ($lang_field_error = $manufacturer->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true && $manufacturer->add()) {
                        $product->id_manufacturer = (int)$manufacturer->id;
                        $manufacturer->associateTo($product->id_shop_list);
                    } else {
                        $this->errors[] = sprintf(
                            Tools::displayError('%1$s (ID: %2$s) cannot be saved'),
                            $manufacturer->name,
                            (isset($manufacturer->id) && !empty($manufacturer->id))? $manufacturer->id : 'null'
                        );
                        $this->errors[] = ($field_error !== true ? $field_error : '').(isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '').
                            Db::getInstance()->getMsgError();
                    }
                }
            }

            if (isset($product->supplier) && is_numeric($product->supplier) && Supplier::supplierExists((int)$product->supplier)) {
                $product->id_supplier = (int)$product->supplier;
            } elseif (isset($product->supplier) && is_string($product->supplier) && !empty($product->supplier)) {
                if ($supplier = Supplier::getIdByName($product->supplier)) {
                    $product->id_supplier = (int)$supplier;
                } else {
                    $supplier = new Supplier();
                    $supplier->name = $product->supplier;
                    $supplier->active = true;

                    if (($field_error = $supplier->validateFields(UNFRIENDLY_ERROR, true)) === true &&
                        ($lang_field_error = $supplier->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true && $supplier->add()) {
                        $product->id_supplier = (int)$supplier->id;
                        $supplier->associateTo($product->id_shop_list);
                    } else {
                        $this->errors[] = sprintf(
                            Tools::displayError('%1$s (ID: %2$s) cannot be saved'),
                            $supplier->name,
                            (isset($supplier->id) && !empty($supplier->id))? $supplier->id : 'null'
                        );
                        $this->errors[] = ($field_error !== true ? $field_error : '').(isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '').
                            Db::getInstance()->getMsgError();
                    }
                }
            }

            if (isset($product->price_tex) && !isset($product->price_tin)) {
                $product->price = $product->price_tex;
            } elseif (isset($product->price_tin) && !isset($product->price_tex)) {
                $product->price = $product->price_tin;
                // If a tax is already included in price, withdraw it from price
                if ($product->tax_rate) {
                    $product->price = (float)number_format($product->price / (1 + $product->tax_rate / 100), 6, '.', '');
                }
            } elseif (isset($product->price_tin) && isset($product->price_tex)) {
                $product->price = $product->price_tex;
            }

            if (!Configuration::get('PS_USE_ECOTAX')) {
                $product->ecotax = 0;
            }

            if (isset($product->category) && is_array($product->category) && count($product->category)) {
                $product->id_category = array(); // Reset default values array
                foreach ($product->category as $value) {
                    if (is_numeric($value)) {
                        if (Category::categoryExists((int)$value)) {
                            $product->id_category[] = (int)$value;
                        } else {
                            $category_to_create = new Category();
                            $category_to_create->id = (int)$value;
                            $category_to_create->name = AdminImportController::createMultiLangField($value);
                            $category_to_create->active = 1;
                            $category_to_create->id_parent = Configuration::get('PS_HOME_CATEGORY'); // Default parent is home for unknown category to create
                            $category_link_rewrite = Tools::link_rewrite($category_to_create->name[$default_language_id]);
                            $category_to_create->link_rewrite = AdminImportController::createMultiLangField($category_link_rewrite);
                            if (($field_error = $category_to_create->validateFields(UNFRIENDLY_ERROR, true)) === true &&
                                ($lang_field_error = $category_to_create->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true && $category_to_create->add()) {
                                $product->id_category[] = (int)$category_to_create->id;
                            } else {
                                $this->errors[] = sprintf(
                                    Tools::displayError('%1$s (ID: %2$s) cannot be saved'),
                                    $category_to_create->name[$default_language_id],
                                    (isset($category_to_create->id) && !empty($category_to_create->id))? $category_to_create->id : 'null'
                                );
                                $this->errors[] = ($field_error !== true ? $field_error : '').(isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '').
                                    Db::getInstance()->getMsgError();
                            }
                        }
                    } elseif (is_string($value) && !empty($value)) {
                        $category = Category::searchByPath($default_language_id, trim($value), $this, 'productImportCreateCat');
                        if ($category['id_category']) {
                            $product->id_category[] = (int)$category['id_category'];
                        } else {
                            $this->errors[] = sprintf(Tools::displayError('%1$s cannot be saved'), trim($value));
                        }
                    }
                }
                $product->id_category = array_values(array_unique($product->id_category));
            }

            // Will update default category if there is none set here. Home if no category at all.
            if (!isset($product->id_category_default) || !$product->id_category_default) {
                // this if will avoid ereasing default category if category column is not present in the CSV file (or ignored)
                if (isset($product->id_category[0])) {
                    $product->id_category_default = (int)$product->id_category[0];
                } else {
                    $defaultProductShop = new Shop($product->id_shop_default);
                    $product->id_category_default = Category::getRootCategory(null, Validate::isLoadedObject($defaultProductShop)?$defaultProductShop:null)->id;
                }
            }

            $link_rewrite = (is_array($product->link_rewrite) && isset($product->link_rewrite[$id_lang])) ? trim($product->link_rewrite[$id_lang]) : '';
            $valid_link = Validate::isLinkRewrite($link_rewrite);

            if ((isset($product->link_rewrite[$id_lang]) && empty($product->link_rewrite[$id_lang])) || !$valid_link) {
                $link_rewrite = Tools::link_rewrite($product->name[$id_lang]);
                if ($link_rewrite == '') {
                    $link_rewrite = 'friendly-url-autogeneration-failed';
                }
            }

            if (!$valid_link) {
                $this->warnings[] = sprintf(
                    Tools::displayError('Rewrite link for %1$s (ID: %2$s) was re-written as %3$s.'),
                    $product->name[$id_lang],
                    (isset($info['id']) && !empty($info['id']))? $info['id'] : 'null',
                    $link_rewrite
                );
            }

            if (!(is_array($product->link_rewrite) && count($product->link_rewrite))) {
                $product->link_rewrite = AdminImportController::createMultiLangField($link_rewrite);
            } else {
                $product->link_rewrite[(int)$id_lang] = $link_rewrite;
            }

            // replace the value of separator by coma
            if ($this->multiple_value_separator != ',') {
                if (is_array($product->meta_keywords)) {
                    foreach ($product->meta_keywords as &$meta_keyword) {
                        if (!empty($meta_keyword)) {
                            $meta_keyword = str_replace($this->multiple_value_separator, ',', $meta_keyword);
                        }
                    }
                }
            }

            // Convert comma into dot for all floating values
            foreach (Product::$definition['fields'] as $key => $array) {
                if ($array['type'] == Product::TYPE_FLOAT) {
                    $product->{$key} = str_replace(',', '.', $product->{$key});
                }
            }

            // Indexation is already 0 if it's a new product, but not if it's an update
            $product->indexed = 0;
            $productExistsInDatabase = false;

            if ($product->id && Product::existsInDatabase((int)$product->id, 'product')) {
                $productExistsInDatabase = true;
            }

            if (($match_ref && $product->reference && $product->existsRefInDatabase($product->reference)) || $productExistsInDatabase) {
                $product->date_upd = date('Y-m-d H:i:s');
            }

            $res = false;
            $field_error = $product->validateFields(UNFRIENDLY_ERROR, true);
            $lang_field_error = $product->validateFieldsLang(UNFRIENDLY_ERROR, true);
            if ($field_error === true && $lang_field_error === true) {
                // check quantity
                if ($product->quantity == null) {
                    $product->quantity = 0;
                }

                // If match ref is specified && ref product && ref product already in base, trying to update
                if ($match_ref && $product->reference && $product->existsRefInDatabase($product->reference)) {
                    $datas = Db::getInstance()->getRow('
                        SELECT product_shop.`date_add`, p.`id_product`
                        FROM `'._DB_PREFIX_.'product` p
                        '.Shop::addSqlAssociation('product', 'p').'
                        WHERE p.`reference` = "'.pSQL($product->reference).'"
                    ', false);
                    $product->id = (int)$datas['id_product'];
                    $product->date_add = pSQL($datas['date_add']);
                    $res = $product->update();
                } // Else If id product && id product already in base, trying to update
                elseif ($productExistsInDatabase) {
                    $datas = Db::getInstance()->getRow('
                        SELECT product_shop.`date_add`
                        FROM `'._DB_PREFIX_.'product` p
                        '.Shop::addSqlAssociation('product', 'p').'
                        WHERE p.`id_product` = '.(int)$product->id, false);
                    $product->date_add = pSQL($datas['date_add']);
                    $res = $product->update();
                }
                // If no id_product or update failed
                $product->force_id = (bool)$force_ids;

                if (!$res) {
                    if (isset($product->date_add) && $product->date_add != '') {
                        $res = $product->add(false);
                    } else {
                        $res = $product->add();
                    }
                }

                if ($product->getType() == Product::PTYPE_VIRTUAL) {
                    StockAvailable::setProductOutOfStock((int)$product->id, 1);
                } else {
                    StockAvailable::setProductOutOfStock((int)$product->id, (int)$product->out_of_stock);
                }
            }

            $shops = array();
            $product_shop = explode($this->multiple_value_separator, $product->shop);
            foreach ($product_shop as $shop) {
                if (empty($shop)) {
                    continue;
                }
                $shop = trim($shop);
                if (!empty($shop) && !is_numeric($shop)) {
                    $shop = Shop::getIdByName($shop);
                }

                if (in_array($shop, $shop_ids)) {
                    $shops[] = $shop;
                } else {
                    $this->addProductWarning(Tools::safeOutput($info['name']), $product->id, $this->l('Shop is not valid'));
                }
            }
            if (empty($shops)) {
                $shops = Shop::getContextListShopID();
            }
            // If both failed, mysql error
            if (!$res) {
                $this->errors[] = sprintf(
                    Tools::displayError('%1$s (ID: %2$s) cannot be saved'),
                    (isset($info['name']) && !empty($info['name']))? Tools::safeOutput($info['name']) : 'No Name',
                    (isset($info['id']) && !empty($info['id']))? Tools::safeOutput($info['id']) : 'No ID'
                );
                $this->errors[] = ($field_error !== true ? $field_error : '').(isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '').
                    Db::getInstance()->getMsgError();
            } else {
                // Product supplier
                if (isset($product->id) && $product->id && isset($product->id_supplier) && property_exists($product, 'supplier_reference')) {
                    $id_product_supplier = (int)ProductSupplier::getIdByProductAndSupplier((int)$product->id, 0, (int)$product->id_supplier);
                    if ($id_product_supplier) {
                        $product_supplier = new ProductSupplier($id_product_supplier);
                    } else {
                        $product_supplier = new ProductSupplier();
                    }

                    $product_supplier->id_product = (int)$product->id;
                    $product_supplier->id_product_attribute = 0;
                    $product_supplier->id_supplier = (int)$product->id_supplier;
                    $product_supplier->product_supplier_price_te = $product->wholesale_price;
                    $product_supplier->product_supplier_reference = $product->supplier_reference;
                    $product_supplier->save();
                }

                // SpecificPrice (only the basic reduction feature is supported by the import)
                if (!$shop_is_feature_active) {
                    $info['shop'] = 1;
                } elseif (!isset($info['shop']) || empty($info['shop'])) {
                    $info['shop'] = implode($this->multiple_value_separator, Shop::getContextListShopID());
                }

                // Get shops for each attributes
                $info['shop'] = explode($this->multiple_value_separator, $info['shop']);

                $id_shop_list = array();
                foreach ($info['shop'] as $shop) {
                    if (!empty($shop) && !is_numeric($shop)) {
                        $id_shop_list[] = (int)Shop::getIdByName($shop);
                    } elseif (!empty($shop)) {
                        $id_shop_list[] = $shop;
                    }
                }

                if ((isset($info['reduction_price']) && $info['reduction_price'] > 0) || (isset($info['reduction_percent']) && $info['reduction_percent'] > 0)) {
                    foreach ($id_shop_list as $id_shop) {
                        $specific_price = SpecificPrice::getSpecificPrice($product->id, $id_shop, 0, 0, 0, 1, 0, 0, 0, 0);

                        if (is_array($specific_price) && isset($specific_price['id_specific_price'])) {
                            $specific_price = new SpecificPrice((int)$specific_price['id_specific_price']);
                        } else {
                            $specific_price = new SpecificPrice();
                        }
                        $specific_price->id_product = (int)$product->id;
                        $specific_price->id_specific_price_rule = 0;
                        $specific_price->id_shop = $id_shop;
                        $specific_price->id_currency = 0;
                        $specific_price->id_country = 0;
                        $specific_price->id_group = 0;
                        $specific_price->price = -1;
                        $specific_price->id_customer = 0;
                        $specific_price->from_quantity = 1;

                        $specific_price->reduction = (isset($info['reduction_price']) && $info['reduction_price']) ? (float)str_replace(',', '.', $info['reduction_price']) : $info['reduction_percent'] / 100;
                        $specific_price->reduction_type = (isset($info['reduction_price']) && $info['reduction_price']) ? 'amount' : 'percentage';
                        $specific_price->from = (isset($info['reduction_from']) && Validate::isDate($info['reduction_from'])) ? $info['reduction_from'] : '0000-00-00 00:00:00';
                        $specific_price->to = (isset($info['reduction_to']) && Validate::isDate($info['reduction_to']))  ? $info['reduction_to'] : '0000-00-00 00:00:00';
                        if (!$specific_price->save()) {
                            $this->addProductWarning(Tools::safeOutput($info['name']), $product->id, $this->l('Discount is invalid'));
                        }
                    }
                }

                if (isset($product->tags) && !empty($product->tags)) {
                    if (isset($product->id) && $product->id) {
                        $tags = Tag::getProductTags($product->id);
                        if (is_array($tags) && count($tags)) {
                            if (!empty($product->tags)) {
                                $product->tags = explode($this->multiple_value_separator, $product->tags);
                            }
                            if (is_array($product->tags) && count($product->tags)) {
                                foreach ($product->tags as $key => $tag) {
                                    if (!empty($tag)) {
                                        $product->tags[$key] = trim($tag);
                                    }
                                }
                                $tags[$id_lang] = $product->tags;
                                $product->tags = $tags;
                            }
                        }
                    }
                    // Delete tags for this id product, for no duplicating error
                    Tag::deleteTagsForProduct($product->id);
                    if (!is_array($product->tags) && !empty($product->tags)) {
                        $product->tags = AdminImportController::createMultiLangField($product->tags);
                        foreach ($product->tags as $key => $tags) {
                            $is_tag_added = Tag::addTags($key, $product->id, $tags, $this->multiple_value_separator);
                            if (!$is_tag_added) {
                                $this->addProductWarning(Tools::safeOutput($info['name']), $product->id, $this->l('Tags list is invalid'));
                                break;
                            }
                        }
                    } else {
                        foreach ($product->tags as $key => $tags) {
                            $str = '';
                            foreach ($tags as $one_tag) {
                                $str .= $one_tag.$this->multiple_value_separator;
                            }
                            $str = rtrim($str, $this->multiple_value_separator);

                            $is_tag_added = Tag::addTags($key, $product->id, $str, $this->multiple_value_separator);
                            if (!$is_tag_added) {
                                $this->addProductWarning(Tools::safeOutput($info['name']), (int)$product->id, 'Invalid tag(s) ('.$str.')');
                                break;
                            }
                        }
                    }
                }

                //delete existing images if "delete_existing_images" is set to 1
                if (isset($product->delete_existing_images)) {
                    if ((bool)$product->delete_existing_images) {
                        $product->deleteImages();
                    }
                }

                if (isset($product->delete_existing_features)) {
                    if ((bool)$product->delete_existing_features) {
                        $product->deleteProductFeatures();
                    }
                }

                if (isset($product->image) && is_array($product->image) && count($product->image)) {
                    $product_has_images = (bool)Image::getImages($this->context->language->id, (int)$product->id);
                    foreach ($product->image as $key => $url) {
                        $url = trim($url);
                        $error = false;
                        if (!empty($url)) {
                            $url = str_replace(' ', '%20', $url);

                            $image = new Image();
                            $image->id_product = (int)$product->id;
                            $image->position = Image::getHighestPosition($product->id) + 1;
                            $image->cover = (!$key && !$product_has_images) ? true : false;
                            // file_exists doesn't work with HTTP protocol
                            if (($field_error = $image->validateFields(UNFRIENDLY_ERROR, true)) === true &&
                                ($lang_field_error = $image->validateFieldsLang(UNFRIENDLY_ERROR, true)) === true && $image->add()) {
                                // associate image to selected shops
                                $image->associateTo($shops);
                                if (!AdminImportController::copyImg($product->id, $image->id, $url, 'products', !$regenerate)) {
                                    $image->delete();
                                    $this->warnings[] = sprintf(Tools::displayError('Error copying image: %s'), $url);
                                }
                            } else {
                                $error = true;
                            }
                        } else {
                            $error = true;
                        }

                        if ($error) {
                            $this->warnings[] = sprintf(Tools::displayError('Product #%1$d: the picture (%2$s) cannot be saved.'), $image->id_product, $url);
                        }
                    }
                }

                if (isset($product->id_category) && is_array($product->id_category)) {
                    $product->updateCategories(array_map('intval', $product->id_category));
                }

                $product->checkDefaultAttributes();
                if (!$product->cache_default_attribute) {
                    Product::updateDefaultAttribute($product->id);
                }

                // Features import
                $features = get_object_vars($product);

                if (isset($features['features']) && !empty($features['features'])) {
                    foreach (explode($this->multiple_value_separator, $features['features']) as $single_feature) {
                        if (empty($single_feature)) {
                            continue;
                        }
                        $tab_feature = explode('-', $single_feature);
                        // print_r( $tab_feature);exit;
                        $feature_name = isset($tab_feature[0]) ? trim($tab_feature[0]) : '';
                        $feature_value = isset($tab_feature[1]) ? trim($tab_feature[1]) : '';
                        $position = isset($tab_feature[2]) ? (int)$tab_feature[2] - 1 : false;
                        $custom = isset($tab_feature[3]) ? (int)$tab_feature[3] : false;
                        if (!empty($feature_name) && !empty($feature_value)) {
                            $id_feature = (int)Feature::addFeatureImport($feature_name, $position);
                            $id_product = null;
                            if ($force_ids || $match_ref) {
                                $id_product = (int)$product->id;
                            }
                            $id_feature_value = (int)FeatureValue::addFeatureValueImport($id_feature, $feature_value, $id_product, $id_lang, $custom);
                            Product::addFeatureProductImport($product->id, $id_feature, $id_feature_value);
                        }
                    }
                }
                // clean feature positions to avoid conflict
                Feature::cleanPositions();

                // set advanced stock managment
                if (isset($product->advanced_stock_management)) {
                    if ($product->advanced_stock_management != 1 && $product->advanced_stock_management != 0) {
                        $this->warnings[] = sprintf(Tools::displayError('Advanced stock management has incorrect value. Not set for product %1$s '), $product->name[$default_language_id]);
                    } elseif (!Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') && $product->advanced_stock_management == 1) {
                        $this->warnings[] = sprintf(Tools::displayError('Advanced stock management is not enabled, cannot enable on product %1$s '), $product->name[$default_language_id]);
                    } elseif ($update_advanced_stock_management_value) {
                        $product->setAdvancedStockManagement($product->advanced_stock_management);
                    }
                    // automaticly disable depends on stock, if a_s_m set to disabled
                    if (StockAvailable::dependsOnStock($product->id) == 1 && $product->advanced_stock_management == 0) {
                        StockAvailable::setProductDependsOnStock($product->id, 0);
                    }
                }

                // Check if warehouse exists
                if (isset($product->warehouse) && $product->warehouse) {
                    if (!Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                        $this->warnings[] = sprintf(Tools::displayError('Advanced stock management is not enabled, warehouse not set on product %1$s '), $product->name[$default_language_id]);
                    } else {
                        if (Warehouse::exists($product->warehouse)) {
                            // Get already associated warehouses
                            $associated_warehouses_collection = WarehouseProductLocation::getCollection($product->id);
                            // Delete any entry in warehouse for this product
                            foreach ($associated_warehouses_collection as $awc) {
                                $awc->delete();
                            }
                            $warehouse_location_entity = new WarehouseProductLocation();
                            $warehouse_location_entity->id_product = $product->id;
                            $warehouse_location_entity->id_product_attribute = 0;
                            $warehouse_location_entity->id_warehouse = $product->warehouse;
                            if (WarehouseProductLocation::getProductLocation($product->id, 0, $product->warehouse) !== false) {
                                $warehouse_location_entity->update();
                            } else {
                                $warehouse_location_entity->save();
                            }
                            StockAvailable::synchronize($product->id);
                        } else {
                            $this->warnings[] = sprintf(Tools::displayError('Warehouse did not exist, cannot set on product %1$s.'), $product->name[$default_language_id]);
                        }
                    }
                }
                if (isset($product->unit_price)) {
                    $product->unit_price = $product->unit_price;

                }

                if (isset($product->msrp)) {
                    $product->msrp = $product->msrp;
                }

                if (isset($product->accessories)) {
                    $productAccessories = $product->accessories;
                    if($productAccessories !== ""){
                        $productAccessories = explode(",",$productAccessories);

                        if(count($productAccessories) > 0){
                            Db::getInstance()->delete('accessory', 'id_product_1 = '.(int)$product->id);
                            foreach ($productAccessories as $reference) {
                                $result = Db::getInstance()->executeS(
                                    'SELECT `id_product` 
                    	 			FROM `'._DB_PREFIX_.'product` 
                    	 			WHERE `reference` = "'.$reference .'"'
                                );
                                $productAccId = $result[0]['id_product'];
                                Db::getInstance()->insert('accessory', array(
                                    'id_product_1' => (int)$product->id,
                                    'id_product_2' => (int)$productAccId
                                ));
                            }

                        }
                    }
                }

                // stock available
                if (isset($product->depends_on_stock)) {
                    if ($product->depends_on_stock != 0 && $product->depends_on_stock != 1) {
                        $this->warnings[] = sprintf(Tools::displayError('Incorrect value for "depends on stock" for product %1$s '), $product->name[$default_language_id]);
                    } elseif ((!$product->advanced_stock_management || $product->advanced_stock_management == 0) && $product->depends_on_stock == 1) {
                        $this->warnings[] = sprintf(Tools::displayError('Advanced stock management not enabled, cannot set "depends on stock" for product %1$s '), $product->name[$default_language_id]);
                    } else {
                        StockAvailable::setProductDependsOnStock($product->id, $product->depends_on_stock);
                    }

                    // This code allows us to set qty and disable depends on stock
                    if (isset($product->quantity)) {
                        // if depends on stock and quantity, add quantity to stock
                        if ($product->depends_on_stock == 1) {
                            $stock_manager = StockManagerFactory::getManager();
                            $price = str_replace(',', '.', $product->wholesale_price);
                            if ($price == 0) {
                                $price = 0.000001;
                            }
                            $price = round(floatval($price), 6);
                            $warehouse = new Warehouse($product->warehouse);
                            if ($stock_manager->addProduct((int)$product->id, 0, $warehouse, (int)$product->quantity, 1, $price, true)) {
                                StockAvailable::synchronize((int)$product->id);
                            }
                        } else {
                            if ($shop_is_feature_active) {
                                foreach ($shops as $shop) {
                                    StockAvailable::setQuantity((int)$product->id, 0, (int)$product->quantity, (int)$shop);
                                }
                            } else {
                                StockAvailable::setQuantity((int)$product->id, 0, (int)$product->quantity, (int)$this->context->shop->id);
                            }
                        }
                    }
                } else {
                    // if not depends_on_stock set, use normal qty

                    if ($shop_is_feature_active) {
                        foreach ($shops as $shop) {
                            StockAvailable::setQuantity((int)$product->id, 0, (int)$product->quantity, (int)$shop);
                        }
                    } else {
                        StockAvailable::setQuantity((int)$product->id, 0, (int)$product->quantity, (int)$this->context->shop->id);
                    }
                }
            }

            // Sample module import

            $fields = array_intersect_key($info, $this->prefixed_sample_fields); // get only module's fields with csv values
            $fields = array_merge($this->prefixed_sample_fields_defaults, $fields); // fill empty csv data with defaults
            $fields = $this->removeSamplePrefix($fields); // get ready to DB
            // Add identifications. Make sure DB key is exists! It doesn't by default!
            $fields['id_product'] = (int)$product->id;
            $fields['id_product_attribute'] = (int)$product->cache_default_attribute;

            Db::getInstance()->insert('wk_sample_product', $fields, false, true, Db::ON_DUPLICATE_KEY);
            Db::getInstance()->insert('wk_sample_product_shop', $fields, false, true, Db::ON_DUPLICATE_KEY);


        } // CSV loop end
        $this->closeCsvFile($handle);
        Module::processDeferedFuncCall();
        Module::processDeferedClearCache();
        Tag::updateTagCount();
    }



}