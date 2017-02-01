<?php

namespace Miaoxing\Wallet\Migration;

use Miaoxing\Plugin\BaseMigration;

class V20170201171230CreateTransactionsTable extends BaseMigration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->schema->table('transactions')
            ->id()
            ->int('appId')
            ->int('userId')
            ->int('recordId')
            ->string('accountType', 16)->comment('账户类型')
            ->string('account')
            ->decimal('amount', 10)->comment('交易金额')
            ->decimal('balance', 10)->comment('余额')
            ->tinyInt('type', 1)->comment('0付款 1提现 2充值 3转账 4退款')
            ->bool('audit')
            ->timestamp('auditTime')
            ->bool('passed')
            ->timestamp('passTime')
            ->string('note', 64)->comment('备注')
            ->timestamps()
            ->userstamps()
            ->exec();
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->schema->dropIfExists('transactions');
    }
}
