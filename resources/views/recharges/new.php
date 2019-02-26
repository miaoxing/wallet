<?php $view->layout() ?>

<?= $block->css() ?>
<link rel="stylesheet" href="<?= $asset('plugins/wallet/css/recharges.css') ?>">
<?= $block->end() ?>

<form class="js-recharge-form form recharge-form" action="<?= $url('recharges/create') ?>" method="post">
  <ul class="recharge-list list-unstyled mt-2">
    <?php foreach ($recharges as $i => $recharge) : ?>
      <li class="js-recharge-item recharge-item">
        <span class="js-recharge-btn recharge-btn d-flex flex-center flex-column text-active-primary border-active-primary
          <?= $i == 0 ? 'active' : '' ?>"
          data-amount="<?= $recharge['topUp'] ?>">
          <?= $recharge['topUp'] ?>元
          <span class="recharge-bonus">送<?= $recharge['bonus'] ?>元</span>
        </span>
      </li>
    <?php endforeach ?>
  </ul>

  <?php require $view->getFile('@payment/payments/select.php') ?>

  <input class="js-amount" type="hidden" name="amount" value="<?= $recharges[0]['topUp'] ?>">

  <div class="form-group form-footer">
    <button type="submit" class="btn btn-primary btn-block">立即支付</button>
  </div>
</form>

<?= $block->js() ?>
<script>
  $('.js-recharge-btn').click(function () {
    $('.js-recharge-btn').removeClass('active');
    $(this).addClass('active');
    $('.js-amount').val($(this).data('amount'));
  });

  require(['plugins/payment/js/payments', 'plugins/app/libs/jquery-form/jquery.form'], function (payments) {
    $('.js-recharge-form').ajaxForm({
      dataType: 'json',
      loading: true,
      beforeSubmit: function () {
        return payments.beforeSubmit();
      },
      success: function (ret) {
        payments.success(ret);
      }
    });
  });
</script>
<?= $block->end() ?>
