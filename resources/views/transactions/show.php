<?php

$view->layout();
?>

<ul class="list list-indented">
  <li class="list-item list-description list-description">
    <h4 class="list-title">
      金额
    </h4>
    <div class="list-detail">
      <?= $transaction['amount'] < 0 ? '-' : '' ?>¥<?= $transaction->getAbsAmount() ?>
    </div>
  </li>
  <li class="list-item list-description">
    <div class="list-title">类型：</div>
    <div class="list-detail">
      <?= $transaction->getTypeText() ?>
    </div>
  </li>
  <li class="list-item list-description">
    <div class="list-title">备注：</div>
    <div class="list-detail">
      <?= $transaction['note'] ?: '无' ?>
    </div>
  </li>
  <li class="list-item list-description">
    <div class="list-title">创建时间：</div>
    <div class="list-detail">
      <?= $transaction['createTime'] ?>
    </div>
  </li>
</ul>
