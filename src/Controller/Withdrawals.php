<?php

namespace Miaoxing\Wallet\Controller;

use Miaoxing\Wallet\Service\Transaction;

class Withdrawals extends \miaoxing\plugin\BaseController
{
    public function indexAction($req)
    {
        $transaction = wei()->transaction;
        $statuses = $transaction->getStatuses();

        unset($statuses['all']);
        $curStatus = array_key_exists($req['status'], $statuses) ? $req['status'] : $transaction->getDefaultStatus();
        $curStatusData = $statuses[$curStatus];

        $rows = 10;
        $page = $req['page'] > 0 ? (int) $req['page'] : 1;

        $transactions = wei()->transaction()->curApp()->mine();

        $transactions->andWhere(['type' => Transaction::TYPE_WITHDRAWAL]);

        $transactions->limit($rows)->page($page);

        $transactions->desc('id');

        $transactions->$curStatus();

        $data = [];
        foreach ($transactions->findAll() as $transaction) {
            $data[] = $transaction->toArray() + [
                    'statusName' => $transaction->getStatusName(),
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
                $moneySum = $transactions->select('SUM(amount)')->fetchColumn();
                $moneySum = sprintf('%.2f', abs($moneySum));

                $headerTitle = '提现记录';

                return get_defined_vars();
        }
    }

    /**
     * 申请提款
     */
    public function newAction()
    {
        $availableMoney = wei()->transaction->getAvailableMoney();

        $headerTitle = '申请提款';

        return get_defined_vars();
    }

    /**
     * 提交申请
     */
    public function createAction($req)
    {
        // 1. 校验数据
        $availableMoney = wei()->transaction->getAvailableMoney();
        $validator = wei()->validate([
            'data' => $req,
            'rules' => [
                'accountType' => 'required',
                'money' => [
                    'greaterThan' => 0,
                    'lessThanOrEqual' => $availableMoney,
                    'greaterThanOrEqual' => 1
                ]
            ],
            'names' => [
                'accountType' => '账号类型',
                'account' => '账号',
                'money' => '提款金额'
            ],
            'messages' => [
                'money' => [
                    'lessThanOrEqual' => '您的可提款金额不足' . $req['money'],
                ]
            ]
        ]);
        if (!$validator->isValid()) {
            return $this->err($validator->getFirstMessage());
        }

        // 2. 提现限制
        $limit = wei()->transaction()->withdrawalLimit();
        if ($limit['code'] < 1) {
            return $this->err($limit['message']);
        }

        // 3. 增加提款单
        $ret = wei()->transaction->withdraw(-$req['money'], [
            'accountType' => $req['accountType'],
            'account' => (string) $req['account'],
        ]);

        if ($ret['code'] != 1) {
            return $this->ret($ret);
        } else {
            return $this->suc([
                'message' => '申请成功',
                'id' => $ret['id']
            ]);
        }
    }

    /**
     * 查看审核单详情
     */
    public function showAction($req)
    {
        $transaction = wei()->transaction()->curApp()->mine()->findOneById($req['id']);

        $headerTitle = '提款详情';

        return get_defined_vars();
    }

    /**
     * 展示实名验证表单
     */
    public function authAction()
    {
        return get_defined_vars();
    }

    /**
     * 提交实名验证
     */
    public function submitAuthAction($req)
    {
        $validator = wei()->validate([
            'data' => $req,
            'rules' => [
                'name' => [
                    'minLength' => 2
                ],
                'idCard' => [
                    'idCardCn' => true
                ]
            ],
            'names' => [
                'name' => '真实姓名',
                'idCard' => '身份证号'
            ]
        ]);

        if (!$validator->isValid()) {
            return $this->err($validator->getFirstMessage());
        }

        $this->curUser->save([
            'name' => $req['name'],
            'idCard' => $req['idCard'] // 暂不支持
        ]);

        return $this->suc();
    }
}
