<?php $view->layout() ?>

<div class="page-header">
  <a class="btn pull-right btn-success" href="<?= $url('admin/recharges/new') ?>">添加充值模板</a>
  <h1>
    充值管理
    <small>
      <i class="fa fa-angle-double-right"></i>
      充值
    </small>
  </h1>
</div>

<div class="row">
  <div class="col-xs-12">
    <!-- PAGE CONTENT BEGINS -->
    <div class="table-responsive">
      <table id="record-table" class="record-table table table-bordered table-hover">
        <thead>
        <tr>
          <th style="width: 220px">类型</th>
          <th>充值金额</th>
          <th style="width: 220px">奖励</th>
          <th style="width: 220px">修改时间</th>
          <th style="width: 220px">操作</th>
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

<script id="table-actions" type="text/html">
  <div class="action-buttons">
    <a href="<%= $.url('admin/recharges/edit', {id: id}) %>" title="编辑">
      <i class="fa fa-edit bigger-130"></i>
    </a>
    <a class="text-danger delete-record" href="javascript:;" data-href="<%= $.url('admin/recharges/delete', {id: id}) %>" title="删除">
      <i class="fa fa-trash-o bigger-130"></i>
    </a>
  </div>
</script>

<?= $block('js') ?>
<script>
  require(['dataTable', 'form', 'jquery-deparam'], function () {
    var recordTable = $('#record-table').dataTable({
      ajax: {
        url: $.queryUrl('admin/recharges.json')
      },
      columns: [
        {
          data: 'type'
        },
        {
          data: 'topUp'
        },
        {
          data: 'bonus'
        },
        {
          data: 'updateTime'
        },
        {
          data: 'id',
          render: function (data, type, full) {
            return template.render('table-actions', full)
          }
        }
      ]
    });

    // 点击删除标签
    recordTable.on('click', '.delete-record', function () {
      var link = $(this);
      $.confirm('删除后将无法还原,确认删除?', function () {
        $.post(link.data('href'), function (result) {
          $.msg(result);
          recordTable.reload();
        }, 'json');
      });
    });
  });
</script>
<?= $block->end() ?>
