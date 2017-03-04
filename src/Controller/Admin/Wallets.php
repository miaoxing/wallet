<?php

namespace Miaoxing\Wallet\Controller\admin;

class Wallets extends \miaoxing\plugin\BaseController
{
    protected $controllerName = '钱包管理';

    protected $actionPermissions = [
        'index' => '列表',
    ];

    public function indexAction($req)
    {
        switch ($req['_format']) {
            case 'json':
                $users = wei()->user();

                // 分页
                $users->limit($req['rows'])->page($req['page']);

                // 排序
                $sort = $req['sort'] ?: 'id';
                $order = $req['order'] == 'asc' ? 'ASC' : 'DESC';
                $users->orderBy($sort, $order);

                if ($req['name'] || $req['contact']) {
                    $users->select('DISTINCT user.*')
                        ->leftJoin('address', 'user.id = address.userId');
                }

                if ($req['name']) {
                    $users->andWhere('address.name LIKE ?', '%' . $req['name'] . '%');
                }

                if ($req['contact']) {
                    $users->andWhere('address.contact LIKE ?', '%' . $req['contact'] . '%');
                }

                if ($req['nickName']) {
                    $users->andWhere('nickName LIKE ?', '%' . $req['nickName'] . '%');
                }

                $data = [];
                foreach ($users as $user) {
                    $data[] = $user->toArray();
                }

                return $this->suc([
                    'data' => $data,
                    'page' => (int) $req['page'],
                    'rows' => (int) $req['rows'],
                    'records' => $users->count(),
                ]);

            default:
                return get_defined_vars();
        }
    }
}
