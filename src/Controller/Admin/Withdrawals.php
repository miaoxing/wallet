<?php

namespace Miaoxing\Wallet\Controller\admin;

use Miaoxing\Wallet\Service\Transaction;

class Withdrawals extends \miaoxing\plugin\BaseController
{
    protected $controllerName = '提现管理';

    protected $actionPermissions = [
        'index' => '列表',
        'audit,auditNoPass' => '审核',
        'log' => '日志'
    ];

    public function indexAction($req)
    {
        $statuses = wei()->transaction->getStatuses();
        $defaultStatus = wei()->transaction->getDefaultStatus();
        $curStatus = array_key_exists($req['status'], $statuses) ? $req['status'] : $defaultStatus;
        $curStatusData = $statuses[$curStatus];

        switch ($req['_format']) {
            case 'json' :
                $transactions = wei()->transaction()->curApp();
                // 提现下限默认为50,只在待审核的时候
                if ($curStatus == "toBeAudit") {
                    $moneyLimit = isset($req['moneyLimit']) ? $req['moneyLimit'] : 50;
                    $transactions->andWhere("ABS(transactions.amount) >= ?", $moneyLimit);
                }

                // 分页
                $transactions->limit($req['rows'])->page($req['page']);

                // 排序
                $transactions->desc($curStatusData['timeField']);

                // 筛选类型
                $transactions->andWhere(['type' => Transaction::TYPE_WITHDRAWAL]);

                $transactions->$curStatus();

                // 时间筛选
                $timeRange = $req[$curStatusData['timeField'] . 'Range'];
                if ($timeRange) {
                    $ranges = explode('~', strtr($timeRange, '.', '-'));
                    $ranges[0] = date('Y-m-d', strtotime($ranges[0]));
                    $ranges[1] = date('Y-m-d', strtotime($ranges[1])) . ' 23:59:59';
                    $transactions->andWhere($curStatusData['timeField'] . ' BETWEEN ? AND ?', [$ranges[0], $ranges[1]]);
                }

                $data = array();
                /** @var \Miaoxing\Wallet\Service\Transaction $transaction */
                foreach ($transactions as $transaction) {
                    $log = wei()->transactionLog()->desc('id')->findOrInit(['transactionId' => $transaction['id']]);
                    $data[] = $transaction->toArray() + [
                            'absAmount' => abs($transaction['amount']),
                            'user' => $transaction->getUser()->toArray(),
                            'statusName' => $transaction->getStatusName(),
                            'accountTypeName' => $transaction->getAccountTypeName(),
                            'description' => $log['note'],
                            'updateUserName' => $this->user->getDisplayNameByIdFromCache($transaction['updateUser']),
                        ];
                }

                return $this->suc([
                    'message' => '读取成功',
                    'data' => $data,
                    'page' => $req['page'],
                    'rows' => $req['rows'],
                    'records' => $transactions->count(),
                ]);

            default :
                return get_defined_vars();
        }
    }

    /**
     * 审核提现
     * @param $req
     * @return string|\Wei\Response
     */
    public function auditAction($req)
    {
        if (!$req['audit']) {
            return $this->err('请选择审核结果');
        }

        $transaction = wei()->transaction()->findOneById($req['id']);

        $result = $transaction->audit($req['audit'], $req['note']);
        if ($result['code'] !== 1) {
            return $this->ret($result);
        }

        if ($req['audit'] == Transaction::AUDIT_NO_PASS) {
            return $this->auditNoPassAction($req);
        }

        return $this->ret($result);
    }

    public function auditNoPassAction($req)
    {
        $transaction = wei()->transaction()->findOneById($req['id']);
        $user = wei()->user()->findOrInitById($transaction['userId']);
        $result = $transaction->refund(-$transaction['amount'], [
            'accountType' => $transaction['accountType'],
            'account' => (string) $transaction['account'],
        ], $user);
        $result['message'] .= '(审核不通过)';

        return $this->ret($result);
    }

    /*
     * 提款日志
     * */
    public function logAction($req)
    {
        switch ($req['_format']) {
            case 'json' :
                $transactionLogs = wei()->transactionLog();
                $transactionLogs->select("transactionLogs.*");
                $transactionLogs->leftJoin("user", "transactionLogs.userId = user.id");

                // 分页
                $transactionLogs->limit($req['rows'])->page($req['page']);
                $transactionLogs->desc("createTime");

                // 用户名,手机号码搜索
                if ($req['search']) {
                    $transactionLogs->andWhere('(user.nickName LIKE ?) OR (user.mobile LIKE ?)',
                        ["%{$req['search']}%", "%{$req['search']}%"]);
                }

                //交易类型(1提现)
                $transactionLogs->andWhere("type=?", Transaction::TYPE_WITHDRAWAL);

                // 时间筛选
                $timeRange = $req["timeRange"];
                if ($timeRange) {
                    $ranges = explode('~', strtr($timeRange, '.', '-'));
                    $ranges[0] = date('Y-m-d', strtotime($ranges[0]));
                    $ranges[1] = date('Y-m-d', strtotime($ranges[1])) . ' 23:59:59';
                    $transactionLogs->andWhere("transactionLogs.createTime" . ' BETWEEN ? AND ?',
                        [$ranges[0], $ranges[1]]);
                }

                $data = array();
                foreach ($transactionLogs as $transactionLog) {
                    $data[] = $transactionLog->toArray() + [
                            'user' => $transactionLog->getUser()->toArray(),
                            'transaction' => $transactionLog->getTransaction()->toArray(),
                            'updateUserName' => $this->user->getDisplayNameByIdFromCache($transactionLog['createUser']),
                        ];
                }

                return $this->suc([
                    'message' => '读取成功',
                    'data' => $data,
                    'page' => $req['page'],
                    'rows' => $req['rows'],
                    'records' => $transactionLogs->count(),
                ]);

            default :
                return get_defined_vars();
        }
    }

}
