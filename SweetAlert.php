<?php

namespace suxiaolin\sweetalert;

use suxiaolin\sweetalert\SweetAlertAsset;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;

class SweetAlert extends \yii\bootstrap\Widget
{
    public $useFlash = false;
    
    public $jsOptions = [];
    
    public $callback = 'function() {}';
    
    /**
     * @var array the valid alert types.
     */
    public $alertTypes = [
        'error',
        'danger',
        'success',
        'info',
        'warning',
    ];
    
    protected $_template = 'sweetAlert({jsOptions}, {callback});';
    
    protected $_defaultJsOptions = [
        'title' => "",
        'text' => '',
        'type' => 'info',
        'showCancelButton' => true,
        'closeOnConfirm' => false,
        'animation' => "slide-from-top"
    ];

    public function init()
    {
        parent::init();
        SweetAlertAsset::register($this->view);
    }
    
    public function run() {
        parent::run();
        if ($this->useFlash) {
            $this->_renderWithSession();
        } else {
            if ( ! isset($this->jsOptions['type']) || !isset($this->jsOptions['text'])) {
                throw new InvalidConfigException('`type` and `text` must be set. please check it.');
            }
            $this->_renderDirect();
        }
    }
    
    protected function _renderSession() {
        $session = \Yii::$app->getSession();
        $flashes = $session->getAllFlashes();
        $appendCss = isset($this->options['class']) ? ' ' . $this->options['class'] : '';

        foreach ($flashes as $type => $data) {
            if (isset($this->alertTypes[$type])) {
                $jsOptions = array();
                if(is_string($data)) {
                    $jsOptions['type'] = $type;
                    $jsOptions['text'] = $data;
                } elseif(is_array($data)) {
                    $jsOptions['type'] = isset($data['type']) ? $data['type'] : '';
                    $jsOptions['title'] = isset($data['title']) ? $data['title'] : '';
                    $jsOptions['text'] = isset($data['text']) ? $data['text'] : '';
                }
                $js = strtr($this->_template, [
                    '{jsOptions}' => Json::encode(ArrayHelper::merge($this->_defaultJsOptions, $jsOptions)),
                    '{callback}' => $this->callback,
                ]);
                echo $js;

                $session->removeFlash($type);
            }
        }
    }
    
    protected function _renderDirect() {
        $js = strtr($this->_template, [
            '{jsOptions}' => Json::encode(ArrayHelper::merge($this->_defaultJsOptions, $this->jsOptions)),
            '{callback}' => $this->callback,
        ]);
        echo $js;
    }
}
