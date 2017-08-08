<?php

namespace Miaoxing\Wallet\Controller\Admin;

class Recharges extends \miaoxing\plugin\BaseController
{
    public function indexAction($req)
    {
        switch ($req['_format']) {
            case 'json':
                $recharges = (array) json_decode(wei()->setting('wallet.recharge'), true);
                $data = [];
                $id = 0;
                foreach ($recharges as $recharge) {
                    $data[] = $recharge + [
                            'id' => $id,
                        ];
                    ++$id;
                }

                return $this->suc([
                    'data' => $data,
                    'page' => (int) $req['page'],
                    'rows' => (int) $req['rows'],
                    'records' => count($recharges),
                ]);

            default:
                return get_defined_vars();
        }
    }

    public function newAction($req)
    {
        return $this->editAction($req);
    }

    public function editAction($req)
    {
        $recharges = json_decode(wei()->setting('wallet.recharge'), true);
        $id = isset($req['id']) ? $req['id'] : count($recharges);
        $recharge = json_encode($recharges[$id], JSON_FORCE_OBJECT);
        if ($recharge == 'null') {
            $recharge = '{}';
        }

        return get_defined_vars();
    }

    public function updateAction($req)
    {
        $recharges = (array) json_decode(wei()->setting('wallet.recharge'), true);

        foreach ($recharges as $id => $recharge) {
            if ($req['id'] != $id && $recharge['topUp'] == $req['topUp']) {
                return $this->err('已存在相同的充值金额，请检查更新');
            }
        }

        $recharges[$req['id']]['topUp'] = $req['topUp'];
        $recharges[$req['id']]['bonus'] = $req['bonus'];
        $recharges[$req['id']]['updateTime'] = date('Y-m-d H:i:s', time());
        $rechargesJson = json_encode($recharges, JSON_FORCE_OBJECT);
        wei()->setting->setValue('wallet.recharge', $rechargesJson);

        return $this->suc('更新成功');
    }

    public function deleteAction($req)
    {
        $recharges = (array) json_decode(wei()->setting('wallet.recharge'), true);
        $data = [];
        $id = 0;
        foreach ($recharges as $recharge) {
            if ($id == $req['id']) {
                ++$id;
                continue;
            }
            $data[] = $recharge;
            ++$id;
        }
        $rechargesJson = json_encode($data, JSON_FORCE_OBJECT);
        wei()->setting->setValue('wallet.recharge', $rechargesJson);

        return $this->suc();
    }
}
