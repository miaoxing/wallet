<?php $view->layout() ?>

<ul class="list list-condensed list-borderless">
  <li class="list-item list-header">
    <h4 class="list-heading">
      金额
      <span class="pull-right m-r">
        <?= $transaction['amount'] < 0 ? '-' : '' ?>¥<?= $transaction->getAbsAmount() ?>
      </span>
    </h4>
  </li>
  <li class="list-item">
    <div class="col-xs-4 list-label">类型：</div>
    <div class="col-xs-8 list-content text-right">
      <?= $transaction->getTypeText() ?>
    </div>
  </li>
  <li class="list-item">
    <div class="col-xs-4 list-label">备注：</div>
    <div class="col-xs-8 list-content text-right">
      <?= $transaction['note'] ?: '无' ?>
    </div>
  </li>
  <li class="list-item">
    <div class="col-xs-4 list-label">创建时间：</div>
    <div class="col-xs-8 list-content text-right">
      <?= $transaction['createTime'] ?>
    </div>
  </li>
</ul>
