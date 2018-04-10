<?php
class ModelExtensionModuleAbandonedCarts extends Model {
	public function checkDuplicates($ip){
		$query = $this->db->query("SELECT ip FROM " . DB_PREFIX . "order WHERE ip = '".$this->db->escape($ip)."'");

		return $query->num_rows;
	}

	public function recoverEmail($order_id) {
		$order_info = $this->getOrder($order_id);

		$language = new Language($order_info['language_code']);
		$language->load($order_info['language_code']);
		$language->load('extension/module/abandoned_carts');

		$text  = sprintf($language->get('failed_cart_greeting'),ucfirst($order_info['firstname']))."\n\n";
		$text .= $language->get('failed_cart_intro') . "\n\n";
		$text .= $language->get('failed_cart_contents') . "\n";
		$order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

		foreach ($order_product_query->rows as $product) {
			$data['products'] = array();
				$data['products'][] = array(
					'name' => $product['name']
				);
		}

		foreach ($order_product_query->rows as $product) {
			$text .= $product['quantity'] . 'x ' . $product['name'] . "\n";

		}

		$text .= "\n".$language->get('failed_cart_body') . "\n\n";
		$text .= $language->get('failed_cart_footer') . "\n\n";
		$text .= $language->get('failed_cart_signoff') . "\n\n";
		$text .= $language->get('failed_cart_signature') . "\n\n";
		$text .= $order_info['store_name'] . "\n";
		$text .= $order_info['store_url'] . "\n";

		$mail = new Mail();
		$mail->protocol      = $this->config->get('config_mail_protocol');
		$mail->parameter     = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port     = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout  = $this->config->get('config_mail_smtp_timeout');
		$mail->setTo($order_info['email']);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender($order_info['store_name']);
		$mail->setSubject($language->get('subject_prefix').' '.$order_info['store_name']);
		$mail->setText($text);
		$mail->send();

		//now that we sent an email, mark it so we dont bug them again
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET abandoned = '1' WHERE order_id = '" . (int)$order_id . "'");
	}

	public function getOrders($data = array()) {
		$implode = array();

		if ($this->config->get('abandoned_carts_criteria')){
			foreach ($this->config->get('abandoned_carts_criteria') as $criteria) {
				$implode[] = "'" . (int)$criteria . "'";
			}

		$criteria_statuses = implode(" OR ", $implode);
		}

		$sql = "SELECT o.order_id, CONCAT(o.firstname, ' ', o.lastname) AS customer, (SELECT os.name FROM " . DB_PREFIX . "order_status os WHERE os.order_status_id = o.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') AS order_status, o.ip, o.user_agent, o.total, o.currency_code, o.currency_value, o.date_added, o.date_modified, o.abandoned FROM `" . DB_PREFIX . "order` o";

		$sql .= " WHERE o.date_added >= DATE_SUB(NOW(), INTERVAL ".$this->config->get('abandoned_carts_limit')." DAY) && o.firstname !='' && lastname !='' && order_status_id=0";

		if (!empty($implode)){
			$sql .= " || order_status_id = " . $criteria_statuses;
		}

		$sort_data = array(
			'o.order_id',
			'customer',
			'order_status',
			'o.total',
			'o.date_added',
			'o.date_modified',
			'o.abandoned',
			'o.total'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY o.order_id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalOrders($data = array()) {
		$implode = array();

		if ($this->config->get('abandoned_carts_criteria')){

			foreach ($this->config->get('abandoned_carts_criteria') as $criteria) {
				$implode[] = "'" . (int)$criteria . "'";

				$criteria_statuses = implode(" OR ", $implode);
			}
		}

		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE abandoned='0' && date_added >= DATE_SUB(NOW(), INTERVAL ".$this->config->get('abandoned_carts_limit')." DAY) && firstname !='' && lastname !='' && order_status_id=0";

		if (!empty($implode)){
			$sql .= " || order_status_id = " . $criteria_statuses;
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getOrder($order_id) {
		$order_query = $this->db->query("SELECT firstname, lastname, email, language_id, store_name, store_url FROM `" . DB_PREFIX . "order` o WHERE o.order_id = '" . (int)$order_id . "'");

			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language->getLanguage($order_query->row['language_id']);

			if ($language_info) {
				$language_code = $language_info['code'];
			} else {
				$language_code = $this->config->get('config_language');
			}

			return array(
				'store_name'    => $order_query->row['store_name'],
				'store_url'     => $order_query->row['store_url'],
				'firstname'     => $order_query->row['firstname'],
				'lastname'      => $order_query->row['lastname'],
				'email'         => $order_query->row['email'],
				'language_code' => $language_code,
			);
	}

	public function deleteOrder($order_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "order_product` WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "order_option` WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "order_voucher` WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "order_history` WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE `or`, ort FROM `" . DB_PREFIX . "order_recurring` `or`, `" . DB_PREFIX . "order_recurring_transaction` `ort` WHERE order_id = '" . (int)$order_id . "' AND ort.order_recurring_id = `or`.order_recurring_id");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "affiliate_transaction` WHERE order_id = '" . (int)$order_id . "'");

		// Delete voucher data as well
		$this->db->query("DELETE FROM `" . DB_PREFIX . "voucher` WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "voucher_history` WHERE order_id = '" . (int)$order_id . "'");
	}
}
