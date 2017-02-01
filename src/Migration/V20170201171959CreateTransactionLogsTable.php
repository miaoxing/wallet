<?php

namespace Miaoxing\Wallet\Migration;

use Miaoxing\Plugin\BaseMigration;

class V20170201171959CreateTransactionLogsTable extends BaseMigration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->schema->table('transactionLogs')
            ->id()
            ->int('transactionId')
            ->int('userId')
            ->tinyInt('type', 1)->comment('1提现')
            ->tinyInt('operation', 1)->comment('操作(1审核通过,2审核不通过,3完成交易)')
            ->string('note', 255)
            ->timestamp('createTime')
            ->int('createUser')
            ->exec();
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->schema->dropIfExists('transactionLogs');
    }
}
