<?php

namespace MiaoxingTest\Wallet\Service;

use Miaoxing\Wallet\Service\Transaction;

class TransactionTest extends \Miaoxing\Plugin\Test\BaseTestCase
{
    public function testRecharge()
    {
        $user = wei()->user()->save();

        $ret = wei()->transaction->recharge(10, [], $user);
        $this->assertEquals(1, $ret['code'], $ret['message']);

        // 用户余额变多了
        $this->assertEquals(10, $user['money']);
        $this->assertEquals(10, $user['rechargeMoney']);

        // 数据库多了一笔交易
        $transaction = wei()->transaction()->find(['userId' => $user['id']]);

        $this->assertArraySubset([
            'amount' => '10.00',
            'balance' => '10.00',
            'type' => Transaction::TYPE_RECHARGE,
        ], $transaction->toArray());
    }

    public function testPayment()
    {
        $user = wei()->user()->save();

        $ret = wei()->transaction->recharge(10, [], $user);
        $this->assertEquals(1, $ret['code'], $ret['message']);

        $ret = wei()->transaction->pay(-5.21, [], $user);
        $this->assertEquals(1, $ret['code'], $ret['message']);

        // 用户余额变少了
        $this->assertEquals('4.79', $user['money']);
        $this->assertEquals('4.79', $user['rechargeMoney']);

        // 数据库多了一笔交易
        $transaction = wei()->transaction()->desc('id')->find(['userId' => $user['id']]);

        $this->assertArraySubset([
            'amount' => '-5.21',
            'balance' => '4.79',
            'type' => Transaction::TYPE_PAYMENT,
        ], $transaction->toArray());
    }

    public function testPaymentButMoneyNotEnough()
    {
        $user = wei()->user()->save();

        $ret = wei()->transaction->recharge(10, [], $user);
        $this->assertEquals(1, $ret['code'], $ret['message']);

        $ret = wei()->transaction->pay(-12, [], $user);
        $this->assertEquals(-2, $ret['code'], $ret['message']);
        $this->assertEquals('很抱歉,您的余额不足', $ret['message']);

        $this->assertEquals(10, $user['money']);
    }

    public function testPaymentUsingRechargeMoney()
    {
        $user = wei()->user()->save([
            'money' => '10.00',
            'rechargeMoney' => '8.00',
        ]);

        $ret = wei()->transaction->pay(-7, [], $user);
        $this->assertEquals(1, $ret['code'], $ret['message']);

        $this->assertEquals(3, $user['money']);
    }

    public function testPaymentUsingAllRechargeMoney()
    {
        $user = wei()->user()->save([
            'money' => '10.00',
            'rechargeMoney' => '8.00',
        ]);

        $ret = wei()->transaction->pay(-9, [], $user);
        $this->assertEquals(1, $ret['code'], $ret['message']);

        $this->assertEquals(1, $user['money']);
    }

    public function testRefund()
    {
        $user = wei()->user()->save([
            'money' => '10.00',
            'rechargeMoney' => '8.00',
        ]);
        $ret = wei()->transaction->refund(10, [], $user);
        $this->assertEquals(1, $ret['code'], $ret['message']);
        $this->assertEquals('20.00', $user['money']);
        $this->assertEquals('18.00', $user['rechargeMoney']);
    }

    public function testChangeMoneyWithInvalidMoney()
    {
        $ret = wei()->transaction->refund('－68.99');
        $this->assertRetErr($ret, -1, '金额必须是有效的数字');
    }
}
