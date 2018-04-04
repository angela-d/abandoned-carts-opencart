<?php
class ControllerReportAbandonedCarts extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('report/abandoned_carts');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/module/abandoned_carts');

		$this->getList();
	}

	public function recover() {
		$this->load->language('report/abandoned_carts');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/module/abandoned_carts');

		if (isset($this->request->post['selected']) && $this->validate()) {
			foreach ($this->request->post['selected'] as $order_id) {
				$this->model_extension_module_abandoned_carts->recoverEmail($order_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			$this->response->redirect($this->url->link('report/abandoned_carts', 'token=' . $this->session->data['token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'o.order_id';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('report/abandoned_carts', 'token=' . $this->session->data['token'] . $url, true)
		);

		$data['recover'] = $this->url->link('report/abandoned_carts/recover', 'token=' . $this->session->data['token'], true);
		$data['delete']  = $this->url->link('report/abandoned_carts/delete', 'token=' . $this->session->data['token'], true);
		$data['orders']  = array();

		$filter_data = array(
			'days'  => $this->config->get('abandoned_carts_limit'),
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$order_total = $this->model_extension_module_abandoned_carts->getTotalOrders($filter_data);

		$results     = $this->model_extension_module_abandoned_carts->getOrders($filter_data);

		foreach ($results as $result) {
			$existing_carts = $this->model_extension_module_abandoned_carts->checkDuplicates($result['ip']);

			$data['orders'][] = array(
				'order_id'        => $result['order_id'],
				'customer'        => $result['customer'],
				'order_status'    => $result['order_status'] ? $result['order_status'] : $this->language->get('text_missing'),
				'total'           => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'date_added'      => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'date_modified'   => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
				'view'            => $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=' . $result['order_id'] . $url, true),
				'ip' 		          => $result['ip'],
				'user_agent'      => $result['user_agent'],
				'abandoned'       => $result['abandoned'],
				'duplicate_count' => $existing_carts,
				'duplicate'       => ($result['abandoned'] =='0' && $existing_carts > 0) ? sprintf($this->language->get('warning_duplicate'),$existing_carts,'<a data-toggle="tooltip" title="" data-original-title="'.$this->language->get('text_search').'" href="'.$this->url->link('customer/customer', 'token=' . $this->session->data['token'].'&filter_ip='.$result['ip'], true).'">'.$result['customer'].'</a>') : ''
			);
		}

		$data['heading_title']          = $this->language->get('heading_title');

		$data['text_list']              = $this->language->get('text_list');
		$data['text_no_results']        = $this->language->get('text_no_results');
		$data['text_confirm']           = $this->language->get('text_confirm');
		$data['text_success']           = $this->language->get('text_success');

		$data['column_order_id']        = $this->language->get('column_order_id');
		$data['column_customer']        = $this->language->get('column_customer');
		$data['column_status']          = $this->language->get('column_status');
		$data['column_total']           = $this->language->get('column_total');
		$data['column_date_added']      = $this->language->get('column_date_added');
		$data['column_date_modified']   = $this->language->get('column_date_modified');
		$data['column_abandoned']       = $this->language->get('column_abandoned');
		$data['column_action']          = $this->language->get('column_action');

		$data['button_recover']         = $this->language->get('button_recover');
		$data['button_delete']          = $this->language->get('button_delete');
		$data['button_view']            = $this->language->get('button_view');

		$data['token']                  = $this->session->data['token'];

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_order']         = $this->url->link('report/abandoned_carts', 'token=' . $this->session->data['token'] . '&sort=o.order_id' . $url, true);
		$data['sort_customer']      = $this->url->link('report/abandoned_carts', 'token=' . $this->session->data['token'] . '&sort=customer' . $url, true);
		$data['sort_status']        = $this->url->link('report/abandoned_carts', 'token=' . $this->session->data['token'] . '&sort=order_status' . $url, true);
		$data['sort_total']         = $this->url->link('report/abandoned_carts', 'token=' . $this->session->data['token'] . '&sort=o.total' . $url, true);
		$data['sort_date_added']    = $this->url->link('report/abandoned_carts', 'token=' . $this->session->data['token'] . '&sort=o.date_added' . $url, true);
		$data['sort_date_modified'] = $this->url->link('report/abandoned_carts', 'token=' . $this->session->data['token'] . '&sort=o.date_modified' . $url, true);
		$data['sort_abandoned']     = $this->url->link('report/abandoned_carts', 'token=' . $this->session->data['token'] . '&sort=o.abandoned' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination         = new Pagination();
		$pagination->total  = $order_total;
		$pagination->page   = $page;
		$pagination->limit  = $this->config->get('config_limit_admin');
		$pagination->url    = $this->url->link('report/abandoned_carts', 'token=' . $this->session->data['token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();
		$data['results']    = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($order_total - $this->config->get('config_limit_admin'))) ? $order_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $order_total, ceil($order_total / $this->config->get('config_limit_admin')));
		$data['sort']       = $sort;
		$data['order']      = $order;

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		$data['header']         = $this->load->controller('common/header');
		$data['column_left']    = $this->load->controller('common/column_left');
		$data['footer']         = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('report/abandoned_carts', $data));
	}


	public function delete() {
		$this->load->language('report/abandoned_carts');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/module/abandoned_carts');

		if (isset($this->request->post['selected']) && $this->validate()) {
			foreach ($this->request->post['selected'] as $order_id) {
				$this->model_extension_module_abandoned_carts->deleteOrder($order_id);
			}

			$this->session->data['success'] = $this->language->get('text_deleted');

			$this->response->redirect($this->url->link('report/abandoned_carts', 'token=' . $this->session->data['token'], true));
		}

		$this->getList();
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'report/abandoned_carts')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
