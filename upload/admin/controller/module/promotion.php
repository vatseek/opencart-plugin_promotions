<?php
/**
 * ControllerModulePromotion
 *
 * Manage promotions in backoffice
 */
class ControllerModulePromotion extends Controller {
    /**
     * @var array errors container
     */
    private $error = array();

    /**
     * @var array breadcrumbs list to index page
     */
    private $indexBreadcrumbs = array (
        'text_home' => 'common/home',
        'text_module' => 'extension/module',
        'heading_title' => 'module/promotion',
    );

    /**
     * @var array breadcrumbs list to slideshow page
     */
    private $slideshowBreadcrumbs = array (
        'text_home' => 'common/home',
        'text_module' => 'extension/module',
        'heading_title' => 'module/promotion',
        'heading_slideshow' => 'module/promotion/slideshow',
    );

    /**
     * @param $breadcrumbsList list of breadcrumbs
     * @return array processed array with links, names, etc..
     */
    protected function createBreadcrumbsArray( $breadcrumbsList )
    {
        $result = array();
        $first = true;
        foreach ($breadcrumbsList as $breadcrumbText => $breadcrumbLink) {
            $separator = ':: ';

            if ($first) {
                $first = false;
                $separator = false;
            }

            $result[] = array(
                'text'      => $this->language->get($breadcrumbText),
                'href'      => $this->url->link($breadcrumbLink, 'token=' . $this->session->data['token'], 'SSL'),
                'separator' => $separator
            );
        }

        return $result;
    }

    /**
     * index page controller
     */
    public function index()
    {
		$this->load->language('module/promotion');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('promotion', $this->request->post);
					
			$this->session->data['success'] = $this->language->get('text_success');
						
			$this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
		}
				
		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_content_top'] = $this->language->get('text_content_top');
		$this->data['text_content_bottom'] = $this->language->get('text_content_bottom');		
		$this->data['text_column_left'] = $this->language->get('text_column_left');
		$this->data['text_column_right'] = $this->language->get('text_column_right');

        $this->data['column_image'] = $this->language->get('column_image');
        $this->data['column_title'] = $this->language->get('column_title');
        $this->data['column_status'] = $this->language->get('column_status');
        $this->data['column_order'] = $this->language->get('column_order');
        $this->data['column_actions'] = $this->language->get('column_actions');

		$this->data['entry_banner'] = $this->language->get('entry_banner');
		$this->data['entry_dimension'] = $this->language->get('entry_dimension'); 
		$this->data['entry_layout'] = $this->language->get('entry_layout');
		$this->data['entry_position'] = $this->language->get('entry_position');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		
 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
				
  		$this->data['breadcrumbs'] = $this->createBreadcrumbsArray($this->indexBreadcrumbs);


        $this->load->model('module/promotion');

        $promotions = $this->model_module_promotion->getPromotions();
        $this->data['promotions'] = array();

        foreach ($promotions as $promotion) {
            $promotion['action_url'] = $this->url->link('module/promotion/update', '&promotion_id=' . $promotion['promotion_id'] . '&token=' . $this->session->data['token']);
            $this->data['promotions'][] = $promotion;
        }

        $this->data['button_insert'] = $this->language->get('button_insert');
        $this->data['button_delete'] = $this->language->get('button_delete');
        $this->data['button_edit'] = $this->language->get('button_edit');
        $this->data['button_slideshow'] = $this->language->get('button_slideshow');

        $this->data['entity_active'] = $this->language->get('entity_active');
        $this->data['entity_inactive'] = $this->language->get('entity_inactive');

        $this->data['insert'] = $this->url->link('module/promotion/update', '&token=' . $this->session->data['token']);
        $this->data['delete'] = $this->url->link('module/promotion/delete', '&token=' . $this->session->data['token']);
        $this->data['slideshow'] = $this->url->link('module/promotion/slideshow', '&token=' . $this->session->data['token']);

		$this->template = 'module/promotion.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

