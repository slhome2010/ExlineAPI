<?php

include_once(DIR_SYSTEM . 'library/kazshipping/kazshipping.php');

class ModelLocalisationCity extends Model {

    public function getCitiesByZoneId($zone_id) {
        $city_data = $this->cache->get('city.' . (int) $zone_id);

        if (!$city_data) {
            $json = array();
            $exline = new Exline();

            $shipping_country_id = isset($this->session->data['shipping_country_id']) ? $this->session->data['shipping_country_id'] : $this->config->get('config_country_id');

            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "country WHERE country_id = '" . (int) $shipping_country_id . "' AND status = '1'");

            if (isset($query->row['iso_code_2'])) {
                $iso_code_2 = $query->row['iso_code_2'];
            } else {
                $iso_code_2 = DEFAULT_ISO;
            }

            $zone = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone WHERE zone_id = '" . (int) $zone_id . "' AND status = '1'");

            $destination_all_regions = $this->cache->get('exline_all_regions'); // список всех регионов из кэша
            if (!$destination_all_regions || isset($destination_all_regions['error'])) {
                $url = DESTINATIONS_ALL_REGIONS_URL . $iso_code_2;
                $destination_all_regions = $exline->connect($url);              // если нет в кэше качаем с API
                $this->cache->set('exline_all_regions', $destination_all_regions);
            }
            // определим только те нас. пункты, которые входят в регион (область)
            $destination_region = array();
            if (isset($destination_all_regions['regions']) && isset($zone->row['name'])) {
                foreach ($destination_all_regions['regions'] as $region) {
                    if (strpos($region['cached_path'], trim($zone->row['name'])) !== false || strpos($zone->row['name'], trim($region['title'])) !== false) {
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
                $sort_order[$key] = $value['name'];
            }

            array_multisort($sort_order, SORT_ASC, $json);

            $city_data = $json;

            $this->cache->set('city.' . (int) $zone_id, $city_data);
        }

        return $city_data;
    }

}
