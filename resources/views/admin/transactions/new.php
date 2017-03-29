<?php $view->layout() ?>

<div class="page-header">
  <h1>
    交易管理
    <small>
      <i class="fa fa-angle-double-right"></i>
      用户余额增减
    </small>
  </h1>
</div>

<div class="row">
  <div class="col-xs-12">
    <form class="form-horizontal js-transaction-form" action="<?= $url('admin/transactions/create') ?>" role="form"
      method="post">
      <div class="form-group">
        <label class="col-lg-2 control-label">
          用户
        </label>

        <div class="col-lg-4">
          <p class="form-control-static"><?= $selectedUser->getNickName() ?></p>
        </div>
      </div>

      <div class="form-group">
        <label class="col-lg-2 control-label">
          余额
        </label>

        <div class="col-lg-4">
          <p class="form-control-static"><?= $selectedUser['money'] ?>元</p>
        </div>
      </div>

      <div class="form-group">
        <label class="col-lg-2 control-label" for="type">
          <span class="text-warning">*</span>
          类型
        </label>

        <div class="col-lg-4">
          <select class="js-type form-control" id="type" name="type" data-rule-required="true">
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="col-lg-2 control-label" for="amount">
          <span class="text-warning">*</span>
          金额
        </label>

        <div class="col-lg-4">
          <div class="input-group">
            <input type="text" class="form-control" id="amount" name="amount" data-rule-required="true">
            <span class="input-group-addon">元</span>
          </div>
        </div>

        <label class="col-lg-6 help-text" for="amount">
          收入请输入大于0的数字,支出请输入小于0的数字
        </label>
      </div>

      <div class="form-group">
        <label class="col-lg-2 control-label" for="account-type">
          账户类型
        </label>

        <div class="col-lg-4">
          <select class="form-control" id="account-type" name="accountType">
            <option value="">无</option>
            <?php foreach (wei()->transaction->getAccountTypeNames() as $type => $name) : ?>
              <option value="<?= $type ?>"><?= $name ?></option>
            <?php endforeach ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="col-lg-2 control-label" for="note">
          <span class="text-warning">*</span>
          操作说明
        </label>

        <div class="col-lg-4">
          <textarea placeholder="如线下充值转线上,退款" class="form-control" rows="2" name="note" id="note"
            data-rule-required="true"></textarea>
        </div>
      </div>

      <input type="hidden" name="userId" id="user-id" value="<?= $selectedUser['id'] ?>">

      <div class="clearfix form-actions form-group">
        <div class="col-lg-offset-2">
          <button class="btn btn-primary" type="submit">
            <i class="fa fa-check bigger-110"></i>
            提交
          </button>
          &nbsp; &nbsp; &nbsp;
          <a class="btn btn-default" href="<?= $url('admin/wallets') ?>">
            <i class="fa fa-undo bigger-110"></i>
            返回列表
          </a>
        </div>
      </div>
    </form>
  </div>
</div>

<?= $block('js') ?>
<script>
  require(['form', 'validator'], function (form) {
    form.toOptions($('.js-type'), <?= json_encode(wei()->transaction->getConstants('type')) ?>, 'id', 'text');

    $('.js-transaction-form')
      .ajaxForm({
        dataType: 'json',
        beforeSubmit: function (arr, $form, options) {
          return $form.valid();
        },
        success: function (ret) {
          $.msg(ret, function () {
            if (ret.code == 1) {
              window.location = $.url('admin/wallets');
            }
          });
        }
      })
      .validate();
  });
</script>
<?= $block->end() ?>

