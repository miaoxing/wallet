<?php $view->layout() ?>

<ul class="list list-condensed list-borderless">
  <li class="list-item list-header">
    <h4 class="list-heading">
      提款
      <span class="pull-right small"><?= $transaction->getStatusName() ?></span>
    </h4>
  </li>
  <li class="list-item border-top">
    <label class="col-xs-4 list-label">提款金额：</label>

    <div class="col-xs-8 list-content">
      <?= $transaction->getAbsAmount() ?>元
    </div>
  </li>
  <li class="list-item">
    <div class="col-xs-4 list-label">申请时间：</div>
    <div class="col-xs-8 list-content">
      <?= substr($transaction['createTime'], 0, 16) ?>
    </div>
  </li>
  <li class="list-item">
    <div class="col-xs-4 list-label">账户类型：</div>
    <div class="col-xs-8 list-content">
      <?= $transaction->getAccountTypeName() ?>
    </div>
  </li>
  <li class="list-item">
    <div class="col-xs-4 list-label">审核结果：</div>
    <div class="col-xs-8 list-content">
      <?= $transaction->getAuditName() ?>
    </div>
  </li>
  <?php if ($transaction['passed']) : ?>
    <li class="list-item">
      <div class="col-xs-4 list-label">转账时间：</div>
      <div class="col-xs-8 list-content">
        <?= substr($transaction['passTime'], 0, 16) ?>
      </div>
    </li>
  <?php endif ?>
  <li class="list-item">
    <div class="col-xs-4 list-label">备注：</div>
    <div class="col-xs-8 list-content">
      <?= $transaction['note'] ?: '无' ?>
    </div>
  </li>
</ul>
