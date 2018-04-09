<?php

$view->layout();
$wei->page->addAsset('plugins/admin/css/filter.css');
?>

<div class="page-header">
  <h1>
    余额明细
  </h1>
</div>

<div class="row">
  <div class="col-xs-12">
    <div class="table-responsive">
      <form class="js-transaction-form search-form well form-horizontal">
        <div class="form-group form-group-sm">
          <label class="col-md-1 control-label" for="type">类型：</label>
          <div class="col-md-3">
            <select class="js-type form-control" name="type" id="type">
              <option value="-1" selected>全部</option>
            </select>
          </div>
        </div>

        <div class="form-group form-group-sm">
          <label class="col-md-1 control-label" for="name">姓名：</label>
          <div class="col-md-3">
            <input type="text" class="form-control" id="name" name="name">
          </div>
        </div>

        <div class="form-group form-group-sm">
          <label for="create-time-range" class="col-sm-1 control-label">创建时间：</label>
          <div class="col-sm-3">
            <input type="text" name="createTimeRange" id="create-time-range" class="js-time-range form-control">
          </div>
        </div>

        <div class="form-group form-group-sm">
          <label for="search" class="col-sm-1 control-label">操作说明：</label>
          <div class="col-sm-3">
            <input type="text" value="<?= $e($req['search']) ?>" class="form-control" name="search">
          </div>
        </div>

        <?php $event->trigger('adminTransactionForm') ?>
      </form>

      <table class="js-transaction-table record-table table table-bordered table-hover">
      </table>
    </div>
  </div>
</div>

<?php require $this->getFile('user:admin/user/richInfo.php') ?>

<?= $block->js() ?>
<script>
  require(['form', 'dataTable', 'jquery-deparam', 'daterangepicker'], function (form) {
    form.toOptions($('.js-type'), <?= json_encode(wei()->transaction->getConsts('type')) ?>, 'id', 'text');

    var $table = $('.js-transaction-table').dataTable({
      sorting: [[0, 'desc']],
      ajax: {
        url: $.queryUrl('admin/transactions.json')
      },
      columns: [
        {
          data: 'id',
          title: '用户',
          sClass: 't-12',
          render: function (data, type, full) {
            return template.render('user-info-tpl', full.user);
          }
        },
        <?php $event->trigger('adminTransactionColumns') ?>
        {
          data: 'typeName',
          title: '类型',
        },
        {
          data: 'amount',
          title: '金额',
          sortable: true
        },
        {
          data: 'balance',
          title: '余额'
        },
        {
          data: 'accountTypeName',
          title: '账户类型',
          render: function (data) {
            return data || '-';
          }
        },
        {
          data: 'createTime',
          title: '创建时间',
        },
        {
          data: 'createUserName',
          title: '操作人',
          render: function (data) {
            return data || '-';
          }
        },
        {
          data: 'note',
          title: '操作说明',
          sClass: 't-8',
          render: function (data) {
            return data || '-';
          }
        }
      ]
    });

    // 筛选
    $('.js-transaction-form').update(function () {
      $table.reload($(this).serialize(), false);
    });

    $('.js-time-range').daterangepicker({}, function (start, end) {
      this.element.trigger('change');
    });
  });
</script>
<?= $block->end() ?>
