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

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
            $this->model_extension_module_mailing->add($this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

//             echo "<pre>";
//             print_r($this->request->post);
//             echo "<pre>";

            $url = '';

            $this->response->redirect($this->url->link('extension/module/mailing', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm("add");
    }

    public function edit() {
        $this->load->language('extension/module/mailing');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/module/mailing');

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
             $this->model_extension_module_mailing->edit($this->request->get['mailing_id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

//             echo "<pre>";
//             print_r($this->request->post);
//             echo "<pre>";

            $url = '';

            $this->response->redirect($this->url->link('extension/module/mailing', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm("edit");
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

        $data['sort_mname'] = $this->url->link('extension/module/mailing', 'user_token=' . $this->session->data['user_token'] . '&sort=m.name' . $url, true);
        $data['sort_date_added'] = $this->url->link('extension/module/mailing', 'user_token=' . $this->session->data['user_token'] . '&sort=date_added' . $url, true);
        $data['sort_date_start'] = $this->url->link('extension/module/mailing', 'user_token=' . $this->session->data['user_token'] . '&sort=date_start' . $url, true);


        $data['sort_name'] = $this->url->link('extension/module/mailing', 'user_token=' . $this->session->data['user_token'] . '&sort=c.name' . $url, true);
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

        $filter_data = array(
            'sort'              => $sort,
            'order'             => $order,
        );

        $mailings = $this->model_extension_module_mailing->getMailings($filter_data);

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

    protected function getForm($type) {
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

		if (isset($this->error['template_name'])) {
			$data['error_name'] = $this->error['template_name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['letter_theme'])) {
			$data['error_letter_theme'] = $this->error['letter_theme'];
		} else {
			$data['error_letter_theme'] = '';
		}

		if (isset($this->error['letter_text'])) {
			$data['error_letter_text'] = $this->error['letter_text'];
		} else {
			$data['error_letter_text'] = '';
		}


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
            $data['sort_name'] = $this->url->link('extension/module/mailing/' . $type, 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
            $data['sort_email'] = $this->url->link('extension/module/mailing/' . $type, 'user_token=' . $this->session->data['user_token'] . '&sort=c.email' . $url, true);
		} else {
			$data['action'] = $this->url->link('extension/module/mailing/edit', 'user_token=' . $this->session->data['user_token'] . '&mailing_id=' . $this->request->get['mailing_id'] . $url, true);
            $data['sort_name'] = $this->url->link('extension/module/mailing/' . $type, 'user_token=' . $this->session->data['user_token'] . '&sort=name' . '&mailing_id=' . $this->request->get['mailing_id'] . $url, true);
            $data['sort_email'] = $this->url->link('extension/module/mailing/' . $type, 'user_token=' . $this->session->data['user_token'] . '&sort=c.email' . '&mailing_id=' . $this->request->get['mailing_id'] . $url, true);
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

        $data['social_icons'] = $this->model_extension_module_mailing->getSocialIcons();

        foreach ($data['social_icons'] as $k => $icon) {
            foreach ($data['mailing_social_links'] as $link) {
                if($icon['icon_id'] == $link['icon_id']) {
                    $data['social_icons'][$k]['link'] = $link['link'];
                }
            }
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

        $data['categories'] = array();

        $this->load->model('catalog/category');

        $categories = $this->model_catalog_category->getCategories($filter_data);

        foreach ($categories as $category) {
            $data['categories'][] = array(
                'category_id' => $category['category_id'],
                'name'        => $category['name'],
            );
        }

		$this->load->model('design/layout');

		$data['layouts'] = $this->model_design_layout->getLayouts();
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/mailing_form', $data));
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
            $this->load->model('extension/module/mailing');
            $this->model_extension_module_mailing->unsubscribe($this->request->get['customer_id']);
        }
    }

    public function checkMailingDate() {
        $this->load->model('extension/module/mailing');

        $mailings = $this->model_extension_module_mailing->getMailings();

        foreach($mailings as $mailing) {
            if($mailing['date_start'] == date("Y-m-d H:i:s")) {
                $this->startMailing($mailing['mailing_id']);
            }
        }
    }

    public function startMailing($id) {
        if(isset($this->request->get['mailing_id']) || isset($id)) {

            $this->load->model('extension/module/mailing');

            $mailing_id = $this->request->get['mailing_id'] ? $this->request->get['mailing_id'] : $id;

            $mailing = $this->model_extension_module_mailing->getMailing($mailing_id);
            $mailing_description = $this->model_extension_module_mailing->getMailingDescriptions($mailing_id);
            $mailing_products = $this->model_extension_module_mailing->getMailingProducts($mailing_id);
            $mailing_social = $this->model_extension_module_mailing->getMailingSocialLinks($mailing_id);
            $mailing_customers = $this->model_extension_module_mailing->getMailingCustomersId($mailing_id);
            $customers_mails = $this->model_extension_module_mailing->getCustomersMail($mailing_customers);

            $social_icons = $this->model_extension_module_mailing->getSocialIcons();

            foreach ($mailing_social as $k => $link) {
                foreach ($social_icons as $icon) {
                    if($link['icon_id'] == $icon['icon_id']) {
                        $mailing_social[$k]['image'] = $icon['image'];
                    }
                }
            }

            $matches = array();
            // get shortcode from text
            preg_match_all('/\[(.*?)\\]/s', $mailing_description["letter_text"], $matches);

            // shortcodes analysis
            if(isset($matches[1])) {
                foreach ($matches[1] as $match) {
                    $snippet_txt = '['. $match . ']';
                    switch ($match) {
                        case "products":
                            $snippet_output = htmlspecialchars($this->getHtmlProducts($mailing_products));
                            $mailing_description["letter_text"] = str_replace($snippet_txt, $snippet_output , $mailing_description["letter_text"]);
                            break;
                        case "social":
                            $snippet_output = htmlspecialchars($this->getHtmlSocial($mailing_social));
                            $mailing_description["letter_text"] = str_replace($snippet_txt, $snippet_output , $mailing_description["letter_text"]);
                            break;
                        default:
                            break;
                    }
                }
            }


            $count_letters = intval($mailing['counter_letters']);

            for($i = 0; $i < count($customers_mails); $i += $count_letters) {
                for($j = $i; $j < $i + $count_letters; $j++) {
                    if(isset($customers_mails[$j])) {
                        // send mail
                        $this->sendMail($customers_mails[$j], $mailing_description);
                    }
                }
                sleep(1);
            }

        }
    }

    protected function sendMail($to, $message_info) {
        $message = htmlspecialchars_decode(
                '<html>
                        <body>' .
                            $message_info["letter_text"] .
                        '</body>
                </html>'
            );

        // FIXME email from DB
        $headers  = "Content-type: text/html; charset=utf-8 \r\n"; 
        $headers .= "From: От кого письмо <shop@opencart.com>\r\n";
        $headers .= "Reply-To: shop@opencart.com\r\n";

        mail($to, $message_info["letter_theme"], $message, $headers);
    }

    protected function getHtmlProducts($products) {
        $html = '<div style="display: flex;">';
        foreach ($products as $product) {
            $html .= '<div style="max-width: 25%; border: 1px solid #ff7c7c; border-radius: 10px; margin-right: 5px; padding: 10px;"><div class="image">';

            if(isset($product['image'])) {
                $html .= '<img src="/image/'. $product['image'] . '" alt="'. $product['name'] . '" title="'. $product['name'] . '" class="img-responsive" />';
            } else {
                $html .= '<img src="/image/cache/no_image-100x100.png" alt="'. $product['name'] . '" title="'. $product['name'] . '" class="img-responsive" />';
            }

            $html .= '</div>
                    <div>
                        <div class="caption">
                            <p>' . $product['name'] . '</p>
                            <p class="price">
                                ' . $this->currency->format($product['price'], $this->session->data['currency']) . ' 
                            </p>
                        </div>
                    </div>
                </div>';
        }
        $html .= '</div>';
        return $html;
    }

    protected function getHtmlSocial($social_links) {
        $html = '<span>Мы в соц сетях:</span>';

        $html .= '<div style="display:flex;">';

        foreach ($social_links as $social_link) {
            $html .= '<a href="' . $social_link["link"] . '" style="margin-right: 15px;"><img src="' . $social_link['image'] . '" style="width: 25px; height: 25px;" alt="' . $social_link["name"] . '"></a>';
        }
        
        $html .= '</div>';
        return $html;
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
        if (!$this->user->hasPermission('modify', 'extension/module/mailing')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['template_name']) < 1) || (utf8_strlen($this->request->post['template_name']) > 64)) {
            $this->error['template_name'] = $this->language->get('error_template_name');
        }

        if ((utf8_strlen($this->request->post['letter_theme']) < 1) || (utf8_strlen($this->request->post['letter_theme']) > 64)) {
            $this->error['letter_theme'] = $this->language->get('error_letter_theme');
        }

        if (utf8_strlen($this->request->post['letter_text']) < 1) {
            $this->error['letter_text'] = $this->language->get('error_letter_text');
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/mailing')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }
}
