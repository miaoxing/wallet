<?php

namespace Miaoxing\Wallet;

use Miaoxing\Order\Service\Order;
use Miaoxing\Payment\Payment\Base;

class Plugin extends \miaoxing\plugin\BasePlugin
{
    /**
     * 充值的订单分类ID
     */
    const RECHARGE_ORDER_CATEGORY_ID = 17;

    protected $name = '用户钱包管理';

    protected $adminNavId = 'finance';

    public function onAdminNavGetNavs(&$navs, &$categories, &$subCategories)
    {
        $categories['finance'] = [
            'name' => '财务',
            'sort' => 50,
        ];

        $subCategories['finance'] = [
            'parentId' => 'finance',
            'name' => '财务',
            'icon' => 'fa fa-money',
        ];

        if (wei()->setting('transaction.enableWallet')) {
            $navs[] = [
                'parentId' => 'finance',
                'url' => 'admin/wallets',
                'name' => '用户钱包管理',
                'sort' => 500,
            ];
        }

        if (wei()->setting('transaction.enableTransactions')) {
            $navs[] = [
                'parentId' => 'finance',
                'url' => 'admin/transactions',
                'name' => '余额明细',
                'sort' => 400,
            ];
        }

        if (wei()->setting('transaction.enableWithdrawal')) {
            $navs[] = [
                'parentId' => 'finance',
                'url' => 'admin/withdrawals',
                'name' => '提现管理',
                'sort' => 300,
            ];
        }

        if (wei()->setting('transaction.enableRecharge')) {
            $navs[] = [
                'parentId' => 'finance',
                'url' => 'admin/recharges',
                'name' => '充值管理',
                'sort' => 200,
            ];
        }

        $subCategories['finance-setting'] = [
            'parentId' => 'finance',
            'name' => '设置',
            'icon' => 'fa fa-gear',
            'sort' => 0,
        ];

        if (wei()->setting('transaction.enableSetting')) {
            $navs[] = [
                'parentId' => 'finance-setting',
                'url' => 'admin/finance-settings',
                'name' => '功能设置',
                'sort' => 0,
            ];
        }
    }

    public function onLinkToGetLinks(&$links, &$types)
    {
        $types['wallet'] = [
            'name' => '钱包',
            'sort' => 400,
        ];

        $links[] = [
            'typeId' => 'wallet',
            'name' => '我的钱包',
            'url' => 'wallet',
        ];

        $links[] = [
            'typeId' => 'wallet',
            'name' => '充值',
            'url' => 'orders/proxy?forward=recharges%2Fnew&showwxpaytitle=1',
        ];
    }

    /**
     * 如果是充值订单,给用户增加余额
     *
     * @param Order $order
     */
    public function onPostOrderPay(Order $order)
    {
        if ($order['categoryId'] != static::RECHARGE_ORDER_CATEGORY_ID) {
            return;
        }

        // 计算总共增加的金额
        $rule = wei()->transaction->getRechargeRuleByAmount($order['amount']);
        $bonus = $rule ? sprintf('%.2f', $rule['bonus']) : 0;

        wei()->transaction->recharge($order['amount'], [
            'note' => $bonus ? '充' . $order['amount'] . '送' . $bonus : '充值',
        ], $order->getUser());

        wei()->transaction->gift($bonus, [
            'note' => '赠送' . $bonus,
        ], $order->getUser());
    }

    /**
     * @param Base $payment
     * @param Order $order
     * @param $orderUrl
     */
    public function onFilterPaymentOrderUrl(Base $payment, Order $order, &$orderUrl)
    {
        if ($order['categoryId'] == static::RECHARGE_ORDER_CATEGORY_ID) {
            $orderUrl = wei()->url('transactions');
        }
    }
}
