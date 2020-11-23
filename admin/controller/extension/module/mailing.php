<?php
class ControllerExtensionModuleMailing extends Controller {
    private $error = array();

    public function index() {
        $this->load->model('setting/setting');

        $this->load->model('extension/module/mailing');
        $this->load->language('extension/module/mailing');

        $this->load->model('customer/customer');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->getList();
    }

    public function add() {
        $this->load->language('extension/module/mailing');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/module/mailing');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') /*&& $this->validateForm()*/) {
            $this->model_extension_module_mailing->add($this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

//             echo "<pre>";
//             print_r($this->request->post);
//             echo "<pre>";

            $url = '';

            $this->response->redirect($this->url->link('extension/module/mailing', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm();
    }

    public function edit() {
        $this->load->language('extension/module/mailing');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/module/mailing');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') /*&& $this->validateForm()*/) {
             $this->model_extension_module_mailing->edit($this->request->get['mailing_id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            // echo "<pre>";
            // print_r($this->request->post);
            // echo "<pre>";

            $url = '';

            $this->response->redirect($this->url->link('extension/module/mailing', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm();
    }

    public function delete() {
		$this->load->language('extension/module/mailing');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/module/mailing');

		if (isset($this->request->get['mailing_id'])) {
			$this->model_extension_module_mailing->delete($this->request->get['mailing_id']);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			$this->response->redirect($this->url->link('extension/module/mailing', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

    protected function getList() {
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'name';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        $url = '';

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        $data['sort_name'] = $this->url->link('extension/module/mailing', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);

        $data['sort_email'] = $this->url->link('extension/module/mailing', 'user_token=' . $this->session->data['user_token'] . '&sort=c.email' . $url, true);
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/mailing', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );

        $data['add'] = $this->url->link('extension/module/mailing/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['copy'] = $this->url->link('extension/module/mailing/copy', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('extension/module/mailing/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $filter_data = array(
            'filter_newsletter' => 1,
            'sort'              => $sort,
            'order'             => $order,
        );

        $this->load->model('customer/customer');

        $newsletter_subs = $this->model_customer_customer->getCustomers($filter_data);

        foreach ($newsletter_subs as $newsletter_sub) {
            $data['customers'][] = array(
                'customer_id' => $newsletter_sub['customer_id'],
                'email'       => $newsletter_sub['email'],
                'name'        => $newsletter_sub['name'],
            );
        }

        $mailings = $this->model_extension_module_mailing->getMailings();

        foreach ($mailings as $mailing) {
            $data['mailings'][] = array(
                'mailing_id' => $mailing['mailing_id'],
                'name'       => $mailing['name'],
                'date_start' => $mailing['date_start'],
                'date_added' => $mailing['date_added'],
            );
        }

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

        $data['user_token'] = $this->session->data['user_token'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/mailing_list', $data));
    }

    protected function getForm() {
		$data['text_form'] = !isset($this->request->get['product_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'name';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        $url = '';

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = array();
		}

		if (isset($this->error['meta_title'])) {
			$data['error_meta_title'] = $this->error['meta_title'];
		} else {
			$data['error_meta_title'] = array();
		}

		if (isset($this->error['model'])) {
			$data['error_model'] = $this->error['model'];
		} else {
			$data['error_model'] = '';
		}

		if (isset($this->error['keyword'])) {
			$data['error_keyword'] = $this->error['keyword'];
		} else {
			$data['error_keyword'] = '';
		}

		$url = '';

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/mailing', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['mailing_id'])) {
			$data['action'] = $this->url->link('extension/module/mailing/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('extension/module/mailing/edit', 'user_token=' . $this->session->data['user_token'] . '&mailing_id=' . $this->request->get['mailing_id'] . $url, true);
		}

        $data['cancel'] = $this->url->link('extension/module/mailing', 'user_token=' . $this->session->data['user_token'] . $url, true);
        
        if (isset($this->request->get['mailing_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$mailing_info = $this->model_extension_module_mailing->getMailing($this->request->get['mailing_id']);
		}

        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->request->post['mailing'])) {
			$data['mailing'] = $this->request->post['mailing'];
		} elseif (isset($this->request->get['mailing_id'])) {
            $data['mailing'] = $this->model_extension_module_mailing->getMailing($this->request->get['mailing_id']);
		} else {
			$data['mailing'] = array();
        }

		if (isset($this->request->post['mailing_description'])) {
			$data['mailing_description'] = $this->request->post['mailing_description'];
		} elseif (isset($this->request->get['mailing_id'])) {
			$data['mailing_description'] = $this->model_extension_module_mailing->getMailingDescriptions($this->request->get['mailing_id']);
		} else {
			$data['mailing_description'] = array();
        }

        if (isset($this->request->post['mailing_products'])) {
            $data['mailing_products'] = $this->request->post['mailing_products'];
        } elseif (isset($this->request->get['mailing_id'])) {
            $data['mailing_products'] = $this->model_extension_module_mailing->getMailingProducts($this->request->get['mailing_id']);
        } else {
            $data['mailing_products'] = array();
        }

        if (isset($this->request->post['mailing_social_links'])) {
            $data['mailing_social_links'] = $this->request->post['mailing_social_links'];
        } elseif (isset($this->request->get['mailing_id'])) {
            $data['mailing_social_links'] = $this->model_extension_module_mailing->getMailingSocialLinks($this->request->get['mailing_id']);
        } else {
            $data['mailing_social_links'] = array();
        }

        if (isset($this->request->post['mailing_customers'])) {
            $data['mailing_customers'] = $this->request->post['mailing_customers'];
        } elseif (isset($this->request->get['mailing_id'])) {
            $data['mailing_customers'] = $this->model_extension_module_mailing->getMailingCustomersId($this->request->get['mailing_id']);
        } else {
            $data['mailing_customers'] = array();
        }

        $filter_data = array(
            'filter_newsletter' => 1,
            'sort'              => $sort,
            'order'             => $order,
        );

        $this->load->model('customer/customer');

        $newsletter_subs = $this->model_customer_customer->getCustomers($filter_data);

        foreach ($newsletter_subs as $newsletter_sub) {
            $data['customers'][] = array(
                'customer_id' => $newsletter_sub['customer_id'],
                'email'       => $newsletter_sub['email'],
                'name'        => $newsletter_sub['name'],
            );
        }

        $data['sort_name'] = $this->url->link('extension/module/mailing', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);

        $data['sort_email'] = $this->url->link('extension/module/mailing', 'user_token=' . $this->session->data['user_token'] . '&sort=c.email' . $url, true);

        $data['categories'] = array();

        $this->load->model('catalog/category');

        $categories = $this->model_catalog_category->getCategories($filter_data);

        foreach ($categories as $category) {
            $data['categories'][] = array(
                'category_id' => $category['category_id'],
                'name'        => $category['name'],
            );
        }

		// TODO
		if (isset($this->request->post['model'])) {
			$data['model'] = $this->request->post['model'];
		} elseif (!empty($product_info)) {
			$data['model'] = $product_info['model'];
		} else {
			$data['model'] = '';
		}

		if (isset($this->request->post['customers'])) {
			$data['customers'] = $this->request->post['customers'];
		} elseif (!empty($product_info)) {
			$data['sku'] = $product_info['sku'];
		} else {
			$data['sku'] = '';
		}

		$this->load->model('design/layout');

		$data['layouts'] = $this->model_design_layout->getLayouts();
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/mailing_form', $data));
    }

    public function addProduct() {
        if(isset($this->request->get['product_id']) && isset($this->request->get['mailing_id']) ) {
            $this->load->model('extension/module/mailing');
            $this->model_extension_module_mailing->addProductToMailing($this->request->get['product_id'], $this->request->get['mailing_id']);
            $this->response->setOutput(123);
        }
    }

    public function getProducts() {
        if(isset($this->request->get['category_id'])) {
            $this->load->model('catalog/product');
            $this->load->model('tool/image');

            $results = $this->model_catalog_product->getProductsByCategoryId($this->request->get['category_id']);

            $json = array();

            foreach ($results as $result) {
                if ($result['image']) {
                    $result['thumb'] = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'));
                } else {
                    $result['thumb'] = '';
                }
                $json[] = $result;
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function unsubcribe() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            var_dump($this->request->get['customer_id']);
            $this->load->model('extension/module/mailing');
            $this->model_extension_module_mailing->unsubscribe($this->request->get['customer_id']);
        }
    }

    public function startMailing() {
        if(isset($this->request->get['mailing_id'])) {
            // TODO
            $this->load->model('extension/module/mailing');

            $mailing = $this->model_extension_module_mailing->getMailing($this->request->get['mailing_id']);;
            $mailing_description = $this->model_extension_module_mailing->getMailingDescriptions($this->request->get['mailing_id']);
            $mailing_products = $this->model_extension_module_mailing->getMailingProducts($this->request->get['mailing_id']);
            $mailing_social = $this->model_extension_module_mailing->getMailingSocialLinks($this->request->get['mailing_id']);
            $mailing_customers = $this->model_extension_module_mailing->getMailingCustomersId($this->request->get['mailing_id']); // FIXME
            $customers_mails = $this->model_extension_module_mailing->getCustomersMail($mailing_customers);

            $count_letters = intval($mailing['counter_letters']);

            for($i = 0; $i < count($customers_mails); $i += $count_letters) {
                $to = "";
                for($j = $i; $j < $i + $count_letters; $j++) {
                    if(isset($customers_mails[$j])) {
                        $to .= "<". $customers_mails[$j] .">, ";
                    }
                }

                // send mail
                $this->sendMail($to, $mailing_description);
                
                sleep(1);
            }
        }
    }

    protected function sendMail($too, $message_info) {
        $to = substr($too, 0, strlen($too)-2);

        $subject = "Заголовок письма"; 

        $message = htmlspecialchars_decode(
                    '<html>
                        <style>
                            .fa-facebook:before {
                                content: "\f09a";
                                font-size: 15px;
                            }                  
                        </style>
                        <body>
                        <i class="fab fa-facebook"></i>
                        ' .
                            $message_info["letter_text"] .
                        '</body>
                    </html>'
                );
        
        // FIXME email from DB
        $headers  = "Content-type: text/html; charset=utf-8 \r\n"; 
        $headers .= "From: От кого письмо <cheburekk570@gmail.com>\r\n"; 
        $headers .= "Reply-To: cheburekk570@gmail.com\r\n"; 

        mail($to, $message_info["letter_theme"], $message, $headers); 
    }

    public function install() {
        if ($this->user->hasPermission('modify', 'extension/module/mailing')) {
            $this->load->model('extension/module/mailing');

            $this->model_extension_module_mailing->install();
        }
    }

    public function uninstall() {
        if ($this->user->hasPermission('modify', 'extension/module/mailing')) {
            $this->load->model('extension/module/mailing');

            $this->model_extension_module_mailing->uninstall();
        }
    }

    protected function validateForm() {
        if (!$this->user->hasPermission('modify', 'extension/module')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

//        foreach ($this->request->post['product_description'] as $language_id => $value) {
//            if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 255)) {
//                $this->error['name'][$language_id] = $this->language->get('error_name');
//            }
//
//            if ((utf8_strlen($value['meta_title']) < 1) || (utf8_strlen($value['meta_title']) > 255)) {
//                $this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
//            }
//        }
//
//        if ((utf8_strlen($this->request->post['model']) < 1) || (utf8_strlen($this->request->post['model']) > 64)) {
//            $this->error['model'] = $this->language->get('error_model');
//        }
//
//        if ($this->request->post['product_seo_url']) {
//            $this->load->model('design/seo_url');
//
//            foreach ($this->request->post['product_seo_url'] as $store_id => $language) {
//                foreach ($language as $language_id => $keyword) {
//                    if (!empty($keyword)) {
//                        if (count(array_keys($language, $keyword)) > 1) {
//                            $this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
//                        }
//
//                        $seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);
//
//                        foreach ($seo_urls as $seo_url) {
//                            if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['product_id']) || (($seo_url['query'] != 'product_id=' . $this->request->get['product_id'])))) {
//                                $this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');
//
//                                break;
//                            }
//                        }
//                    }
//                }
//            }
//        }
//
//        if ($this->error && !isset($this->error['warning'])) {
//            $this->error['warning'] = $this->language->get('error_warning');
//        }

        return !$this->error;
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/payment/stripe')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }
}
