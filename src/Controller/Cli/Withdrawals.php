<?php

namespace Miaoxing\Wallet\Controller\Cli;

use Miaoxing\Wallet\Service\Transaction;

class Withdrawals extends \Miaoxing\Plugin\BaseController
{
    /**
     * 较小金额直接转账,小于指定金额,在1小时前提交申请的提款
     */
    public function autoTransferOfLessAmountAction()
    {
        $transactions = wei()->transaction()->curApp()
            ->andWhere('amount <= ?', wei()->transaction->getAutoRechargeMoney())
            ->andWhere('audit = ?', 0)
            ->andWhere(['type' => Transaction::TYPE_WITHDRAWAL])
            ->andWhere('createTime <= ?', date('Y-m-d H:i:s', time() - 3600))
            ->findAll();

        foreach ($transactions as $transaction) {
            //1.先审核
            $transaction->audit(Transaction::AUDIT_PASS, '自动审核通过');
            //2.在转账
            $transaction->transferWithdrawal('小于指定金额,在1小时前提交申请的提款自动转账');
        }

        return $this->suc();
    }

    /**
     * 通过审核的自动转账
     */
    public function autoTransferOfAuditedAction()
    {
        $transactions = wei()->transaction()->curApp()
            ->andWhere(['audit' => 1])
            ->andWhere(['passed' => 0])
            ->andWhere(['type' => Transaction::TYPE_WITHDRAWAL])
            ->findAll();

        foreach ($transactions as $transaction) {
            $transaction->transferWithdrawal('通过审核的自动转账');
        }

        return $this->suc();
    }
}
