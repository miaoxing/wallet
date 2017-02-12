<?php

namespace Miaoxing\Wallet\Service;

use miaoxing\plugin\BaseModel;
use Miaoxing\Plugin\Service\User;

/**
 * 配置
 * 1. enableRecharge 是否启用充值 默认为0
 */
class Transaction extends BaseModel
{
    use \Miaoxing\App\Constant;

    /**
     * 付款
     */
    const TYPE_PAYMENT = 0;

    /**
     * 提现
     */
    const TYPE_WITHDRAWAL = 1;

    /**
     * 充值
     */
    const TYPE_RECHARGE = 2;

    /**
     * 转账
     */
    const TYPE_TRANSFER = 3;

    /**
     * 退款(退款到余额,不是到第三方账户)
     */
    const TYPE_REFUND = 4;

    /**
     * 赠送(充值赠送)
     */
    const TYPE_GIFT = 5;

    /**
     * 提现申请未通过
     */
    const AUDIT_NO_PASS = 2;

    /**
     * 提现申请未通过
     */
    const AUDIT_PASS = 1;

    /**
     * @var array
     */
    protected $typeTable = [
        self::TYPE_PAYMENT => [
            'text' => '付款',
            'method' => 'pay',
        ],
        self::TYPE_WITHDRAWAL => [
            'text' => '提现',
            'method' => 'withdraw',
        ],
        self::TYPE_RECHARGE => [
            'text' => '充值',
            'method' => 'recharge',
        ],
        self::TYPE_TRANSFER => [
            'text' => '转账',
            'method' => 'transfer',
        ],
        self::TYPE_REFUND => [
            'text' => '退款',
            'method' => 'refund',
        ],
        self::TYPE_GIFT => [
            'text' => '赠送',
            'method' => 'gift',
        ],
    ];

    protected $autoId = true;

    protected $table = 'transactions';

    protected $providers = [
        'db' => 'app.db',
    ];

    protected $statuses = [
        'all' => [
            'name' => '全部',
            'timeField' => 'createTime',
            'timeName' => '申请时间',
        ],
        'toBeAudit' => [
            'name' => '待审核',
            'timeField' => 'createTime',
            'timeName' => '申请时间',
        ],
        'toBeTransfer' => [
            'name' => '待转账',
            'timeField' => 'auditTime',
            'timeName' => '审核时间',
        ],
        'transferred' => [
            'name' => '已转账',
            'timeField' => 'passTime',
            'timeName' => '转账时间',
        ],
        'auditNotPass' => [
            'name' => '审核不通过',
            'timeField' => 'auditTime',
            'timeName' => '审核时间',
        ],
    ];

    protected $defaultStatus = 'toBeAudit';

    protected $auditStatuses = [
        0 => '待审核',
        1 => '审核通过',
        2 => '审核不通过',
    ];

    protected $transferStatuses = [
        0 => '未转账',
        1 => '已转账',
    ];

    protected $accountTypeNames = [
        'wechatPayV3' => '微信',
//        'alipay' => '支付宝',
        //'tenpay' => '财付通',
        //'bankCard' => '银行卡',
    ];

    /**
     * @var User
     */
    protected $user;

    /**
     * 自动转账的最小金额
     *
     * @var int
     */
    protected $autoRechargeMoney = 50;

    /**
     * 提现审核不通过消息模板ID
     * @var string
     */
    protected $auditNoPassTplId;

    /**
     * 提现成功消息模板ID
     * @var string
     */
    protected $withdrawalSucTplId;

    /**
     * Record: 获取审核状态的名称
     *
     * @return mixed
     */
    public function getAuditName()
    {
        return $this->auditStatuses[$this['audit']];
    }

    /**
     * Record: 获取当前提款单状态
     */
    public function getStatusName()
    {
        switch (true) {
            case $this['audit'] == 2:
                return '审核不通过';

            case $this['audit'] == 1 && $this['passed'] == 0:
                return '待转账';

            case $this['passed'] == 1:
                return '已转账';

            case $this['audit'] == 0:
                return '待审核';

            default:
                return '状态错误';
        }
    }

