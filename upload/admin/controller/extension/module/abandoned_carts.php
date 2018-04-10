<?php
class ControllerExtensionModuleAbandonedCarts extends Controller {
	private $error = array();

  public function install() {
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "order` ADD `abandoned`
			TINYINT(1) NOT NULL DEFAULT '0' AFTER `date_modified`;
		");

		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'report/' . $this->request->get['extension']);
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'report/' . $this->request->get['extension']);

		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'controller/extension/module/' . $this->request->get['extension']);
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'controller/extension/module/' . $this->request->get['extension']);
	}

	public function uninstall() {
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "order` DROP `abandoned`;
		");
	}


	public function index() {
		$this->load->language('extension/module/abandoned_carts');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('abandoned_carts', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true));
		}

		$data['heading_title']           = $this->language->get('heading_title');

		$data['text_edit']               = $this->language->get('text_edit');
		$data['text_enabled']            = $this->language->get('text_enabled');
		$data['text_disabled']           = $this->language->get('text_disabled');

		$data['entry_name']              = $this->language->get('entry_name');
		$data['entry_abandoned_status']  = $this->language->get('entry_abandoned_status');
		$data['entry_limit']             = $this->language->get('entry_limit');
		$data['entry_limit_info']        = $this->language->get('entry_limit_info');
		$data['entry_status']            = $this->language->get('entry_status');

		$data['help_abandoned_status']	 = $this->language->get('help_abandoned_status');

		$data['button_save']             = $this->language->get('button_save');
		$data['button_cancel']           = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		// allow the shop admin to specify which status(es) are deemed abandoned
		$this->load->model('localisation/order_status');

		$data['abandoned_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->error['limit'])) {
			$data['error_limit'] = $this->error['limit'];
		} else {
			$data['error_limit'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/abandoned_carts', 'token=' . $this->session->data['token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/abandoned_carts', 'token=' . $this->session->data['token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		$data['action'] = $this->url->link('extension/module/abandoned_carts', 'token=' . $this->session->data['token'], true);

		$data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true);

		if (isset($this->request->post['abandoned_carts_criteria'])) {
			$data['abandoned_carts_criteria'] = $this->request->post['abandoned_carts_criteria'];
		} elseif ($this->config->get('abandoned_carts_criteria')) {
			$data['abandoned_carts_criteria'] = $this->config->get('abandoned_carts_criteria');
		} else {
			$data['abandoned_carts_criteria'] = array();
		}

		if (isset($this->request->post['abandoned_carts_limit'])) {
			$data['abandoned_carts_limit'] = $this->request->post['abandoned_carts_limit'];
		} elseif (!empty($this->config->get('abandoned_carts_limit'))) {
			$data['abandoned_carts_limit'] = $this->config->get('abandoned_carts_limit');
		} else {
			$data['abandoned_carts_limit'] = 5;
		}

		if (isset($this->request->post['abandoned_carts_status'])) {
			$data['abandoned_carts_status'] = $this->request->post['abandoned_carts_status'];
		} elseif (!empty($this->config->get('abandoned_carts_status'))) {
			$data['abandoned_carts_status'] = $this->config->get('abandoned_carts_status');
		} else {
			$data['abandoned_carts_status'] = '';
		}

		$data['header']      = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer']      = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/abandoned_carts', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/abandoned_carts')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ($this->request->post['abandoned_carts_limit'] < 1) {
			$this->error['abandoned_carts_limit'] = $this->language->get('error_limit');
		}

		if ($this->request->post['abandoned_carts_limit'] < 1) {
			$this->error['abandoned_carts_limit'] = $this->language->get('error_limit');
		}

		return !$this->error;
	}
}
