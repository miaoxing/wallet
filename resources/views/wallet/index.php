<?php $view->layout() ?>

<?= $block->css() ?>
<link rel="stylesheet" href="<?= $asset('plugins/wallet/css/wallet.css') ?>">
<?= $block->end() ?>

<div class="bg-white border-bottom wallet-balance-container">
  <i class="wallet-icon wallet-icon-balance bg-primary">&#xe60c;</i>
  账户余额
  <span class="text-primary wallet-balance"><?= $curUser['money'] ?>元</span>
</div>

<ul class="list list-indented">
  <?php if ($setting('transaction.enableRecharge')) : ?>
    <li class="list-item-link">
      <a class="list-item list-has-arrow" href="<?= wei()->order->payUrl('recharges/new') ?>">
        <div class="list-col wallet-icon-col">
          <i class="wallet-icon wallet-icon-list">&#xe608;</i>
        </div>
        <div class="list-col list-middle">
          <h4 class="list-title">
            充值
          </h4>
        </div>
        <i class="bm-angle-right list-arrow"></i>
      </a>
    </li>
  <?php endif ?>
  <?php if ($setting('transaction.enableWithdrawal')) : ?>
    <li class="list-item-link">
      <a class="list-item list-has-arrow" href="<?= $url('withdrawals/new') ?>">
        <div class="list-col wallet-icon-col">
          <i class="wallet-icon wallet-icon-list">&#xe607;</i>
        </div>
        <div class="list-col list-middle">
          <h4 class="list-title">
            提现
          </h4>
        </div>
        <i class="bm-angle-right list-arrow"></i>
      </a>
    </li>
    <li class="list-item-link">
      <a class="list-item list-has-arrow" href="<?= $url('withdrawals') ?>">
        <div class="list-col wallet-icon-col">
          <i class="wallet-icon wallet-icon-list">&#xe606;</i>
        </div>
        <div class="list-col list-middle">
          <h4 class="list-title">
            提现记录
          </h4>
        </div>
        <i class="bm-angle-right list-arrow"></i>
      </a>
    </li>
  <?php endif ?>
  <li class="list-item-link">
    <a class="list-item list-has-arrow" href="<?= $url('transactions') ?>">
      <div class="list-col wallet-icon-col">
        <i class="wallet-icon wallet-icon-list">&#xe615;</i>
      </div>
      <div class="list-col list-middle">
        <h4 class="list-title">
          余额明细
        </h4>
      </div>
      <i class="bm-angle-right list-arrow"></i>
    </a>
  </li>
</ul>

<p class="m-2 mt-4">
  <span class="wallet-icon">&#xe602;</span>
  什么是余额?
  <br>
  <span class="text-muted">余额是您在我们系统的一个账户，在支付订单时，您可以使用余额抵消部分付款金额。</span>
</p>