    /**
     * @return array
     */
    public function getAccountTypeNames()
    {
        return $this->accountTypeNames;
    }

    /**
     * @return string
     */
    public function getAccountTypeName()
    {
        return $this->accountTypeNames[$this['accountType']];
    }

    /**
     * 待审核和待发放的总金额
     * @param User $user
     * @return string
     */
    public function getFrozenMoney(User $user = null)
    {
        $user || $user = wei()->curUser;

        $toAuditMoney = wei()->transaction()
            ->curApp()
            ->select('sum(amount) as amountSum')
            ->andWhere(['type' => Transaction::TYPE_WITHDRAWAL])
            ->andWhere(['userId' => $user['id']])
            ->andWhere(['audit' => 0])
            ->fetch();

        $toTransferMoney = wei()->transaction()
            ->curApp()
            ->select('sum(amount) as amountSum')
            ->andWhere(['type' => Transaction::TYPE_WITHDRAWAL])
            ->andWhere(['userId' => $user['id']])
            ->andWhere([
                'audit' => 1,
                'passed' => 0
            ])
            ->fetch();

        return sprintf('%.2f', abs($toAuditMoney['amountSum'] + $toTransferMoney['amountSum']));
    }

    /**
     * 获取用户的可提款金额
     *
     * @param User $user
     * @return string
     */
    public function getAvailableMoney(User $user = null)
    {
        $user || $user = wei()->curUser;

        return sprintf('%.2f', $user['money'] - $user['rechargeMoney']);
    }

    /**
     * @return User
     */
    public function getUser()
    {
        $this->user || $this->user = wei()->user()->findOrInitById($this['userId']);

        return $this->user;
    }

    /**
     * Record: 设置提款单审核结果
     *
     * @param int $audit
     * @param string $note
     * @return array
     */
    public function audit($audit, $note = null)
    {
        if ($this['passed'] == 1) {
            return ['code' => -2, 'message' => '提款单已提款,不可以审核'];
        }

        $this->save([
            'audit' => $audit,
            'note' => $note,
            'auditTime' => date('Y-m-d H:i:s'),
            'updateUser' => wei()->curUser['id'],
        ]);

        wei()->event->trigger('postAuditTransfers', [$this]);

        if ($audit == Transaction::AUDIT_NO_PASS) {
            $user = wei()->user()->findById($this['userId']);
            $this->sendAuditNoPassTplMsg($user);
        }

        wei()->transactionLog()->save([
            'transactionId' => $this['id'],
            'userId' => $this['userId'],
            'type' => $this['type'],
            'operation' => $audit,
            'note' => $note,
        ]);

        return ['code' => 1, 'message' => '操作成功'];
    }

    /**
     * 提现审核结果通知
     * @param User $user
     * @return array
     */
    public function sendAuditNoPassTplMsg(User $user)
    {
        $data = $this->getSendAuditNoPassTplData();
        $url = wei()->url->full('withdrawals');

        $account = wei()->wechatAccount->getCurrentAccount();
        $ret = $account->sendTemplate($user, $this->auditNoPassTplId, $data, $url);

        return $ret;
    }

    /**
     * 提现审核结果通知
     *
     * {{first.DATA}}
     * 提现金额：{{keyword1.DATA}}
     * 提现方式：{{keyword2.DATA}}
     * 申请时间：{{keyword3.DATA}}
     * 审核结果：{{keyword4.DATA}}
     * 审核时间：{{keyword5.DATA}}
     * {{remark.DATA}}
     *
     */
    public function getSendAuditNoPassTplData()
    {
        return [
            'first' => [
                'value' => '您好。您的提现申请已处理。',
            ],
            'keyword1' => [
                'value' => $this['amount'] ?: '-',
            ],
            'keyword2' => [
                'value' => '普通提现',
            ],
            'keyword3' => [
                'value' => $this['createTime'] ?: '-',
            ],
            'keyword4' => [
                'value' => $this->getStatusName() ?: '-',
            ],
            'keyword5' => [
                'value' => $this['auditTime'] ?: '-',
            ],
            'remark' => [
                'value' => '点击查看详情',
                'color' => '#44b549',
            ],
        ];
    }

