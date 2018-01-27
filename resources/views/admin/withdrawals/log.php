<?php $view->layout() ?>

<!-- /.page-header -->
<div class="page-header">
  <h1>
    提款管理
    <small>
      <i class="fa fa-angle-double-right"></i>
      提款日志
    </small>
  </h1>
</div>

<div class="row">
  <div class="col-xs-12">
    <!-- PAGE CONTENT BEGINS -->
    <div class="table-responsive">
      <div class="well form-well">
        <form class="form-inline" id="search-form" role="form">
          <div class="form-group">
            <input type="text" class="form-control" name="search" placeholder="请输入用户名、手机号码搜索">
          </div>

          <div class="form-group">
            <input type="text" class="form-control" name="timeRange" id="time-range" placeholder="请选择时间范围">
          </div>

        </form>
      </div>
      <table id="record-table" class="record-table table table-bordered table-hover">
        <thead>
        <tr>
          <th>提款人</th>
          <th>操作时间</th>
          <th>金额</th>
          <th>操作</th>
          <th>操作人</th>
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

<?php require $this->getFile('user:admin/user/richInfo.php') ?>

<!-- 底部统计 -->
<script id="footerTpl" type="text/html">
  <tfoot class="order-summary">
  <tr>
    <td colspan="<%= colspan %>">
      总日志数: <strong><%= logCount %></strong>，
      总审核通过金额: ￥<strong><%= passedTotalMoney %></strong>
      总审核不通过金额: ￥<strong><%= noPassedtotalMoney %></strong>
      总完成金额: ￥<strong><%= finishtotalMoney %></strong>
    </td>
  </tr>
  </tfoot>
</script>


<?= $block->js() ?>
<script>
  require(['dataTable', 'jquery-deparam', 'form', 'daterangepicker'], function () {
    $('#search-form').loadParams().update(function () {
      recordTable.reload($(this).serialize(), false);
    });

    var recordTable = $('#record-table').dataTable({
      ajax: {
        url: $.queryUrl('admin/withdrawals/log.json')
      },
      columns: [
        {
          data: 'user',
          sClass: 'user-media-td',
          render: function (data, type, full) {
            return template.render('user-info-tpl', data);
          }
        },
        {
          data: 'createTime'
        },
        {
          data: 'transaction.amount'
        },
        {
          data: 'operation',
          sClass: 'text-center',
          render: function (data, type, full) {
            var table = {
              "1": "审核通过",
              "2": "审核不通过",
              "3": "完成交易"
            };

            return table[data];
          }
        },
        {
          data: 'updateUserName',
          sClass: 'text-center'
        }
      ],
      footerCallback: function (tfoot, data, start, end, display) {
        // 渲染底部商品数量等统计
        $(this).find('tfoot').remove();
        var colspan = $(this).find('thead th').length;
        var ptm = 0, nptm = 0, ftm = 0;
        $(this).append(template.render('footerTpl', {
          colspan: colspan,
          logCount: function () {
            var c = 0;
            for (var i in data) {
              c++;
            }
            return c;
          }(),
          passedTotalMoney: function () {
            for (var i in data) {
              if(data[i]["operation"] == 1){
                ptm += parseInt(data[i]["transaction"]['amount']);
              } else if(data[i]["operation"] == 2) {
                nptm += parseInt(data[i]["transaction"]['amount']);
              } else if(data[i]["operation"] == 3) {
                ftm += parseInt(data[i]["transaction"]['amount']);
              }
            }
            return ptm.toFixed(2);
          }(),
          noPassedtotalMoney: function () {
            return nptm.toFixed(2);
          }(),
          finishtotalMoney:function () {
            return ftm.toFixed(2);
          }()
        }));
      }
    });

    $('#time-range').daterangepicker({}, function (start, end) {
      this.element.trigger('change');
    });
  });
</script>
<?= $block->end() ?>
