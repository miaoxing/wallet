<?php $view->layout() ?>

<ul class="transaction-list list list-indented">
</ul>

<script type="text/html" class="transaction-item-tpl">
  <li>
    <a class="list-item list-has-arrow" href="<%= $.url('transactions/%s', id) %>">
      <div class="list-col">
        <h4 class="list-title">
          <%= typeName %>
          <span class="float-right"><%= amount < 0 ? '-' : '' %>¥<%= absAmount %></span>
        </h4>

        <div class="list-text">
          <%= createTime.substr(0, 16) %>
        </div>
      </div>
      <i class="bm-angle-right list-arrow"></i>
    </a>
  </li>
</script>

<?= $block->js() ?>
<script>
  require(['plugins/app/libs/artTemplate/template.min', 'plugins/app/libs/jquery-list/jquery-list'], function () {

    var list = $('.transaction-list').list({
      url: '<?= $url->query('transactions.json') ?>',
      template: template.compile($('.transaction-item-tpl').html()),
      localData: <?= json_encode($ret) ?>
    });
  });
</script>
<?= $block->end() ?>
