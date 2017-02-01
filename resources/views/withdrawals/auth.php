<?php $view->layout() ?>

<form class="form-combined auth-form" method="post">
    <div class="form-group notice">
        为了您的账户安全,请先完成实名认证
    </div>
    <div class="form-group border-top">
        <label for="name" class="control-label">真实姓名</label>
        <div class="col-control">
            <input type="text" class="form-control" id="name" name="name" placeholder="请输入银行卡开户人姓名">
        </div>
    </div>
    <div class="form-group">
        <label for="idCard" class="control-label">身份证号</label>
        <div class="col-control">
            <input type="text" class="form-control" id="idCard" name="idCard" placeholder="15或18位数字">
        </div>
    </div>
    <div class="form-group form-actions">
        <button type="submit" class="btn btn-primary btn-block">下一步</button>
    </div>
</form>

<script>
    require(['jquery-form'], function () {
        $('.auth-form').ajaxForm({
            url: $.url('wallet/submitAuth'),
            dataType: 'json',
            success: function (result) {
                $.msg(result, function () {
                    if (result.code == 1) {
                        window.location.href = $.url('wallet/withdrawals');
                    }
                });
            }
        });
    });
</script>
