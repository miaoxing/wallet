<?php $view->layout() ?>

<ul class="header-tab nav tab-underline border-bottom">
  <?php foreach ($statuses as $status => $statusData) : ?>
    <li class="border-primary <?= $curStatus == $status ? 'active' : '' ?>">
      <a class="text-active-primary" href="<?= $url('withdrawals', ['status' => $status]) ?>">
        <?= $statusData['name'] ?>
      </a>
    </li>
  <?php endforeach ?>
</ul>

<ul class="stat-nav flex flex-equal text-center border-top-bottom">
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
    <a class="list-item has-feedback" href="<%= $.url('withdrawals/%s', id) %>">
      <div class="list-col">
        <h4 class="list-heading">
          <%= absAmount %>元
        </h4>

        <div class="list-body">
          <?= $curStatusData['timeName'] ?>: <%= <?= $curStatusData['timeField'] ?>.substr(0, 16) %>
          <?php if ($curStatus == 'auditNotPass') : ?>
            <br>备注: <%= note %>
          <?php endif ?>
        </div>
      </div>
      <i class="bm-angle-right list-feedback"></i>
    </a>
  </li>
</script>

<?= $block->js() ?>
<script>
  require(['comps/artTemplate/template.min'], function () {
    template.helper('$', $);

    var list = $('.withdrawal-list').list({
      url: '<?= $url->query('withdrawals.json') ?>',
      template: template.compile($('.withdrawal-item-tpl').html()),
      localData: <?= json_encode($ret) ?>
    });
  });
</script>
<?= $block->end() ?>
