<?php $view->layout() ?>

<div class="page-header">
  <h1>
    功能设置
  </h1>
</div>

<div class="row">
  <div class="col-xs-12">
    <form action="<?= $url('admin/finance-settings/update') ?>" class="js-setting-form form-horizontal" method="post"
      role="form">
      <div class="form-group">
        <label class="col-lg-2 control-label" for="balance">
          <span class="text-warning">*</span>
          启用余额支付
        </label>

        <div class="col-lg-4">
          <label class="radio-inline">
            <input type="radio" class="js-balance" id="balance" name="settings[payments.balance]" value="1"> 是
          </label>
          <label class="radio-inline">
            <input type="radio" class="js-balance" name="settings[payments.balance]" value="0"> 否
          </label>
        </div>

        <label class="col-lg-4 help-text" for="balance">
          用户账户中有余额,才会在支付时显示余额支付
        </label>
      </div>

      <div class="form-group">
        <label class="col-lg-2 control-label" for="enable-recharge">
          <span class="text-warning">*</span>
          启用充值
        </label>

        <div class="col-lg-4">
          <label class="radio-inline">
            <input type="radio" class="js-enable-recharge" id="enable-recharge"
              name="settings[transaction.enableRecharge]" value="1"> 是
          </label>
          <label class="radio-inline">
            <input type="radio" class="js-enable-recharge" name="settings[transaction.enableRecharge]" value="0"> 否
          </label>
        </div>
      </div>

      <div class="form-group">
        <label class="col-lg-2 control-label" for="enable-withdrawal">
          <span class="text-warning">*</span>
          启用提现
        </label>

        <div class="col-lg-4">
          <label class="radio-inline">
            <input type="radio" class="js-enable-withdrawal" id="enable-withdrawal"
              name="settings[transaction.enableWithdrawal]" value="1"> 是
          </label>
          <label class="radio-inline">
            <input type="radio" class="js-enable-withdrawal" name="settings[transaction.enableWithdrawal]" value="0"> 否
          </label>
        </div>
      </div>

      <div class="clearfix form-actions form-group">
        <div class="col-lg-offset-2">
          <button class="btn btn-primary" type="submit">
            <i class="fa fa-check bigger-110"></i>
            提交
          </button>
        </div>
      </div>
    </form>
  </div>
  <!-- PAGE CONTENT ENDS -->
</div><!-- /.col -->
<!-- /.row -->

<?= $block('js') ?>
<script>
  require(['form', 'ueditor', 'validator'], function () {
    $('.js-setting-form')
      .loadJSON(<?= json_encode([
      'js-balance' => $setting('payments.balance') ?: '0',
      'js-enable-recharge' => $setting('transaction.enableRecharge') ?: '0',
      'js-enable-withdrawal' => $setting('transaction.enableWithdrawal') ?: '0',
]) ?>)
      .ajaxForm({
        dataType: 'json',
        beforeSubmit: function (arr, $form, options) {
          return $form.valid();
        }
      })
      .validate();
  });
</script>
<?= $block->end() ?>
