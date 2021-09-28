<?php

include_once(DIR_SYSTEM . 'library/kazshipping/kazshipping.php');
define('MODULE_VERSION', 'v2.1.7');

class ControllerShippingExline extends Controller {

    private $error = array();
    private $token;

    public function index() {
        $extension = version_compare(VERSION, '2.3.0', '>=') ? "extension/" : "";
        $link = version_compare(VERSION, '2.3.0', '>=') ? "extension/extension" : "extension/shipping";

        if (version_compare(VERSION, '3.0.0', '>=')) {
            $link = "marketplace/extension";
        }

        if (version_compare(VERSION, '2.2.0', '>=')) {
            $this->load->language($extension . 'shipping/exline');
            $ssl = true;
        } else {
            $this->language->load('shipping/exline');
            $ssl = 'SSL';
        }

        if (isset($this->session->data['user_token'])) {
            $this->token = $this->session->data['user_token'];
            $token_name = 'user_token';
        }
        if (isset($this->session->data['token'])) {
            $this->token = $this->session->data['token'];
            $token_name = 'token';
        }

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('exline', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            /* $this->response->redirect($this->url->link($link, 'token=' . $this->session->data['token'], 'SSL')); */
            if (version_compare(VERSION, '2.0.1', '>=')) { // иначе вылетает из админки
                $this->response->redirect($this->url->link($link, $token_name . '=' . $this->token . '&type=shipping', $ssl));
            } else {
                $this->redirect($this->url->link($link, $token_name . '=' . $this->token, $ssl));
            }
        }

        $data['heading_title'] = $this->language->get('heading_title') . ' ' . MODULE_VERSION;
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_all_zones'] = $this->language->get('text_all_zones');
        $data['text_none'] = $this->language->get('text_none');

        $data['entry_tax_class'] = $this->language->get('entry_tax_class');
        $data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $data['entry_origin'] = $this->language->get('entry_origin');
        $data['entry_insurance'] = $this->language->get('entry_insurance');
        $data['entry_percent'] = $this->language->get('entry_percent');
        $data['entry_pricing_policy'] = $this->language->get('entry_pricing_policy');

        $data['help_origin'] = $this->language->get('help_origin');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['error_origin_country'] = $this->language->get('error_origin_country');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $token_name . '=' . $this->token, $ssl),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => version_compare(VERSION, '2.3.0', '>=') ? $this->language->get('text_extension') : $this->language->get('text_shipping'),
            'href' => $this->url->link($link, $token_name . '=' . $this->token . '&type=shipping', $ssl),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link($extension . 'shipping/exline', $token_name . '=' . $this->token, $ssl),
            'separator' => ' :: '
        );

        $data['action'] = $this->url->link($extension . 'shipping/exline', $token_name . '=' . $this->token, $ssl);
        $data['cancel'] = $this->url->link($link, $token_name . '=' . $this->token . '&type=shipping', $ssl);
        $data['token'] = $this->token;

        if (isset($this->request->post['exline_tax_class_id'])) {
            $data['exline_tax_class_id'] = $this->request->post['exline_tax_class_id'];
        } else {
            $data['exline_tax_class_id'] = $this->config->get('exline_tax_class_id');
        }

        $this->load->model('localisation/tax_class');

        $data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->request->post['exline_geo_zone_id'])) {
            $data['exline_geo_zone_id'] = $this->request->post['exline_geo_zone_id'];
        } else {
            $data['exline_geo_zone_id'] = $this->config->get('exline_geo_zone_id');
        }

        if (isset($this->request->post['exline_status'])) {
            $data['exline_status'] = $this->request->post['exline_status'];
        } else {
            $data['exline_status'] = $this->config->get('exline_status');
        }

        if (isset($this->request->post['exline_sort_order'])) {
            $data['exline_sort_order'] = $this->request->post['exline_sort_order'];
        } else {
            $data['exline_sort_order'] = $this->config->get('exline_sort_order');
        }

        if (isset($this->request->post['exline_insurance'])) {
            $data['exline_insurance'] = $this->request->post['exline_insurance'];
        } else {
            $data['exline_insurance'] = $this->config->get('exline_insurance');
        }

        if (isset($this->request->post['exline_percent'])) {
            $data['exline_percent'] = $this->request->post['exline_percent'];
        } else {
            $data['exline_percent'] = $this->config->get('exline_percent');
        }

        if (isset($this->request->post['exline_pricing_policy'])) {
            $data['exline_pricing_policy'] = $this->request->post['exline_pricing_policy'];
        } else {
            $data['exline_pricing_policy'] = $this->config->get('exline_pricing_policy');
        }

        $this->load->model('localisation/country');
        $data['iso_code_2'] = $this->model_localisation_country->getCountry($this->config->get('config_country_id'))['iso_code_2'];

        if (isset($this->request->post['origin_city'])) {
            $data['exline_origin_city'] = $this->request->post['exline_origin_city'];
        } else {
            $data['exline_origin_city'] = $this->config->get('exline_origin_city');
        }

        if (isset($this->request->post['origin_id'])) {
            $data['exline_origin_id'] = $this->request->post['exline_origin_id'];
        } else {
            $data['exline_origin_id'] = $this->config->get('exline_origin_id');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
		$data['extension'] = $extension;

		$tpl = version_compare(VERSION, '2.2.0', '>=') ? "" : ".tpl";
        $this->response->setOutput($this->load->view($extension . 'shipping/exline' . $tpl, $data));
    }

    protected function validate() {
        $extension = version_compare(VERSION, '2.3.0', '>=') ? "extension/" : "";
        if (!$this->user->hasPermission('modify', $extension . 'shipping/exline')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function autocomplete() {
        $json = array();
        $exline = new Exline();

        if (isset($this->request->get['iso_code_2'])) {
            $iso_code_2 = $this->request->get['iso_code_2'];
        } else {
            $iso_code_2 = DEFAULT_ISO;
        }

        $url = ORIGINS_ALL_REGIONS_URL . $iso_code_2;

        $results = $exline->connect($url);

        foreach ($results['regions'] as $result) {
            $json[] = array(
                'id' => $result['id'],
                'title' => strip_tags(html_entity_decode($result['title'], ENT_QUOTES, 'UTF-8'))
            );
        }

        $sort_order = array();

        foreach ($json as $key => $value) {
            $sort_order[$key] = $value['title'];
        }

        array_multisort($sort_order, SORT_ASC, $json);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

}

class ControllerExtensionShippingExline extends ControllerShippingExline {

}
