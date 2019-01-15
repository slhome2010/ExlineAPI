<?php

include_once(DIR_SYSTEM . 'library/kazshipping/kazshipping.php');
include_once(DIR_SYSTEM . 'library/kazpost/Classes/PHPExcel/IOFactory.php');

class ControllerCheckoutKazshipping extends Controller {

    public function autocomplete() {
        $json = array();
        if (isset($this->request->get['shipping_method'])) {
            $shipping_method = explode(".", $this->request->get['shipping_method'])[0];
        }

        if ($shipping_method == "exline") {  // ------------------- Exline -----------------
            $exline = new Exline();

            $this->load->model('localisation/country');
            $this->load->model('localisation/zone');

            if (isset($this->request->get['country_id']) && $this->request->get['country_id'] != "undefined") {
                $iso_code_2 = $this->model_localisation_country->getCountry($this->request->get['country_id'])['iso_code_2'];
            } else {
                $iso_code_2 = DEFAULT_ISO;
            }

            if (isset($this->request->get['zone_id'])) {
                $zone_id = $this->request->get['zone_id'];
                $zone = $this->model_localisation_zone->getZone($zone_id)['name'];
            } else {
                $zone_id = '0';
                $zone = '';
            }

            $city_data = $this->cache->get('city.' . (int) $zone_id);

            if (!$city_data) {
                $destination_all_regions = $this->cache->get('exline_all_regions');
                if (!$destination_all_regions || isset($destination_all_regions['error'])) {
                    $url = DESTINATIONS_ALL_REGIONS_URL . $iso_code_2;
                    $destination_all_regions = $exline->connect($url);
                    $this->cache->set('exline_all_regions', $destination_all_regions);
                }
                // определим только те нас. пункты, которые входят в регион (область)
                $destination_region = array();
                if (isset($destination_all_regions['regions']) && isset($zone)) {
                    foreach ($destination_all_regions['regions'] as $region) {
                        if (strpos($region['cached_path'], trim($zone)) !== false || strpos($zone, trim($region['title'])) !== false) {
                            $destination_region[] = $region;
                        }
                    }
                }

                foreach ($destination_region as $region) {
                    $cached_path = explode(',', $region['cached_path']);
                    $json[] = array(
                        'id' => $region['id'],
                        'city_id' => strip_tags(html_entity_decode($region['title'] . (isset($cached_path[2]) ? ', ' . $cached_path[2] : ''), ENT_QUOTES, 'UTF-8')),
                        'name' => strip_tags(html_entity_decode($region['title'] . (isset($cached_path[2]) ? ', ' . $cached_path[2] : ''), ENT_QUOTES, 'UTF-8')),
                        'title' => strip_tags(html_entity_decode($region['title'] . (isset($cached_path[2]) ? ', ' . $cached_path[2] : ''), ENT_QUOTES, 'UTF-8'))
                    );
                }

                $sort_order = array();

                foreach ($json as $key => $value) {
                    $sort_order[$key] = $value['title'];
                }

                array_multisort($sort_order, SORT_ASC, $json);
                $city_data = $json;
                $this->cache->set('city.' . (int) $zone_id, $city_data);
            } else {
                $json = $city_data;
            }
        } else if ($shipping_method == "kazpost") {    // ------------------- Kazpost -----------------
			$server = $this->config->get('kazpost_api_server');
			$code =  $server === '2' ? 1 : 0;
			$file =  $server === '2' ? $this->config->get('kazpost_server2_xls') : $this->config->get('kazpost_server1_xls');
			
			$objPHPExcel = PHPExcel_IOFactory::load(DIR_SYSTEM . $file);	
			$objPHPExcel->setActiveSheetIndexByName($this->config->get('kazpost_fromto_sheetname'));
			$aSheet = $code ===0 ? $objPHPExcel->getActiveSheet()->toArray(true, true):$objPHPExcel->getActiveSheet()->toArray(true, true, true);

            foreach ($aSheet as $aSheet) {
                if (is_numeric($aSheet[0])) {
                    $json[] = array(
                        'id' => $aSheet[$code],
                        'city_id' => $aSheet[$code],
                        'name' => strip_tags(html_entity_decode($aSheet[$code+1], ENT_QUOTES, 'UTF-8')),
                        'title' => strip_tags(html_entity_decode($aSheet[$code+1], ENT_QUOTES, 'UTF-8'))
                    );
                }
            }
			
            $sort_order = array();

            foreach ($json as $key => $value) {
                $sort_order[$key] = $value['title'];
            }

            array_multisort($sort_order, SORT_ASC, $json);
        }

        if (isset($this->request->get['filter_name']) && $this->request->get['filter_name']) {
            $json_filter = array_filter($json, function($v) {
                return mb_stristr($v['title'], $this->request->get['filter_name']);
            });
        } else {
            $json_filter = $json;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json_filter));
    }

}
