<?php

namespace Miaoxing\Wallet\Controller\Admin;

class FinanceSettings extends \miaoxing\plugin\BaseController
{
    protected $controllerName = '财务功能设置';

    protected $actionPermissions = [
        'index,update' => '设置',
    ];

    public function indexAction()
    {
        return get_defined_vars();
    }

    public function updateAction($req)
    {
        // TODO 1. 配置改为统一的前缀
        // TODO 2. Setting功能是差不多的,可以改为只有一个页面,tab切换?
        $this->setting->setValues((array)$req['settings'], ['payments.', 'transaction.']);
        return $this->suc();
    }
}
