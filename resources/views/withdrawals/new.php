<?php $view->layout() ?>

<form class="form js-withdrawal-form" method="post" action="<?= $url('withdrawals/create') ?>">
  <div class="form-group">
    <label class="control-label">
      可提款金额
    </label>

    <div class="col-control">
      <input type="text" class="form-control" readonly value="<?= $availableMoney ?>元">
      <input type="hidden" name="availableMoney" class="form-control js-can-get-money" value="<?= $availableMoney ?>">
    </div>
  </div>
  <div class="form-group">
    <label for="account-type" class="control-label">账户类型</label>

    <div class="col-control">
      <select class="form-control" id="account-type" name="accountType">
        <?php foreach (wei()->transaction->getAccountTypeNames() as $type => $name) : ?>
          <option value="<?= $type ?>"><?= $name ?></option>
        <?php endforeach ?>
      </select>
      <a href="javascript:" class="bm-angle-right form-control-feedback"></a>
    </div>
  </div>
  <div class="form-group hide">
    <label for="idCard" class="control-label">账号</label>

    <div class="col-control">
      <input type="text" class="form-control" id="account" name="account" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="money" class="control-label">提款金额</label>

    <div class="col-control">
      <div class="input-group">
        <input type="text" class="js-money form-control" id="money" name="money" value="">
        <span class="input-group-append border-left">
          <span class="input-group-text">元</span>
          <button type="button" class="btn btn-outline-primary js-get-all-money border-left">
            输入全部
          </button>
        </span>
      </div>
    </div>
  </div>
  <div class="form-group form-footer">
    <button type="submit" class="btn btn-primary btn-block">提交</button>
  </div>
</form>

<p class="text-xs text-muted m-2">
  注意：充值金额不能提现。
</p>

<?= $block->js() ?>
<script>
  require(['plugins/product/js/numeric', 'plugins/app/libs/jquery-form/jquery.form'], function (numeric) {
    $('.js-money').change(function () {
      $(this).val(numeric.toFloat($(this).val()).toFixed(2));
    });

    $('.js-withdrawal-form').ajaxForm({
      dataType: 'json',
      success: function (ret) {
        $.msg(ret, function () {
          if (ret.code == 1) {
            window.location.href = $.url('withdrawals/%s', ret.id);
          }
        });
      }
    });

    $('.js-get-all-money').click(function () {
      $('.js-money').val($('.js-can-get-money').val());
    });
  });
</script>
<?= $block->end() ?>