        // set error success messages
        $this->data['error'] = $this->error;
        $this->data['success'] = '';
        if (isset($this->session->data['success']) && $this->session->data['success'] ) {
            $this->data['success'] = $this->session->data['success'];
            $this->session->data['success'] = '';
        }
				
		$this->response->setOutput($this->render());
	}

    /**
     * delete controller page
     */
    public function delete()
    {
        if (!$this->validate()) {
            $this->redirect($this->url->link('module/promotion', 'token=' . $this->session->data['token'], 'SSL'));
        }

        if ( isset($this->request->post['selected'])) {
            $selectedPromotions = array();
            foreach ($this->request->post['selected'] as $selectedPromotionItem ){
                $selectedPromotions[] = (int)$selectedPromotionItem;
            }

            if (sizeof($selectedPromotions) > 0) {
                $this->load->model('module/promotion');
                $this->model_module_promotion->deletePromotions($selectedPromotions);
            }

            $this->session->data['success'] = 'Delete successful';
            $this->redirect($this->url->link('module/promotion', 'token=' . $this->session->data['token'], 'SSL'));
        }
    }

    /**
     * validate of user permissions
     *
     * @return bool is_valid
     */
    private function validate() {
		if (!$this->user->hasPermission('modify', 'module/promotion')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}

    /**
     * @param string $image image name
     * @return string image link
     */
    protected function setImageLink($image)
    {
        if ($image) {
            return $this->model_tool_image->resize($image, 100, 100);
        }
        else
        {
            return $this->model_tool_image->resize('no_image.jpg', 100, 100);
        }
    }

    /**
     * save promotion page
     */
    public function save()
    {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

            $this->load->model('module/promotion');
            if ( $this->model_module_promotion->savePromotion($this->request->post)) {
                $this->session->data['success'] = 'Saved successful';
                $this->redirect($this->url->link('module/promotion', 'token=' . $this->session->data['token'], 'SSL'));
            }
        }
        else {
            $this->redirect($this->url->link('module/promotion', 'token=' . $this->session->data['token'], 'SSL'));
        }
    }

    /**
     * update promotion page
     */
    public function update()
    {
        $this->load->language('module/promotion');

        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        $this->load->model('tool/image');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('banner', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
        }

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        $this->data['breadcrumbs'] = $this->createBreadcrumbsArray($this->indexBreadcrumbs);


        $this->load->model('module/promotion');

        $promotions = $this->model_module_promotion->getPromotions();
        foreach ($promotions as $promotion) {
            $promotion['action_url'] = $this->url->link('module/promotion/update', '&promotion_id=' . $promotion['promotion_id'] . '&token=' . $this->session->data['token']);
            $this->data['promotions'][] = $promotion;
        }

        $this->data['button_insert'] = $this->language->get('button_insert');
        $this->data['button_delete'] = $this->language->get('button_delete');
        $this->data['button_edit'] = $this->language->get('button_edit');
        $this->data['button_save'] = $this->language->get('button_save');
        $this->data['button_cancel'] = $this->language->get('button_cancel');

        $this->data['tab_general'] = $this->language->get('tab_general');
        $this->data['tab_data'] = $this->language->get('tab_data');

        $this->data['entity_active'] = $this->language->get('entity_active');
        $this->data['entity_inactive'] = $this->language->get('entity_inactive');
        $this->data['entry_name'] = $this->language->get('entry_name');
        $this->data['entry_description'] = $this->language->get('entry_description');
        $this->data['entry_meta_description'] = $this->language->get('entry_meta_description');
        $this->data['entry_image'] = $this->language->get('entry_image');

        $this->data['column_image'] = $this->language->get('column_image');
        $this->data['column_title'] = $this->language->get('column_title');
        $this->data['column_status'] = $this->language->get('column_status');
        $this->data['column_order'] = $this->language->get('column_order');
        $this->data['column_position'] = $this->language->get('column_position');
        $this->data['column_actions'] = $this->language->get('column_actions');

        $this->data['text_browse'] = $this->language->get('text_browse');
        $this->data['text_clear'] = $this->language->get('text_clear');
        $this->data['text_image_manager'] = $this->language->get('text_image_manager');

        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->request->get['promotion_id'])) {
            $promotionId = $this->request->get['promotion_id'];
            $this->data['promotion'] = $this->model_module_promotion->getPromotionById($promotionId);
            $this->data['promotion_description'] = $this->model_module_promotion->getPromotionDescriptions($promotionId);
        }
        else {
            $this->data['promotion'] = $this->model_module_promotion->getTableDBFields('promotion');
            $descriptionFields = $this->model_module_promotion->getTableDBFields('promotion_description');

            foreach ($this->data['languages'] as $language) {
                $this->data['promotion_description'][$language['language_id']] = $descriptionFields;
            }
        }

        foreach($this->data['promotion_description'] as $promotionId => $promotion) {
            if (!$this->data['promotion_description'][$promotionId]['image']) {
                $this->data['promotion_description'][$promotionId]['image']  ='no_image.jpg';
            }
            $this->data['promotion_description'][$promotionId]['image_url'] = $this->setImageLink($this->data['promotion_description'][$promotionId]['image']);
        }

        $this->data['token'] = $this->session->data['token'];
        $this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);
        $this->data['action'] = $this->url->link('module/promotion/save', '&token=' . $this->session->data['token']);

        $this->template = 'module/promotion_form.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->data['error'] = $this->error;
        $this->data['success'] = '';

        $this->response->setOutput($this->render());

    }

    /**
     * edit slideshow page
     */
    public function slideshow() {
        $this->load->language('module/promotion');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('promotion', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->redirect($this->url->link('module/promotion', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->data['text_content_top'] = $this->language->get('text_content_top');
        $this->data['text_content_bottom'] = $this->language->get('text_content_bottom');
        $this->data['text_column_left'] = $this->language->get('text_column_left');
        $this->data['text_column_right'] = $this->language->get('text_column_right');

        $this->data['entry_banner'] = $this->language->get('entry_banner');
        $this->data['entry_dimension'] = $this->language->get('entry_dimension');
        $this->data['entry_layout'] = $this->language->get('entry_layout');
        $this->data['entry_position'] = $this->language->get('entry_position');
        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

        $this->data['button_save'] = $this->language->get('button_save');
        $this->data['button_cancel'] = $this->language->get('button_cancel');
        $this->data['button_add_module'] = $this->language->get('button_add_module');
        $this->data['button_remove'] = $this->language->get('button_remove');

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->error['dimension'])) {
            $this->data['error_dimension'] = $this->error['dimension'];
        } else {
            $this->data['error_dimension'] = array();
        }

        $this->data['breadcrumbs'] = $this->createBreadcrumbsArray($this->slideshowBreadcrumbs);

        $this->data['action'] = $this->url->link('module/promotion/slideshow', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['cancel'] = $this->url->link('module/promotion', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['modules'] = array();

        if (isset($this->request->post['promotion_module'])) {
            $this->data['modules'] = $this->request->post['promotion_module'];
        } elseif ($this->config->get('promotion_module')) {
            $this->data['modules'] = $this->config->get('promotion_module');
        }

        $this->load->model('design/layout');

        $this->data['layouts'] = $this->model_design_layout->getLayouts();

        $this->load->model('design/banner');

        $this->data['banners'] = array('current banner');
        //$this->data['banners'] = $this->model_design_banner->getBanners();

        $this->template = 'module/promotionslideshow.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }



    /**
     * Installing module data
     */
    public function install() {
        $this->load->model('module/promotion');
        $this->model_module_promotion->install();
    }

    /**
     * Clear module data
     */
    public function uninstall() {
        $this->load->model('module/promotion');
        $this->model_module_promotion->uninstall();
    }
}