    /**
     * Record: 转账提款金额到用户外部账户,即提款单打款
     *
     * @param string $note
     * @return array
     */
    public function transferWithdrawal($note = '')
    {
        if ($this['audit'] != 1) {
            return ['code' => -2, 'message' => '提款单必须审核通过才能转账'];
        }

        if ($this['passed']) {
            return ['code' => -1, 'message' => '该提款单已经转账过'];
        }

        $ret = $this->transfersApi('提现操作');

        // 提现失败
        if ($ret['code'] != 1) {
            // 更新最后操作人和时间
            $this->save();
            wei()->transactionLog()->save([
                'transactionId' => $this['id'],
                'userId' => $this['userId'],
                'type' => $this['type'],
                'operation' => 4, // 操作失败
                'note' => $ret['message'],
                'createTime' => date('Y-m-d H:i:s'),
                'createUser' => wei()->curUser['id'],
            ]);
            wei()->logger->alert($ret['message'], $ret);

            return $ret;
        }

        // 提现成功
        $this->save([
            'passed' => 1,
            'passTime' => date('Y-m-d H:i:s'),
        ]);

        wei()->event->trigger('postTransfers', [$this]);

        $user = wei()->user()->findById($this['userId']);
        $this->sendWithdrawalSucTplMsg($user);

        wei()->transactionLog()->save([
            'transactionId' => $this['id'],
            'userId' => $this['userId'],
            'type' => $this['type'],
            'operation' => 3, // 完成交易
            'note' => $note,
            'createTime' => date('Y-m-d H:i:s'),
            'createUser' => wei()->curUser['id'],
        ]);

        return $ret;
    }

    /**
     * 提现完成通知
     *
     * @param User $user
     * @return array
     */
    public function sendWithdrawalSucTplMsg(User $user)
    {
        $data = $this->getSendWithdrawalSucTplData($user);
        $url = wei()->url->full('withdrawals');

        $account = wei()->wechatAccount->getCurrentAccount();
        $ret = $account->sendTemplate($user, $this->withdrawalSucTplId, $data, $url);

        return $ret;
    }

    /**
     * 提现完成通知
     *
     * {{first.DATA}}
     * 提现金额：{{keyword1.DATA}}
     * 提现账号：{{keyword2.DATA}}
     * 提现时间：{{keyword3.DATA}}
     * {{remark.DATA}}
     *
     * @param User $user
     * @return array
     */
    public function getSendWithdrawalSucTplData(User $user)
    {
        return [
            'first' => [
                'value' => '您好，您的提现申请成功，钱已到账',
            ],
            'keyword1' => [
                'value' => $this['amount'] ?: '-',
            ],
            'keyword2' => [
                'value' => $user->getNickName() ?: '-',
            ],
            'keyword3' => [
                'value' => $this['passTime'] ?: '-',
            ],
            'remark' => [
                'value' => '点击查看详情',
                'color' => '#44b549',
            ],
        ];
    }

    /**
     * @return int
     */
    public function getAutoRechargeMoney()
    {
        return $this->autoRechargeMoney;
    }

    /**
     * 获取所有状态的配置
     *
     * @return array
     */
    public function getStatuses()
    {
        return $this->statuses;
    }

    /**
     * 获取默认状态
     *
     * @return string
     */
    public function getDefaultStatus()
    {
        return $this->defaultStatus;
    }

    /**
     * QueryBuilder: 不筛选任何记录
     *
     * @return $this
     */
    public function all()
    {
        return $this;
    }

    /**
     * QueryBuilder: 筛选待审核的记录
     *
     * @return $this
     */
    public function toBeAudit()
    {
        return $this->andWhere([
            'audit' => 0,
        ]);
    }

    /**
     * QueryBuilder: 筛选待转账的记录
     *
     * @return $this
     */
    public function toBeTransfer()
    {
        return $this->andWhere([
            'audit' => 1,
            'passed' => 0,
        ]);
    }

    /**
     * QueryBuilder: 筛选已转账的记录
     *
     * @return $this
     */
    public function transferred()
    {
        return $this->andWhere([
            'passed' => 1,
        ]);
    }

