<?php

$view->layout();
$wei->page->addAsset('plugins/admin/css/filter.css');
?>

<div class="page-header">
  <h1>
    提现管理
    <small>
      <i class="fa fa-angle-double-right"></i>
      提现列表
    </small>
  </h1>
</div>

<div class="row">
  <div class="col-12">
    <!-- PAGE CONTENT BEGINS -->
    <div class="table-responsive">
      <ul class="nav tab-underline m-b">
        <?php foreach ($statuses as $status => $statusData) : ?>
          <li class="nav-item">
            <a class="nav-link <?= $curStatus == $status ? 'active' : '' ?>" href="<?= $url('admin/withdrawals', ['status' => $status] + $req->getQueries()) ?>">
              <?= $statusData['name'] ?>
            </a>
          </li>
        <?php endforeach ?>
      </ul>

      <form class="js-withdrawal-form search-form well form-horizontal">
        <div class="form-group">
          <label class="col-md-1 control-label" for="money-limit">金额：</label>
          <div class="col-md-3">
            <select name="moneyLimit" id="money-limit" class="form-control">
              <option value="0">全部</option>
              <option value="<?= wei()->transaction->getAutoRechargeMoney(); ?>" selected>
                提现大于等于<?= wei()->transaction->getAutoRechargeMoney(); ?>
              </option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="col-md-1 control-label" for="name">姓名：</label>
          <div class="col-md-3">
            <input type="text" class="form-control" id="name" name="name">
          </div>
        </div>

        <div class="form-group">
          <label class="col-md-1 control-label" for="mobile">手机：</label>
          <div class="col-md-3">
            <input type="text" class="form-control" id="mobile" name="mobile">
          </div>
        </div>

        <div class="form-group">
          <label for="create-time-range" class="col-sm-1 control-label">申请时间：</label>
          <div class="col-sm-3">
            <input type="text" name="createTimeRange" id="create-time-range" class="js-time-range form-control">
          </div>
        </div>

        <div class="form-group">
          <label for="audit-time-range" class="col-sm-1 control-label">审核时间：</label>
          <div class="col-sm-3">
            <input type="text" name="auditTimeRange" id="audit-time-range" class="js-time-range form-control">
          </div>
        </div>

        <?php $event->trigger('adminWithdrawalForm') ?>
      </form>

      <table id="record-table" class="record-table table table-bordered table-hover">
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
    <a class="audit" href="javascript:" title="审核">
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
          <h5 class="modal-title">审核提款单</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="audit" class="col-3 control-label">状态</label>

            <div class="col-9">
              <label class="radio-inline">
                <input type="radio" class="audit" name="audit" value="1"> 通过
              </label>
              <label class="radio-inline">
                <input type="radio" class="audit" name="audit" value="2"> 不通过
              </label>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-3" for="note">备注</label>

            <div class="col-6">
              <input type="text" name="note" id="note" class="form-control">

              <p class="form-text">前台用户可见</p>
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

<?php require $view->getFile('@user/admin/user/richInfo.php') ?>

<?= $block->js() ?>
<script>
  require(['dataTable', 'form', 'jquery-deparam', 'daterangepicker'], function () {
    var Withdrawal = function () {
    };

    $.extend(Withdrawal.prototype, {
      timeField: 'createTime',
      indexAction: function (options) {
        $.extend(this, options);

        var recordTable = $('#record-table').dataTable({
          sorting: [],
          ajax: {
            url: $.queryUrl('admin/withdrawals.json')
          },
          columns: [
            {
              data: 'user',
              title: '提款人',
              render: function (data, type, full) {
                return template.render('user-info-tpl', data);
              }
            },
            <?php $event->trigger('adminWithdrawalColumns') ?>
            {
              data: 'amount',
              title: '提款金额(元)',
              sClass: 't-6',
              sortable: true,
              render: function (data, type, full) {
                return full.absAmount;
              }
            },
            {
              title: '账户类型',
              data: 'accountTypeName',
              sClass: 't-4'
            },
            {
              data: this.timeField,
              title: '<?= $curStatusData['timeName'] ?>',
              sClass: 't-8',
              render: function (data, type, full) {
                  return data.substr(0, 16);
                }
              },
              {
                data: 'statusName',
                title: '状态',
                sClass: 't-4'
              },
              {
                data: 'updateUserName',
                title: '操作人',
                sClass: 't-4',
                render: function (data, type, full) {
                  return data || '-';
                }
              },
              {
                data: 'description',
                title: '操作说明',
                render: function (data, type, full) {
                  return data ? '<div class="js-tooltip truncate">' + data + '</div>' : '-';
                }
              },
              {
                data: 'id',
                title: '审核',
                sClass: 't-4',
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
          $('.js-withdrawal-form').update(function () {
            recordTable.reload($(this).serialize(), false);
          });

          // 时间筛选
          $('.js-time-range').daterangepicker({}, function (start, end) {
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
