<?php $view->layout() ?>

<div class="page-header">
  <h1>
    提款管理
    <small>
      <i class="fa fa-angle-double-right"></i>
      提款列表
    </small>
  </h1>
</div>

<div class="row">
  <div class="col-xs-12">
    <!-- PAGE CONTENT BEGINS -->
    <div class="table-responsive">
      <form class="form-inline" id="search-form" role="form">

        <ul class="nav nav-tabs statuses">
          <?php foreach ($statuses as $status => $statusData) : ?>
            <li class="<?= $status ?> <?= $curStatus == $status ? 'active' : '' ?>">
              <a href="<?= $url('admin/withdrawals', ['status' => $status] + $_GET) ?>"><?= $statusData['name'] ?></a>
            </li>
          <?php endforeach ?>
        </ul>

        <div class="well form-well">
          <?php if ($curStatus == 'toBeAudit') : ?>
            <div class="form-group">
              <select name="moneyLimit" id="moneyLimit" class="form-control">
                <option value="0">全部</option>
                <option value="<?= wei()->transaction->getAutoRechargeMoney(); ?>" selected>
                  提现大于等于<?= wei()->transaction->getAutoRechargeMoney(); ?>
                </option>
              </select>
            </div>
          <?php endif ?>
          <div class="form-group">
            <input type="text" class="form-control" name="<?= $curStatusData['timeField'] ?>Range" id="timeRange"
              placeholder="请选择<?= $curStatusData['timeName'] ?>范围">
          </div>
        </div>
      </form>

      <table id="record-table" class="record-table table table-bordered table-hover">
        <thead>
        <tr>
          <th>提款人</th>
          <th class="t-5">提款金额(元)</th>
          <th class="t-4">账户类型</th>
          <th class="t-4">账号</th>
          <th class="t-8"><?= $curStatusData['timeName'] ?></th>
          <th class="t-4">状态</th>
          <th class="t-4">操作人</th>
          <th>操作说明</th>
          <th class="t-4">审核</th>
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

<script id="operate-col-tpl" type="text/html">
  <div class="action-buttons">
    <% if (audit == '0' && passed == '0') { %>
    <a class="audit" href="javascript:;" title="审核">
      操作
    </a>
    <% } else if (audit == '1' && passed == '0'){ %>
    <span title="已审核,等待系统自动转账">-</span>
    <% } else { %>
    <span title="已转账,不可再审核">-</span>
    <% } %>
  </div>
</script>

<!-- 底部统计 -->
<script id="footerTpl" type="text/html">
  <tfoot class="order-summary">
  <tr>
    <td class="text-right" colspan="<%= colspan %>">
      总提款单数: <strong><%= withdrawalCount %></strong>，
      总提款金额: ￥<strong><%= totalWithdrawalMoney %></strong>
    </td>
  </tr>
  </tfoot>
</script>


<div id="audit-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form class="form-horizontal" action="<?= $url('admin/withdrawals/audit') ?>" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">审核提款单</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="audit" class="col-xs-3 control-label">状态</label>

            <div class="col-xs-9">
              <label class="radio-inline">
                <input type="radio" class="audit" name="audit" value="1"> 通过
              </label>
              <label class="radio-inline">
                <input type="radio" class="audit" name="audit" value="2"> 不通过
              </label>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-xs-3" for="note">备注</label>

            <div class="col-xs-6">
              <input type="text" name="note" id="note" class="form-control">

              <p class="help-block">前台用户可见</p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="id" class="id">
          <button type="submit" class="btn btn-default">保存</button>
        </div>
      </form>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div><!-- /.modal -->

<?php require $view->getFile('user:admin/user/richInfo.php') ?>

<?= $block('js') ?>
<script>
  require(['dataTable', 'form', 'jquery-deparam', 'daterangepicker'], function () {
    var Withdrawal = function () {
    };

    $.extend(Withdrawal.prototype, {
      timeField: 'createTime',
      indexAction: function (options) {
        $.extend(this, options);

        var recordTable = $('#record-table').dataTable({
          ajax: {
            url: $.queryUrl('admin/withdrawals.json')
          },
          columns: [
            {
              data: 'user',
              render: function (data, type, full) {
                return template.render('user-info-tpl', data);
              }
            },
            {
              data: 'absAmount'
            },
            {
              data: 'accountTypeName'
            },
            {
              data: 'account',
              render: function (data, type, full) {
                return data || '无';
              }
            },
            {
              data: this.timeField,
              render: function (data, type, full) {
                return data.substr(0, 16);
              }
            },
            {
              data: 'statusName'
            },
            {
              data: 'updateUserName',
              render: function (data, type, full) {
                return data || '-';
              }
            },
            {
              data: 'description',
              render: function (data, type, full) {
                return data ? '<div class="js-tooltip truncate">' + data + '</div>' : '-';
              }
            },
            {
              data: 'id',
              render: function (data, type, full) {
                return template.render('operate-col-tpl', full);
              }
            }
          ],
          footerCallback: function (tfoot, data, start, end, display) {
            // 渲染底部商品数量等统计
            $(this).find('tfoot').remove();
            var colspan = $(this).find('thead th').length;
            $(this).append(template.render('footerTpl', {
              colspan: colspan,
              withdrawalCount: function () {
                var withdrawalCount = 0;
                for (var i in data) {
                  withdrawalCount++;
                }
                return withdrawalCount;
              }(),
              totalWithdrawalMoney: function () {
                var totalWithdrawalMoney = 0;
                for (var i in data) {
                  totalWithdrawalMoney += parseInt(data[i]['absAmount']);
                }
                return totalWithdrawalMoney.toFixed(2);
              }()
            }));
          }
        });

        // 筛选
        $('#search-form').update(function () {
          recordTable.reload($(this).serialize(), false);
        });

        // 时间筛选
        $('#timeRange').daterangepicker({}, function (start, end) {
          this.element.trigger('change');
        });

        // 审核
        recordTable.on('click', '.audit', function () {
          var data = recordTable.fnGetData($(this).parents('tr:first')[0]);
          $('#audit-modal').loadJSON(data).modal('show');
        });

        $('#audit-modal form').ajaxForm({
          dataType: 'json',
          success: function (result) {
            $.msg(result, function () {
              if (result.code === 1) {
                $('#audit-modal').modal('hide');
              }
            });
            recordTable.reload();
          }
        });

        recordTable.tooltip({
          container: 'body',
          selector: '.js-tooltip',
          title: function () {
            return $(this).html();
          }
        });
      }
    });

    new Withdrawal().indexAction({
      timeField: '<?= $curStatusData['timeField'] ?>'
    });
  });
</script>
<?= $block->end() ?>