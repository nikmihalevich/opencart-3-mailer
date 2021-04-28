<?php
class ControllerExtensionModuleMailing extends Controller {
    private $error = array();

    public function index() {
        $this->load->model('extension/module/mailing');
        $this->load->language('extension/module/mailing');

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

            $url = '';

            $this->response->redirect($this->url->link('extension/module/mailing', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm("edit");
    }

    public function copyMailing() {
        $this->load->language('extension/module/mailing');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/module/mailing');

        if (isset($this->request->get['mailing_id']) && $this->validateCopy()) {
            $this->model_extension_module_mailing->copyMailing($this->request->get['mailing_id']);
            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';


            $this->response->redirect($this->url->link('extension/module/mailing', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getList();
    }

    public function delete() {
		$this->load->language('extension/module/mailing');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/module/mailing');

		if (isset($this->request->get['mailing_id']) && $this->validateDelete()) {
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

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $mailing_id) {
                $this->model_extension_module_mailing->delete($mailing_id);
            }

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
            $sort = 'mailing_id';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['mailingpage'])) {
            $mailingpage = $this->request->get['mailingpage'];
        } else {
            $mailingpage = 1;
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
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

        $data['add'] = $this->url->link('extension/module/mailing/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('extension/module/mailing/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $filter_data = array(
            'filter_newsletter' => 1,
            'sort'              => $sort,
            'order'             => $order,
            'start'             => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit'             => $this->config->get('config_limit_admin')
        );

        $this->load->model('customer/customer');

        $customer_total = $this->model_customer_customer->getTotalCustomers($filter_data);

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

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $data['user_token'] = $this->session->data['user_token'];

        $pagination = new Pagination();
        $pagination->total = $customer_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('extension/module/mailing', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($customer_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($customer_total - $this->config->get('config_limit_admin'))) ? $customer_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $customer_total, ceil($customer_total / $this->config->get('config_limit_admin')));

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
			$blocks_info  = $this->model_extension_module_mailing->getBlocks($this->request->get['mailing_id']);

            $this->load->model('catalog/product');
            $this->load->model('tool/image');
            foreach ($blocks_info as $k => $block) {
                $blocks_info[$k]['background_image'] = ($this->config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG) . "image/" . $block['bg_image'];
                $block_data_info = $this->model_extension_module_mailing->getBlockData($block['id']);
                foreach ($block_data_info as $kk => $block_data) {
                    if ($block_data['bg_image']) {
                        $block_data_info[$kk]['thumb'] = $this->model_tool_image->resize($block_data['bg_image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'));
                    } else {
                        $block_data_info[$kk]['thumb'] = '';
                    }
                    $results = array();
                    foreach ($block_data['products'] as $product) {
                        $results[] = $this->model_catalog_product->getProduct($product['product_id']);
                    }
                    foreach ($results as $kkk => $result) {
                        $results[$kkk]['price'] = $this->currency->format($result['price'], $this->config->get('config_currency'));
                        if ($results[$kkk]['image']) {
                            $results[$kkk]['thumb'] = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'));
                        } else {
                            $results[$kkk]['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);;
                        }
                    }
                    $block_data_info[$kk]['products'] = $results;
                }

                $blocks_info[$k]['blocks_data'] = $block_data_info;
			}

			$data['blocks'] = $blocks_info;
		}

        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->request->get['mailing_id'])) {
            $data['mailing'] = $this->model_extension_module_mailing->getMailing($this->request->get['mailing_id']);
		} else {
			$data['mailing'] = array();
        }

        if(isset($data['mailing']['date_start']) && $data['mailing']['date_start'] == '-0001-11-30T00:00') {
            $data['mailing']['date_start'] = '';
        }

		if (isset($this->request->get['mailing_id'])) {
			$data['mailing_description'] = $this->model_extension_module_mailing->getMailingDescriptions($this->request->get['mailing_id']);
		} else {
			$data['mailing_description'] = array();
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

        $data['categories'] = array();

        $this->load->model('catalog/category');

        $categories = $this->model_catalog_category->getCategories($filter_data);

        foreach ($categories as $category) {
            $data['categories'][] = array(
                'category_id' => $category['category_id'],
                'name'        => $category['name'],
            );
        }

        if (isset($this->request->post['template_name'])) {
            $data['mailing']['name'] = $this->request->post['template_name'];
        }

        if (isset($this->request->post['letter_theme'])) {
            $data['mailing_description']['letter_theme'] = $this->request->post['letter_theme'];
        }

        if (isset($this->request->post['letter_text'])) {
            $data['mailing_description']['letter_text'] = $this->request->post['letter_text'];
        }

        $this->load->model('tool/image');
        $data['img_placeholder'] = $this->model_tool_image->resize('no_image.png', 40, 40);

		$this->load->model('design/layout');

		$data['layouts'] = $this->model_design_layout->getLayouts();
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/mailing_form', $data));
    }

    public function addBlock() {
        if(isset($this->request->get['mailing_id']) && isset($this->request->get['grid_id']) && $this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model('extension/module/mailing');
            $block_id = $this->model_extension_module_mailing->addBlock($this->request->get['mailing_id'], $this->request->get['grid_id']);
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($block_id));
        }
    }

    public function editBlock() {
        if(isset($this->request->get['block_id']) && $this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model('extension/module/mailing');
            $formData = $_POST['block'];

            $this->model_extension_module_mailing->editBlock($this->request->get['block_id'], $formData);
        }
    }

    public function deleteBlock() {
        if(isset($this->request->get['block_id']) && $this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model('extension/module/mailing');

            $this->model_extension_module_mailing->deleteBlock($this->request->get['block_id']);
        }
    }

    public function getBlock() {
        if(isset($this->request->get['block_id']) && $this->request->server['REQUEST_METHOD'] == 'GET') {
            $this->load->model('extension/module/mailing');

            $block = $this->model_extension_module_mailing->getBlock($this->request->get['block_id']);

            $block['background_image'] = ($this->config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG) . "image/" . $block['bg_image'];

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($block));
        }
    }

    public function addBlockData() {
        if(isset($this->request->get['block_id']) && isset($this->request->get['col_id']) && $this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model('extension/module/mailing');
            // end this
            $formData = $_POST;
            $formData['block_id'] = $this->request->get['block_id'];
            $formData['col_id'] = $this->request->get['col_id'];
            $block = $this->model_extension_module_mailing->getBlock($this->request->get['block_id']);
            $block_grid_width = '';
            switch ($block['grid_id']) {
                case '1':
                    $block_grid_width = 'w-100';
                    break;
                case '2':
                    $block_grid_width = 'w-50';
                    break;
                case '3':
                    $block_grid_width = 'w-33';
                    break;
                case '4':
                    $block_grid_width = 'w-25';
                    break;
                case '5':
                    if($formData['col_id'] == '1') {
                        $block_grid_width = 'w-33';
                    } else {
                        $block_grid_width = 'w-66';
                    }
                    break;
                case '6':
                    if($formData['col_id'] == '1') {
                        $block_grid_width = 'w-66';
                    } else {
                        $block_grid_width = 'w-33';
                    }
                    break;
                case '7':
                    if($formData['col_id'] == '3') {
                        $block_grid_width = 'w-50';
                    } else {
                        $block_grid_width = 'w-25';
                    }
                    break;
                case '8':
                    if($formData['col_id'] == '1') {
                        $block_grid_width = 'w-50';
                    } else {
                        $block_grid_width = 'w-25';
                    }
                    break;
                case '9':
                    if($formData['col_id'] == '2') {
                        $block_grid_width = 'w-50';
                    } else {
                        $block_grid_width = 'w-25';
                    }
                    break;
                case '10':
                    $block_grid_width = 'w-20';
                    break;
                case '11':
                    $block_grid_width = 'w-16';
                    break;
                case '12':
                    if($formData['col_id'] == '2') {
                        $block_grid_width = 'w-66';
                    } else {
                        $block_grid_width = 'w-16';
                    }
                    break;

                default:
                    break;
            }
            $formData['block_grid_width'] = $block_grid_width;
            $block_data_id = $this->model_extension_module_mailing->addBlockData($formData);
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($block_data_id));
        }
    }

    public function editBlockData() {
        if(isset($this->request->get['block_data_id']) && $this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model('extension/module/mailing');
            $formData = $_POST;
            $formData['block_data_id'] = $this->request->get['block_data_id'];
            $this->model_extension_module_mailing->editBlockData($formData);
        }
    }

    public function deleteBlockData() {
        if(isset($this->request->get['block_data_id']) && $this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model('extension/module/mailing');

            $this->model_extension_module_mailing->deleteBlockData($this->request->get['block_data_id']);
        }
    }

    public function getBlockData() {
        if(isset($this->request->get['block_data_id']) && $this->request->server['REQUEST_METHOD'] == 'GET') {
            $this->load->model('extension/module/mailing');

            $block_data = $this->model_extension_module_mailing->getBlockDataByBlockDataId($this->request->get['block_data_id']);

            $block_data['background_image'] = ($this->config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG) . "image/" . $block_data['bg_image'];

            if (isset($block_data['products'])) {
                $results = array();
                $this->load->model('catalog/product');
                foreach ($block_data['products'] as $product) {
                    $results[] = $this->model_catalog_product->getProduct($product['product_id']);
                }

                $json = array();
                $this->load->model('tool/image');

                foreach ($results as $result) {
                    $result['price'] = $this->currency->format($result['price'], $this->config->get('config_currency'));
                    if ($result['image']) {
                        $result['thumb'] = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'));
                    } else {
                        $result['thumb'] = '';
                    }
                    $json[] = $result;
                }

                $block_data['products'] = $json;
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($block_data));
        }
    }

    public function getProducts() {
        if(isset($this->request->get['category_id'])) {
            $this->load->model('catalog/product');
            $this->load->model('tool/image');

            $results = $this->model_catalog_product->getProductsByCategoryId($this->request->get['category_id']);

            $json = array();

            foreach ($results as $result) {
                $result['price'] = $this->currency->format($result['price'], $this->config->get('config_currency'));
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

    public function unsubscribeFromMailing() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model('extension/module/mailing');
            $this->model_extension_module_mailing->unsubscribeFromMailing($this->request->get['mailing_id'], $this->request->get['customer_id']);
        }
    }

    public function subscribeToMailing() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model('extension/module/mailing');
            $this->model_extension_module_mailing->subscribeToMailing($this->request->get['mailing_id'], $this->request->get['customer_id']);
        }
    }

