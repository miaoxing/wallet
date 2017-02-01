<?php $view->layout() ?>

<div class="page-header">
  <h1>
    余额明细
  </h1>
</div>

<div class="row">
  <div class="col-xs-12">
    <div class="table-responsive">
      <div class="well form-well">
        <form class="form-inline" id="search-form" role="form">
          <div class="form-group">
            <select class="js-type form-control" name="type" id="type">
              <option value="-1" selected>全部</option>
            </select>
          </div>

          <div class="form-group">
            <input type="text" value="<?= $e($req['search']) ?>" class="form-control" name="search" placeholder="请输入备注搜索"
                   style="width: 330px">
          </div>
        </form>
      </div>

      <table class="js-transaction-table record-table table table-bordered table-hover">
        <thead>
        <tr>
          <th style="width: 200px">用户</th>
          <th>类型</th>
          <th>金额</th>
          <th>余额</th>
          <th>账户类型</th>
          <th style="width: 140px">创建时间</th>
          <th>操作人</th>
          <th style="width: 120px">操作说明</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require $this->getFile('user:admin/user/richInfo.php') ?>

<?= $block('js') ?>
<script>
  require(['form', 'dataTable', 'jquery-deparam'], function (form) {
    form.toOptions($('.js-type'), <?= json_encode(wei()->transaction->getConstants('type')) ?>, 'id', 'text');

    var recordTable = $('.js-transaction-table').dataTable({
      sorting : [[0, 'desc']],
      ajax: {
        url: $.queryUrl('admin/transactions.json')
      },
      columns: [
        {
          data: 'id',
          render: function (data, type, full) {
            return template.render('user-info-tpl', full.user);
          }
        },
        {
          data: 'typeName'
        },
        {
          data: 'amount',
          sortable: true
        },
        {
          data: 'balance'
        },
        {
          data: 'accountTypeName',
          render: function (data) {
            return data || '-';
          }
        },
        {
          data: 'createTime'
        },
        {
          data: 'createUserName',
          render: function (data) {
            return data || '-';
          }
        },
        {
          data: 'note',
          render: function (data) {
            return data || '-';
          }
        }
      ]
    });

    // 筛选
    $('#search-form').update(function () {
      recordTable.reload($(this).serialize(), false);
    });
  });
</script>
<?= $block->end() ?>
