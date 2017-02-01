<?php

namespace Miaoxing\Wallet\Service;

use miaoxing\plugin\BaseModel;

class TransactionLog extends BaseModel
{
    protected $table = 'transactionLogs';

    protected $providers = [
        'db' => 'app.db',
    ];

    protected $user;

    protected $transaction;

    public function getUser()
    {
        $this->user || $this->user = wei()->user()->findOrInitById($this['userId']);
        return $this->user;
    }

    public function getTransaction()
    {
        $this->transaction || $this->transaction = wei()->transaction()->findOrInitById($this['transactionId']);
        return $this->transaction;
    }
}
