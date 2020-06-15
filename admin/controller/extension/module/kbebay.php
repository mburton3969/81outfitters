<?php

/*
 * This file is used of Add products in the new Walmart tables
 * This file is having all the function which is used in submitting products on Walmart
 * File is added by Nikhil on 03-07-2017 
 */

class ControllerExtensionModuleKbebay extends Controller
{

    private $error = array();
    private $session_token_key = 'token';
    private $session_token = '';
    private $module_path = '';

    public function __construct($registry)
    {
        parent::__construct($registry);

        if (VERSION >= 2.0 && VERSION <= 2.2) {
            $this->session_token_key = 'token';
            $this->session_token = $this->session->data['token'];

            /* BreadCrumb Path */
            $this->extension_path = 'extension/module';

            /* Main Module Path */
            $this->module_path = 'module';
        } else if (VERSION < 3.0) {
            $this->session_token_key = 'token';
            $this->session_token = $this->session->data['token'];

            $this->extension_path = 'extension/extension';
            $this->module_path = 'extension/module';
        } else {
            $this->session_token_key = 'user_token';
            $this->session_token = $this->session->data['user_token'];

            $this->extension_path = 'marketplace/extension';
            $this->module_path = 'extension/module';
        }
    }

    public function index()
    {
        $this->response->redirect($this->url->link($this->module_path . '/kbebay/generalSettings', $this->session_token_key . '=' . $this->session_token, 'SSL'));
    }

