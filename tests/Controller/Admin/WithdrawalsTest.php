<?php

namespace MiaoxingTest\Wallet\Controller\Admin;

class WithdrawalsTest extends \Miaoxing\Plugin\Test\BaseControllerTestCase
{
    public function testAuditNotPass()
    {
        // 创建用户并登录
        $user = wei()->user()->save([
            'admin' => true,
            'money' => 100,
        ]);

        wei()->curUser->loginById($user['id']);

        // 申请退款
        $ret = wei()->tester()
            ->controller('withdrawals')
            ->action('create')
            ->request([
                'accountType' => 'wechatPayV3',
                'money' => 50,
            ])
            ->json()
            ->exec()
            ->response();

        $this->assertEquals(1, $ret['code'], $ret['message']);

        $user->reload();
        $this->assertEquals(50, $user['money']);

        // 后台审核不通过
        $ret = wei()->tester()
            ->controller('admin/withdrawals')
            ->action('audit')
            ->request([
                'id' => $ret['id'],
                'audit' => 2,
                'note' => '不通过',
            ])
            ->json()
            ->exec()
            ->response();

        $this->assertEquals(1, $ret['code'], $ret['message']);

        $user->reload();
        $this->assertEquals(100, $user['money']);
    }
}
