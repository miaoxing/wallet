<?php

namespace MiaoxingTest\Wallet\Controller;

class WithdrawalsTest extends \Miaoxing\Plugin\Test\BaseControllerTestCase
{
    public function testCreateAndShow()
    {
        // 1. 创建用户并登录
        $user = wei()->user()->save([
            'money' => '100.00',
        ]);
        wei()->curUser->loginById($user['id']);

        // 2. 调用提现接口
        $ret = wei()->tester()
            ->controller('withdrawals')
            ->action('create')
            ->request([
                'accountType' => 'wechatPayV3',
                'money' => '90.00',
            ])
            ->json()
            ->exec()
            ->response();
        $this->assertEquals(1, $ret['code'], $ret['message']);

        // 3. 显示提现金额
        $ret = wei()->tester()
            ->controller('withdrawals')
            ->action('show')
            ->request([
                'id' => $ret['id'],
            ])
            ->exec()
            ->response();

        $this->assertContains('90.00元', $ret);
        $this->assertNotContains('-90.00元', $ret);
        $this->assertContains('待审核', $ret);
    }

    public function testGetAvailableMoney()
    {
        // 1. 创建用户并登录
        $user = wei()->user()->save();

        // 2. 为用户转账和充值各100元
        wei()->transaction->transfer(100, [], $user);
        wei()->transaction->recharge(100, [], $user);

        $this->assertEquals(200, $user['money']);

        // 充值的不能提现,所以只有100元
        $this->assertEquals(100, wei()->transaction->getAvailableMoney($user));

        // 3. 提现了50元
        wei()->transaction->withdraw(-50, [], $user);

        // 余额有150元
        $this->assertEquals(150, $user['money']);

        // 剩下50元可提
        $this->assertEquals(50, wei()->transaction->getAvailableMoney($user), 'User is ' . $user['id']);
    }
}