    public function generalSettings()
    {

        $this->load->language($this->module_path . '/kbebay');
        $this->document->setTitle($this->language->get('heading_title_main'));

        $this->load->model('setting/setting');
        $this->load->model('setting/kbebay');

        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->request->post['ebay_general_settings'] = $this->request->post['ebay'];
            unset($this->request->post['ebay']);
            $this->session->data['success'] = $this->language->get('ebay_text_success');
            $this->model_setting_kbebay->editSetting('ebay_general_settings', $this->request->post, $store_id);
            if (!isset($this->request->post['save'])) {
                $this->response->redirect($this->url->link($this->module_path . '/kbebay/generalSettings', $this->session_token_key . '=' . $this->session_token, 'SSL'));
            } else if (!isset($this->session_token)) {
                $this->response->redirect($this->url->link($this->module_path . '/kbebay/generalSettings', $this->session_token_key . '=' . $this->session_token, 'SSL'));
            }
        }
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link($this->extension_path, $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->url->link($this->module_path . '/kbebay', $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_general'),
            'href' => $this->url->link($this->module_path . '/kbebay/generalSettings', $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => ' :: '
        );

        //links
        $data['general_settings'] = $this->url->link($this->module_path . '/kbebay/generalSettings', $this->session_token_key . '=' . $this->session_token, 'SSL');
        $data['action'] = $this->url->link($this->module_path . '/kbebay/generalSettings', $this->session_token_key . '=' . $this->session_token, 'SSL');
        $data['route'] = $this->url->link($this->module_path . '/kbebay/generalSettings', $this->session_token_key . '=' . $this->session_token, 'SSL');
        $data['cancel'] = $this->url->link($this->module_path . '/kbebay', $this->session_token_key . '=' . $this->session_token, 'SSL');
        $data['token'] = $this->session_token;
        $data['session_token_key'] = $this->session_token_key;
        $data['module_path'] = $this->module_path;

        $data['ebay'] = array();

        $data['heading_title'] = $this->language->get('heading_title');
        $data['heading_title_main'] = $this->language->get('heading_title_main');

        // General Settings tab & info
        $data['text_general_enable'] = $this->language->get('text_general_enable');
        $data['text_general_account_type'] = $this->language->get('text_general_account_type');
        $data['text_sandbox'] = $this->language->get('text_sandbox');
        $data['text_live'] = $this->language->get('text_live');
        $data['text_ebay_dev_id'] = $this->language->get('text_ebay_dev_id');
        $data['text_ebay_app_id'] = $this->language->get('text_ebay_app_id');
        $data['text_ebay_cert_id'] = $this->language->get('text_ebay_cert_id');
        $data['text_ebay_ru_name'] = $this->language->get('text_ebay_ru_name');
        $data['text_ebay_token'] = $this->language->get('text_ebay_token');
        $data['text_ebay_paypal_email'] = $this->language->get('text_ebay_paypal_email');
        $data['text_status'] = $this->language->get('text_status');
        $data['text_action'] = $this->language->get('text_action');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_listing_link'] = $this->language->get('text_listing_link');
        $data['text_token_add_link'] = $this->language->get('text_token_add_link');

        //Tooltips
        $data['text_edit_general'] = $this->language->get('text_edit_general');

        //buttons
        $data['button_save'] = $this->language->get('button_save');
        $data['button_edit'] = $this->language->get('button_edit');
        $data['button_save_and_stay'] = $this->language->get('button_save_and_stay');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_add_module'] = $this->language->get('button_add_module');
        $data['button_remove'] = $this->language->get('button_remove');

        if ($this->model_setting_kbebay->getSetting('ebay_general_settings', $store_id)) {
            $settings = $this->model_setting_kbebay->getSetting('ebay_general_settings', $store_id);
            if (isset($this->request->get['selectedCentral'])) {
                $data['ebay'] = $settings['ebay_general_settings'];
            } else {
                $data['ebay'] = $settings['ebay_general_settings'];
            }
        }
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        if (isset($this->session->data['error'])) {
            $data['error']['error_warning'] = $this->session->data['error'];
            unset($this->session->data['error']);
        } else {
            $data['error'] = '';
        }
        if (isset($this->error['ebay_dev_id'])) {
            $data['error_ebay_dev_id'] = $this->error['ebay_dev_id'];
        } else {
            $data['error_ebay_dev_id'] = '';
        }
        if (isset($this->error['ebay_app_id'])) {
            $data['error_ebay_app_id'] = $this->error['ebay_app_id'];
        } else {
            $data['error_ebay_app_id'] = '';
        }
        if (isset($this->error['ebay_cert_id'])) {
            $data['error_ebay_cert_id'] = $this->error['ebay_cert_id'];
        } else {
            $data['error_ebay_cert_id'] = '';
        }
        if (isset($this->error['ebay_ru_name'])) {
            $data['error_ebay_ru_name'] = $this->error['ebay_ru_name'];
        } else {
            $data['error_ebay_ru_name'] = '';
        }
        if (isset($this->error['ebay_token'])) {
            $data['error_ebay_token'] = $this->error['ebay_token'];
        } else {
            $data['error_ebay_token'] = '';
        }
        if (isset($this->error['ebay_paypal_email'])) {
            $data['error_paypal_email'] = $this->error['ebay_paypal_email'];
        } else {
            $data['error_paypal_email'] = '';
        }
        if (isset($this->request->post['ebay']['general']['enable'])) {
            $data['ebay']['general']['enable'] = $this->request->post['ebay']['general']['enable'];
        }
        if (isset($this->request->post['ebay']['general']['account_type'])) {
            $data['ebay']['general']['account_type'] = $this->request->post['ebay']['general']['account_type'];
        }
        if (isset($this->request->post['ebay']['general']['dev_id'])) {
            $data['ebay']['general']['dev_id'] = $this->request->post['ebay']['general']['dev_id'];
        }
        if (isset($this->request->post['ebay']['general']['app_id'])) {
            $data['ebay']['general']['app_id'] = $this->request->post['ebay']['general']['app_id'];
        }
        if (isset($this->request->post['ebay']['general']['cert_id'])) {
            $data['ebay']['general']['cert_id'] = $this->request->post['ebay']['general']['cert_id'];
        }
        if (isset($this->request->post['ebay']['general']['ru_name'])) {
            $data['ebay']['general']['ru_name'] = $this->request->post['ebay']['general']['ru_name'];
        }
        if (isset($this->request->post['ebay']['general']['token'])) {
            $data['ebay']['general']['token'] = $this->request->post['ebay']['general']['token'];
        }
        if (isset($this->request->post['ebay']['general']['paypal_email'])) {
            $data['ebay']['general']['paypal_email'] = $this->request->post['ebay']['general']['paypal_email'];
        }
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $active_tab['active_tab'] = 1;
        $data['tab_common'] = $this->load->controller($this->module_path . '/kbebay/common', $active_tab);

        if (VERSION >= '2.2.0.0') {
            $this->response->setOutput($this->load->view($this->module_path . '/kbebay/general_settings', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path . '/kbebay/general_settings.tpl', $data));
        }
    }

    public function common($data = array())
    {
        $this->load->language($this->module_path . '/kbebay');

        $data['text_gs'] = $this->language->get('text_gs');
        $data['text_pm'] = $this->language->get('text_pm');
        $data['text_sp'] = $this->language->get('text_sp');
        $data['text_pl'] = $this->language->get('text_pl');
        $data['text_ol'] = $this->language->get('text_ol');
        $data['text_sync'] = $this->language->get('text_sync');
        $data['text_os'] = $this->language->get('text_os');
        $data['text_support'] = $this->language->get('text_support');

        //links
        $data['general_settings'] = $this->url->link($this->module_path . '/kbebay/generalSettings', $this->session_token_key . '=' . $this->session_token, true);
        $data['profile_management'] = $this->url->link($this->module_path . '/kbebay/profileManagement', $this->session_token_key . '=' . $this->session_token, true);
        $data['shipping_profile'] = $this->url->link($this->module_path . '/kbebay/shippingProfile', $this->session_token_key . '=' . $this->session_token, true);
        $data['product_listing'] = $this->url->link($this->module_path . '/kbebay/productListing', $this->session_token_key . '=' . $this->session_token, true);
        $data['order_listing'] = $this->url->link($this->module_path . '/kbebay/ebayOrders', $this->session_token_key . '=' . $this->session_token, true);
        $data['order_settings'] = $this->url->link($this->module_path . '/kbebay/orderSettings', $this->session_token_key . '=' . $this->session_token, true);
        $data['synchronization'] = $this->url->link($this->module_path . '/kbebay/synchronization', $this->session_token_key . '=' . $this->session_token, true);
        $data['support'] = $this->url->link($this->module_path . '/kbebay/support', $this->session_token_key . '=' . $this->session_token, true);
        if (VERSION >= '2.2.0.0') {
            return $this->load->view($this->module_path . '/kbebay/common', $data);
        } else {
            return $this->load->view($this->module_path . '/kbebay/common.tpl', $data);
        }
    }

    public function orderSettings()
    {
        $this->load->language($this->module_path . '/kbebay');

        $this->document->setTitle($this->language->get('heading_title_main'));

        $this->load->model('setting/setting');
        $this->load->model('setting/kbebay');

        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->request->post['ebay_order_settings'] = $this->request->post['ebay'];
            unset($this->request->post['ebay']);
            $this->session->data['success'] = $this->language->get('ebay_text_success');
            $this->model_setting_kbebay->editSetting('ebay_order_settings', $this->request->post, $store_id);
            if (!isset($this->request->post['save'])) {
                $this->response->redirect($this->url->link($this->module_path . '/kbebay/orderSettings', $this->session_token_key . '=' . $this->session_token, 'SSL'));
            } else if (!isset($this->session_token)) {
                $this->response->redirect($this->url->link($this->module_path . '/kbebay/orderSettings', $this->session_token_key . '=' . $this->session_token, 'SSL'));
            }
        }

        if (isset($this->error['ebay_default_status'])) {
            $data['error_ebay_default_status'] = $this->error['ebay_default_status'];
        } else {
            $data['error_ebay_default_status'] = '';
        }
        if (isset($this->error['ebay_cancel_status'])) {
            $data['error_ebay_cancelled_status'] = $this->error['ebay_cancel_status'];
        } else {
            $data['error_ebay_cancelled_status'] = '';
        }
        if (isset($this->error['ebay_shipped_status'])) {
            $data['error_ebay_shipped_status'] = $this->error['ebay_shipped_status'];
        } else {
            $data['error_ebay_shipped_status'] = '';
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link($this->extension_path, $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->url->link($this->module_path . '/kbebay', $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_order'),
            'href' => $this->url->link($this->module_path . '/kbebay/orderSettings', $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => ' :: '
        );

        //links
        $data['action'] = $this->url->link($this->module_path . '/kbebay/orderSettings', $this->session_token_key . '=' . $this->session_token, 'SSL');
        $data['route'] = $this->url->link($this->module_path . '/kbebay/orderSettings', $this->session_token_key . '=' . $this->session_token, 'SSL');
        $data['cancel'] = $this->url->link($this->module_path . '/kbebay', $this->session_token_key . '=' . $this->session_token, 'SSL');
        $data['token'] = $this->session_token;
        $data['session_token_key'] = $this->session_token_key;

        $data['heading_title'] = $this->language->get('heading_title');
        $data['heading_title_main'] = $this->language->get('heading_title_main');


        // General Settings tab & info
        $data['text_status_order_default'] = $this->language->get('text_status_order_default');
        $data['text_status_order_default_hint'] = $this->language->get('text_status_order_default_hint');
        $data['text_status_order_shipped_hint'] = $this->language->get('text_status_order_shipped_hint');
        $data['text_status_order_cancelled_hint'] = $this->language->get('text_status_order_cancel_hint');

        $data['text_status_order_cancelled'] = $this->language->get('text_status_order_cancelled');
        $data['text_status_order_paid'] = $this->language->get('text_status_order_paid');
        $data['text_status_order_shipped'] = $this->language->get('text_status_order_shipped');
        $data['text_action'] = $this->language->get('text_action');

        //Tooltips
        $data['text_edit_order'] = $this->language->get('text_edit_order');
        $data['text_select'] = $this->language->get('text_select');


        //buttons
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_remove'] = $this->language->get('button_remove');

        $data['ebay_order_default_status_id'] = '';
        $data['ebay_order_cancel_status_id'] = array();
        $data['ebay_order_shipped_status_id'] = array();
        if ($this->model_setting_kbebay->getSetting('ebay_order_settings', $store_id)) {
            $settings = $this->model_setting_kbebay->getSetting('ebay_order_settings', $store_id);
            $data['ebay_order_default_status_id'] = $settings['ebay_order_settings']['order']['default_status'];
            //$data['ebay_order_cancel_status_id'] = @$settings['ebay_order_settings']['order']['cancel_status'];
            $data['ebay_order_shipped_status_id'] = $settings['ebay_order_settings']['order']['shipped_status'];
        }
        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        if (isset($this->session->data['error'])) {
            $data['error'] = $this->session->data['error'];
            $data['error_warning'] = $this->session->data['error'];
            unset($this->session->data['error']);
        } else {
            $data['error'] = '';
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $active_tab['active_tab'] = 8;
        $data['tab_common'] = $this->load->controller($this->module_path . '/kbebay/common', $active_tab);

        if (VERSION >= '2.2.0.0') {
            $this->response->setOutput($this->load->view($this->module_path . '/kbebay/ebay_order', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path . '/kbebay/ebay_order.tpl', $data));
        }
    }

    public function support()
    {
        $this->load->language($this->module_path . '/kbebay');
        $this->document->setTitle($this->language->get('heading_title_main'));

        $this->load->model('setting/setting');
        $this->load->model('setting/kbebay');

        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link($this->extension_path, $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->url->link($this->module_path . '/kbebay', $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_support'),
            'href' => $this->url->link($this->module_path . '/kbebay/support', $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => ' :: '
        );
        $data['heading_title'] = $this->language->get('heading_title');
        $data['heading_title_main'] = $this->language->get('heading_title_main');
        $data['text_support'] = $this->language->get('text_support');

        $data['text_support_other'] = $this->language->get('text_support_other');
        $data['text_support_marketplace'] = $this->language->get('text_support_marketplace');
        $data['text_support_marketplace_descp'] = $this->language->get('text_support_marketplace_descp');
        $data['text_support_etsy'] = $this->language->get('text_support_etsy');
        $data['text_support_etsy_descp'] = $this->language->get('text_support_etsy_descp');
        $data['text_support_ebay'] = $this->language->get('text_support_ebay');
        $data['text_support_ebay_descp'] = $this->language->get('text_support_ebay_descp');
        $data['text_support_mab'] = $this->language->get('text_support_mab');
        $data['text_support_mab_descp'] = $this->language->get('text_support_mab_descp');
        $data['text_support_view_more'] = $this->language->get('text_support_view_more');
        $data['text_support_ticket1'] = $this->language->get('text_support_ticket1');
        $data['text_support_ticket2'] = $this->language->get('text_support_ticket2');
        $data['text_support_ticket3'] = $this->language->get('text_support_ticket3');
        $data['text_click_here'] = $this->language->get('text_click_here');
        $data['text_user_manual'] = $this->language->get('text_user_manual');
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $active_tab['active_tab'] = 7;
        $data['tab_common'] = $this->load->controller($this->module_path . '/kbebay/common', $active_tab);
        if (VERSION >= '2.2.0.0') {
            $this->response->setOutput($this->load->view($this->module_path . '/kbebay/support', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path . '/kbebay/support.tpl', $data));
        }
    }

    public function saveToken()
    {
        $response = array();
        $this->load->model('setting/kbebay');
        $this->load->language($this->module_path . '/kbebay');

        if (isset($this->request->post["ebay_country_id"])) {
            if ($this->request->post["status"] == 1) {
                if ($this->request->post["token"]) {
                    $call_name = 'GetSessionID';
                    $token = $this->request->post["token"];
                    $token = str_replace(" ", "+", $token);
                    $config = $this->model_setting_kbebay->getConfiguration();
                    if ($config['account_type'] == 'sandbox') {
                        $sandbox = true;
                    } else {
                        $sandbox = false;
                    }
                    $response = array("type" => "success", "message" => $this->language->get('token_saved'));
                    $headers = $this->model_setting_kbebay->getEbayHeaders($call_name, $this->request->post["ebay_country_id"]);
                    $resultJson = $this->model_setting_kbebay->getEbayAccount($headers, $token, $config['ru_name'], $sandbox);
                    $result = json_decode($resultJson, true);
                    if (isset($result['Ack']) && $result['Ack'] == 'Failure') {
                        $response = array("type" => "error", "message" => $this->language->get('token_error') . " " . $result['Errors']['ShortMessage']);
                    } else {
                        $this->db->query('UPDATE ' . DB_PREFIX . 'kb_ebay_sites SET token = "' . $token . '",enabled = ' . $this->request->post["status"] . ' where id_ebay_countries = "' . $this->request->post["ebay_country_id"] . '"');
                        $response = array("type" => "success", "message" => $this->language->get('token_saved'));
                    }
                } else {
                    $response = array("type" => "error", "message" => $this->language->get('token_blank_error'));
                }
            } else {
                $this->db->query('UPDATE ' . DB_PREFIX . 'kb_ebay_sites SET token = "' . str_replace(" ", "+", $this->request->post["token"]) . '",enabled = ' . $this->request->post["status"] . ' where id_ebay_countries = "' . $this->request->post["ebay_country_id"] . '"');
                $response = array("type" => "success", "message" => $this->language->get('token_saved'));
            }
        } else {
            $response = array("type" => "error", "message" => $this->language->get('token_error'));
        }
        echo json_encode($response);
        die();
    }

    public function profileManagement()
    {
        $this->checkGeneralSettings();
        if (isset($this->request->get['filter_profile_name'])) {
            $filter_profile_name = $this->request->get['filter_profile_name'];
        } else {
            $filter_profile_name = null;
        }
        if (isset($this->request->get['filter_ebay_category'])) {
            $filter_ebay_category = $this->request->get['filter_ebay_category'];
        } else {
            $filter_ebay_category = null;
        }
        if (isset($this->request->get['filter_store_category'])) {
            $filter_store_category = $this->request->get['filter_store_category'];
        } else {
            $filter_store_category = null;
        }
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }
        $this->load->language($this->module_path . '/kbebay');

        $this->document->setTitle($this->language->get('heading_title_main'));

        $this->load->model('setting/setting');
        $this->load->model('setting/kbebay');
        
        if (isset($this->request->get['action']) && $this->request->get['action'] == 'delete') {
            $result = $this->model_setting_kbebay->deleteProfile($this->request->get['id_ebay_profiles']);
            if($result == false) {
                $this->session->data['error'] = $this->language->get('text_error_delete_profile');
            } else {
                $this->session->data['success'] = $this->language->get('text_success_profile');
            }
        }

        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'id_ebay_profiles';
        }
        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array) $this->request->post['selected'];
        } else {
            $data['selected'] = array();
        }

        $url = '';
        $sort_url = '';
        $filter_url = '';

        if (isset($this->request->get['filter_profile_name'])) {
            $url .= '&filter_profile_name=' . urlencode(html_entity_decode($this->request->get['filter_profile_name'], ENT_QUOTES, 'UTF-8'));
            $sort_url .= '&filter_profile_name=' . urlencode(html_entity_decode($this->request->get['filter_profile_name'], ENT_QUOTES, 'UTF-8'));
            $filter_url .= '&filter_profile_name=' . urlencode(html_entity_decode($this->request->get['filter_profile_name'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_ebay_category'])) {
            $url .= '&filter_ebay_category=' . urlencode(html_entity_decode($this->request->get['filter_ebay_category'], ENT_QUOTES, 'UTF-8'));
            $sort_url .= '&filter_ebay_category=' . urlencode(html_entity_decode($this->request->get['filter_ebay_category'], ENT_QUOTES, 'UTF-8'));
            $filter_url .= '&filter_ebay_category=' . urlencode(html_entity_decode($this->request->get['filter_ebay_category'], ENT_QUOTES, 'UTF-8'));
        }
        if (isset($this->request->get['filter_store_category'])) {
            $url .= '&filter_store_category=' . urlencode(html_entity_decode($this->request->get['filter_store_category'], ENT_QUOTES, 'UTF-8'));
            $sort_url .= '&filter_store_category=' . urlencode(html_entity_decode($this->request->get['filter_store_category'], ENT_QUOTES, 'UTF-8'));
            $filter_url .= '&filter_store_category=' . urlencode(html_entity_decode($this->request->get['filter_store_category'], ENT_QUOTES, 'UTF-8'));
        }
        if (isset($this->request->get['filter_ebay_site'])) {
            $url .= '&filter_ebay_site=' . urlencode(html_entity_decode($this->request->get['filter_ebay_site'], ENT_QUOTES, 'UTF-8'));
            $sort_url .= '&filter_ebay_site=' . urlencode(html_entity_decode($this->request->get['filter_ebay_site'], ENT_QUOTES, 'UTF-8'));
            $filter_url .= '&filter_ebay_site=' . urlencode(html_entity_decode($this->request->get['filter_ebay_site'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['page'])) {
            $filter_url .= '&page=' . $this->request->get['page'];
            $sort_url .= '&page=' . $this->request->get['page'];
        }
        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
            $filter_url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
            $filter_url .= '&order=' . $this->request->get['order'];
        }

        if ($order == 'ASC') {
            $sort_url .= '&order=DESC';
        } else {
            $sort_url .= '&order=ASC';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link($this->extension_path, $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->url->link($this->module_path . '/kbebay', $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_profile'),
            'href' => $this->url->link($this->module_path . '/kbebay/profileManagement', $this->session_token_key . '=' . $this->session_token . $filter_url, 'SSL'),
            'separator' => ' :: '
        );

        $filter_data = array(
            'filter_profile_name' => $filter_profile_name,
            'filter_ebay_category' => $filter_ebay_category,
            'filter_store_category' => $filter_store_category,
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $data['sort_id_ebay_profiles'] = $this->url->link($this->module_path . '/kbebay/profileManagement', $this->session_token_key . '=' . $this->session_token . '&sort=id_ebay_profiles' . $sort_url, true);
        $data['sort_profile_name'] = $this->url->link($this->module_path . '/kbebay/profileManagement', $this->session_token_key . '=' . $this->session_token . '&sort=profile_name' . $sort_url, true);
        $data['sort_ebay_catgeory_text'] = $this->url->link($this->module_path . '/kbebay/profileManagement', $this->session_token_key . '=' . $this->session_token . '&sort=ebay_catgeory_text' . $sort_url, true);
        $data['sort_store_category_text'] = $this->url->link($this->module_path . '/kbebay/profileManagement', $this->session_token_key . '=' . $this->session_token . '&sort=store_category_text' . $sort_url, true);
        $data['sort_ebay_site'] = $this->url->link($this->module_path . '/kbebay/profileManagement', $this->session_token_key . '=' . $this->session_token . '&sort=s.description' . $sort_url, true);
        $data['sort_status'] = $this->url->link($this->module_path . '/kbebay/profileManagement', $this->session_token_key . '=' . $this->session_token . '&sort=status' . $sort_url, true);
        $data['sort_active'] = $this->url->link($this->module_path . '/kbebay/profileManagement', $this->session_token_key . '=' . $this->session_token . '&sort=active' . $sort_url, true);
        $data['sort_date_updated'] = $this->url->link($this->module_path . '/kbebay/profileManagement', $this->session_token_key . '=' . $this->session_token . '&sort=p.date_updated' . $sort_url, true);

        $profile_total = $this->model_setting_kbebay->getProfileTotal($filter_data);
        $profile_result = $this->model_setting_kbebay->getProfileDetails($filter_data);
        $data['profiles'] = array();
        foreach ($profile_result as $result) {
            $data['profiles'][] = array(
                'id_ebay_profiles' => $result['id_ebay_profiles'],
                'profile_name' => $result['profile_name'],
                'ebay_catgeory_text' => $result['ebay_catgeory_text'],
                'store_category_text' => $result['store_category_text'],
                'site_id' => $result['description'],
                'status' => $result['status'],
                'active' => ($result['active']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
                'edit' => $this->url->link($this->module_path . '/kbebay/profileUpdate', $this->session_token_key . '=' . $this->session_token . "&id_ebay_profiles=" . $result['id_ebay_profiles'] . $url, true),
                'delete' => $this->url->link($this->module_path . '/kbebay/profileManagement', $this->session_token_key . '=' . $this->session_token . "&action=delete&id_ebay_profiles=" . $result['id_ebay_profiles'] . $url, true)
            );
        }
        $pagination = new Pagination();
        $pagination->total = $profile_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link($this->module_path . '/kbebay/profileManagement', $this->session_token_key . '=' . $this->session_token . $url . '&page={page}', true);
        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($profile_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($profile_total - $this->config->get('config_limit_admin'))) ? $profile_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $profile_total, ceil($profile_total / $this->config->get('config_limit_admin')));

        //links
        $data['general_settings'] = $this->url->link($this->module_path . '/kbebay/generalSettings', $this->session_token_key . '=' . $this->session_token, 'SSL');
        $data['action'] = $this->url->link($this->module_path . '/kbebay', $this->session_token_key . '=' . $this->session_token, 'SSL');
        $data['route'] = $this->url->link($this->module_path . '/kbebay', $this->session_token_key . '=' . $this->session_token, 'SSL');
        $data['cancel'] = $this->url->link($this->module_path . '/kbebay', $this->session_token_key . '=' . $this->session_token, 'SSL');
        $data['delete'] = $this->url->link($this->module_path . '/kbebay/deleteProfile', $this->session_token_key . '=' . $this->session_token, 'SSL');
        $data['add'] = $this->url->link($this->module_path . '/kbebay/profileUpdate', $this->session_token_key . '=' . $this->session_token, true);
        $data['profile_management'] = $this->url->link($this->module_path . '/kbebay/profileManagement', $this->session_token_key . '=' . $this->session_token, true);

        $data['token'] = $this->session_token;
        $data['session_token_key'] = $this->session_token_key;

        $data['heading_title'] = $this->language->get('heading_title');
        $data['heading_title_main'] = $this->language->get('heading_title_main');
        $data['text_edit_profile'] = $this->language->get('text_edit_profile');

        // Filter info
        $data['text_filter_profile_name'] = $this->language->get('text_filter_profile_name');
        $data['text_filter_ebay_category'] = $this->language->get('text_filter_ebay_category');
        $data['text_filter_store_category'] = $this->language->get('text_filter_store_category');
        $data['text_filter_ebay_site'] = $this->language->get('text_filter_ebay_site');
        $data['column_profile_id'] = $this->language->get('column_profile_id');
        $data['column_profile_name'] = $this->language->get('column_profile_name');
        $data['column_ebay_catgeory_text'] = $this->language->get('column_ebay_catgeory_text');
        $data['column_store_category_text'] = $this->language->get('column_store_category_text');
        $data['column_ebay_site'] = $this->language->get('column_ebay_site');
        $data['column_status'] = $this->language->get('column_status');
        $data['column_active'] = $this->language->get('column_active');
        $data['column_action'] = $this->language->get('column_action');
        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['txt_profile_confirm_delete'] = $this->language->get('txt_profile_confirm_delete');

        //Tooltips
        //buttons
        $data['button_save'] = $this->language->get('button_save');
        $data['button_save_and_stay'] = $this->language->get('button_save_and_stay');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_add_module'] = $this->language->get('button_add_module');
        $data['button_remove'] = $this->language->get('button_remove');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['text_confirm'] = $this->language->get('text_confirm');
        $data['button_filter'] = $this->language->get('button_filter');
        $data['button_reset'] = $this->language->get('button_reset');
        $data['button_add'] = $this->language->get('button_add');
        $data['button_edit'] = $this->language->get('button_edit');
        $data['button_local_sync'] = $this->language->get('button_local_sync');

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        if (isset($this->session->data['error'])) {
            $data['error']['error_warning'] = $this->session->data['error'];
            unset($this->session->data['error']);
        } else {
            $data['error'] = '';
        }

        $data['sort'] = $sort;
        $data['order'] = strtolower($order);

        $data['filter_profile_name'] = $filter_profile_name;
        $data['filter_ebay_category'] = $filter_ebay_category;
        $data['filter_store_category'] = $filter_store_category;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $active_tab['active_tab'] = 2;
        $data['tab_common'] = $this->load->controller($this->module_path . '/kbebay/common', $active_tab);
        if (VERSION >= '2.2.0.0') {
            $this->response->setOutput($this->load->view($this->module_path . '/kbebay/profile', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path . '/kbebay/profile.tpl', $data));
        }
    }

    private function validate()
    {
        $this->error = array();
        $this->load->language($this->module_path . '/kbebay');
        if (!$this->user->hasPermission('modify', $this->module_path . '/kbebay')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        if (isset($this->request->post['ebay']['general'])) {
            if (!$this->request->post['ebay']['general']['dev_id']) {
                $this->error['ebay_dev_id'] = $this->language->get('error_ebay_dev_id');
            }
            if (!$this->request->post['ebay']['general']['app_id']) {
                $this->error['ebay_app_id'] = $this->language->get('error_ebay_app_id');
            }
            if (!$this->request->post['ebay']['general']['cert_id']) {
                $this->error['ebay_cert_id'] = $this->language->get('error_ebay_cert_id');
            }
            if (!$this->request->post['ebay']['general']['ru_name']) {
                $this->error['ebay_ru_name'] = $this->language->get('error_ebay_ru_name');
            }
            if (!$this->request->post['ebay']['general']['token']) {
                //$this->error['ebay_token'] = $this->language->get('error_ebay_token');
            }
            if (!$this->request->post['ebay']['general']['paypal_email']) {
                $this->error['ebay_paypal_email'] = $this->language->get('error_paypal_email');
            }
        }
        if (isset($this->request->post['ebay']['profile'])) {
            if (!$this->request->post['ebay']['profile']['profile_title']) {
                $this->error['ebay_profile_title'] = $this->language->get('error_ebay_profile_title');
            }
            if (empty($this->request->post['product_category'])) {
                $this->error['oc_store_category'] = $this->language->get('error_oc_store_category');
            }

            //if ($this->request->post['ebay']['profile']['product_quantity'] != "" && !ctype_digit($this->request->post['ebay']['profile']['product_quantity'])) {
            //    $this->error['ebay_product_quantity'] = $this->language->get('error_ebay_product_quantity_int');
            //} else if ($this->request->post['ebay']['profile']['product_quantity'] < 0) {
            //    $this->error['ebay_product_quantity'] = $this->language->get('error_ebay_product_quantity_negative');
            //}

            if ($this->request->post['ebay']['profile']['product_dispatch_time'] == "") {
                $this->error['ebay_product_dispatch_time'] = $this->language->get('error_ebay_product_dispatch_time');
            } else if (!ctype_digit($this->request->post['ebay']['profile']['product_dispatch_time'])) {
                $this->error['ebay_product_dispatch_time'] = $this->language->get('error_ebay_dispatch_time_int');
            }
            if (!empty($this->request->post['product_category'])) {
                if (isset($this->request->post['ebay']['profile']['id_ebay_profiles'])) {
                    $status = $this->model_setting_kbebay->checkProfileCategory($this->request->post['product_category'], $this->request->post['ebay']['profile']['ebay-sites'], $this->request->post['ebay']['profile']['id_ebay_profiles']);
                } else {
                    $status = $this->model_setting_kbebay->checkProfileCategory($this->request->post['product_category'], $this->request->post['ebay']['profile']['ebay-sites']);
                }
                if (!empty($status)) {
                    $this->error['oc_store_category'] = $this->language->get('ebay_text_error_category_already_mapped') . $status[0]['profile_name'];
                }
            }
            if ($this->request->post['ebay']['profile']['id_ebay_cat_final'] == '') {
                $this->error['ebay_category'] = $this->language->get('error_ebay_category');
            }
            if ($this->request->post['ebay']['profile']['ebay_duration'] == '') {
                $this->error['ebay_duration'] = $this->language->get('error_ebay_profile_duration');
            }
            if ($this->request->post['ebay']['profile']['ebay_product_condition'] == '') {
                $this->error['ebay_product_condition'] = $this->language->get('error_ebay_product_condition');
            }
        }
        if (isset($this->request->post['ebay']['order'])) {
            if ($this->request->post['ebay']['order']['default_status'] == '') {
                $this->error['ebay_default_status'] = $this->language->get('error_order_status_error');
            }
            if (empty($this->request->post['ebay']['order']['cancel_status'])) {
                //$this->error['ebay_cancel_status'] = $this->language->get('error_order_status_error');
            }
            if (empty($this->request->post['ebay']['order']['shipped_status'])) {
                $this->error['ebay_shipped_status'] = $this->language->get('error_order_status_error');
            }
        }

        if (!empty($this->error)) {
            return false;
        } else {
            return true;
        }
    }

    public function profileUpdate()
    {
        $this->checkGeneralSettings();

        $this->load->language($this->module_path . '/kbebay');

        $this->document->setTitle($this->language->get('heading_title_main'));

        $this->load->model('setting/setting');
        $this->load->model('setting/kbebay');
        $this->load->model('catalog/category');
        $this->load->model('localisation/currency');
        $this->load->model('localisation/language');

        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }
        $this->document->addScript('view/javascript/velovalidation.js');
        $data['label_select_currency'][0] = $this->language->get('label_select_currency');
        $data['shipping_profiles'][0] = $this->language->get('text_shipping_select_profile');
        $data['text_shipping_select_profile'] = $this->language->get('text_shipping_select_profile');
        $data['text_label_select_currency'] = $this->language->get('label_select_currency');
        $avaliablePaymentMethoods = array();
        $data['ebay_payment_method'] = array();
        if (isset($this->request->get['id_ebay_profiles'])) {
            $filter_data = array();
            $profile_details = $this->model_setting_kbebay->getProfileDetails($filter_data, $this->request->get['id_ebay_profiles']);
            if (count($profile_details) > 0) {
                $data['ebay']['profile'] = array();
                $currency_data = $this->model_localisation_currency->getCurrency($profile_details[0]['ebay_currency']);
                if ($currency_data) {
                    $data['ajaxProcessGetCategories'][$currency_data['currency_id']] = $currency_data['title'];
                }

                $shippingProfiles = $this->model_setting_kbebay->getShippingProfile($profile_details[0]['ebay_site']);
                foreach ($shippingProfiles as $shippingProfile) {
                    $data['shipping_profiles'][$shippingProfile['id_ebay_shipping']] = $shippingProfile['shipping_profile_name'];
                }

                if ($profile_details[0]['return_enable'] == 'ReturnsAccepted') {
                    $profile_details[0]['return_enable'] = 1;
                } else {
                    $profile_details[0]['return_enable'] = 0;
                }
                $data['ebay']['profile'] = $profile_details[0];

                $call_name = 'GetCategoryFeatures';
                $siteDetails = $this->model_setting_kbebay->getEbaySiteById($profile_details[0]['site_id']);
                $token = $siteDetails['token'];
                $config = $this->model_setting_kbebay->getConfiguration();
                if ($config['account_type'] == 'sandbox') {
                    $sandbox = true;
                } else {
                    $sandbox = false;
                }
                $headers = $this->model_setting_kbebay->getEbayHeaders($call_name, $profile_details[0]['site_id']);
                $avaliablePaymentRequest = $this->model_setting_kbebay->getCategoryFeatures($headers, $token, $profile_details[0]['ebay_category_id'], $sandbox, 'PaymentMethods');
                $avaliablePaymentRequestArray = json_decode($avaliablePaymentRequest, true);
                if (isset($avaliablePaymentRequestArray['SiteDefaults']['PaymentMethod'])) {
                    $avaliablePaymentMethoods = $avaliablePaymentRequestArray['SiteDefaults']['PaymentMethod'];
                }

                $data['ebay_select_sites'] = $profile_details[0]['site_id'];
                $data['ebay_select_duration'] = $profile_details[0]['duration'];
                $data['ebay_select_condition'] = $profile_details[0]['product_condition'];
                $data['ebay_vat_percentage'] = $profile_details[0]['vat_percentage'];
                $data['ebay_return_time'] = $profile_details[0]['return_days'];
                $data['ebay_return_type'] = $profile_details[0]['refund'];
                $data['ebay_return_shipping'] = $profile_details[0]['return_shipping'];
                $data['ebay_return_description'] = $profile_details[0]['return_description'];
                $data['selected_shipping_profile'] = $profile_details[0]['ebay_shipping_profile'];
                $data['ebay_language'] = $profile_details[0]['ebay_language'];
                $data['ebay_language'] = $profile_details[0]['ebay_language'];
                $data['ebay_payment_method'] = explode(",", $profile_details[0]['ebay_payment_method']);
                $data['vatenabled'] = $profile_details[0]['vatenabled'];
                $data['html_template'] = $profile_details[0]['html_template'];

                $categories = explode(",", $profile_details[0]['store_category_id']);
                $data['product_categories'] = array();
                foreach ($categories as $category_id) {
                    $category_info = $this->model_catalog_category->getCategory($category_id);
                    if ($category_info) {
                        $data['product_categories'][] = array(
                            'category_id' => $category_info['category_id'],
                            'name' => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
                        );
                    }
                }
                $data['ebay_currency'] = $profile_details[0]['ebay_currency'];
                $data['ebay_category_id'] = $profile_details[0]['ebay_category_id'];
                $data['site_id'] = $profile_details[0]['site_id'];
                $data['id_ebay_profiles'] = $profile_details[0]['id_ebay_profiles'];
                $data['ebay_catgeory_text'] = $profile_details[0]['ebay_catgeory_text'];
                $data['price_management'] = $profile_details[0]['price_management'];
                $data['increase_decrease'] = $profile_details[0]['increase_decrease'];
                $data['product_price'] = $profile_details[0]['product_price'];
                $data['product_threshold_price'] = $profile_details[0]['product_threshold_price'];
                $data['percentage_fixed'] = $profile_details[0]['percentage_fixed'];
                $data['store_category'] = $profile_details[0]['store_category'];

                /* Find ebay store category  */
                $ebay_store_categories = $this->model_setting_kbebay->getEbayStoreCategory($profile_details[0]['site_id']);

                /* Find Currencies from the ebay_sites table & check if exist in Opencart OR not */
                $currency_iso = $this->model_setting_kbebay->getCurrency($profile_details[0]['site_id']);
                $currency_iso_array = explode(",", $currency_iso['currency_iso_code']);
                if (!empty($currency_iso_array)) {
                    foreach ($currency_iso_array as $currency_iso_item) {
                        $currency_data = $this->model_localisation_currency->getCurrencyByCode($currency_iso_item);
                        if ($currency_data) {
                            $data['label_select_currency'][$currency_data['code']] = $currency_data['title'];
                        }
                    }
                }
            } else {
                $this->session->data['error'] = $this->language->get('ebay_invalid_profile');
                $this->response->redirect($this->url->link($this->module_path . '/kbebay/profileManagement', $this->session_token_key . '=' . $this->session_token, 'SSL'));
            }
        } else {
            $data['ebay_select_sites'] = "";
            $data['ebay_select_duration'] = "";
            $data['ebay_select_condition'] = "";
            $data['ebay_vat_percentage'] = "";
            $data['ebay_return_time'] = "";
            $data['ebay_return_type'] = "";
            $data['ebay_return_shipping'] = "";
            $data['ebay_return_description'] = "";
            $data['ebay_language'] = "";
            $data['selected_shipping_profile'] = 0;
            $data['ebay_currency'] = "";
            $data['vatenabled'] = "no";
            $data['store_category'] = "";
            $data['html_template'] = "{product_description}";
            $ebay_store_categories = array();
            $categories = array();
            $data['product_categories'] = array();
            foreach ($categories as $category_id) {
                $category_info = $this->model_catalog_category->getCategory($category_id);
                if ($category_info) {
                    $data['product_categories'][] = array(
                        'category_id' => $category_info['category_id'],
                        'name' => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
                    );
                }
            }
            $data['ebay']['profile'] = array(
                'return_enable' => 0,
                'percentage_fixed' => 0,
                'product_price' => "",
                'product_threshold_price' => "",
                'price_management' => 0,
                'increase_decrease' => 0
            );
        }
        $data['avaliablePaymentMethoods'] = $avaliablePaymentMethoods;

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link($this->extension_path, $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->url->link($this->module_path . '/kbebay', $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_profile'),
            'href' => $this->url->link($this->module_path . '/kbebay/profileManagement', $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => ' :: '
        );

        if (isset($this->request->get['id_ebay_profiles'])) {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title_profile_edit'),
                'href' => $this->url->link($this->module_path . '/kbebay/profileUpdate', $this->session_token_key . '=' . $this->session_token, 'SSL'),
                'separator' => ' :: '
            );
        } else {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title_profile_add'),
                'href' => $this->url->link($this->module_path . '/kbebay/profileUpdate', $this->session_token_key . '=' . $this->session_token, 'SSL'),
                'separator' => ' :: '
            );
        }

        //links
        $data['cancel'] = $this->url->link($this->module_path . '/kbebay/profileManagement', $this->session_token_key . '=' . $this->session_token, 'SSL');
        $data['token'] = $this->session_token;
        $data['session_token_key'] = $this->session_token_key;
        $data['module_path'] = $this->module_path;

        $data['heading_title'] = $this->language->get('heading_title');
        $data['heading_title_main'] = $this->language->get('heading_title_main');


        // General Settings tab & info
        $data['save_general'] = $this->language->get('save_general');
        $data['text_data_tab'] = $this->language->get('text_data_tab');
        $data['text_specifics'] = $this->language->get('text_specifics');
        $data['text_profile_title'] = $this->language->get('text_profile_title');
        $data['text_ebay_category'] = $this->language->get('text_ebay_category');
        $data['text_store_category'] = $this->language->get('text_store_category');
        $data['text_shipping_template'] = $this->language->get('text_shipping_template');
        $data['text_product_update'] = $this->language->get('text_product_update');
        $data['text_product_id'] = $this->language->get('text_product_id');
        $data['text_product_name'] = $this->language->get('text_product_name');
        $data['text_product_sku'] = $this->language->get('text_product_sku');
        $data['text_product_price'] = $this->language->get('text_product_price');
        $data['product_threshold_price'] = $this->language->get('product_threshold_price');
        $data['product_threshold_price_hint'] = $this->language->get('product_threshold_price_hint');
        $data['text_select'] = $this->language->get('text_select');
        $data['text_vat_percentage_hint'] = $this->language->get('text_vat_percentage_hint');
        $data['text_field_empty_error'] = $this->language->get('text_field_empty_error');
        $data['error_ebay_product_quantity_int'] = $this->language->get('error_ebay_product_quantity_int');
        $data['text_select_ebay_store_category'] = $this->language->get('text_select_ebay_store_category');
        $data['text_ebay_store_category'] = $this->language->get('text_ebay_store_category');
        $data['product_description_template'] = $this->language->get('product_description_template');
        $data['text_html_template_hint'] = $this->language->get('text_html_template_hint');
        
        if (isset($this->request->get['id_ebay_profiles'])) {
            $sync_store_category_url = HTTPS_CATALOG . 'index.php?route=ebay_feed/cron/getStoreCategories&site_id=' . $profile_details[0]['site_id'];
        } else {
            $sync_store_category_url = HTTPS_CATALOG . 'index.php?route=ebay_feed/cron/getStoreCategories&site_id=';
        }
        $data['sync_store_category'] = sprintf($this->language->get('sync_store_category'), $sync_store_category_url);
        $data['sync_store_category_url'] = $sync_store_category_url;

        $data['text_product_quantity'] = $this->language->get('text_product_quantity');
        $data['text_status'] = $this->language->get('text_status');
        $data['text_action'] = $this->language->get('text_action');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_select_attr'] = $this->language->get('text_select_attr');
        $data['text_ebay_sites'] = $this->language->get('text_ebay_sites');
        $data['text_ebay_category_select'] = $this->language->get('text_ebay_category_select');
        $data['text_duration'] = $this->language->get('text_duration');
        $data['text_product_condition'] = $this->language->get('text_product_condition');
        $data['text_product_quantity'] = $this->language->get('text_product_quantity');
        $data['text_product_dispatch_time'] = $this->language->get('text_product_dispatch_time');
        $data['text_vat_percentage'] = $this->language->get('text_vat_percentage');
        $data['text_profile_return'] = $this->language->get('text_profile_return');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_return_time'] = $this->language->get('text_return_time');
        $data['text_return_type'] = $this->language->get('text_return_type');
        $data['text_return_shipping'] = $this->language->get('text_return_shipping');
        $data['text_return_description'] = $this->language->get('text_return_description');
        $data['text_payment_method'] = $this->language->get('text_payment_method');

        $data['site_add_text'] = sprintf($this->language->get('site_add_text'), $this->url->link($this->module_path . '/kbebay/sites', $this->session_token_key . '=' . $this->session_token, 'SSL'));

        $data['text_select_currency'] = $this->language->get('text_select_currency');
        $data['text_shipping_profile'] = $this->language->get('text_shipping_profile');

        $data['text_select_language'] = $this->language->get('text_select_language');
        $data['text_select_shipping_profile'] = $this->language->get('text_select_shipping_profile');
        $data['text_alter_quantity'] = $this->language->get('text_alter_quantity');
        $data['text_quantity_hint'] = $this->language->get('text_quantity_hint');
        $data['text_price_management'] = $this->language->get('text_price_management');
        $data['text_increase_decrese_price'] = $this->language->get('text_increase_decrese_price');
        $data['text_price_value'] = $this->language->get('text_price_value');
        $data['text_price_percentage_fixed'] = $this->language->get('text_price_percentage_fixed');
        $data['text_duration_hint'] = $this->language->get('text_duration_hint');
        $data['text_increase_price'] = $this->language->get('text_increase_price');
        $data['text_decrease_price'] = $this->language->get('text_decrease_price');
        $data['text_price_fixed'] = $this->language->get('text_price_fixed');
        $data['text_price_percentage'] = $this->language->get('text_price_percentage');


        $data['text_change_category'] = $this->language->get('text_change_category');
        $data['text_confirm_category'] = $this->language->get('text_confirm_category');
        $data['select_leaf'] = $this->language->get('select_leaf');

        $data['price_value_type'] = array(
            "0" => $this->language->get('text_price_percentage'),
            "1" => $this->language->get('text_price_fixed')
        );

        //Tooltips
        if (isset($this->request->get['id_ebay_profiles'])) {
            $data['text_edit_profile_add'] = $this->language->get('text_edit_profile_edit');
        } else {
            $data['text_edit_profile_add'] = $this->language->get('text_edit_profile_add');
        }

        //buttons
        $data['button_save'] = $this->language->get('button_save');
        $data['button_save_and_stay'] = $this->language->get('button_save_and_stay');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_add_module'] = $this->language->get('button_add_module');
        $data['button_remove'] = $this->language->get('button_remove');
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        if (isset($this->session->data['error'])) {
            $data['error'] = $this->session->data['error'];
            unset($this->session->data['error']);
        } else {
            $data['error'] = '';
        }
        if (isset($this->error['walmart_profile_title'])) {
            $data['error_walmart_profile_title'] = $this->error['walmart_profile_title'];
        } else {
            $data['error_walmart_profile_title'] = '';
        }
        if (isset($this->error['walmart_store_category'])) {
            $data['error_walmart_store_category'] = $this->error['walmart_store_category'];
        } else {
            $data['error_walmart_store_category'] = '';
        }
        if (isset($this->error['ebay_product_quantity'])) {
            $data['error_ebay_product_quantity'] = $this->error['ebay_product_quantity'];
        } else {
            $data['error_ebay_product_quantity'] = '';
        }
        if (isset($this->error['ebay_product_dispatch_time'])) {
            $data['error_ebay_product_dispatch_time'] = $this->error['ebay_product_dispatch_time'];
        } else {
            $data['error_ebay_product_dispatch_time'] = '';
        }
        $ebay_sites = $this->model_setting_kbebay->getEbaySites(1);
        array_unshift($ebay_sites, array('id_ebay_countries' => 'select', 'description' => $this->language->get('text_ebay_site_select')));
        $ebay_dispatch_time = array(
            '' => array(
                'name' => $this->language->get('text_handling_time')
            ),
            '0' => array(
                'name' => $this->language->get('text_same_business_day')
            ),
            '1' => array(
                'name' => $this->language->get('1_day')
            ),
            '2' => array(
                'name' => $this->language->get('2_day')
            ),
            '3' => array(
                'name' => $this->language->get('3_day')
            ),
            '4' => array(
                'name' => $this->language->get('4_day')
            ),
            '5' => array(
                'name' => $this->language->get('5_day')
            ),
            '6' => array(
                'name' => $this->language->get('10_day')
            ),
            '7' => array(
                'name' => $this->language->get('15_day')
            ),
            '8' => array(
                'name' => $this->language->get('20_day')
            ),
            '9' => array(
                'name' => $this->language->get('30_day')
            ),
        );

        $ebay_duration = array(
            '0' => array(
                'id_type' => '',
                'name' => $this->language->get('text_ebay_duration')
            ),
            '1' => array(
                'id_type' => 'Days_1',
                'name' => $this->language->get('1_day')
            ),
            '2' => array(
                'id_type' => 'Days_3',
                'name' => $this->language->get('3_day')
            ),
            '3' => array(
                'id_type' => 'Days_5',
                'name' => $this->language->get('5_day')
            ),
            '4' => array(
                'id_type' => 'Days_7',
                'name' => $this->language->get('7_day')
            ),
            '5' => array(
                'id_type' => 'Days_10',
                'name' => $this->language->get('10_day')
            ),
            '6' => array(
                'id_type' => 'Days_30',
                'name' => $this->language->get('30_day')
            ),
            '7' => array(
                'id_type' => 'GTC',
                'name' => $this->language->get('GTC')
            ),
        );
        $product_condition = array(
            '0' => array(
                'id_type' => '',
                'name' => $this->language->get('text_ebay_product_condition')
            ),
            '1' => array(
                'id_type' => '1000',
                'name' => $this->language->get('condition_new')
            ),
            '2' => array(
                'id_type' => '1750',
                'name' => $this->language->get('condition_new_defects')
            ),
            '3' => array(
                'id_type' => '2000',
                'name' => $this->language->get('condition_man_ref')
            ),
            '4' => array(
                'id_type' => '2500',
                'name' => $this->language->get('condition_sel_ref')
            ),
            '5' => array(
                'id_type' => '3000',
                'name' => $this->language->get('condition_used')
            ),
            '6' => array(
                'id_type' => '4000',
                'name' => $this->language->get('condition_used_vgood')
            ),
            '6' => array(
                'id_type' => '5000',
                'name' => $this->language->get('condition_used_good')
            ),
        );
        $return_time = array(
            '0' => array(
                'id' => 'Days_14',
                'name' => $this->language->get('Days_14')
            ),
            '1' => array(
                'id' => 'Days_30',
                'name' => $this->language->get('Days_30')
            ),
            '2' => array(
                'id' => 'Days_60',
                'name' => $this->language->get('Days_60')
            ),
        );
        $return_type = array(
            '0' => array(
                'id' => 'MoneyBack',
                'name' => $this->language->get('money_back')
            ),
            '1' => array(
                'id' => 'MoneyBackOrReplacement',
                'name' => $this->language->get('money_back_replacement')
            ),
            '2' => array(
                'id' => 'MoneyBackOrExchange',
                'name' => $this->language->get('money_back_exchange')
            ),
        );
        $return_shipping = array(
            '0' => array(
                'id' => 'Buyer',
                'name' => $this->language->get('text_buyer')
            ),
            '1' => array(
                'id' => 'Seller',
                'name' => $this->language->get('text_seller')
            ),
        );
        $data['allcurrencies'] = array();
        $results = $this->model_localisation_currency->getCurrencies();
        foreach ($results as $result) {
            if ($result['status']) {
                $data['allcurrencies'][] = array(
                    'currency_id' => $result['currency_id'],
                    'title' => $result['title'],
                    'code' => $result['code'],
                    'symbol_left' => $result['symbol_left'],
                    'symbol_right' => $result['symbol_right']
                );
            }
        }
        $data['languages'] = $this->model_localisation_language->getLanguages();

        $data['ebay_store_categories'] = $ebay_store_categories;
        $data['ebay_sites'] = $ebay_sites;
        $data['ebay_duration'] = $ebay_duration;
        $data['ebay_dispatch_time'] = $ebay_dispatch_time;
        $data['product_condition'] = $product_condition;
        $data['return_time'] = $return_time;
        $data['return_type'] = $return_type;
        $data['return_shipping'] = $return_shipping;
        $data['image_loader'] = HTTPS_SERVER . 'view/image/loader.gif';
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $active_tab['active_tab'] = 2;
        $data['tab_common'] = $this->load->controller($this->module_path . '/kbebay/common', $active_tab);
        
        if (VERSION >= '2.2.0.0') {
            $this->response->setOutput($this->load->view($this->module_path . '/kbebay/profile_update', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path . '/kbebay/profile_update.tpl', $data));
        }
    }

    public function synchronization()
    {
        $this->checkGeneralSettings();
        $this->load->language($this->module_path . '/kbebay');

        $this->document->setTitle($this->language->get('heading_title_main'));
        $this->load->model('setting/setting');
        $this->load->model('setting/kbebay');

        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link($this->extension_path, $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->url->link($this->module_path . '/kbebay', $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => ' :: '
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_sync'),
            'href' => $this->url->link($this->module_path . '/kbebay/Synchronization', $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => ' :: '
        );

        //links
        $base_url = HTTPS_CATALOG;
        $kbebay_secure_key = $this->model_setting_kbebay->getSetting('kbebay_secure_key');
        $secure_key = $kbebay_secure_key['kbebay_secure_key_key'];

        $data['order_sync_link'] = $base_url . 'index.php?route=ebay_feed/cron/processGetOrderFromEbay&key=' . $secure_key;
        $data['local_sync_ebay_link'] = $base_url . 'index.php?route=ebay_feed/cron/syncLocal&key=' . $secure_key;
        $data['product_sync_ebay_link'] = $base_url . 'index.php?route=ebay_feed/cron/listProductToEbay&key=' . $secure_key;
        $data['order_sync_ebay_link'] = $base_url . 'index.php?route=ebay_feed/cron/processOrderUpdate&key=' . $secure_key;
        $data['product_relist_ebay_link'] = $base_url . 'index.php?route=ebay_feed/cron/processRelistProducts&key=' . $secure_key;
        $data['product_delete_ebay_link'] = $base_url . 'index.php?route=ebay_feed/cron/processEndProducts&key=' . $secure_key;
        $data['product_revise_ebay_link'] = $base_url . 'index.php?route=ebay_feed/cron/listProductToEbay&revise=1&key=' . $secure_key;
        $data['product_report_ebay_link'] = $base_url . 'index.php?route=ebay_feed/cron/processEbayReport&key=' . $secure_key;
        $data['category_sync_link'] = $this->url->link($this->module_path . '/kbebay/ajaxProcessEbayCategoryimport', $this->session_token_key . '=' . $this->session_token, 'SSL');

        $data['token'] = $this->session_token;
        $data['session_token_key'] = $this->session_token_key;

        $data['image_loader'] = HTTPS_SERVER . 'view/image/loader.gif';

        $data['heading_title'] = $this->language->get('heading_title');
        $data['heading_title_main'] = $this->language->get('heading_title_main');


        $data['text_local_sync'] = $this->language->get('text_local_sync');
        $data['local_sync_cron_hint'] = $this->language->get('local_sync_cron_hint');
        
        $data['text_sync_product'] = $this->language->get('text_sync_product');
        $data['text_sync_product_hint'] = $this->language->get('text_sync_product_hint');
        
        $data['text_status'] = $this->language->get('text_status');
        $data['text_action'] = $this->language->get('text_action');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_cron_every_15_mins'] = $this->language->get('text_cron_every_15_mins');
        $data['text_cron_once_a_day'] = $this->language->get('text_cron_once_a_day');
        $data['text_cron_every_30_mins'] = $this->language->get('text_cron_every_30_mins');
        $data['text_cron_every_hour'] = $this->language->get('text_cron_every_hour');
        $data['text_cron_every_3_hours'] = $this->language->get('text_cron_every_3_hours');
        
        $data['text_edit_general'] = $this->language->get('text_edit_general');
        $data['text_edit_sync'] = $this->language->get('text_edit_sync');
        $data['text_price_sync'] = $this->language->get('text_price_sync');
        $data['text_inv_sync'] = $this->language->get('text_inv_sync');
        $data['text_order_download'] = $this->language->get('text_order_download');
        $data['text_order_ack'] = $this->language->get('text_order_ack');
        $data['text_update_shipment'] = $this->language->get('text_update_shipment');
        $data['text_feed_status'] = $this->language->get('text_feed_status');
        $data['text_sync_product'] = $this->language->get('text_sync_product');
        $data['text_product_sync_default'] = $this->language->get('text_product_sync_default');
        $data['text_product_sync_to_ebay'] = $this->language->get('text_product_sync_to_ebay');
        $data['text_product_relist_to_ebay'] = $this->language->get('text_product_relist_to_ebay');
        $data['text_product_revise_to_ebay'] = $this->language->get('text_product_revise_to_ebay');
        $data['text_product_delete_to_ebay'] = $this->language->get('text_product_delete_to_ebay');
        $data['text_product_delete_button'] = $this->language->get('text_product_delete_button');
        $data['text_product_list_report_ebay'] = $this->language->get('text_product_list_report_ebay');
        $data['text_product_list_report_button'] = $this->language->get('text_product_list_report_button');
        $data['text_edit_product_sync'] = $this->language->get('text_edit_product_sync');
        $data['text_edit_order_sync'] = $this->language->get('text_edit_order_sync');
        $data['text_sync_ebay_orders'] = $this->language->get('text_sync_ebay_orders');
        $data['text_update_order_status'] = $this->language->get('text_update_order_status');
        $data['text_sync_order'] = $this->language->get('text_sync_order');
        $data['text_update_order'] = $this->language->get('text_update_order');
        
        $data['product_relist_sync_cron_hint'] = $this->language->get('product_relist_sync_cron_hint');
        $data['product_delete_cron_hint'] = $this->language->get('product_delete_cron_hint');
        $data['product_update_sync_cron_hint'] = $this->language->get('product_update_sync_cron_hint');
        $data['product_status_sync_cron_hint'] = $this->language->get('product_status_sync_cron_hint');
        $data['order_sync_cron_hint'] = $this->language->get('order_sync_cron_hint');
        $data['order_status_sync_cron_hint'] = $this->language->get('order_status_sync_cron_hint');
        
        //buttons
        $data['text_cron_config'] = $this->language->get('text_cron_config');
        $data['text_cron_config_help'] = $this->language->get('text_cron_config_help');
        $data['text_cron_via_cp'] = $this->language->get('text_cron_via_cp');
        $data['text_cron_via_ssh'] = $this->language->get('text_cron_via_ssh');

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        if (isset($this->session->data['error'])) {
            $data['error'] = $this->session->data['error'];
            unset($this->session->data['error']);
        } else {
            $data['error'] = '';
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $active_tab['active_tab'] = 6;
        $data['tab_common'] = $this->load->controller($this->module_path . '/kbebay/common', $active_tab);

        if (VERSION >= '2.2.0.0') {
            $this->response->setOutput($this->load->view($this->module_path . '/kbebay/synchronization', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path . '/kbebay/synchronization.tpl', $data));
        }
    }

    public function productListing()
    {
        $this->checkGeneralSettings();

        if (isset($this->request->get['filter_product_name'])) {
            $filter_product_name = $this->request->get['filter_product_name'];
        } else {
            $filter_product_name = null;
        }

        if (isset($this->request->get['filter_listing_status'])) {
            $filter_listing_status = $this->request->get['filter_listing_status'];
        } else {
            $filter_listing_status = null;
        }

        if (isset($this->request->get['filter_ebay_profile'])) {
            $filter_ebay_profile = $this->request->get['filter_ebay_profile'];
        } else {
            $filter_ebay_profile = null;
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $this->load->language($this->module_path . '/kbebay');
        $this->document->setTitle($this->language->get('heading_title_main'));
        $this->load->model('setting/setting');
        $this->load->model('setting/kbebay');
        $this->load->model('tool/image');

        /* Update Profile Code */
        $flag = false;
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if (isset($this->request->post['switch_profile']) && $this->request->post['switch_profile'] == 'switch_profile') {
                if ($this->request->post['selected_products'] != "") {
                    $products = explode(",", $this->request->post['selected_products']);
                    foreach ($products as $product) {
                        if ($this->request->post['profile'] != "") {
                            $flag = $this->model_setting_kbebay->switchProfile($product, $this->request->post['profile']);
                        }
                    }
                }
            }
        }
        
        if(isset($this->request->get['action']) && $this->request->get['action'] != "") {
            if($this->request->get['action'] == "disable") {
                $this->model_setting_kbebay->disableProduct($this->request->get["id_ebay_profile_products"]);
                $this->session->data['success'] = $this->language->get('disable_product_success');
            }
            
            if($this->request->get['action'] == "enable") {
                $this->model_setting_kbebay->enableProduct($this->request->get["id_ebay_profile_products"]);
                $this->session->data['success'] = $this->language->get('enable_product_success');
            }
        }
        
        if(isset($this->request->get['bulk_action']) && $this->request->get['bulk_action'] != "") {
            if(isset($this->request->post['selected']) && count($this->request->post['selected']) > 0) {
                if($this->request->get['bulk_action'] == "disable") {
                    if (isset($this->request->post['selected'])) {
                        foreach($this->request->post['selected'] as $selected) {
                            $this->model_setting_kbebay->disableProduct($selected);
                        }
                    }
                    $this->session->data['success'] = $this->language->get('disable_products_success');
                }

                if($this->request->get['bulk_action'] == "enable") {
                    if (isset($this->request->post['selected'])) {
                        foreach($this->request->post['selected'] as $selected) {
                            $this->model_setting_kbebay->enableProduct($selected);
                        }
                    }
                    $this->session->data['success'] = $this->language->get('enable_products_success');
                }
            } else {
                $this->session->data['error'] = $this->language->get('select_product_error');
            }
        }        

        if ($flag == true) {
            $this->session->data['error'] = $this->language->get('txt_profile_switch_error');
        }

        if (isset($this->request->post['selected_products'])) {
            $data['selected'] = (array) explode(",", $this->request->post['selected_products']);
        } else {
            $data['selected'] = array();
        }

        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'id_ebay_profile_products';
        }
        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        $url = '';
        $filter_url = '';
        $sort_url = '';
        if (isset($this->request->get['filter_product_name'])) {
            $url .= '&filter_product_name=' . urlencode(html_entity_decode($this->request->get['filter_product_name'], ENT_QUOTES, 'UTF-8'));
            $filter_url .= '&filter_product_name=' . urlencode(html_entity_decode($this->request->get['filter_product_name'], ENT_QUOTES, 'UTF-8'));
            $sort_url .= '&filter_product_name=' . urlencode(html_entity_decode($this->request->get['filter_product_name'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_listing_status'])) {
            $url .= '&filter_listing_status=' . urlencode(html_entity_decode($this->request->get['filter_listing_status'], ENT_QUOTES, 'UTF-8'));
            $filter_url .= '&filter_listing_status=' . urlencode(html_entity_decode($this->request->get['filter_listing_status'], ENT_QUOTES, 'UTF-8'));
            $sort_url .= '&filter_listing_status=' . urlencode(html_entity_decode($this->request->get['filter_listing_status'], ENT_QUOTES, 'UTF-8'));
        }
        if (isset($this->request->get['filter_ebay_profile'])) {
            $url .= '&filter_ebay_profile=' . urlencode(html_entity_decode($this->request->get['filter_ebay_profile'], ENT_QUOTES, 'UTF-8'));
            $filter_url .= '&filter_ebay_profile=' . urlencode(html_entity_decode($this->request->get['filter_ebay_profile'], ENT_QUOTES, 'UTF-8'));
            $sort_url .= '&filter_ebay_profile=' . urlencode(html_entity_decode($this->request->get['filter_ebay_profile'], ENT_QUOTES, 'UTF-8'));
        }
        if (isset($this->request->get['filter_min_proc_days'])) {
            $url .= '&filter_min_proc_days=' . urlencode(html_entity_decode($this->request->get['filter_min_proc_days'], ENT_QUOTES, 'UTF-8'));
            $filter_url .= '&filter_min_proc_days=' . urlencode(html_entity_decode($this->request->get['filter_min_proc_days'], ENT_QUOTES, 'UTF-8'));
            $sort_url .= '&filter_min_proc_days=' . urlencode(html_entity_decode($this->request->get['filter_min_proc_days'], ENT_QUOTES, 'UTF-8'));
        }
        if (isset($this->request->get['filter_max_proc_days'])) {
            $url .= '&filter_max_proc_days=' . urlencode(html_entity_decode($this->request->get['filter_max_proc_days'], ENT_QUOTES, 'UTF-8'));
            $filter_url .= '&filter_max_proc_days=' . urlencode(html_entity_decode($this->request->get['filter_max_proc_days'], ENT_QUOTES, 'UTF-8'));
            $sort_url .= '&filter_max_proc_days=' . urlencode(html_entity_decode($this->request->get['filter_max_proc_days'], ENT_QUOTES, 'UTF-8'));
        }
        if (isset($this->request->get['page'])) {
            $filter_url .= '&page=' . $this->request->get['page'];
            $sort_url .= '&page=' . $this->request->get['page'];
        }
        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
            $filter_url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
            $filter_url .= '&order=' . $this->request->get['order'];
        }

        if ($order == 'ASC') {
            $sort_url .= '&order=DESC';
        } else {
            $sort_url .= '&order=ASC';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link($this->extension_path, $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->url->link($this->module_path . '/kbebay', $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_product_list'),
            'href' => $this->url->link($this->module_path . '/kbebay/productListing', $this->session_token_key . '=' . $this->session_token . $filter_url, 'SSL'),
            'separator' => ' :: '
        );

        $filter_data = array(
            'filter_product_name' => $filter_product_name,
            'filter_listing_status' => $filter_listing_status,
            'filter_ebay_profile' => $filter_ebay_profile,
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $data['sort_product_list_id'] = $this->url->link($this->module_path . '/kbebay/productListing', $this->session_token_key . '=' . $this->session_token . '&sort=pl.id_ebay_profile_products' . $sort_url, true);
        $data['sort_listing_id'] = $this->url->link($this->module_path . '/kbebay/productListing', $this->session_token_key . '=' . $this->session_token . '&sort=pl.ebay_listiing_id' . $sort_url, true);
        $data['sort_product_name'] = $this->url->link($this->module_path . '/kbebay/productListing', $this->session_token_key . '=' . $this->session_token . '&sort=pd.name' . $sort_url, true);
        $data['sort_profile_name'] = $this->url->link($this->module_path . '/kbebay/productListing', $this->session_token_key . '=' . $this->session_token . '&sort=ep.profile_name' . $sort_url, true);
        $data['sort_list_status'] = $this->url->link($this->module_path . '/kbebay/productListing', $this->session_token_key . '=' . $this->session_token . '&sort=pl.status' . $sort_url, true);
        $data['sort_ebay_list_status'] = $this->url->link($this->module_path . '/kbebay/productListing', $this->session_token_key . '=' . $this->session_token . '&sort=pl.ebay_status' . $sort_url, true);
        $data['sort_list_date'] = $this->url->link($this->module_path . '/kbebay/productListing', $this->session_token_key . '=' . $this->session_token . '&sort=pl.date_added' . $sort_url, true);
        $data['sort_listing_id'] = $this->url->link($this->module_path . '/kbebay/productListing', $this->session_token_key . '=' . $this->session_token . '&sort=pl.ebay_listiing_id' . $sort_url, true);

        $product_list_total = $this->model_setting_kbebay->getTotalProductListed($filter_data);
        $product_list_details = $this->model_setting_kbebay->getProductListed($filter_data);
        $data['product_listed'] = array();

        foreach ($product_list_details as $result) {
            if (is_file(DIR_IMAGE . $result['image'])) {
                $image = $this->model_tool_image->resize($result['image'], 40, 40);
            } else {
                $image = $this->model_tool_image->resize('no_image.png', 40, 40);
            }
            $product_error = $this->model_setting_kbebay->getProductError($result['id_product'], $result['id_ebay_profiles']);
            if (!empty($product_error)) {
                $data_error = json_decode($product_error['error'], true);
                if (isset($data_error['LongMessage'])) {
                    if ($data_error['SeverityCode'] == 'Error') {
                        $error_message = $data_error['LongMessage'];
                    }
                } else {
                    $error_message = '';
                    foreach ($data_error as $data_error_item) {
                        if ($data_error_item['SeverityCode'] == 'Error') {
                            if ($error_message == "") {
                                $error_message .= $data_error_item['LongMessage'];
                            } else {
                                $error_message .= "\n" . $data_error_item['LongMessage'];
                            }
                        }
                    }
                }
            } else {
                $error_message = "";
            }
            
            $status = $result['status'];
            if($result["is_disabled"] == 1) {
                $status = 'Disabled';
            }
            
            $data['product_listed'][] = array(
                'id_ebay_profile_products' => $result['id_ebay_profile_products'],
                'name' => $result['name'],
                'image' => $image,
                'ebay_listiing_id' => $result['ebay_listiing_id'],
                'profile_name' => $result['profile_name'] . " (" . $result['site_name'] . ")",
                'status' => $status,
                'is_disabled' => $result['is_disabled'],
                'ebay_status' => $result['ebay_status'],
                'date_added' => $result['date_added'],
                'product_id' => $result['id_product'],
                'item_url' => $result['item_url'],
                'message' => $error_message,
                'admin_edit_link' => $this->url->link('catalog/product/edit', $this->session_token_key . '=' . $this->session_token . "&product_id=" . $result['id_product'], true),
                'url_delete' => $this->url->link($this->module_path . '/kbebay/deleteListedProduct', $this->session_token_key . '=' . $this->session_token . $filter_url . "&id=" . $result['id_product'] . "&profile_id=" . $result['id_ebay_profiles'], true),
                'url_red' => $this->url->link($this->module_path . '/kbebay/productListing', $this->session_token_key . '=' . $this->session_token . $filter_url . "&profile_id=" . $result['id_ebay_profiles'], true),
                'url_relist' => $this->url->link($this->module_path . '/kbebay/relistProduct', $this->session_token_key . '=' . $this->session_token . $filter_url . "&id=" . $result['id_product'] . "&profile_id=" . $result['id_ebay_profiles'], true),
                'url_revise' => $this->url->link($this->module_path . '/kbebay/reviseProduct', $this->session_token_key . '=' . $this->session_token . $filter_url . "&id=" . $result['id_product'] . "&profile_id=" . $result['id_ebay_profiles'], true),
                'url_enable' => $this->url->link($this->module_path . '/kbebay/productListing', $this->session_token_key . '=' . $this->session_token . $filter_url . "&id=" . $result['id_product'] . "&action=enable&id_ebay_profile_products=" . $result['id_ebay_profile_products'], true),
                'url_disable' => $this->url->link($this->module_path . '/kbebay/productListing', $this->session_token_key . '=' . $this->session_token . $filter_url . "&id=" . $result['id_product'] . "&action=disable&id_ebay_profile_products=" . $result['id_ebay_profile_products'], true),
                'url_sync_new' => HTTPS_CATALOG . 'index.php?route=ebay_feed/cron/listProductToEbay&id_ebay_profile_products=' . $result['id_ebay_profile_products'],
                'url_sync_update' => HTTPS_CATALOG . 'index.php?route=ebay_feed/cron/listProductToEbay&id_ebay_profile_products=' . $result['id_ebay_profile_products']."&revise=1",
            );
        }

        $pagination = new Pagination();
        $pagination->total = $product_list_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link($this->module_path . '/kbebay/productListing', $this->session_token_key . '=' . $this->session_token . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($product_list_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_list_total - $this->config->get('config_limit_admin'))) ? $product_list_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_list_total, ceil($product_list_total / $this->config->get('config_limit_admin')));


        $data['listing_status'] = array(
            '' => '',
            'New' => $this->language->get('text_product_status_new'),
            'Listed' => $this->language->get('text_product_status_listed'),
            'Disabled' => $this->language->get('text_product_status_disabled'),
            'Updated' => $this->language->get('text_product_status_updated'),
            'Deleted' => $this->language->get('text_product_status_deleted'),
            'Relist' => $this->language->get('text_product_status_relist')
        );

        $profile_filter_data = array(
            'start' => 0,
            'limit' => 1000
        );

        $profiles = $this->model_setting_kbebay->getProfileDetails($profile_filter_data);
        $profiles_to_filter = array();
        foreach ($profiles as $profile) {
            $profiles_to_filter[] = array("id" => $profile['id_ebay_profiles'], "name" => $profile['profile_name']);
        }
        $data['profiles_to_filter'] = $profiles_to_filter;

        //links
        $data['route'] = $this->url->link($this->module_path . '/kbebay/productListing', $this->session_token_key . '=' . $this->session_token, 'SSL');
        $data['cancel'] = $this->url->link($this->module_path . '/kbebay', $this->session_token_key . '=' . $this->session_token, 'SSL');
        $data['enable_action'] = $this->url->link($this->module_path . '/kbebay/productListing', $this->session_token_key . '=' . $this->session_token . "&bulk_action=enable" . $url, true);
        $data['disable_action'] = $this->url->link($this->module_path . '/kbebay/productListing', $this->session_token_key . '=' . $this->session_token . "&bulk_action=disable" . $url, true);

        $data['local_sync_link'] = HTTPS_CATALOG . 'index.php?route=ebay_feed/cron/syncLocal';
        $data['feed_sync_link'] = HTTPS_CATALOG . 'index.php?route=ebay_feed/cron/listProductToEbay';
        $data['sync_product_status_link'] = HTTPS_CATALOG . 'index.php?route=ebay_feed/cron/processEbayReport';
        $data['text_local_sync'] = $this->language->get('text_local_sync');
        $data['text_feed_sync'] = $this->language->get('text_feed_sync');
        $data['text_product_status_sync'] = $this->language->get('text_product_status_sync');
        $data['text_sync_product'] = $this->language->get('text_sync_product');
        
        $data['token'] = $this->session_token;
        $data['session_token_key'] = $this->session_token_key;

        $data['heading_title'] = $this->language->get('heading_title');
        $data['heading_title_main'] = $this->language->get('heading_title_main');
        $data['text_edit_product_list'] = $this->language->get('text_edit_product_list');
        $data['text_delete_products'] = $this->language->get('text_delete_products');
        $data['text_mark_update'] = $this->language->get('text_mark_update');
        $data['text_mark_relisting'] = $this->language->get('text_mark_relisting');

        // Filter info
        $data['text_filter_product_name'] = $this->language->get('text_filter_product_name');
        $data['text_filter_shipping_name'] = $this->language->get('text_filter_shipping_name');
        $data['text_filter_listing_status'] = $this->language->get('text_filter_listing_status');
        $data['text_filter_ebay_profile'] = $this->language->get('text_filter_ebay_profile');
        $data['text_filter_max_proc_days'] = $this->language->get('text_filter_max_proc_days');
        $data['column_product_list_id'] = $this->language->get('column_product_list_id');
        $data['column_product_image'] = $this->language->get('column_product_image');
        $data['column_product_name'] = $this->language->get('column_product_name');
        $data['column_profile_name'] = $this->language->get('text_profile_title');
        $data['column_list_status'] = $this->language->get('column_list_status');
        $data['column_ebay_list_status'] = $this->language->get('column_ebay_list_status');
        $data['column_list_date'] = $this->language->get('column_list_date');
        $data['column_product_listing_id'] = $this->language->get('column_product_listing_id');
        $data['column_action'] = $this->language->get('column_action');
        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['button_add'] = $this->language->get('button_add');
        $data['text_view_errors'] = $this->language->get('text_view_errors');
        $data['entry_error_message'] = $this->language->get('entry_error_message');
        $data['entry_no_error'] = $this->language->get('entry_no_error');
        $data['button_swtich_profile'] = $this->language->get('button_swtich_profile');
        $data['txt_set_new_profile'] = $this->language->get('txt_set_new_profile');
        $data['txt_profile_swtich_hint'] = $this->language->get('txt_profile_swtich_hint');
        $data['txt_set_select_profile'] = $this->language->get('txt_set_select_profile');
        $data['button_enable_products'] = $this->language->get('button_enable_products');
        $data['button_disable_products'] = $this->language->get('button_disable_products');
        $data['button_enable_product'] = $this->language->get('button_enable_product');
        $data['button_disable_product'] = $this->language->get('button_disable_product');
        $data['text_confirm_product_delete'] = $this->language->get('text_confirm_product_delete');
        
        //Buttons
        $data['button_save'] = $this->language->get('button_save');
        $data['button_save_and_stay'] = $this->language->get('button_save_and_stay');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_add_module'] = $this->language->get('button_add_module');
        $data['button_remove'] = $this->language->get('button_remove');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['text_confirm'] = $this->language->get('text_confirm');
        $data['button_filter'] = $this->language->get('button_filter');
        $data['button_reset'] = $this->language->get('button_reset');

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        if (isset($this->session->data['error'])) {
            $data['error'] = $this->session->data['error'];
            unset($this->session->data['error']);
        } else {
            $data['error'] = '';
        }

        $data['sort'] = $sort;
        $data['order'] = strtolower($order);
        $data['filter_product_name'] = $filter_product_name;
        $data['filter_listing_status'] = $filter_listing_status;
        $data['filter_ebay_profile'] = $filter_ebay_profile;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $active_tab['active_tab'] = 4;
        $data['tab_common'] = $this->load->controller($this->module_path . '/kbebay/common', $active_tab);

        if (VERSION >= '2.2.0.0') {
            $this->response->setOutput($this->load->view($this->module_path . '/kbebay/ebay_product_list', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path . '/kbebay/ebay_product_list.tpl', $data));
        }
    }

    public function ebayOrders()
    {
        $this->checkGeneralSettings();
        if (isset($this->request->get['filter_product_name'])) {
            $filter_product_name = $this->request->get['filter_product_name'];
        } else {
            $filter_product_name = null;
        }
        if (isset($this->request->get['filter_listing_status'])) {
            $filter_listing_status = $this->request->get['filter_listing_status'];
        } else {
            $filter_listing_status = null;
        }
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $this->load->language($this->module_path . '/kbebay');
        $this->document->setTitle($this->language->get('heading_title_main'));

        $this->load->model('setting/setting');
        $this->load->model('setting/kbebay');
        $this->load->model('tool/image');

        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'id_ebay_profile_products';
        }
        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'DESC';
        }
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array) $this->request->post['selected'];
        } else {
            $data['selected'] = array();
        }

        $url = '';
        $filter_url = '';
        $sort_url = '';

        if (isset($this->request->get['filter_product_name'])) {
            $url .= '&filter_product_name=' . urlencode(html_entity_decode($this->request->get['filter_product_name'], ENT_QUOTES, 'UTF-8'));
            $filter_url .= '&filter_product_name=' . urlencode(html_entity_decode($this->request->get['filter_product_name'], ENT_QUOTES, 'UTF-8'));
            $sort_url .= '&filter_product_name=' . urlencode(html_entity_decode($this->request->get['filter_product_name'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_listing_status'])) {
            $url .= '&filter_listing_status=' . urlencode(html_entity_decode($this->request->get['filter_listing_status'], ENT_QUOTES, 'UTF-8'));
            $filter_url .= '&filter_listing_status=' . urlencode(html_entity_decode($this->request->get['filter_listing_status'], ENT_QUOTES, 'UTF-8'));
            $sort_url .= '&filter_listing_status=' . urlencode(html_entity_decode($this->request->get['filter_listing_status'], ENT_QUOTES, 'UTF-8'));
        }
        if (isset($this->request->get['filter_shipping_country'])) {
            $url .= '&filter_shipping_country=' . urlencode(html_entity_decode($this->request->get['filter_shipping_country'], ENT_QUOTES, 'UTF-8'));
            $filter_url .= '&filter_shipping_country=' . urlencode(html_entity_decode($this->request->get['filter_shipping_country'], ENT_QUOTES, 'UTF-8'));
            $sort_url .= '&filter_shipping_country=' . urlencode(html_entity_decode($this->request->get['filter_shipping_country'], ENT_QUOTES, 'UTF-8'));
        }
        if (isset($this->request->get['filter_min_proc_days'])) {
            $url .= '&filter_min_proc_days=' . urlencode(html_entity_decode($this->request->get['filter_min_proc_days'], ENT_QUOTES, 'UTF-8'));
            $filter_url .= '&filter_min_proc_days=' . urlencode(html_entity_decode($this->request->get['filter_min_proc_days'], ENT_QUOTES, 'UTF-8'));
            $sort_url .= '&filter_min_proc_days=' . urlencode(html_entity_decode($this->request->get['filter_min_proc_days'], ENT_QUOTES, 'UTF-8'));
        }
        if (isset($this->request->get['filter_max_proc_days'])) {
            $url .= '&filter_max_proc_days=' . urlencode(html_entity_decode($this->request->get['filter_max_proc_days'], ENT_QUOTES, 'UTF-8'));
            $filter_url .= '&filter_max_proc_days=' . urlencode(html_entity_decode($this->request->get['filter_max_proc_days'], ENT_QUOTES, 'UTF-8'));
            $sort_url .= '&filter_max_proc_days=' . urlencode(html_entity_decode($this->request->get['filter_max_proc_days'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['page'])) {
            $filter_url .= '&page=' . $this->request->get['page'];
            $sort_url .= '&page=' . $this->request->get['page'];
        }
        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
            $filter_url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
            $filter_url .= '&order=' . $this->request->get['order'];
        }

        if ($order == 'ASC') {
            $sort_url .= '&order=DESC';
        } else {
            $sort_url .= '&order=ASC';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link($this->extension_path, $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->url->link($this->module_path . '/kbebay', $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_edit_order_list'),
            'href' => $this->url->link($this->module_path . '/kbebay/ebayOrders', $this->session_token_key . '=' . $this->session_token . $filter_url, 'SSL'),
            'separator' => ' :: '
        );

        $filter_data = array(
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin'),
            'sort' => $sort,
            'order' => $order,
        );

        $data['sort_order_id'] = $this->url->link($this->module_path . '/kbebay/ebayOrders', $this->session_token_key . '=' . $this->session_token . '&sort=o.order_id' . $sort_url, true);
        $data['sort_ebay_order_id'] = $this->url->link($this->module_path . '/kbebay/ebayOrders', $this->session_token_key . '=' . $this->session_token . '&sort=eo.ebay_order_id' . $sort_url, true);
        $data['sort_total'] = $this->url->link($this->module_path . '/kbebay/ebayOrders', $this->session_token_key . '=' . $this->session_token . '&sort=o.total' . $sort_url, true);
        $data['sort_payment_method'] = $this->url->link($this->module_path . '/kbebay/ebayOrders', $this->session_token_key . '=' . $this->session_token . '&sort=o.payment_method' . $sort_url, true);
        $data['sort_date_added'] = $this->url->link($this->module_path . '/kbebay/ebayOrders', $this->session_token_key . '=' . $this->session_token . '&sort=o.date_added' . $sort_url, true);

        $product_list_total = $this->model_setting_kbebay->getTotalOrderListed($filter_data);
        if (isset($product_list_total) && $product_list_total == ''){
            $product_list_total = 0;
        }
        $product_list_details = $this->model_setting_kbebay->getOrderListed($filter_data);
        $data['product_listed'] = array();
        foreach ($product_list_details as $result) {
            $data['product_listed'][] = array(
                'order_id' => $result['order_id'],
                'ebay_order_id' => $result['ebay_order_id'],
                'firstname' => $result['firstname'] . ' ' . $result['lastname'],
                'total' => $result['total'],
                'payment_method' => $result['payment_method'],
                'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'action' => $this->url->link('sale/order/info', $this->session_token_key . '=' . $this->session_token . '&order_id=' . $result["order_id"], true),
            );
        }

        $pagination = new Pagination();
        $pagination->total = $product_list_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link($this->module_path . '/kbebay/ebayOrders', $this->session_token_key . '=' . $this->session_token . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($product_list_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_list_total - $this->config->get('config_limit_admin'))) ? $product_list_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_list_total, ceil($product_list_total / $this->config->get('config_limit_admin')));

        //links
        $data['token'] = $this->session_token;
        $data['session_token_key'] = $this->session_token_key;

        $data['heading_title'] = $this->language->get('heading_title');
        $data['heading_title_main'] = $this->language->get('heading_title_main');
        $data['text_edit_order_list'] = $this->language->get('text_edit_order_list');

        // Filter info
        $data['text_filter_product_name'] = $this->language->get('text_filter_product_name');
        $data['text_filter_shipping_name'] = $this->language->get('text_filter_shipping_name');
        $data['text_filter_listing_status'] = $this->language->get('text_filter_listing_status');
        $data['text_filter_min_proc_days'] = $this->language->get('text_filter_min_proc_days');
        $data['text_filter_max_proc_days'] = $this->language->get('text_filter_max_proc_days');
        $data['column_order_id'] = $this->language->get('column_order_id');
        $data['column_product_image'] = $this->language->get('column_product_image');
        $data['column_customer_name'] = $this->language->get('column_customer_name');
        $data['column_total'] = $this->language->get('column_total');
        $data['column_payment'] = $this->language->get('column_payment');
        $data['column_order_date'] = $this->language->get('column_order_date');
        $data['column_ebay_order_id'] = $this->language->get('column_ebay_order_id');
        $data['column_action'] = $this->language->get('column_action');
        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['button_add'] = $this->language->get('button_add');

        //Tooltips
        //buttons
        $data['button_save'] = $this->language->get('button_save');
        $data['button_save_and_stay'] = $this->language->get('button_save_and_stay');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_add_module'] = $this->language->get('button_add_module');
        $data['button_remove'] = $this->language->get('button_remove');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['text_confirm'] = $this->language->get('text_confirm');
        $data['button_filter'] = $this->language->get('button_filter');
        $data['button_view'] = $this->language->get('button_view');


        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        if (isset($this->session->data['error'])) {
            $data['error'] = $this->session->data['error'];
            unset($this->session->data['error']);
        } else {
            $data['error'] = '';
        }

        $data['sort'] = $sort;
        $data['order'] = strtolower($order);
        $data['route'] = $this->url->link($this->module_path . '/kbebay/ebayOrders', $this->session_token_key . '=' . $this->session_token, true);

        $data['filter_product_name'] = $filter_product_name;
        $data['filter_listing_status'] = $filter_listing_status;
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $active_tab['active_tab'] = 5;
        $data['tab_common'] = $this->load->controller($this->module_path . '/kbebay/common', $active_tab);

        if (VERSION >= '2.2.0.0') {
            $this->response->setOutput($this->load->view($this->module_path . '/kbebay/order_list', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path . '/kbebay/order_list.tpl', $data));
        }
    }

    public function ajaxProcessGetCategories()
    {
        $store_id = $this->request->get['store_id'];
        $this->load->model('setting/kbebay');
        $this->load->model('localisation/currency');
        $this->load->language($this->module_path . '/kbebay');
        $status = $this->model_setting_kbebay->checkCountryStatus($store_id);
        if ($status) {
            $data['categories'] = $this->model_setting_kbebay->getEbayCategories($store_id);
            $currency_iso = $this->model_setting_kbebay->getCurrency($store_id);

            /* Find Currencies from the ebay_sites table & check if exist in Opencart OR not */
            $currency_iso_array = explode(",", $currency_iso['currency_iso_code']);
            $store_currecny_exist = false;
            if (!empty($currency_iso_array)) {
                foreach ($currency_iso_array as $currency_iso_item) {
                    $currency_data = $this->model_localisation_currency->getCurrencyByCode($currency_iso_item);
                    if ($currency_data) {
                        $data['currency'][] = $currency_data;
                        $store_currecny_exist = true;
                    }
                }
            }

            if ($store_currecny_exist == false) {
                $data['currency_error'] = sprintf($this->language->get('ebay_text_currency_error'), $currency_iso['currency_iso_code']);
            }
            $shipping_profile = $this->model_setting_kbebay->getShippingProfile($store_id);
            if (!empty($shipping_profile)) {
                $data['shipping_profiles'] = $this->model_setting_kbebay->getShippingProfile($store_id);
            } else {
                $data['shipping_profile_error'] = sprintf($this->language->get('ebay_text_shipping_profile_error'), $this->url->link($this->module_path . '/kbebay/shippingProfile', $this->session_token_key . '=' . $this->session_token, 'SSL'));
            }
            $site_details = $this->model_setting_kbebay->getEbaySiteById($store_id);
            $data['vat_enabled'] = $site_details['vatenabled'];

            $data['store_categories'] = $this->model_setting_kbebay->getEbayStoreCategory($store_id);
        } else {
            $data['error'] = sprintf($this->language->get('ebay_text_error_site'), HTTPS_CATALOG . 'index.php?route=ebay_feed/cron/importCategories&site_id=' . $store_id);
        }
        echo json_encode($data);
        die;
    }

    public function ajaxProcessGetSubcategories()
    {
        $store_id = $this->request->get['store_id'];
        $category_id = $this->request->get['category_id'];
        $this->load->model('setting/kbebay');
        $data = $this->model_setting_kbebay->getEbaySubcategories($category_id, $store_id);
        echo json_encode($data);
        die;
    }

    public function saveGeneralProfileData()
    {
        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }
        $this->load->model('setting/kbebay');
        $data = $this->request->post;
        if ($this->validate()) {
            if (isset($data['ebay']['profile']['id_ebay_profiles']) && $data['ebay']['profile']['id_ebay_profiles'] != '') {
                $status = $this->model_setting_kbebay->editProfile($data);
            } else {
                $status = $this->model_setting_kbebay->addProfile($data);
            }
            if ($status) {
                $this->model_setting_kbebay->getSpecificsDetails($data['ebay']['profile']['id_ebay_cat_final'], $data['ebay']['profile']['ebay-sites'], $status);
            }
        } else {
            $data['error'] = $this->error;
            echo json_encode($data);
            die();
        }
    }

    public function saveSpecificsData()
    {
        $data = $this->request->post;
        $this->load->model('setting/kbebay');
        $this->load->language($this->module_path . '/kbebay');
        $this->model_setting_kbebay->saveProfileSpecifics($data);
        $this->session->data['success'] = $this->language->get('profile_update_success_message');
        $this->response->redirect($this->url->link($this->module_path . '/kbebay/profileManagement', $this->session_token_key . '=' . $this->session_token, 'SSL'));
    }

    public function getCategorysSecificsData()
    {
        $ebay_category_id = $this->request->get['ebay_category_id'];
        $id_ebay_profiles = $this->request->get['id_ebay_profiles'];
        $site_id = $this->request->get['site_id'];
        $this->load->model('setting/kbebay');
        $this->model_setting_kbebay->getSpecificsDetails($ebay_category_id, $site_id, $id_ebay_profiles);
    }

    public function install()
    {
        $this->load->model('setting/setting');
        $createTableSQL = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "kb_ebay_categories` (
                            `id_ebay_categories` int(11) NOT NULL auto_increment,
                            `ebay_categories` int(11) NOT NULL,
                            `ebay_category_name` varchar(200) NOT NULL,
                            `ebay_category_level` smallint(6) NOT NULL,
                            `id_ebay_category_parent` int(11) NOT NULL,
                            `ebay_leaf_category` enum('true','false') NOT NULL,
                            `ebay_bestoffer_enabled` enum('true','false') NOT NULL,
                            `ebay_autopay_enabled` enum('true','false') NOT NULL,
                            `ebay_site_id` int(3) NOT NULL,
                            PRIMARY KEY  (`id_ebay_categories`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
        $this->db->query($createTableSQL);
        $createTableSQL = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "kb_ebay_errors` (
                            `id_ebay_errors` int(11) NOT NULL auto_increment,
                            `request_name` varchar(20) NOT NULL,
                            `error` text NOT NULL,
                            `error_type` varchar(20) default NULL,
                            `id_product` int(11) default NULL,
                            `id_product_attribute` int(11) default NULL,
                            `date_added` timestamp NOT NULL default CURRENT_TIMESTAMP,
                            PRIMARY KEY  (`id_ebay_errors`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
        $this->db->query($createTableSQL);

        $createTableSQL = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "kb_ebay_orders` (
                        `id_ebay_orders` int(11) NOT NULL auto_increment,
                        `ebay_order_id` varchar(50) NOT NULL,
                        `ebay_line_item_id` varchar(100) default NULL,
                        `store_order_id` int(10) NOT NULL,
                        `ebay_site` int(10) NOT NULL,
                        `ebay_order_status` varchar(20) default NULL,
                        `is_status_updated` enum('0','1') NOT NULL default '0',
                        `order_ebay_data` text,
                        `date_added` timestamp NOT NULL default CURRENT_TIMESTAMP,
                        PRIMARY KEY  (`id_ebay_orders`)
                      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
        $this->db->query($createTableSQL);

        $createTableSQL = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "kb_ebay_profiles` (
                        `id_ebay_profiles` int(11) NOT NULL auto_increment,
                        `profile_name` varchar(255) default NULL,
                        `ebay_site` varchar(10) NOT NULL,
                        `ebay_category_id` varchar(50) default NULL,
                        `ebay_catgeory_text` text,
                        `ebay_payment_method` text,
                        `ebay_currency` varchar(20) DEFAULT NULL,
                        `ebay_language` varchar(20) DEFAULT NULL,
                        `ebay_shipping_service` text,
                        `ebay_shipping_profile` int(11) DEFAULT NULL,
                        `store_category_id` text,
                        `store_category_text` text,
                        `duration` varchar(10) default NULL,
                        `product_quantity` int(11) default NULL,
                        `dispatch_days` int(10) default NULL,
                        `price_management` int(2) NOT NULL,
                        `increase_decrease` enum('0','1') NOT NULL,
                        `product_price` FLOAT(11) NOT NULL,
                        `product_threshold_price` FLOAT(11) NOT NULL,
                        `percentage_fixed` enum('0','1') NOT NULL,
                        `product_condition` int(10) default NULL,
                        `status` enum('draft','completed','deleted','disabled') NOT NULL,
                        `return_enable` enum('ReturnsAccepted','ReturnsNotAccepted') default NULL,
                        `return_days` varchar(10) NOT NULL,
                        `refund` varchar(25) NOT NULL,
                        `return_description` text, 
                        `return_shipping` enum('Buyer','Seller') NOT NULL,
                        `active` tinyint(1) NOT NULL default '1',
                        `site_id` int(5) NOT NULL,
                        `date_added` timestamp NOT NULL default CURRENT_TIMESTAMP,
                        `date_modified` datetime default NULL,
                        PRIMARY KEY  (`id_ebay_profiles`),
                        KEY `site_id` (`site_id`)
                      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
        $this->db->query($createTableSQL);

        $createTableSQL = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "kb_ebay_profile_products` (
                    `id_ebay_profile_products` int(11) NOT NULL auto_increment,
                    `id_ebay_profiles` int(11) NOT NULL,
                    `id_product` int(11) NOT NULL,
                    `id_product_attribute` int(11) default NULL,
                    `product_reference` varchar(32) default NULL,
                    `upc` varchar(12) default NULL,
                    `ebay_listiing_id` varchar(20) default NULL,
                    `status` enum('New','Listed','Updated','Deleted','Relist') NOT NULL default 'New',
                    `ebay_status` varchar(20) default NULL,
                    `relist` enum('0','1') NOT NULL default '0',
                    `revise` enum('0','1') NOT NULL default '0',
                    `end` enum('0','1') NOT NULL default '0',
                    `date_added` timestamp NOT NULL default CURRENT_TIMESTAMP,
                    `date_updated` datetime default NULL,
                    PRIMARY KEY  (`id_ebay_profile_products`)
                  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
        $this->db->query($createTableSQL);
        $createTableSQL = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "kb_ebay_sites` (
                    `id_ebay_site` int(11) NOT NULL auto_increment,
                    `id_ebay_countries` int(11) NOT NULL,
                    `abbreviation` varchar(10) NOT NULL,
                    `site_name` varchar(100) NOT NULL,
                    `description` varchar(255) NOT NULL,
                    `language` varchar(255) NOT NULL,
                    `language_iso_code` varchar(10) NOT NULL,
                    `currency_iso_code` varchar(10) NOT NULL,
                    `ebay_iso_code` varchar(10) NOT NULL,
                    `site_url` varchar(500) NOT NULL,
                    `token` text DEFAULT NULL,
                    `enabled` int(2) NOT NULL,
                    PRIMARY KEY  (`id_ebay_site`)
                  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
        $this->db->query($createTableSQL);
        
        $productOptionTable = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "kb_ebay_profile_product_options` (
                    `profile_product_option_id` int(11) NOT NULL AUTO_INCREMENT,
                    `product_id` int(11) NOT NULL,
                    `profile_id` int(11) NOT NULL,
                    `option_sku` varchar(100) NOT NULL,
                    PRIMARY KEY (`profile_product_option_id`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
        $this->db->query($productOptionTable);

        $results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "kb_ebay_sites`");
        if (!($results->num_rows)) {
            $createTableSQL = "INSERT INTO `" . DB_PREFIX . "kb_ebay_sites` (`id_ebay_site`, `id_ebay_countries`, `abbreviation`, `site_name`, `description`, `language`, `language_iso_code`, `currency_iso_code`, `ebay_iso_code`, `site_url`, `token`, `enabled`) VALUES
                    (1, 15, 'AU', 'Australia', 'eBay Australia', 'English (Australia)', 'en_AU', 'AUD', 'AU', 'http://www.ebay.com.au', NULL, 0),
                    (2, 16, 'AT', 'Austria', 'eBay Austria', 'German (Austria)', 'de_AT', 'EUR', 'AT', 'http://www.ebay.at', NULL, 0),
                    (3, 123, 'BENL', 'Belgium_Dutch', 'eBay Belgium (Dutch)', 'Dutch (Belgium)', 'nl_BE', 'EUR', 'BE', 'http://www.ebay.be', NULL, 0),
                    (4, 23, 'BEFR', 'Belgium_French', 'eBay Belgium (French)', 'French (Belgium)', 'fr_BE', 'EUR', 'BE', 'http://www.ebay.be', NULL, 0),
                    (5, 2, 'CA', 'Canada', 'eBay Canada', 'English (Canada)', 'en_CA', 'CAD,USD', 'CA', 'http://www.ebay.ca', NULL, 0),
                    (6, 71, 'FR', 'France', 'eBay France', 'French (France)', 'fr_FR', 'EUR', 'FR', 'http://www.ebay.fr', NULL, 0),
                    (7, 77, 'DE', 'Germany', 'eBay Germany', 'German (Germany)', 'de_DE', 'EUR', 'DE', 'http://www.ebay.com.de', NULL, 0),
                    (8, 201, 'HK', 'HongKong', 'eBay HongKong', 'Chinese (Hong Kong SAR China)', 'zh_HK', 'HKD', 'HK', 'http://www.ebay.com.hk', NULL, 0),
                    (9, 203, 'IN', 'India', 'eBay India', 'Hindi (India)', 'hi_IN', 'INR', 'IN', 'http://www.ebay.in', NULL, 0),
                    (10, 205, 'IE', 'Ireland', 'eBay Ireland', 'English (Ireland)', 'en_IE', 'EUR', 'IE', 'http://www.ebay.com.ie', NULL, 0),
                    (11, 101, 'IT', 'Italy', 'eBay Italy', 'Italian (Italy)', 'it_IT', 'EUR', 'IT', 'http://www.ebay.com.it', NULL, 0),
                    (12, 207, 'MY', 'Malaysia', 'eBay Malaysia', 'Malay (Malaysia)', 'ms_MY', 'MYR', 'MY', 'http://www.ebay.com.my', NULL, 0),
                    (13, 146, 'NL', 'Netherlands', 'eBay Netherlands', 'Dutch (Netherlands)', 'nl_NL', 'EUR', 'NL', 'http://www.ebay.com.nl', NULL, 0),
                    (14, 211, 'PH', 'Philippines', 'eBay Philippines', 'Filipino (Philippines)', 'fil_PH', 'PHP', 'PH', 'http://www.ebay.com.ph', NULL, 0),
                    (15, 212, 'PL', 'Poland', 'eBay Poland', 'Polish (Poland)', 'pl_PL', 'PLN', 'PL', 'http://www.ebay.com.pl', NULL, 0),
                    (16, 216, 'SG', 'Singapore', 'eBay Singapore', 'Chinese (Singapore)', 'zh_SG', 'SGD', 'SG', 'http://www.ebay.com.sg', NULL, 0),
                    (17, 186, 'ES', 'Spain', 'eBay Spain', 'Spanish (Spain)', 'es_ES', 'EUR', 'ES', 'http://www.ebay.es', NULL, 0),
                    (18, 193, 'CH', 'Switzerland', 'eBay Switzerland', 'German (Switzerland) & Italian (Switzerland)', 'de_CH,it_CH', 'CHF', 'CH', 'http://www.ebay.ch', NULL, 0),
                    (19, 3, 'UK', 'UK', 'eBay United Kingdom', 'English (United Kingdom)', 'en_GB', 'GBP', 'GB', 'http://www.ebay.co.uk', NULL, 0),
                    (20, 0, 'US', 'US', 'eBay United States', 'English (United States)', 'en_US', 'USD', 'US', 'http://www.ebay.com', '', 0);";
            $this->db->query($createTableSQL);
        }
        $createTableSQL = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "kb_ebay_profile_specifics` (
                    `id_ebay_profile_specifics` int(11) NOT NULL auto_increment,
                    `id_ebay_profiles` int(11) NOT NULL,
                    `id_ebay_category_specifics` int(11) NOT NULL,
                    `attribute_mapped` int(11) default NULL,
                    `feature_mapped` int(11) default NULL,
                    `ebay_value_mapped` varchar(100) default NULL,
                    `custom_value_mapped` varchar(100) default NULL,
                    `manufacturer` smallint(6) default NULL,
                    PRIMARY KEY  (`id_ebay_profile_specifics`)
                  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
        $this->db->query($createTableSQL);
        $createTableSQL = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "kb_ebay_sites` (
                    `id_ebay_site` int(11) NOT NULL auto_increment,
                    `id_ebay_countries` int(11) NOT NULL,
                    `abbreviation` varchar(10) NOT NULL,
                    `description` varchar(255) NOT NULL,
                    `language` varchar(255) NOT NULL,
                    `language_iso_code` varchar(10) NOT NULL,
                    `currency_iso_code` varchar(10) NOT NULL,
                    `site_url` varchar(500) default NULL,
                    PRIMARY KEY  (`id_ebay_site`)
                  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='TABLE FOR SAVING EBAY SITES ALONG WITH THERE LANGUAGE AND CU' AUTO_INCREMENT=1 ;";
        $this->db->query($createTableSQL);
        $createTableSQL = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "kb_ebay_category_specifics` (
                    `id_ebay_category_specifics` int(11) NOT NULL auto_increment,
                    `id_ebay_categories` int(11) NOT NULL,
                    `specific_name` varchar(200) NOT NULL,
                    `specific_values` text NOT NULL,
                    `multiple_allowed` enum('0','1') NOT NULL default '0',
                    `is_mandatory` enum('0','1') NOT NULL default '0',
                    `site_id` int(11) NOT NULL,
                    `date_added` timestamp NOT NULL default CURRENT_TIMESTAMP,
                    PRIMARY KEY  (`id_ebay_category_specifics`)
                  ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
        $this->db->query($createTableSQL);

        $createTableSQL = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "kb_ebay_shipping_methods` (
                `id_ebay_shipping` int(11) NOT NULL auto_increment,
                `ebay_shipping_name` varchar(200) NOT NULL,
                `ebay_shipping_desc` varchar(200) NOT NULL,
                `service_type` text NOT NULL,
                `package_type` text NOT NULL,
                `international_shipping` int(1) NOT NULL DEFAULT '0',
                `site_id` int(3) NOT NULL,
                PRIMARY KEY  (`id_ebay_shipping`),
                KEY `site_id` (`site_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        $this->db->query($createTableSQL);

        $createTableSQL = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "kb_ebay_shipping` (
            `id_ebay_shipping` int(11) NOT NULL AUTO_INCREMENT,
            `shipping_profile_name` varchar(200) NOT NULL,
            `site_id` int(11) NOT NULL,
            `service_type` varchar(100) NOT NULL,
            `domestic_shipping` text NOT NULL,
            `international_shipping_allowed` int(11) NOT NULL DEFAULT '0',
            `international_shipping` text NOT NULL,
            `excluded_location` text NOT NULL,
            `package_type` text NOT NULL,
            `postal_code` varchar(20) DEFAULT NULL,
            `date_add` date NOT NULL,
            `date_upd` date NOT NULL,
            `active` int(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`id_ebay_shipping`)
          ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1";
        $this->db->query($createTableSQL);

        $vat_enabled = 'SELECT column_name FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA="' . DB_DATABASE . '" AND TABLE_NAME="' . DB_PREFIX . 'kb_ebay_sites" AND column_name="vatenabled"';
        $vat_enabled_results = $this->db->query($vat_enabled);
        if ($vat_enabled_results->num_rows <= 0) {
            $this->db->query('ALTER TABLE ' . DB_PREFIX . 'kb_ebay_sites ADD COLUMN `vatenabled` varchar(10) DEFAULT NULL');
            $this->db->query('UPDATE ' . DB_PREFIX . 'kb_ebay_sites SET `vatenabled` = "yes" WHERE id_ebay_countries = 16');
            $this->db->query('UPDATE ' . DB_PREFIX . 'kb_ebay_sites SET `vatenabled` = "no" WHERE id_ebay_countries = 15');
            $this->db->query('UPDATE ' . DB_PREFIX . 'kb_ebay_sites SET `vatenabled` = "yes" WHERE id_ebay_countries = 23');
            $this->db->query('UPDATE ' . DB_PREFIX . 'kb_ebay_sites SET `vatenabled` = "yes" WHERE id_ebay_countries = 123');
            $this->db->query('UPDATE ' . DB_PREFIX . 'kb_ebay_sites SET `vatenabled` = "no" WHERE id_ebay_countries = 2');
            $this->db->query('UPDATE ' . DB_PREFIX . 'kb_ebay_sites SET `vatenabled` = "no" WHERE id_ebay_countries = 210');
            $this->db->query('UPDATE ' . DB_PREFIX . 'kb_ebay_sites SET `vatenabled` = "yes" WHERE id_ebay_countries = 193');
            $this->db->query('UPDATE ' . DB_PREFIX . 'kb_ebay_sites SET `vatenabled` = "yes" WHERE id_ebay_countries = 77');
            $this->db->query('UPDATE ' . DB_PREFIX . 'kb_ebay_sites SET `vatenabled` = "yes" WHERE id_ebay_countries = 186');
            $this->db->query('UPDATE ' . DB_PREFIX . 'kb_ebay_sites SET `vatenabled` = "yes" WHERE id_ebay_countries = 71');
            $this->db->query('UPDATE ' . DB_PREFIX . 'kb_ebay_sites SET `vatenabled` = "no" WHERE id_ebay_countries = 201');
            $this->db->query('UPDATE ' . DB_PREFIX . 'kb_ebay_sites SET `vatenabled` = "yes" WHERE id_ebay_countries = 205');
            $this->db->query('UPDATE ' . DB_PREFIX . 'kb_ebay_sites SET `vatenabled` = "no" WHERE id_ebay_countries = 203');
            $this->db->query('UPDATE ' . DB_PREFIX . 'kb_ebay_sites SET `vatenabled` = "yes" WHERE id_ebay_countries = 101');
            $this->db->query('UPDATE ' . DB_PREFIX . 'kb_ebay_sites SET `vatenabled` = "no" WHERE id_ebay_countries = 207');
            $this->db->query('UPDATE ' . DB_PREFIX . 'kb_ebay_sites SET `vatenabled` = "yes" WHERE id_ebay_countries = 146');
            $this->db->query('UPDATE ' . DB_PREFIX . 'kb_ebay_sites SET `vatenabled` = "no" WHERE id_ebay_countries = 211');
            $this->db->query('UPDATE ' . DB_PREFIX . 'kb_ebay_sites SET `vatenabled` = "no" WHERE id_ebay_countries = 212');
            $this->db->query('UPDATE ' . DB_PREFIX . 'kb_ebay_sites SET `vatenabled` = "no" WHERE id_ebay_countries = 216');
            $this->db->query('UPDATE ' . DB_PREFIX . 'kb_ebay_sites SET `vatenabled` = "yes" WHERE id_ebay_countries = 3');
            $this->db->query('UPDATE ' . DB_PREFIX . 'kb_ebay_sites SET `vatenabled` = "no" WHERE id_ebay_countries = 0');
        }

        $vat_percentage = 'SELECT column_name FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA="' . DB_DATABASE . '" AND TABLE_NAME="' . DB_PREFIX . 'kb_ebay_profiles" AND column_name="vat_percentage"';
        $vat_percentage_results = $this->db->query($vat_percentage);
        if ($vat_percentage_results->num_rows <= 0) {
            $this->db->query('ALTER TABLE ' . DB_PREFIX . 'kb_ebay_profiles ADD COLUMN `vat_percentage` INT(10) DEFAULT NULL');
        }

        $item_url = 'SELECT column_name FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA="' . DB_DATABASE . '" AND TABLE_NAME="' . DB_PREFIX . 'kb_ebay_profile_products" AND column_name="item_url"';
        $item_url_results = $this->db->query($item_url);
        if ($item_url_results->num_rows <= 0) {
            $this->db->query('ALTER TABLE ' . DB_PREFIX . 'kb_ebay_profile_products ADD COLUMN `item_url` VARCHAR(300) DEFAULT NULL');
        }
        
        $html_template = 'SELECT column_name FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA="' . DB_DATABASE . '" AND TABLE_NAME="' . DB_PREFIX . 'kb_ebay_profiles" AND column_name="html_template"';
        $html_template_results = $this->db->query($html_template);
        if ($html_template_results->num_rows <= 0) {
            $this->db->query('ALTER TABLE ' . DB_PREFIX . 'kb_ebay_profiles ADD COLUMN `html_template` VARCHAR(300000) DEFAULT NULL');
        }

        $createTableSQL = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "kb_ebay_store_category` (
                `category_id` int(11) NOT NULL AUTO_INCREMENT,
                `ebay_store_category_id` varchar(20) NOT NULL,
                `category_name` varchar(300) NOT NULL,
                `site_id` INT NOT NULL,
                PRIMARY KEY (`category_id`)
              ) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
        $this->db->query($createTableSQL);

        $store_category = 'SELECT column_name FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA="' . DB_DATABASE . '" AND TABLE_NAME="' . DB_PREFIX . 'kb_ebay_profiles" AND column_name="store_category"';
        $store_category_results = $this->db->query($store_category);
        if ($store_category_results->num_rows <= 0) {
            $this->db->query('ALTER TABLE ' . DB_PREFIX . 'kb_ebay_profiles ADD COLUMN `store_category` VARCHAR(20) DEFAULT NULL');
        }

        $error_profile = 'SELECT column_name FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA="' . DB_DATABASE . '" AND TABLE_NAME="' . DB_PREFIX . 'kb_ebay_errors" AND column_name="id_profile"';
        $error_profile_results = $this->db->query($error_profile);
        if ($error_profile_results->num_rows <= 0) {
            $this->db->query('ALTER TABLE ' . DB_PREFIX . 'kb_ebay_errors ADD COLUMN `id_profile` INT DEFAULT NULL');
        }

        $local_sync_flag = 'SELECT column_name FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA="' . DB_DATABASE . '" AND TABLE_NAME="' . DB_PREFIX . 'kb_ebay_profile_products" AND column_name="local_sync_flag"';
        $local_sync_flag_results = $this->db->query($local_sync_flag);
        if ($local_sync_flag_results->num_rows <= 0) {
            $this->db->query("ALTER TABLE " . DB_PREFIX . "kb_ebay_profile_products ADD COLUMN `local_sync_flag` enum('0','1') NOT NULL default '0' AFTER ebay_status");
        }

        $is_disabled = 'SELECT column_name FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA="' . DB_DATABASE . '" AND TABLE_NAME="' . DB_PREFIX . 'kb_ebay_profile_products" AND column_name="is_disabled"';
        $is_disabled_results = $this->db->query($is_disabled);
        if ($is_disabled_results->num_rows <= 0) {
            $this->db->query("ALTER TABLE " . DB_PREFIX . "kb_ebay_profile_products ADD COLUMN is_disabled enum('0','1') NOT NULL default '0'");
        }
		
        
        //$this->db->query("ALTER TABLE `" . DB_PREFIX . "kb_ebay_profile_products` CHANGE `status` `status` ENUM('New','Listed','Updated','Deleted','Relist','Ended') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'New'");;
        
        $secure_key['kbebay_secure_key_key'] = $this->generateRandomString();
        $this->model_setting_setting->editSetting('kbebay_secure_key', $secure_key);


        /* For3.0 Enable/Disable Status */
        $enable_status['module_kbebay_status'] = '1';
        $this->model_setting_setting->editSetting('module_kbebay', $enable_status);

        if (VERSION >= '2.0.0.0' && VERSION < '2.0.1.0') {
            $this->load->model('tool/event');
            $this->model_tool_event->addEvent('kbebay', 'post.order.history.add', 'ebay_feed/cron/on_order_history_add');
            $this->model_tool_event->addEvent('kbebay', 'post.admin.product.edit', 'module/kbebay/onProductUpdate');
            $this->model_tool_event->addEvent('kbebay', 'post.admin.product.delete', 'module/kbebay/onProductDelete');
        } elseif (VERSION >= '2.0.1.0' && VERSION <= '2.1.0.2') {
            $this->load->model('extension/event');
            $this->model_extension_event->addEvent('kbebay', 'post.order.history.add', 'ebay_feed/cron/on_order_history_add');
            $this->model_extension_event->addEvent('kbebay', 'post.admin.product.edit', 'module/kbebay/onProductUpdate');
            $this->model_extension_event->addEvent('kbebay', 'post.admin.product.delete', 'module/kbebay/onProductDelete');
        } elseif (VERSION >= '2.2.0.0' && VERSION < '3.0.0.0') {
            $this->load->model('extension/event');
            $this->model_extension_event->addEvent('kbebay', 'catalog/model/checkout/order/addOrderHistory/after', 'ebay_feed/cron/on_order_history_add');
            $this->model_extension_event->addEvent('kbebay', 'admin/model/catalog/product/editProduct/after', $this->module_path . '/kbebay/onProductUpdate');
            $this->model_extension_event->addEvent('kbebay', 'admin/model/catalog/product/deleteProduct/after', $this->module_path . '/kbebay/onProductDelete');
        } elseif (VERSION >= '3.0.0.0') {
            $this->load->model('setting/event');
            $this->model_setting_event->addEvent('kbebay', 'catalog/model/checkout/order/addOrderHistory/after', 'ebay_feed/cron/on_order_history_add');
            $this->model_setting_event->addEvent('kbebay', 'admin/model/catalog/product/editProduct/after', $this->module_path . '/kbebay/onProductUpdate');
            $this->model_setting_event->addEvent('kbebay', 'admin/model/catalog/product/deleteProduct/after', $this->module_path . '/kbebay/onProductDelete');
        }
    }

    private function generateRandomString($length = 20)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function uninstall()
    {
        if (VERSION >= '2.0.0.0' && VERSION < '2.0.1.0') {
            $this->load->model('tool/event');
            $this->model_tool_event->deleteEvent('kbebay');
        } elseif (VERSION >= '2.0.1.0' && VERSION < '3.0.0.0') {
            $this->load->model('extension/event');
            $this->model_extension_event->deleteEvent('kbebay');
        } elseif (VERSION >= '3.0.0.0') {
            $this->load->model('setting/event');
            $this->model_setting_event->deleteEvent('kbebay');
        }
    }

    public function deleteProfile()
    {
        $this->checkGeneralSettings();
        $this->load->model('setting/kbebay');
        $this->load->language($this->module_path . '/kbebay');
        if (isset($this->request->post['selected'])) {
            $flag = true;
            foreach ($this->request->post['selected'] as $profile_id) {
                $result = $this->model_setting_kbebay->deleteProfile($profile_id);
                if ($result == false) {
                    $flag = false;
                }
            }
            if ($flag == false) {
                $this->session->data['error'] = $this->language->get('text_profile_delete_error');
            } else {
                $this->session->data['success'] = $this->language->get('text_success_profile');
            }
            $this->response->redirect($this->url->link($this->module_path . '/kbebay/profileManagement', $this->session_token_key . '=' . $this->session_token, true));
        } else {
            $this->session->data['error'] = $this->language->get('text_error_profile');
            $this->response->redirect($this->url->link($this->module_path . '/kbebay/profileManagement', $this->session_token_key . '=' . $this->session_token, true));
        }
    }

    public function onProductUpdate($event, $data = array())
    {   
        if(VERSION >= '2.0.0.0' && VERSION <= '2.0.3.1') {
            if (!empty($event)) {
                $this->load->model('setting/kbebay');
                $this->model_setting_kbebay->reviseProduct($event);
            }
        } else {
            if (!empty($data[0])) {
                $this->load->model('setting/kbebay');
                $this->model_setting_kbebay->reviseProduct($data[0]);
            }
        }
    }

    public function onProductDelete($event, $data)
    {   
        if(VERSION >= '2.0.0.0' && VERSION <= '2.0.3.1') {
            if (!empty($event)) {
                $this->load->model('setting/kbebay');
                $this->load->model('setting/kbebay');
                $this->model_setting_kbebay->deleteListedProduct($event);
                /* Execute CRON Automatically to delete the products */
                $this->executeCRON('processEndProducts');
            }
        } else {
            if (!empty($data[0])) {
                $this->load->model('setting/kbebay');
                $this->load->model('setting/kbebay');
                $this->model_setting_kbebay->deleteListedProduct($data[0]);
                /* Execute CRON Automatically to delete the products */
                $this->executeCRON('processEndProducts');
            }
        }
    }

    public function deleteListedProduct()
    {
        $this->checkGeneralSettings();
        if (!empty($this->request->get['id'])) {
            $product_id = $this->request->get['id'];
            $profile_id = $this->request->get['profile_id'];
            $this->load->model('setting/kbebay');
            $this->model_setting_kbebay->deleteListedProduct($product_id, $profile_id);
            $this->session->data['success'] = $this->language->get('product_deleted_successfully');

            /* Execute CRON Automatically to delete the products */
            $this->executeCRON('processEndProducts');
        }
        $this->productListing();
    }

    public function relistProduct()
    {
        $this->checkGeneralSettings();
        if (!empty($this->request->get['id'])) {
            $product_id = $this->request->get['id'];
            $profile_id = $this->request->get['profile_id'];
            $this->load->model('setting/kbebay');
            $this->model_setting_kbebay->relistProduct($product_id, $profile_id);
            $this->session->data['success'] = $this->language->get('product_relist_successfully');
        }
        $this->productListing();
    }

    public function reviseProduct()
    {
        $this->checkGeneralSettings();
        if (!empty($this->request->get['id'])) {
            $product_id = $this->request->get['id'];
            $profile_id = $this->request->get['profile_id'];
            $this->load->model('setting/kbebay');
            $this->model_setting_kbebay->reviseProduct($product_id, $profile_id);
            $this->session->data['success'] = $this->language->get('product_revise_successfully');
        }
        //$this->response->redirect($this->url->link($this->module_path.'/kbebay/productListing', $this->session_token_key . '=' . $this->session_token, true));
        $this->productListing();
    }

    public function sites()
    {
        $this->checkGeneralSettings();
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $this->load->language($this->module_path . '/kbebay');
        $this->document->setTitle($this->language->get('heading_title_main'));

        $this->load->model('setting/setting');
        $this->load->model('setting/kbebay');

        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'id_ebay_site';
        }
        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array) $this->request->post['selected'];
        } else {
            $data['selected'] = array();
        }
        $url = '';
        if (isset($this->request->get['page'])) {
            //$url .= '&page=' . $this->request->get['page'];
        }
        if (isset($this->request->get['sort'])) {
            //$url .= '&sort=' . $this->request->get['sort'];
        }
        if (isset($this->request->get['order'])) {
            //$url .= '&order=' . $this->request->get['order'];
        }
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', $this->session_token_key . '=' . $this->session_token . $url, 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link($this->extension_path, $this->session_token_key . '=' . $this->session_token . $url, 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->url->link($this->module_path . '/kbebay', $this->session_token_key . '=' . $this->session_token . $url, 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_sites'),
            'href' => $this->url->link($this->module_path . '/kbebay/sites', $this->session_token_key . '=' . $this->session_token . $url, 'SSL'),
            'separator' => ' :: '
        );

        $filter_data = array(
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );
        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        $data['sort_id_ebay_site'] = $this->url->link($this->module_path . '/kbebay/sites', $this->session_token_key . '=' . $this->session_token . '&sort=id_ebay_site' . $url, true);
        $data['sort_iso_code'] = $this->url->link($this->module_path . '/kbebay/sites', $this->session_token_key . '=' . $this->session_token . '&sort=abbreviation' . $url, true);
        $data['sort_site_name'] = $this->url->link($this->module_path . '/kbebay/sites', $this->session_token_key . '=' . $this->session_token . '&sort=site_name' . $url, true);
        $data['sort_active'] = $this->url->link($this->module_path . '/kbebay/sites', $this->session_token_key . '=' . $this->session_token . '&sort=enabled' . $url, true);
        $sites_total = $this->model_setting_kbebay->getEbaySitesCount();
        $sites_result = $this->model_setting_kbebay->getEbaySites(0, $filter_data);
        $data['sites'] = array();
        foreach ($sites_result as $result) {
            $data['sites'][] = array(
                'id_ebay_site' => $result['id_ebay_site'],
                'abbreviation' => $result['abbreviation'],
                'site_name' => $result['site_name'],
                'id_ebay_countries' => $result['id_ebay_countries'],
                'token' => $result['token'],
                'status' => $result['enabled'],
                'active' => ($result['enabled']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
                'edit' => $this->url->link($this->module_path . '/kbebay/edit_site', $this->session_token_key . '=' . $this->session_token . "&site_id=" . $result['id_ebay_site'] . $url, true)
            );
        }
        $pagination = new Pagination();
        $pagination->total = $sites_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link($this->module_path . '/kbebay/sites', $this->session_token_key . '=' . $this->session_token . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($sites_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($sites_total - $this->config->get('config_limit_admin'))) ? $sites_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $sites_total, ceil($sites_total / $this->config->get('config_limit_admin')));
        //links
        $data['general_settings'] = $this->url->link($this->module_path . '/kbebay/generalSettings', $this->session_token_key . '=' . $this->session_token, 'SSL');
        $data['action'] = $this->url->link($this->module_path . '/kbebay', $this->session_token_key . '=' . $this->session_token, 'SSL');
        $data['route'] = $this->url->link($this->module_path . '/kbebay', $this->session_token_key . '=' . $this->session_token, 'SSL');
        $data['cancel'] = $this->url->link($this->module_path . '/kbebay', $this->session_token_key . '=' . $this->session_token, 'SSL');
        $data['token'] = $this->session_token;
        $data['session_token_key'] = $this->session_token_key;
        $data['module_path'] = $this->module_path;

        $data['heading_title'] = $this->language->get('heading_title');
        $data['heading_title_main'] = $this->language->get('heading_title_main');
        $data['text_ebay_sites'] = $this->language->get('text_ebay_sites_heading');

        // Filter info
        $data['text_filter_profile_name'] = $this->language->get('text_filter_profile_name');
        $data['text_filter_ebay_category'] = $this->language->get('text_filter_ebay_category');
        $data['text_filter_store_category'] = $this->language->get('text_filter_store_category');
        $data['text_filter_ebay_site'] = $this->language->get('text_filter_ebay_site');
        $data['column_site_id'] = $this->language->get('column_site_id');
        $data['column_site_name'] = $this->language->get('column_site_name');
        $data['column_iso_code'] = $this->language->get('column_iso_code');
        $data['column_status'] = $this->language->get('column_status');
        $data['column_active'] = $this->language->get('column_active');
        $data['column_action'] = $this->language->get('column_action');
        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['text_ebay_token'] = $this->language->get('text_ebay_token');
        $data['text_edit_token'] = $this->language->get('text_edit_token');
        $data['text_token_status'] = $this->language->get('text_token_status');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        //Tooltips
        //buttons
        $data['button_save'] = $this->language->get('button_save');
        $data['button_save_and_stay'] = $this->language->get('button_save_and_stay');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_add_module'] = $this->language->get('button_add_module');
        $data['button_remove'] = $this->language->get('button_remove');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['text_confirm'] = $this->language->get('text_confirm');
        $data['button_filter'] = $this->language->get('button_filter');
        $data['button_reset'] = $this->language->get('button_reset');
        $data['button_add'] = $this->language->get('button_add');
        $data['button_edit'] = $this->language->get('button_edit');

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        if (isset($this->session->data['error'])) {
            $data['error'] = $this->session->data['error'];
            unset($this->session->data['error']);
        } else {
            $data['error'] = '';
        }
        $data['sort'] = $sort;
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $active_tab['active_tab'] = 1;
        $data['tab_common'] = $this->load->controller($this->module_path . '/kbebay/common', $active_tab);

        if (VERSION >= '2.2.0.0') {
            $this->response->setOutput($this->load->view($this->module_path . '/kbebay/sites', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path . '/kbebay/sites.tpl', $data));
        }
    }

    public function shippingProfile()
    {
        $this->checkGeneralSettings();

        $this->load->language($this->module_path . '/kbebay');
        $this->document->setTitle($this->language->get('heading_title_main'));

        $this->load->model('setting/setting');
        $this->load->model('setting/kbebay');

        $data['heading_title'] = $this->language->get('heading_title');
        $data['heading_title_main'] = $this->language->get('heading_title_main');
        $data['text_ebay_sites'] = $this->language->get('text_ebay_sites_heading');
        $data['text_shipping_profile_list'] = $this->language->get('text_shipping_profile_list');
        $data['button_filter'] = $this->language->get('button_filter');
        $data['button_reset'] = $this->language->get('button_reset');
        $data['text_ebay_sites_heading'] = $this->language->get('text_ebay_sites_heading');
        $data['text_international_shipping_allowed'] = $this->language->get('text_international_shipping_allowed');
        $data['text_shipping_profile_title'] = $this->language->get('text_shipping_profile_title');
        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['button_add'] = $this->language->get('button_add');
        $data['button_edit'] = $this->language->get('button_edit');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['entry_error_message'] = $this->language->get('entry_error_message');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_confirm'] = $this->language->get('text_confirm');

        $data['column_shipping_profile_id'] = $this->language->get('column_shipping_profile_id');
        $data['column_shipping_profile_name'] = $this->language->get('column_shipping_profile_name');
        $data['column_shipping_profile_site'] = $this->language->get('column_shipping_profile_site');
        $data['column_shipping_profile_international_shipping'] = $this->language->get('column_shipping_profile_international_shipping');
        $data['column_shipping_profile_last_update'] = $this->language->get('column_shipping_profile_last_update');
        $data['column_action'] = $this->language->get('column_action');

        if (isset($this->request->get['filter_shipping_profile_name'])) {
            $filter_shipping_profile_name = $this->request->get['filter_shipping_profile_name'];
        } else {
            $filter_shipping_profile_name = "";
        }
        if (isset($this->request->get['filter_shipping_profile_site_name'])) {
            $filter_shipping_profile_site_name = $this->request->get['filter_shipping_profile_site_name'];
        } else {
            $filter_shipping_profile_site_name = "";
        }
        if (isset($this->request->get['filter_international_shipping'])) {
            $filter_international_shipping = $this->request->get['filter_international_shipping'];
        } else {
            $filter_international_shipping = "";
        }
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }
        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'id_ebay_shipping';
        }
        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $url = '';
        $sort_url = '';
        $filter_url = '';

        if (isset($this->request->get['filter_shipping_profile_name'])) {
            $url .= '&filter_shipping_profile_name=' . urlencode(html_entity_decode($this->request->get['filter_shipping_profile_name'], ENT_QUOTES, 'UTF-8'));
            $sort_url .= '&filter_shipping_profile_name=' . urlencode(html_entity_decode($this->request->get['filter_shipping_profile_name'], ENT_QUOTES, 'UTF-8'));
            $filter_url .= '&filter_shipping_profile_name=' . urlencode(html_entity_decode($this->request->get['filter_shipping_profile_name'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_shipping_profile_site_name'])) {
            $url .= '&filter_shipping_profile_site_name=' . urlencode(html_entity_decode($this->request->get['filter_shipping_profile_site_name'], ENT_QUOTES, 'UTF-8'));
            $sort_url .= '&filter_shipping_profile_site_name=' . urlencode(html_entity_decode($this->request->get['filter_shipping_profile_site_name'], ENT_QUOTES, 'UTF-8'));
            $filter_url .= '&filter_shipping_profile_site_name=' . urlencode(html_entity_decode($this->request->get['filter_shipping_profile_site_name'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_international_shipping'])) {
            $url .= '&filter_international_shipping=' . urlencode(html_entity_decode($this->request->get['filter_international_shipping'], ENT_QUOTES, 'UTF-8'));
            $sort_url .= '&filter_international_shipping=' . urlencode(html_entity_decode($this->request->get['filter_international_shipping'], ENT_QUOTES, 'UTF-8'));
            $filter_url .= '&filter_international_shipping=' . urlencode(html_entity_decode($this->request->get['filter_international_shipping'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['page'])) {
            $filter_url .= '&page=' . $this->request->get['page'];
            $sort_url .= '&page=' . $this->request->get['page'];
        }
        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
            $filter_url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
            $filter_url .= '&order=' . $this->request->get['order'];
        }

        if ($order == 'ASC') {
            $sort_url .= '&order=DESC';
        } else {
            $sort_url .= '&order=ASC';
        }

        $filter_data = array(
            'filter_shipping_profile_name' => $filter_shipping_profile_name,
            'filter_shipping_profile_site_name' => $filter_shipping_profile_site_name,
            'filter_international_shipping' => $filter_international_shipping,
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );


        $shipping_profiles = $this->model_setting_kbebay->getShippingProfileList($filter_data);
        $shipping_profile_total = $this->model_setting_kbebay->getShippingTotalProfiles($filter_data);

        foreach ($shipping_profiles as $shipping_profile) {
            $site_name = $this->model_setting_kbebay->getEbaySiteById($shipping_profile['site_id']);
            $data['shipping_profiles'][] = array(
                'id_ebay_shipping' => $shipping_profile['id_ebay_shipping'],
                'shipping_profile_name' => $shipping_profile['shipping_profile_name'],
                'site_name' => $site_name['description'],
                'international_shipping_allowed' => $shipping_profile['international_shipping_allowed'] ? $this->language->get('text_yes') : $this->language->get('text_no'),
                'date_upd' => $shipping_profile['date_upd'],
                'edit' => $this->url->link($this->module_path . '/kbebay/shippingProfileUpdate', 'id_ebay_shipping=' . $shipping_profile['id_ebay_shipping'] . '&' . $this->session_token_key . '=' . $this->session_token, 'SSL'),
                'delete' => $this->url->link($this->module_path . '/kbebay/shippingProfileDelete', 'id_ebay_shipping=' . $shipping_profile['id_ebay_shipping'] . '&' . $this->session_token_key . '=' . $this->session_token . $filter_url, 'SSL'),
            );
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link($this->extension_path, $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_shipping_profiles'),
            'href' => $this->url->link($this->module_path . '/kbebay/shippingProfile', $this->session_token_key . '=' . $this->session_token . $filter_url, 'SSL'),
            'separator' => ' :: '
        );

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        if (isset($this->session->data['error'])) {
            $data['error'] = $this->session->data['error'];
            unset($this->session->data['error']);
        } else {
            $data['error'] = '';
        }

        $ebay_sites = $this->model_setting_kbebay->getEbaySites(1);
        $data['ebay_sites'] = $ebay_sites;

        $data['sort_id_shipping_profile'] = $this->url->link($this->module_path . '/kbebay/shippingProfile', $this->session_token_key . '=' . $this->session_token . '&sort=id_ebay_shipping' . $sort_url, true);
        $data['sort_ebay_shipping_profile_name'] = $this->url->link($this->module_path . '/kbebay/shippingProfile', $this->session_token_key . '=' . $this->session_token . '&sort=shipping_profile_name' . $sort_url, true);
        $data['sort_ebay_shipping_profile_site'] = $this->url->link($this->module_path . '/kbebay/shippingProfile', $this->session_token_key . '=' . $this->session_token . '&sort=site_id' . $sort_url, true);
        $data['sort_ebay_shipping_profile_last_update'] = $this->url->link($this->module_path . '/kbebay/shippingProfile', $this->session_token_key . '=' . $this->session_token . '&sort=date_upd' . $sort_url, true);
        $data['sort_ebay_shipping_profile_international'] = $this->url->link($this->module_path . '/kbebay/shippingProfile', $this->session_token_key . '=' . $this->session_token . '&sort=international_shipping_allowed' . $sort_url, true);

        $data['add'] = $this->url->link($this->module_path . '/kbebay/shippingProfileUpdate', $this->session_token_key . '=' . $this->session_token, true);
        $data['token'] = $this->session_token;
        $data['session_token_key'] = $this->session_token_key;
        $data['module_path'] = $this->module_path;

        $pagination = new Pagination();
        $pagination->total = $shipping_profile_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link($this->module_path . '/kbebay/shippingProfile', $this->session_token_key . '=' . $this->session_token . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($shipping_profile_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($shipping_profile_total - $this->config->get('config_limit_admin'))) ? $shipping_profile_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $shipping_profile_total, ceil($shipping_profile_total / $this->config->get('config_limit_admin')));

        $data['sort'] = $sort;
        $data['order'] = strtolower($order);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['filter_shipping_profile_name'] = $filter_shipping_profile_name;
        $data['filter_shipping_profile_site_name'] = $filter_shipping_profile_site_name;
        $data['filter_international_shipping'] = $filter_international_shipping;

        $active_tab['active_tab'] = 3;
        $data['tab_common'] = $this->load->controller($this->module_path . '/kbebay/common', $active_tab);
        if (VERSION >= '2.2.0.0') {
            $this->response->setOutput($this->load->view($this->module_path . '/kbebay/shipping_profile', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path . '/kbebay/shipping_profile.tpl', $data));
        }
    }

    public function shippingProfileDelete()
    {
        $this->load->language($this->module_path . '/kbebay');
        if (isset($this->request->get["id_ebay_shipping"])) {
            $eBayProfile = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'kb_ebay_profiles WHERE ebay_shipping_profile = "' . $this->request->get["id_ebay_shipping"] . '"');
            if ($eBayProfile->num_rows > 0) {
                $this->session->data['error'] = $this->language->get('text_error_shipping_profile_deleted');
            } else {
                $this->db->query('DELETE FROM ' . DB_PREFIX . 'kb_ebay_shipping WHERE id_ebay_shipping = "' . $this->request->get["id_ebay_shipping"] . '"');
                $this->session->data['success'] = $this->language->get('text_shipping_profile_deleted');
            }
        }
        //$this->response->redirect($this->url->link($this->module_path.'/kbebay/shippingProfile', $this->session_token_key . '=' . $this->session_token, 'SSL'));
        $this->shippingProfile();
    }

    public function ajaxGetCategoryFeature()
    {

        $this->load->language($this->module_path . '/kbebay');
        $this->load->model('setting/kbebay');
        $call_name = 'GetCategoryFeatures';
        $siteDetails = $this->model_setting_kbebay->getEbaySiteById($this->request->post['site_id']);
        $token = $siteDetails['token'];
        $config = $this->model_setting_kbebay->getConfiguration();
        if ($config['account_type'] == 'sandbox') {
            $sandbox = true;
        } else {
            $sandbox = false;
        }
        $headers = $this->model_setting_kbebay->getEbayHeaders($call_name, $this->request->post['site_id']);
        echo $this->model_setting_kbebay->getCategoryFeatures($headers, $token, $this->request->post['selected_cat'], $sandbox, 'PaymentMethods');
        die();
    }

    public function shippingProfileUpdate()
    {
        $this->checkGeneralSettings();
        $this->load->language($this->module_path . '/kbebay');
        $this->document->setTitle($this->language->get('heading_title_main'));

        $this->load->model('setting/setting');
        $this->load->model('setting/kbebay');

        if (isset($this->request->post) && !empty($this->request->post)) {
            $this->model_setting_kbebay->saveShippingProfile($this->request->post);
            $this->session->data['success'] = $this->language->get('text_shipping_profile_save');
            $this->response->redirect($this->url->link($this->module_path . '/kbebay/shippingProfile', $this->session_token_key . '=' . $this->session_token, 'SSL'));
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['heading_title_main'] = $this->language->get('heading_title_main');
        $data['text_ebay_sites'] = $this->language->get('text_ebay_sites_heading');
        $data['text_service_types'] = $this->language->get('text_service_types');
        $data['text_shipping_add_profile'] = $this->language->get('text_shipping_add_profile');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['button_add'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['text_shipping_profile_title'] = $this->language->get('text_shipping_profile_title');
        $data['text_international_shipping_allowed'] = $this->language->get('text_international_shipping_allowed');
        $data['text_exclude_destination'] = $this->language->get('text_exclude_destination');
        $data['text_add_domestic_shipping'] = $this->language->get('text_add_domestic_shipping');
        $data['text_add_international_shipping'] = $this->language->get('text_add_international_shipping');
        $data['text_shipping_postal_code'] = $this->language->get('text_shipping_postal_code');
        $data['text_select_state_error'] = $this->language->get('text_select_state_error');
        $data['text_site_select'] = $this->language->get('text_site_select');
        $data['text_error_empty_field'] = $this->language->get('text_error_empty_field');
        $data['text_error_valid_amount'] = $this->language->get('text_error_valid_amount');
        $data['text_error_number_field'] = $this->language->get('text_error_number_field');
        $data['text_remove'] = $this->language->get('text_remove');
        $data['text_none'] = $this->language->get('text_none');

        $data['text_package_type'] = $this->language->get('text_package_type');
        $data['column_domestic_shipping_service'] = $this->language->get('column_domestic_shipping_service');
        $data['column_domestic_free_shipping'] = $this->language->get('column_domestic_free_shipping');
        $data['column_domestic_shipping_priority'] = $this->language->get('column_domestic_shipping_priority');
        $data['column_domestic_shipping_service_cost'] = $this->language->get('column_domestic_shipping_service_cost');
        $data['column_domestic_shipping_additional_cost'] = $this->language->get('column_domestic_shipping_additional_cost');
        $data['column_action'] = $this->language->get('column_action');
        $data['column_international_shipping_service'] = $this->language->get('column_international_shipping_service');
        $data['column_international_shipping_location'] = $this->language->get('column_international_shipping_location');
        $data['column_international_shipping_priority'] = $this->language->get('column_international_shipping_priority');
        $data['column_international_shipping_service_cost'] = $this->language->get('column_international_shipping_service_cost');
        $data['column_international_shipping_additional_cost'] = $this->language->get('column_international_shipping_additional_cost');
        $data['cancel'] = $this->url->link($this->module_path . '/kbebay/shippingProfile', $this->session_token_key . '=' . $this->session_token, 'SSL');
        $this->document->addScript('view/javascript/velovalidation.js');

        if (isset($this->request->get['id_ebay_shipping'])) {
            $id_ebay_shipping = $this->request->get['id_ebay_shipping'];
        }

        if (isset($id_ebay_shipping)) {
            $shipping_profile_data = $this->model_setting_kbebay->getShippingProfileData($id_ebay_shipping);
            $data['id_ebay_shipping'] = $id_ebay_shipping;
            $data['ebay_select_sites'] = $shipping_profile_data['site_id'];
            $data['ebay_service_type'] = $shipping_profile_data['service_type'];
            $data['shipping_profile_name'] = $shipping_profile_data['shipping_profile_name'];
            $data['ebay_package_type'] = $shipping_profile_data['package_type'];
            $data['shipping_postal_code'] = $shipping_profile_data['postal_code'];
            $data['domestic_shippings'] = json_decode($shipping_profile_data['domestic_shipping'], true);
            $data['international_shippings'] = json_decode($shipping_profile_data['international_shipping'], true);
            $data['international_shipping_allowed'] = $shipping_profile_data['international_shipping_allowed'];
            $data['shipping_excluded_location'] = $shipping_profile_data['excluded_location'];
            $i = 0;
            $ship_international = array();
            foreach ($data['international_shippings'] as $data_ship) {
                $ship_international[$i] = $data_ship['location'];
                $i++;
            }
            $data['ship_international_string'] = json_encode($ship_international, true);
        } else {
            $data['ebay_select_sites'] = "select";
            $data['ebay_package_type'] = '';
            $data['domestic_shippings'] = array();
            $data['international_shippings'] = array();
            $data['shipping_postal_code'] = "";
            $data['international_shipping_allowed'] = 0;
            $data['shipping_profile_name'] = "";
            $data['ebay_service_type'] = "Flat";
            $data['ship_international_string'] = json_encode(array(), true);
            $data['shipping_excluded_location'] = json_encode(array(), true);
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link($this->extension_path, $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->url->link($this->module_path . '/kbebay', $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_shipping_profiles'),
            'href' => $this->url->link($this->module_path . '/kbebay/shippingProfile', $this->session_token_key . '=' . $this->session_token, 'SSL'),
            'separator' => ' :: '
        );

        if (isset($this->request->get['id_ebay_shipping'])) {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_shipping_add_profile'),
                'href' => $this->url->link($this->module_path . '/kbebay/shippingProfileUpdate', $this->session_token_key . '=' . $this->session_token . "&id_ebay_shipping=" . $this->request->get['id_ebay_shipping'], 'SSL'),
                'separator' => ' :: '
            );
        } else {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_shipping_add_profile'),
                'href' => $this->url->link($this->module_path . '/kbebay/shippingProfileUpdate', $this->session_token_key . '=' . $this->session_token, 'SSL'),
                'separator' => ' :: '
            );
        }


        $ebay_sites = $this->model_setting_kbebay->getEbaySites(1);
        array_unshift($ebay_sites, array('id_ebay_countries' => 'select', 'description' => $this->language->get('text_ebay_site_select')));
        $data['ebay_sites'] = $ebay_sites;


        $service_type_list = array(
            array(
                'service_value' => 'Flat',
                'service_name' => $this->language->get('Flat'),
            ),
            array(
                'service_value' => 'Calculated',
                'service_name' => $this->language->get('Calculated'),
            )
        );

        /* Default Values */
        $data['service_type_list'] = $service_type_list;
        $data['token'] = $this->session_token;
        $data['session_token_key'] = $this->session_token_key;
        $data['module_path'] = $this->module_path;

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        if (isset($this->session->data['error'])) {
            $data['error'] = $this->session->data['error'];
            unset($this->session->data['error']);
        } else {
            $data['error'] = '';
        }
        $data['sort'] = "";
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $active_tab['active_tab'] = 3;
        $data['tab_common'] = $this->load->controller($this->module_path . '/kbebay/common', $active_tab);
        if (VERSION >= '2.2.0.0') {
            $this->response->setOutput($this->load->view($this->module_path . '/kbebay/update_shipping_profile', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path . '/kbebay/update_shipping_profile.tpl', $data));
        }
    }

    public function getInternationalShippingDetails()
    {
        $response = array();
        if (isset($this->request->get['site_id'])) {
            $this->load->language($this->module_path . '/kbebay');
            $this->load->model('setting/setting');
            $this->load->model('setting/kbebay');

            $international_service_query = $this->db->query('SELECT distinct ebay_shipping_name FROM ' . DB_PREFIX . 'kb_ebay_shipping_methods where site_id =' . (int) $this->request->get['site_id'] . ' and service_type Like "%' . $this->request->get['service_type'] . '%" and international_shipping = 1');
            if ($international_service_query->num_rows > 0) {
                $international_service_array = array();
                foreach ($international_service_query->rows as $service) {
                    if ($service['ebay_shipping_name'] != '') {
                        $international_service_array[$service['ebay_shipping_name']] = $service['ebay_shipping_name'];
                    }
                }
                $response = array("type" => "success", "service_type" => $international_service_array);
            } else {
                $response = array("type" => "failed", "message" => sprintf($this->language->get('ebay_text_import_shipping_method'), HTTPS_CATALOG . 'index.php?route=ebay_feed/cron/importShippings'));
            }
        }
        echo json_encode($response);
    }

    public function getDomesticShippingDetails()
    {
        $response = array();
        if (isset($this->request->get['site_id'])) {
            $this->load->language($this->module_path . '/kbebay');
            $this->load->model('setting/setting');
            $this->load->model('setting/kbebay');

            $domestic_service_query = $this->db->query('SELECT distinct ebay_shipping_name FROM ' . DB_PREFIX . 'kb_ebay_shipping_methods where site_id =' . (int) $this->request->get['site_id'] . ' and service_type Like "%' . $this->request->get['service_type'] . '%" and international_shipping = 0');
            if ($domestic_service_query->num_rows > 0) {
                $domestic_service_array = array();
                foreach ($domestic_service_query->rows as $service) {
                    if ($service['ebay_shipping_name'] != '') {
                        $domestic_service_array[$service['ebay_shipping_name']] = $service['ebay_shipping_name'];
                    }
                }

                /* Get Type */
                $service_types = $this->db->query('SELECT DISTINCT(service_type) as service_type  FROM ' . DB_PREFIX . 'kb_ebay_shipping_methods where site_id = ' . (int) $this->request->get['site_id']);
                if ($service_types->num_rows > 0) {
                    $service_type_array = array();
                    foreach ($service_types->rows as $service_type) {
                        $service_type_explode = explode(",", $service_type['service_type']);
                        foreach ($service_type_explode as $service_type_exploded) {
                            if ($service_type_exploded == 'Flat' || $service_type_exploded == 'Calculated') {
                                $service_type_array[$service_type_exploded] = $this->language->get($service_type_exploded);
                            }
                        }
                    }
                }

                $response = array("type" => "success", "service_type" => $domestic_service_array, 'service_type_array' => $service_type_array);
            } else {
                $response = array("type" => "failed", "message" => sprintf($this->language->get('ebay_text_import_shipping_method'), HTTPS_CATALOG . 'index.php?route=ebay_feed/cron/importShippings'));
            }
        }
        echo json_encode($response);
    }

    public function getShippingDetails()
    {
        $response = array();
        if (isset($this->request->get['store_id'])) {
            $this->load->language($this->module_path . '/kbebay');
            $this->load->model('setting/setting');
            $this->load->model('setting/kbebay');
            $results = $this->model_setting_kbebay->getShippingMethodsById($this->request->get['store_id']);
            if (!empty($results)) {
                $package_type_query = $this->db->query('SELECT distinct package_type FROM ' . DB_PREFIX . 'kb_ebay_shipping_methods where site_id =' . (int) $this->request->get['store_id'] . ' and service_type Like "%' . $this->request->get['service_type'] . '%"');
                $package_type_array = array();
                $package_string = "";
                foreach ($package_type_query->rows as $package_type) {

                    if ($package_type['package_type'] != '') {
                        $package_string.= $package_type['package_type'];
                        //$package_type_array[$package_type['package_type']] = $package_type['package_type'];
                    }
                    $package_string.=",";
                }
                $package_string_array = explode(",", $package_string);
                $package_string_array = array_unique($package_string_array);
                foreach ($package_string_array as $package) {
                    if ($package != "") {
                        $package_type_array[$package] = $package;
                    }
                }
                $response = array("type" => "success", "package_type" => $package_type_array);
            } else {
                $response = array("type" => "failed", "message" => sprintf($this->language->get('ebay_text_import_shipping_method'), HTTPS_CATALOG . 'index.php?route=ebay_feed/cron/importShippings'));
            }
        }
        echo json_encode($response);
    }

    public function getExcludeLocation1()
    {
        $call_name = 'GeteBayDetails';
        $this->load->model('setting/kbebay');
        $siteDetails = $this->model_setting_kbebay->getEbaySiteById($this->request->get['site_id']);
        $token = $siteDetails['token'];
        $config = $this->model_setting_kbebay->getConfiguration();
        if ($config['account_type'] == 'sandbox') {
            $sandbox = true;
        } else {
            $sandbox = false;
        }
        $headers = $this->model_setting_kbebay->getEbayHeaders($call_name, $this->request->get['site_id']);
        $excluded_location_data = $this->model_setting_kbebay->getExcludedLocations($headers, $token, 'ExcludeShippingLocationDetails', $sandbox);
        $excluded_shipping_array = json_decode($excluded_location_data, true);
        $final_excluded_location_array = array();
        $oneDimensionalArray = array();
        if (!empty($excluded_shipping_array)) {
            if (isset($excluded_shipping_array['ExcludeShippingLocationDetails'])) {
                foreach ($excluded_shipping_array['ExcludeShippingLocationDetails'] as $excluded_location) {
                    $final_excluded_location_array[$excluded_location['Region']][] = array(
                        'Location' => $excluded_location['Location'],
                        'Description' => $excluded_location['Description']);
                }
                $oneDimensionalArray = call_user_func_array('array_merge', $final_excluded_location_array);
            }
            //Sort on the basis of name to display in Dropdown
            array_multisort(array_column($oneDimensionalArray, 'Description'), SORT_ASC, $oneDimensionalArray);
            echo json_encode($oneDimensionalArray);
        }
        die();
    }
    
    public function getExcludeLocation()
    {
        $call_name = 'GeteBayDetails';
        $this->load->model('setting/kbebay');
        $siteDetails = $this->model_setting_kbebay->getEbaySiteById($this->request->get['site_id']);
        $token = $siteDetails['token'];
        $config = $this->model_setting_kbebay->getConfiguration();
        if ($config['account_type'] == 'sandbox') {
            $sandbox = true;
        } else {
            $sandbox = false;
        }
        $headers = $this->model_setting_kbebay->getEbayHeaders($call_name, $this->request->get['site_id']);
        if(isset($this->request->get['type']) && $this->request->get['type'] == 1) {
            $excluded_location_data = $this->model_setting_kbebay->getExcludedLocations($headers, $token, 'ShippingLocationDetails', $sandbox);
            $excluded_shipping_array = json_decode($excluded_location_data, true);
            $final_excluded_location_array = array();
            $oneDimensionalArray = array();
            if (!empty($excluded_shipping_array)) {
                if (isset($excluded_shipping_array['ShippingLocationDetails'])) {
                    foreach ($excluded_shipping_array['ShippingLocationDetails'] as $excluded_location) {
                        $final_excluded_location_array[$excluded_location['ShippingLocation']][] = array(
                            'Location' => $excluded_location['ShippingLocation'],
                            'Description' => $excluded_location['Description']);
                    }
                    $oneDimensionalArray = call_user_func_array('array_merge', $final_excluded_location_array);
                }
                //Sort on the basis of name to display in Dropdown
                array_multisort(array_column($oneDimensionalArray, 'Description'), SORT_ASC, $oneDimensionalArray);
            }
            $international_service_array = array();
            $international_service_query = $this->db->query('SELECT distinct ebay_shipping_name FROM ' . DB_PREFIX . 'kb_ebay_shipping_methods where site_id =' . (int) $this->request->get['site_id'] . ' and service_type Like "%' . $this->request->get['service_type'] . '%" and international_shipping = 1');
            if ($international_service_query->num_rows > 0) {
                $international_service_array = array();
                foreach ($international_service_query->rows as $service) {
                    if ($service['ebay_shipping_name'] != '') {
                        $international_service_array[$service['ebay_shipping_name']] = $service['ebay_shipping_name'];
                    }
                }
            } 
            echo json_encode(array("location" => $oneDimensionalArray, "service_type" => $international_service_array));
        } else {
            $excluded_location_data = $this->model_setting_kbebay->getExcludedLocations($headers, $token, 'ExcludeShippingLocationDetails', $sandbox);
            $excluded_shipping_array = json_decode($excluded_location_data, true);
            $final_excluded_location_array = array();
            $oneDimensionalArray = array();
            if (!empty($excluded_shipping_array)) {
                if (isset($excluded_shipping_array['ExcludeShippingLocationDetails'])) {
                    foreach ($excluded_shipping_array['ExcludeShippingLocationDetails'] as $excluded_location) {
                        $final_excluded_location_array[$excluded_location['Region']][] = array(
                            'Location' => $excluded_location['Location'],
                            'Description' => $excluded_location['Description']);
                    }
                    $oneDimensionalArray = call_user_func_array('array_merge', $final_excluded_location_array);
                }
                //Sort on the basis of name to display in Dropdown
                array_multisort(array_column($oneDimensionalArray, 'Description'), SORT_ASC, $oneDimensionalArray);
                echo json_encode($oneDimensionalArray);
            }
        } 
        die();
    }

    public function getGetTaxTable()
    {
        $call_name = 'GetTaxTable';
        $this->load->model('setting/kbebay');
        $siteDetails = $this->model_setting_kbebay->getEbaySiteById($this->request->get['site_id']);
        $token = $siteDetails['token'];
        $config = $this->model_setting_kbebay->getConfiguration();
        if ($config['account_type'] == 'sandbox') {
            $sandbox = true;
        } else {
            $sandbox = false;
        }
        $headers = $this->model_setting_kbebay->getEbayHeaders($call_name, $this->request->get['site_id']);
        $tax_data = $this->model_setting_kbebay->getGetTaxTable($headers, $token, $sandbox);
        $tax_data_array = json_decode($tax_data, true);
        //echo $tax_data_array;
        die();
    }

    public function getVATInfo()
    {
        $call_name = 'GetUser';
        $this->load->model('setting/kbebay');
        $siteDetails = $this->model_setting_kbebay->getEbaySiteById($this->request->get['site_id']);
        $token = $siteDetails['token'];
        $config = $this->model_setting_kbebay->getConfiguration();
        if ($config['account_type'] == 'sandbox') {
            $sandbox = true;
        } else {
            $sandbox = false;
        }
        $headers = $this->model_setting_kbebay->getEbayHeaders($call_name, $this->request->get['site_id']);
        $tax_data = $this->model_setting_kbebay->getVATInfo($headers, $token, $sandbox);
        $tax_data_array = json_decode($tax_data, true);
        die();
    }

    private function checkGeneralSettings()
    {
        $this->load->language($this->module_path . '/kbebay');
        $this->load->model('setting/kbebay');

        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }

        $config = $this->model_setting_kbebay->getConfiguration();
        if (empty($config)) {
            $this->session->data['error'] = $this->language->get('text_save_general_setting');
            $this->response->redirect($this->url->link($this->module_path . '/kbebay/generalSettings', $this->session_token_key . '=' . $this->session_token, 'SSL'));
        } else {
            $orderSettings = $this->model_setting_kbebay->getSetting('ebay_order_settings', $store_id);
            if (empty($orderSettings)) {
                $this->session->data['error'] = $this->language->get('text_save_order_setting');
                $this->response->redirect($this->url->link($this->module_path . '/kbebay/orderSettings', $this->session_token_key . '=' . $this->session_token, 'SSL'));
            }
        }
    }

    private function executeCRON($name)
    {
        $url = HTTPS_CATALOG . "index.php?route=ebay_feed/cron/" . $name;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

}

?>
