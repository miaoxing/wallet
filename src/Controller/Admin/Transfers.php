<?php

namespace Miaoxing\Wallet\Controller\Admin;

class Transfers extends \miaoxing\plugin\BaseController
{
    /**
     * 最大的批量转账数量
     */
    const MAX_TRANSFER_COUNT = 100;

    /**
     * 批量转账
     * @param $req
     * @return $this
     */
    public function uploadAction($req)
    {
        //1.限制转账数量
        $count = count((array) $req['data']);
        if ($count > static::MAX_TRANSFER_COUNT) {
            return $this->err('批量转账最大转账数量不能超过100条');
        }

        foreach ((array) $req['data'] as $key => $transfer) {
            //2.1 验证是否存在该用户
            $user = wei()->user()->findById($transfer[0]);
            if (!$user) {
                return $this->err('不存在用户ID为' . $transfer[0] . '的用户！');
            }
            //2.2 获取账户类型
            $accountType = '';
            foreach (wei()->transaction->getAccountTypeNames() as $type => $name) {
                if ($name == $transfer[3]) {
                    $accountType = $type;
                }
            }
            if (!$accountType) {
                return $this->err('不存在该账户类型：' . $transfer[3]);
            }
            //2.3 转账金额金额验证
            $amount = $transfer[4];
            if ($amount <= 0) {
                return $this->err('转账金额不能小于等于0！');
            }

            $data = [
                'accountType' => $accountType,
                'note' => $transfer[5],
            ];

            $ret = wei()->transaction->transfer($amount, $data, $user);
        }

        return $this->ret($ret);
    }
}
