<?php

namespace Miaoxing\Wallet\Controller;

class Transactions extends \miaoxing\plugin\BaseController
{
    public function indexAction($req)
    {
        $rows = 10;
        $page = $req['page'] > 0 ? (int) $req['page'] : 1;

        $transactions = wei()->transaction()->curApp()->mine();

        $transactions->limit($rows)->page($page);

        $transactions->desc('id');

        $data = [];
        foreach ($transactions->findAll() as $transaction) {
            $data[] = $transaction->toArray() + [
                    'typeName' => $transaction->getTypeText(),
                    'absAmount' => $transaction->getAbsAmount()
                ];
        }

        $ret = [
            'data' => $data,
            'page' => $page,
            'rows' => $rows,
            'records' => $transactions->count(),
        ];

        switch ($req['_format']) {
            case 'json':
                return $this->ret($ret);

            default:
                $headerTitle = '余额明细';

                return get_defined_vars();
        }
    }

    public function showAction($req)
    {
        $transaction = wei()->transaction()->curApp()->mine()->findOneById($req['id']);

        $headerTitle = '余额详情';

        return get_defined_vars();
    }
}