    /**
     * QueryBuilder: 筛选审核转账不通过的记录
     *
     * @return $this
     */
    public function auditNotPass()
    {
        return $this->andWhere([
            'audit' => 2,
        ]);
    }

    /**
     * 微信企业支付api
     * @param $content
     * @return array
     */
    public function transfersApi($content)
    {
        // 商户订单号加上时间,目前已知余额不足后,再次使用相同的ID,微信会返回"SYSTEMERROR"
        $data = [
            'partner_trade_no' => $this['id'] . '-' . time(),
            'openid' => $this->getUser()->get('wechatOpenId'),
            'amount' => abs($this['amount'] * 100),
            'desc' => $content,
        ];
        $wechatPay = wei()->payment->createCurrentWechatPayService();
        $api = $wechatPay->getApi();

        return $api->transfers($data);
    }

    /**
     * Repo: 获取类型对应的方法
     */
    public function getTypeMethods()
    {
        return wei()->coll->column($this->getConstants('type'), 'method');
    }

    /**
     * Record: 获取交易的类型文案
     */
    public function getTypeText()
    {
        return $this->getConstantValue('type', $this['type'], 'text');
    }

    /**
     * 获取用户已提现的金额
     * @param User $user
     * @return string
     */
    public function getWithdrawalMoney(User $user = null)
    {
        $user || $user = wei()->curUser;
        $withdrawalMoney = wei()->transaction()
            ->curApp()
            ->andWhere(['userId' => $user['id']])
            ->andWhere(['type' => static::TYPE_WITHDRAWAL])
            ->andWhere(['audit' => 1])
            ->select('SUM(amount)')
            ->fetchColumn();

        return sprintf('%.2f', abs($withdrawalMoney));
    }

    /**
     * 提现限制
     * @return array
     */
    public function withdrawalLimit()
    {
        $ranges[0] = date('Y-m-d', time());
        $ranges[1] = date('Y-m-d H:i:s', time());
        $withdrawalCountByDay = wei()->transaction()
            ->curApp()
            ->mine()
            ->andWhere(['type' => static::TYPE_WITHDRAWAL])
            ->andWhere('createTime between ? and ?', [$ranges[0], $ranges[1]])
            ->count();
        if ($withdrawalCountByDay >= 1) {
            return [
                'code' => -1,
                'message' => '一天之内提现不超过1笔',
            ];
        }

        $ranges[0] = date('Y-m-d', time() - 6 * 86400);
        $ranges[1] = date('Y-m-d H:i:s', time());
        $withdrawalCountBy7Day = wei()->transaction()
            ->curApp()
            ->mine()
            ->andWhere(['type' => static::TYPE_WITHDRAWAL])
            ->andWhere('createTime between ? and ?', [$ranges[0], $ranges[1]])
            ->count();
        if ($withdrawalCountBy7Day >= 2) {
            return [
                'code' => -2,
                'message' => '七天之内不超过2笔',
            ];
        }

        $ranges[0] = date('Y-m-d', time() - 29 * 86400);
        $ranges[1] = date('Y-m-d H:i:s', time());
        $withdrawalCountBy30Day = wei()->transaction()
            ->curApp()
            ->mine()
            ->andWhere(['type' => static::TYPE_WITHDRAWAL])
            ->andWhere('createTime between ? and ?', [$ranges[0], $ranges[1]])
            ->count();
        if ($withdrawalCountBy30Day >= 5) {
            return [
                'code' => -5,
                'message' => '三十天之内不超过5笔',
            ];
        }

        return [
            'code' => 1,
            'message' => '操作成功',
        ];
    }

