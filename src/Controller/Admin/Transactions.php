<?php

namespace Miaoxing\Wallet\Controller\Admin;

class Transactions extends \Miaoxing\Plugin\BaseController
{
    protected $controllerName = '余额明细';

    protected $actionPermissions = [
        'index' => '列表',
    ];

    public function indexAction($req)
    {
        switch ($req['_format']) {
            case 'json':
                $transactions = wei()->transaction()
                    ->select('transactions.*')
                    ->curApp();

                // 分页
                $transactions->limit($req['rows'])->page($req['page']);

                // 排序
                $sort = $req['sort'] ?: 'id';
                $order = $req['order'] == 'asc' ? 'ASC' : 'DESC';
                $transactions->orderBy('transactions.' . $sort, $order);

                if ($req['userId']) {
                    $transactions->andWhere(['userId' => $req['userId']]);
                }

                // 筛选
                if (isset($req['type']) && $req['type'] >= 0) {
                    $transactions->andWhere(['type' => $req['type']]);
                }

                if (isset($req['search']) && $req['search']) {
                    $transactions->andWhere('(note LIKE ?)', [
                        '%' . $req['search'] . '%',
                    ]);
                }

                if ($req['name']) {
                    $userTable = $this->app->getNamespace() . '.user';
                    $transactions->leftJoin($userTable, 'user.id = transactions.userId')
                        ->andWhere('user.name LIKE ?', '%' . $req['name'] . '%');
                }

                // 时间筛选
                $timeRange = $req['createTimeRange'];
                if ($timeRange) {
                    $ranges = explode('~', strtr($timeRange, '.', '-'));
                    $ranges[0] = date('Y-m-d', strtotime($ranges[0]));
                    $ranges[1] = date('Y-m-d', strtotime($ranges[1])) . ' 23:59:59';
                    $transactions->andWhere('transactions.createTime BETWEEN ? AND ?', [$ranges[0], $ranges[1]]);
                }

                $this->event->trigger('preAdminTransactionListFind', [$req, $transactions]);

                $data = [];
                foreach ($transactions->findAll() as $transaction) {
                    $data[] = $transaction->toArray() + [
                            'typeName' => $transaction->getTypeText(),
                            'user' => $transaction->getUser()->toArray(),
                            'statusName' => $transaction->getStatusName(),
                            'accountTypeName' => $transaction->getAccountTypeName(),
                            'createUserName' => $this->user->getDisplayNameByIdFromCache($transaction['createUser']),
                        ];
                }

                $this->event->trigger('postAdminTransactionListFind', [$req, &$data]);

                return $this->suc([
                    'data' => $data,
                    'page' => $req['page'],
                    'rows' => $req['rows'],
                    'records' => $transactions->count(),
                ]);

            default:
                return get_defined_vars();
        }
    }

    public function newAction($req)
    {
        $selectedUser = wei()->user()->findOneById($req['userId']);

        return get_defined_vars();
    }

    public function createAction($req)
    {
        $selectedUser = wei()->user()->findOneById($req['userId']);

        $typeMethods = wei()->transaction->getTypeMethods();

        $validator = wei()->validate([
            'data' => $req,
            'rules' => [
                'type' => [
                    'in' => [
                        'array' => array_keys($typeMethods),
                    ],
                ],
                'amount' => [
                    'number' => true,
                    'notEqualTo' => '0',
                ],
            ],
            'names' => [
                'type' => '类型',
                'amount' => '金额',
            ],
            'messages' => [
                'type' => [
                    'in' => '%name%不正确',
                ],
            ],
        ]);
        if (!$validator->isValid()) {
            return $this->err($validator->getFirstMessage());
        }

        $method = $typeMethods[$req['type']];
        $ret = wei()->transaction->$method($req['amount'], [
            'note' => $req['note'],
        ], $selectedUser);

        return $this->ret($ret);
    }
}