    public function unsubscribeAllFromMailing() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model('extension/module/mailing');
            $this->model_extension_module_mailing->unsubscribeAllFromMailing($this->request->get['mailing_id']);
        }
    }

    public function subscribeAllToMailing() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model('extension/module/mailing');
            $this->model_extension_module_mailing->subcribeAllToMailing($this->request->get['mailing_id']);
        }
    }

    public function mailingCustomersId() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model('extension/module/mailing');
            $mailing_customers = $this->model_extension_module_mailing->getMailingCustomersId($this->request->get['mailing_id']);

            $this->response->addHeader('Content-Type: application/json');
		    $this->response->setOutput(json_encode($mailing_customers));
        }
    }

    public function subscribedCustomersId() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $filter_data = array(
                'filter_newsletter' => 1,
            );

            $this->load->model('customer/customer');

            $newsletter_subs_id = array();
            $newsletter_subs = $this->model_customer_customer->getCustomers($filter_data);

            foreach ($newsletter_subs as $key => $newsletter_sub) {
                $newsletter_subs_id[$key] = $newsletter_sub['customer_id'];
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($newsletter_subs_id));
        }
    }

    public function checkMailingDate() {
        $this->load->model('extension/module/mailing');

        $mailings = $this->model_extension_module_mailing->getMailings();

        date_default_timezone_set('Europe/Moscow');

        foreach($mailings as $mailing) {
            if(substr($mailing['date_start'], 0, -3) == date("Y-m-d H:i")) {
                $this->mailing($mailing['mailing_id']);
            }
        }
    }

    public function startMailing() {
        if(isset($this->request->get['mailing_id'])) {
            $this->load->model('extension/module/mailing');

            $mailing_customers = $this->model_extension_module_mailing->getMailingCustomersId($this->request->get['mailing_id']);

            if(!empty($mailing_customers)) {
                $this->mailing($this->request->get['mailing_id']);
            } else {
                $this->response->setOutput("Для начала рассылки добавьте в нее пользователей!");
            }
        }
    }

    protected function mailing($id) {
        if(isset($id)) {
            $this->load->model('extension/module/mailing');

            $mailing_id = $id;

            $mailing = $this->model_extension_module_mailing->getMailing($mailing_id);
            $mailing_description = $this->model_extension_module_mailing->getMailingDescriptions($mailing_id);
            $mailing_customers = $this->model_extension_module_mailing->getMailingCustomersId($mailing_id);
            $customers_mails = $this->model_extension_module_mailing->getCustomersMail($mailing_customers);

            $blocks_info  = $this->model_extension_module_mailing->getBlocks($mailing_id);

            $this->load->model('catalog/product');
            $this->load->model('tool/image');
            foreach ($blocks_info as $k => $block) {
                $blocks_info[$k]['background_image'] = ($this->config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG) . "image/" . $block['bg_image'];
                $block_data_info = $this->model_extension_module_mailing->getBlockData($block['id']);
                foreach ($block_data_info as $kk => $block_data) {
                    if ($block_data['bg_image']) {
                        $block_data_info[$kk]['thumb'] = $this->model_tool_image->resize($block_data['bg_image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'));
                    } else {
                        $block_data_info[$kk]['thumb'] = '';
                    }
                    $results = array();
                    foreach ($block_data['products'] as $product) {
                        $results[] = $this->model_catalog_product->getProduct($product['product_id']);
                    }
                    foreach ($results as $kkk => $result) {
                        $results[$kkk]['price'] = $this->currency->format($result['price'], $this->config->get('config_currency'));
                        $link = $this->url->link('product/product', 'product_id=' . $results[$kkk]['product_id']);
                        $link_arr = explode('/', $link);
                        if(($key = array_search('admin', $link_arr)) !== false){
                            unset($link_arr[$key]);
                        }
                        $results[$kkk]['link'] = implode('/', $link_arr);
                        if ($results[$kkk]['image']) {
                            $results[$kkk]['thumb'] = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'));
                        } else {
                            $results[$kkk]['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
                        }
                    }
                    $block_data_info[$kk]['products'] = $results;
                }

                $blocks_info[$k]['blocks_data'] = $block_data_info;
            }

            $mailing_blocks['blocks'] = $blocks_info;
            $mailing_blocks['title'] = $mailing_description['letter_theme'];

            $count_letters = intval($mailing['counter_letters']);

            for($i = 0; $i < count($customers_mails); $i += $count_letters) {
                for($j = $i; $j < $i + $count_letters; $j++) {
                    if(isset($customers_mails[$j])) {
                        // send mail
                        $this->sendMail($customers_mails[$j], $mailing_description, $mailing_blocks);
                    }
                }
                sleep(1);
            }
        }
    }

    protected function sendMail($to, $message_info, $data) {
        $from = $this->config->get('config_email');

        $mail = new Mail($this->config->get('config_mail_engine'));
        $mail->parameter = $this->config->get('config_mail_parameter');
        $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
        $mail->smtp_username = $this->config->get('config_mail_smtp_username');
        $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
        $mail->smtp_port = $this->config->get('config_mail_smtp_port');
        $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

        $mail->setTo($to);
        $mail->setFrom($from);
        $mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
        $mail->setSubject(html_entity_decode(sprintf($message_info["letter_theme"]), ENT_QUOTES, 'UTF-8'));
        $mail->setHtml($this->load->view('mail/mailing_template', $data));
        $mail->send();
    }

    public function autocompleteCustomers() {
        $json = array();

        if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_email'])) {
            if (isset($this->request->get['filter_name'])) {
                $filter_name = $this->request->get['filter_name'];
            } else {
                $filter_name = '';
            }

            if (isset($this->request->get['filter_email'])) {
                $filter_email = $this->request->get['filter_email'];
            } else {
                $filter_email = '';
            }

            if (isset($this->request->get['filter_affiliate'])) {
                $filter_affiliate = $this->request->get['filter_affiliate'];
            } else {
                $filter_affiliate = '';
            }

            $this->load->model('customer/customer');

            $filter_data = array(
                'filter_name'      => $filter_name,
                'filter_email'     => $filter_email,
                'filter_affiliate' => $filter_affiliate,
                'filter_newsletter'=> 1,
                'start'            => 0,
                'limit'            => 5
            );

            $results = $this->model_customer_customer->getCustomers($filter_data);

            foreach ($results as $result) {
                $json[] = array(
                    'customer_id'       => $result['customer_id'],
                    'customer_group_id' => $result['customer_group_id'],
                    'name'              => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
                    'customer_group'    => $result['customer_group'],
                    'firstname'         => $result['firstname'],
                    'lastname'          => $result['lastname'],
                    'email'             => $result['email'],
                    'telephone'         => $result['telephone'],
                    'custom_field'      => json_decode($result['custom_field'], true),
                    'address'           => $this->model_customer_customer->getAddresses($result['customer_id'])
                );
            }
        }

        $sort_order = array();

        foreach ($json as $key => $value) {
            $sort_order[$key] = $value['name'];
        }

        array_multisort($sort_order, SORT_ASC, $json);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function autocompleteMailings() {
        $json = array();

        if (isset($this->request->get['filter_mailing_name'])) {
            if (isset($this->request->get['filter_mailing_name'])) {
                $filter_name = $this->request->get['filter_mailing_name'];
            } else {
                $filter_name = '';
            }

            $this->load->model('extension/module/mailing');

            $filter_data = array(
                'filter_name'      => $filter_name,
                'start'            => 0,
                'limit'            => 5
            );

            $results = $this->model_extension_module_mailing->getMailingsAutocomplete($filter_data);

            foreach ($results as $result) {
                $json[] = array(
                    'mailing_id'       => $result['mailing_id'],
                    'name'             => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
                    'date_start'       => $result['date_start'],
                    'date_added'       => $result['date_added'],
                );
            }
        }

        $sort_order = array();

        foreach ($json as $key => $value) {
            $sort_order[$key] = $value['name'];
        }

        array_multisort($sort_order, SORT_ASC, $json);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
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

    public function configure() {
        $this->load->language('extension/extension/module');

        if (!$this->user->hasPermission('modify', 'extension/extension/module')) {
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true));
        } else {
            $this->load->model('user/user_group');

            $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/pp_button');
            $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/pp_button');

            $this->install();

            $this->response->redirect($this->url->link('design/layout', 'user_token=' . $this->session->data['user_token'], true));
        }
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'extension/module/mailing')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
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

    protected function validateCopy() {
        if (!$this->user->hasPermission('modify', 'extension/module/mailing')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}