    /**
     * 更改用户账户余额
     *
     * @param float $money
     * @param array $transaction 交易数据,见transactions表
     * @param User $user 默认为当前用户
     * @return array
     */
    public function changeMoney($money, array $transaction = [], User $user = null)
    {
        $user || $user = wei()->curUser;

        // 1. 格式检查
        if (!is_numeric($money)) {
            return ['code' => -1, 'message' => '金额必须是有效的数字'];
        }

        // 2. 检查余额是否足够
        if ($money < 0 && $money + $user['money'] < 0) {
            $ret = ['code' => -2, 'message' => '很抱歉,您的余额不足'];
            wei()->logger->warning('Change user money fail', $ret + [
                    'amount' => $money,
                    'balance' => $user['money'],
                ]);

            return $ret;
        }

        // 3. 如果是充值,奖励,退款 加到充值余额中
        if (isset($transaction['type']) && ($transaction['type'] == static::TYPE_RECHARGE || $transaction['type'] == static::TYPE_GIFT || $transaction['type'] == static::TYPE_REFUND)) {
            $user['rechargeMoney'] += $money;
        }

        // 4. 如果是扣款,并且不是提现,优先扣除充值余额
        if ($money < 0 && $transaction['type'] != static::TYPE_WITHDRAWAL && $user['rechargeMoney'] > 0) {
            $user['rechargeMoney'] += $money;
            $user['rechargeMoney'] < 0 && $user['rechargeMoney'] = 0;
        }

        // 5. 记录一笔交易
        $balance = $user['money'] + $money;
        $record = wei()->transaction()->setAppId()->saveData([
                'userId' => $user['id'],
                'amount' => $money,
                'balance' => $balance,
            ] + $transaction);

        // 6. 扣款并保存,之后还原金额为数字
        $user->incr('money', $money);
        $user->save();
        $user['money'] = $balance;

        wei()->event->trigger('postChangeMoney', [$record, $user]);

        return [
            'code' => 1,
            'message' => '更改成功',
            'id' => $record['id'],
        ];
    }

    /**
     * 用户充值
     *
     * @param string $money
     * @param array $transaction
     * @param User $user
     * @return array
     */
    public function recharge($money, array $transaction = [], User $user = null)
    {
        return $this->changeMoney($money, $transaction + [
                'type' => static::TYPE_RECHARGE,
            ], $user);
    }

    /**
     * 赠送
     *
     * @param string $money
     * @param array $transaction
     * @param User $user
     * @return array
     */
    public function gift($money, array $transaction = [], User $user = null)
    {
        return $this->changeMoney($money, $transaction + [
                'type' => static::TYPE_GIFT,
            ], $user);
    }

    /**
     * 用户付款
     *
     * @param string $money
     * @param array $transaction
     * @param User $user
     * @return array
     */
    public function pay($money, array $transaction = [], User $user = null)
    {
        return $this->changeMoney($money, $transaction + [
                'type' => static::TYPE_PAYMENT,
                'note' => '订单',
            ], $user);
    }

    /**
     * 用户提现
     *
     * @param string $money
     * @param array $transaction
     * @param User $user
     * @return array
     */
    public function withdraw($money, array $transaction = [], User $user = null)
    {
        return $this->changeMoney($money, $transaction + [
                'type' => static::TYPE_WITHDRAWAL,
            ], $user);
    }

    /**
     * 账户回滚
     *
     * @param string $money
     * @param array $transaction
     * @param User $user
     * @return array
     */
    public function refund($money, array $transaction = [], User $user = null)
    {
        return $this->changeMoney($money, $transaction + [
                'type' => static::TYPE_REFUND,
            ], $user);
    }

    /**
     * 用户转账(可以增加,也可以减少)
     *
     * @param string $money
     * @param array $transaction
     * @param User $user
     * @return array
     */
    public function transfer($money, array $transaction = [], User $user = null)
    {
        return $this->changeMoney($money, $transaction + [
                'type' => static::TYPE_TRANSFER,
            ], $user);
    }

    /**
     * 获取正数的提款金额
     *
     * @return string
     */
    public function getAbsAmount()
    {
        return sprintf('%.2f', abs($this['amount']));
    }

    /**
     * Repo: 获取充值奖励规则
     *
     * @return array
     */
    public function getRechargeRules()
    {
        $recharges = wei()->setting('wallet.recharge');

        return (array) json_decode($recharges, true);
    }

    /**
     * Repo: 根据金额获取充值奖励规则
     *
     * @param string $amount
     * @return bool
     */
    public function getRechargeRuleByAmount($amount)
    {
        $recharges = $this->getRechargeRules();
        foreach ($recharges as $recharge) {
            if ($recharge['topUp'] == $amount) {
                return $recharge;
            }
        }

        return false;
    }
}
