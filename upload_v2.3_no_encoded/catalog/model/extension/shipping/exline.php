<?php

require_once(DIR_SYSTEM . 'library/kazshipping/kazshipping.php');
@require_once(DIR_SYSTEM . 'license/sllic.lic');

class ModelShippingExline extends Model {

    function getQuote($address) {
        $extension = version_compare(VERSION, '2.3.0', '>=') ? "extension/" : "";
        $this->load->language($extension . 'shipping/exline');

        $exline = new Exline();

        // проверим, что адрес попадает в геозону
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get('exline_geo_zone_id') . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");
        $iso_code_2 = isset($address['iso_code_2']) ? $address['iso_code_2'] : DEFAULT_ISO; // выделим код страны

        if (!$this->config->get('exline_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $quote_data = array();
        //file_put_contents('exline.txt', print_r($address,true), FILE_APPEND);
        if ($status) {
            // Проверим, что для указанной страны вообще существует доставка
            $destination_all_regions = $this->cache->get('exline_all_regions');
            // file_put_contents('exline.txt', print_r($destination_all_regions,true), FILE_APPEND);
            if (!$destination_all_regions || isset($destination_all_regions['error'])) {
                $url = DESTINATIONS_ALL_REGIONS_URL . $iso_code_2;
                $destination_all_regions = $exline->connect($url);
                $this->cache->set('exline_all_regions', $destination_all_regions);
            }

            $destination_region = array();
            if (isset($destination_all_regions['regions']) && isset($address['zone']) && $address['zone'] != "") {
                // Разберем  "cached_path":"Казахстан, Акмолинская область, Буландынский район" чтоб выделить область, указанную в адресе
                foreach ($destination_all_regions['regions'] as $region) {
                    if (strpos($region['cached_path'], trim($address['zone'])) !== false || strpos($address['zone'], trim($region['title'])) !== false) {
                        $destination_region[] = $region;
                    }
                }
            }

            $destination_city = array();

            if ($destination_region) {
                // получим список пунктов назначения
                $url = DESTINATIONS_URL . rawurlencode(explode(',', $address['city'])[0]);
                $destination_cities = $exline->connect($url);

                //  входит ли найденное в региональный список
                foreach ($destination_region as $region) {
                    foreach ($destination_cities['regions'] as $city) {
                        if ($city === $region) {
                            $destination_city[] = $city;
                        }
                    }
                }
            }

            // расчет стоимости этой доставки
            if ($destination_city) {
                // проверим лицензию
                if (class_exists('Vendor')) {
                    $vendor = new Vendor();
                }
                $vendor->franchise();

                // Прикинем вес
                if ($this->config->get('config_weight_class_id') == 1) {
                    $weight = $this->cart->getWeight();
                } else {
                    $weight = $this->cart->getWeight() / 1000;
                }

                // Прикинем габариты
                $products = $this->cart->getProducts();
                $w = [];
                $h = [];
                $l = [];
                foreach ($products as $product) {
                    if ($product['length_class_id'] == 1) {
                        $w[] = $product['width'];
                        $h[] = $product['height'];
                        $l[] = $product['length'];
                    } else {
                        $w[] = $product['width'] / 10;
                        $h[] = $product['height'] / 10;
                        $l[] += $product['length'] / 10;
                    }
                }
                $width = max($w);
                $height = max($h);
                $length = max($l);

                // учтем страховку
                if ($this->config->get('exline_insurance')) {
                    $declared_value = $this->config->get('exline_insurance');
                } else {
                    $declared_value = '15000';
                }

                $destination_id = (string) $destination_city[0]['id'];
                // вспомним пункт отправления и токен
                $origin_id = $this->config->get('exline_origin_id');
                $exline_pricing_policy = $this->config->get('exline_pricing_policy');
                $token = $exline_pricing_policy ? '&pricing_policy=' . $exline_pricing_policy : '';

                $url = CALCULATIONS_URL . $origin_id . '&destination_id=' . $destination_id . '&weight=' . $weight . '&w=' . $width . '&l=' . $length . '&h=' . $height . '&declared_value=' . $declared_value . '&service=standard' . $token;
                $exline_request_standard = $exline->connect($url);
                $exline_cost_standard = $exline_request_standard['calculation']['price'] + $exline_request_standard['calculation']['fuel_surplus'] + $exline_request_standard['calculation']['declared_value_fee']; // топл. сбор  + insuranse

                $url = CALCULATIONS_URL . $origin_id . '&destination_id=' . $destination_id . '&weight=' . $weight . '&w=' . $width . '&l=' . $length . '&h=' . $height . '&declared_value=' . $declared_value . '&service=express' . $token;
                $exline_request_express = $exline->connect($url);
                $exline_cost_express = $exline_request_express['calculation']['price'] + $exline_request_express['calculation']['fuel_surplus'] + $exline_request_express['calculation']['declared_value_fee'];

                $quote_data['exline_standard'] = array(
                    'code' => 'exline.exline_standard',
                    'title' => $this->language->get('text_description') . ' Стандарт',
                    'cost' => $exline_cost_standard,
                    'tax_class_id' => $this->config->get('exline_tax_class_id'),
                    'text' => $this->currency->format($this->tax->calculate($this->currency->convert($exline_cost_standard, 'KZT', $this->session->data['currency']), $this->config->get('exline_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'], 1) . '   ' . ((strtolower($exline_request_standard['calculation']['human_range']) === 'no data') ? '' : $exline_request_standard['calculation']['human_range'])
                );
                $quote_data['exline_express'] = array(
                    'code' => 'exline.exline_express',
                    'title' => $this->language->get('text_description') . ' Экспресс',
                    'cost' => $exline_cost_express,
                    'tax_class_id' => $this->config->get('exline_tax_class_id'),
                    'text' => $this->currency->format($this->tax->calculate($this->currency->convert($exline_cost_express, 'KZT', $this->session->data['currency']), $this->config->get('exline_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'], 1) . '   ' . ((strtolower($exline_request_express['calculation']['human_range']) === 'no data') ? '' : $exline_request_express['calculation']['human_range'])
                );
            } else {

                $quote_data['exline_standard'] = array(
                    'code' => 'exline.exline_standard',
                    'title' => $this->language->get('text_description') . ' Стандарт',
                    'cost' => $this->currency->convert(0, 'KZT', $this->config->get('config_currency')),
                    'tax_class_id' => $this->config->get('exline_tax_class_id'),
                    'text' => $this->language->get('error_destination_city')
                );
                $quote_data['exline_express'] = array(
                    'code' => 'exline.exline_express',
                    'title' => $this->language->get('text_description') . ' Экспресс',
                    'cost' => $this->currency->convert(0, 'KZT', $this->config->get('config_currency')),
                    'tax_class_id' => $this->config->get('exline_tax_class_id'),
                    'text' => $this->language->get('error_destination_city')
                );
            }
        }
        // теперь выдача результатов расчета на checkout
        $method_data = array();

        if ($quote_data) {
            $method_data = array(
                'code' => 'exline',
                'title' => $this->language->get('text_title'),
                'quote' => $quote_data,
                'sort_order' => $this->config->get('exline_sort_order'),
                'error' => false
            );
        }
        return $method_data;
    }

}

class ModelExtensionShippingExline extends ModelShippingExline {

}
