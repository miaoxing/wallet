<?php $view->layout() ?>

<ul class="header-nav nav nav-underline">
  <?php foreach ($statuses as $status => $statusData) : ?>
    <li class="nav-item">
      <a class="nav-link text-active-primary border-active-primary <?= $curStatus == $status ? 'active' : '' ?>"
        href="<?= $url('withdrawals', ['status' => $status]) ?>">
        <?= $statusData['name'] ?>
      </a>
    </li>
  <?php endforeach ?>
</ul>

<ul class="stat-nav d-flex flex-equal text-center border-y">
  <li class="border-right">
    记录
    <span class="stat-num text-primary"><?= $ret['records'] ?></span>
  </li>
  <li>
    总金额
    <span class="stat-num text-primary">¥<?= $moneySum ?></span>
  </li>
</ul>

<ul class="withdrawal-list list list-indented">
</ul>

<script type="text/html" class="withdrawal-item-tpl">
  <li>
    <a class="list-item list-has-arrow" href="<%= $.url('withdrawals/%s', id) %>">
      <div class="list-col">
        <h4 class="list-title">
          <%= absAmount %>元
        </h4>

        <div class="list-text">
          <?= $curStatusData['timeName'] ?>: <%= <?= $curStatusData['timeField'] ?>.substr(0, 16) %>
          <?php if ($curStatus == 'auditNotPass') : ?>
            <br>备注: <%= note %>
          <?php endif ?>
        </div>
      </div>
      <i class="bm-angle-right list-arrow"></i>
    </a>
  </li>
</script>

<?= $block->js() ?>
<script>
  require(['plugins/app/libs/artTemplate/template.min', 'plugins/app/libs/jquery-list/jquery-list'], function () {

    var list = $('.withdrawal-list').list({
      url: '<?= $url->query('withdrawals.json') ?>',
      template: template.compile($('.withdrawal-item-tpl').html()),
      localData: <?= json_encode($ret) ?>
    });
  });
</script>
<?= $block->end() ?>
