<?php

namespace Miaoxing\Wallet\Controller;

class Wallet extends \Miaoxing\Plugin\BaseController
{
    public function indexAction($req)
    {
        $this->page->setTitle('我的钱包');

        return get_defined_vars();
    }
}
