<?php

namespace Miaoxing\Wallet\Controller;

class Wallet extends \miaoxing\plugin\BaseController
{
    public function indexAction($req)
    {
        $headerTitle = '我的钱包';
        return get_defined_vars();
    }
}
