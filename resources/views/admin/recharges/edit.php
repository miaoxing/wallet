<?php $view->layout() ?>

<div class="page-header">
  <h1>
    充值管理
    <small>
      <i class="fa fa-angle-double-right"></i>
      添加充值模板
    </small>
  </h1>
</div>

<div class="row">
  <div class="col-xs-12">
    <!-- PAGE CONTENT BEGINS -->
    <form id="recharge-form" class="form-horizontal" method="post" role="form">

      <div class="form-group">
        <label class="col-lg-2 control-label" for="recordTable">
          <span class="text-warning">*</span>
          类型
        </label>

        <div class="col-lg-4">
            充值
        </div>
      </div>

      <div class="form-group">
        <label class="col-lg-2 control-label" for="top-up">
          <span class="text-warning">*</span>
          充值金额
        </label>

        <div class="col-lg-4">
          <input type="text" class="form-control" name="topUp" id="top-up" data-rule-required="true">
        </div>
      </div>

      <div class="form-group">
        <label class="col-lg-2 control-label" for="bonus">
          <span class="text-warning">*</span>
          奖励金额
        </label>

        <div class="col-lg-4">
          <input type="text" class="form-control" name="bonus" id="bonus" data-rule-required="true">
        </div>
      </div>

      <input type="hidden" name="id" id="id" value="<?= $id ?>"/>

      <div class="clearfix form-actions form-group">
        <div class="col-lg-offset-2">
          <button class="btn btn-primary" type="submit">
            <i class="fa fa-check bigger-110"></i>
            提交
          </button>
          &nbsp; &nbsp; &nbsp;
          <a class="btn btn-default" href="<?= $url('admin/recharges/index') ?>">
            <i class="fa fa-undo bigger-110"></i>
            返回列表
          </a>
        </div>
      </div>
    </form>
  </div>
  <!-- PAGE CONTENT ENDS -->
</div><!-- /.col -->
<!-- /.row -->

<?= $block('js') ?>
<script>
  require(['form', 'validator', 'assets/spectrum'], function () {
    $('#recharge-form')
      .loadJSON(<?= $recharge ?>)
      .ajaxForm({
        url: '<?= $url('admin/recharges/update?id='.$id) ?>',
        dataType: 'json',
        beforeSubmit: function (arr, $form, options) {
          return $form.valid();
        },
        success: function (result) {
          $.msg(result, function () {
            if (result.code > 0) {
              window.location = $.url('admin/recharges/index');
            }
          });
        }
      })
      .validate();
  });
</script>
<?= $block->end() ?>
