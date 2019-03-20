<?php

$view->layout();
?>

<ul class="list list-indented">
  <li class="list-item list-description">
    <h4 class="list-title">状态：</h4>

    <div class="list-detail">
      <?= $transaction->getStatusName() ?>
    </div>
  </li>
  <li class="list-item list-description">
    <h4 class="list-title">提款金额：</h4>

    <div class="list-detail">
      <?= $transaction->getAbsAmount() ?>元
    </div>
  </li>
  <li class="list-item list-description">
    <h4 class="list-title">申请时间：</h4>
    <div class="list-detail">
      <?= substr($transaction['createTime'], 0, 16) ?>
    </div>
  </li>
  <li class="list-item list-description">
    <h4 class="list-title">账户类型：</h4>
    <div class="list-detail">
      <?= $transaction->getAccountTypeName() ?>
    </div>
  </li>
  <li class="list-item list-description">
    <h4 class="list-title">审核结果：</h4>
    <div class="list-detail">
      <?= $transaction->getAuditName() ?>
    </div>
  </li>
  <?php if ($transaction['passed']) : ?>
    <li class="list-item list-description">
      <h4 class="list-title">转账时间：</h4>
      <div class="list-detail">
        <?= substr($transaction['passTime'], 0, 16) ?>
      </div>
    </li>
  <?php endif ?>
  <li class="list-item list-description">
    <h4 class="list-title">备注：</h4>
    <div class="list-detail">
      <?= $transaction['note'] ?: '无' ?>
    </div>
  </li>
</ul>
