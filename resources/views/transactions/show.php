<?php $view->layout() ?>

<ul class="list list-condensed list-borderless">
  <li class="list-item list-header d-flex">
    <h4 class="list-item-title w-100">
      金额
      <span class="float-right">
        <?= $transaction['amount'] < 0 ? '-' : '' ?>¥<?= $transaction->getAbsAmount() ?>
      </span>
    </h4>
  </li>
  <li class="list-item d-flex">
    <div class="col-4 list-label">类型：</div>
    <div class="col-8 list-content text-right pr-0">
      <?= $transaction->getTypeText() ?>
    </div>
  </li>
  <li class="list-item d-flex">
    <div class="col-4 list-label">备注：</div>
    <div class="col-8 list-content text-right pr-0">
      <?= $transaction['note'] ?: '无' ?>
    </div>
  </li>
  <li class="list-item d-flex">
    <div class="col-4 list-label">创建时间：</div>
    <div class="col-8 list-content text-right pr-0">
      <?= $transaction['createTime'] ?>
    </div>
  </li>
</ul>
