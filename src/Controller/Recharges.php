<?php

namespace Miaoxing\Wallet\Controller;

use Miaoxing\Wallet\Plugin;

class Recharges extends \Miaoxing\Plugin\BaseController
{
    public function newAction()
    {
        $recharges = wei()->transaction->getRechargeRules();
        if (!$recharges) {
            return $this->err('未开启充值功能,请先配置充值规则');
        }

        $payments = wei()->payment()
            ->notDeleted()
            ->andWhere("id != 'cashOnDelivery'")
            ->enabled()
            ->desc('sort')
            ->findAll();

        $this->page->setTitle('充值');

        return get_defined_vars();
    }

    public function createAction($req)
    {
        // 1. 检查金额是否正确
        $recharges = wei()->transaction->getRechargeRules();

        $found = false;
        foreach ($recharges as $recharge) {
            if ($recharge['topUp'] === $req['amount']) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            return $this->err('请选择正确的金额');
        }

        // 2. 查找或创建充值专属商品
        $product = wei()->product()->findOrInit(['name' => '充值商品']);
        if ($product->isNew()) {
            $ret = $product->create([
                'quantity' => 1000000000,
                'price' => $req['amount'],
                'images' => [
                    '/plugins/wallet/images/recharge.png',
                ],
                'visible' => false,
                'detail' => '请勿更改或删除',
            ]);
            if ($ret['code'] !== 1) {
                return $this->ret($ret);
            }

            $firstSku = $product->getFirstSku();
        } else {
            $firstSku = $product->getFirstSku();
            $firstSku->save([
                'price' => $req['amount'],
            ]);
        }

        // 3. 根据商品创建创建订单
        $order = wei()->order();
        $ret = $order->createFromSkus([[
            'skuId' => $firstSku['id'],
            'quantity' => 1,
        ]], [
            'payType' => $req['payType'],
            'categoryId' => Plugin::RECHARGE_ORDER_CATEGORY_ID,
        ], [
            'requireAddress' => false,
        ]);
        if ($ret['code'] !== 1) {
            return $this->ret($ret);
        }

        // 4. 返回支付信息
        return $this->ret($ret);
    }
}
