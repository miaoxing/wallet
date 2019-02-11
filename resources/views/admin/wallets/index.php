<?php $view->layout() ?>

<div class="page-header">
  <div class="pull-right">
    <form id="transfer-upload-form" class="form-horizontal" method="post" role="form">
      <div class="excel-fileinput fileinput fileinput-new" data-provides="fileinput" title="最多只能批量导入100条">
        <span class="btn btn-default btn-file" >
          <span class="fileinput-new" >批量转账</span>
            <input type="file" name="file">
        </span>
        <a href="<?= $asset('plugins/wallet/files/批量转账模板.xls') ?>" class="btn btn-link">下载模板</a>
      </div>
    </form>
  </div>
  <h1>
    用户钱包管理
  </h1>
</div>

<div class="row">
  <div class="col-12">
    <div class="table-responsive">
      <div class="well form-well">
        <form class="js-wallet-form form-inline" role="form">
          <div class="form-group">
            <label for="name">姓名</label>
            <input type="text" class="form-control" name="name" id="name">
          </div>

          <div class="form-group">
            <label for="contact">手机号码</label>
            <input type="text" class="form-control" name="mobile" id="mobile">
          </div>

          <div class="form-group">
            <label for="nick-name">昵称</label>
            <input type="text" class="form-control" name="nickName" id="nick-name">
          </div>
        </form>
      </div>

      <table class="js-wallet-table record-table table table-bordered table-hover">
        <thead>
        <tr>
          <th>用户</th>
          <th>余额</th>
          <th>充值余额</th>
          <th class="t-7">操作</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
    <!-- /.table-responsive -->
    <!-- PAGE CONTENT ENDS -->
  </div>
  <!-- /col -->
</div>
<!-- /row -->

<?php require $this->getFile('@user/admin/user/richInfo.php') ?>

<script type="text/html" id="actionColTpl">
  <a href="<%= $.url('admin/transactions', {userId: id}) %>">余额明细</a>
  <a href="<%= $.url('admin/transactions/new', {userId: id}) %>">更改</a>
</script>

<?= $block->js() ?>
<script>
  require(['dataTable', 'form', 'jquery-deparam','plugins/excel/js/excel'], function () {
    var recordTable = $('.js-wallet-table').dataTable({
      sorting : [[0, 'desc']],
      ajax: {
        url: $.queryUrl('admin/wallets.json')
      },
      columns: [
        {
          data: 'id',
          render: function (data, type, full) {
            return template.render('user-info-tpl', full);
          }
        },
        {
          data: 'money',
          sortable: true
        },
        {
          data: 'rechargeMoney',
          sortable: true
        },
        {
          data: 'id',
          render: function (data, type, full) {
            return template.render('actionColTpl', full);
          }
        }
      ]
    });

    $('.js-wallet-form').update(function () {
      recordTable.reload($(this).serialize(), false);
    });

    //批量转账
    $('.excel-fileinput').on('change.bs.fileinput', function (event) {
      $('#transfer-upload-form').uploadFile('admin/transfers/upload', 6, function(result){
        if(result.code == 1) {
          window.location.href = '<?=wei()->url('admin/wallets')?>';
        } else {
          alert(result.message);
        }
      });
      $(this).fileinput('clear');
    });
  });
</script>
<?= $block->end() ?>
