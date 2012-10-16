<?php
/**
 * ControllerModulePromotion
 *
 * @copyright  2012 Stfalcon (http://stfalcon.com/)
 */
class ControllerModulePromotion extends Controller
{

    /**
     * render promotions banner
     *
     * @param $setting module settings
     */
    protected function index($setting)
    {
		static $module = 0;
		
		$this->load->model('module/promotion');
		$this->load->model('tool/image');

		$this->document->addScript('catalog/view/javascript/jquery/nivo-slider/jquery.nivo.slider.pack.js');
		
		if (file_exists('catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/slideshow.css')) {
			$this->document->addStyle('catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/slideshow.css');
		} else {
			$this->document->addStyle('catalog/view/theme/default/stylesheet/slideshow.css');
		}

		$this->data['width'] = $setting['width'];
		$this->data['height'] = $setting['height'];
		
		$this->data['promotions'] = $this->model_module_promotion->getPromotions();

        foreach ($this->data['promotions'] as $promotionItem => $result) {
            $this->data['promotions'][$promotionItem]['image_link'] = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
            $this->data['promotions'][$promotionItem]['item_link'] = $this->url->link('module/promotion/show', 'promotion_id=' . $result['promotion_id'] );
        }
		
		$this->data['module'] = $module++;
						
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/slideshow.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/promotionslideshow.tpl';
		} else {
			$this->template = 'default/template/module/promotionslideshow.tpl';
		}
		
		$this->render();
	}

    /**
     * show promotion show
     */
    public function show() {
        $this->load->language('module/promotion');

        if (isset($this->request->get['promotion_id'])) {
            $promotionId = $this->request->get['promotion_id'];
        } else {
            $this->renderErrorPage();
            die;
        }

        $this->load->model('module/promotion');
        if($promotionInfo = $this->model_module_promotion->getPromotion($promotionId)) {

            $this->language->load('module/promotion');
            $this->load->model('tool/image');

            $this->data['breadcrumbs'] = array();
            $this->data['breadcrumbs'][] = array(
                'text'      => $this->language->get('text_home'),
                'href'      => $this->url->link('common/home'),
                'separator' => false
            );

            $this->document->setTitle($promotionInfo['name']);

            $this->data['breadcrumbs'][] = array(
                'text'      => $promotionInfo['name'],
                'href'      => $this->url->link('module/promotion', 'promotion_id=' .  $promotionId),
                'separator' => $this->language->get('text_separator')
            );

            $this->data['heading_title'] = $promotionInfo['name'];

            $this->data['button_continue'] = $this->language->get('button_continue');

            $this->load->model('setting/setting');
            $promotion = $this->model_setting_setting->getSetting('promotion');

            if (isset($promotion['promotion_module'][0]['width']) && isset($promotion['promotion_module'][0]['height'])) {
                $settings['height'] = $promotion['promotion_module'][0]['height'];
                $settings['width'] = $promotion['promotion_module'][0]['width'];
            }
            else {
                $settings['height'] = 280;
                $settings['width'] = 880;
            }

            $this->data['image'] = $promotionInfo['image'];
            $this->data['image_url'] = $this->model_tool_image->resize($promotionInfo['image'],$settings['width'],$settings['height']);
            $this->data['description'] = html_entity_decode($promotionInfo['description'], ENT_QUOTES, 'UTF-8');
            $this->data['continue'] = $this->url->link('common/home');

            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/module/promoinformation.tpl')) {
                $this->template = $this->config->get('config_template') . '/template/module/promoinformation.tpl';
            } else {
                $this->template = 'default/template/module/promoinformation.tpl';
            }

            $this->data['products'] = $this->model_module_promotion->getProductsOfPromotion($promotionId);

            foreach ($this->data['products'] as $arrayKey => $result) {
                if ($result['image']) {
                    $image = $this->model_tool_image->resize($result['image'], $this->config->get('config_image_related_width'), $this->config->get('config_image_related_height'));
                } else {
                    $image = false;
                }

                if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
                    $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')));
                } else {
                    $price = false;
                }

                if ((float)$result['special']) {
                    $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')));
                } else {
                    $special = false;
                }

                $this->data['products'][$arrayKey] = array(
                    'product_id' => $result['product_id'],
                    'thumb'   	 => $image,
                    'name'    	 => $result['name'],
                    'price'   	 => $price,
                    'special' 	 => $special,
                    'href'    	 => $this->url->link('product/product', 'product_id=' . $result['product_id']),
                );
            }

            $this->data['button_cart'] = $this->language->get('button_cart');
            $this->data['button_wishlist'] = $this->language->get('button_wishlist');
            $this->data['button_compare'] = $this->language->get('button_compare');
            $this->data['button_continue'] = $this->language->get('button_continue');

            $this->children = array(
                'common/column_left',
                'common/column_right',
                'common/content_top',
                'common/content_bottom',
                'common/footer',
                'common/header'
            );

            $this->response->setOutput($this->render());
        } else {
            $this->renderErrorPage($promotionId);
            die;
        }
    }

    /**
     * render promotion error page
     *
     * @param int $promotionId errored promotion id
     */
    protected function renderErrorPage($promotionId = 0) {
        $this->load->language('module/promotion');

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_error'),
            'href'      => $this->url->link('information/information', 'information_id=' . $promotionId),
            'separator' => $this->language->get('text_separator')
        );

        $this->document->setTitle($this->language->get('text_error'));

        $this->data['heading_title'] = $this->language->get('text_error');

        $this->data['text_error'] = $this->language->get('text_error');

        $this->data['button_continue'] = $this->language->get('button_continue');

        $this->data['continue'] = $this->url->link('common/home');

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/error/not_found.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/error/not_found.tpl';
        } else {
            $this->template = 'default/template/error/not_found.tpl';
        }

        $this->children = array(
            'common/column_left',
            'common/column_right',
            'common/content_top',
            'common/content_bottom',
            'common/footer',
            'common/header'
        );

        $this->response->setOutput($this->render());
    }
}
?>