<?php $view->layout() ?>

<ul class="transaction-list list list-indented">
</ul>

<script type="text/html" class="transaction-item-tpl">
  <li>
    <a class="list-item has-feedback" href="<%= $.url('transactions/%s', id) %>">
      <div class="list-col">
        <h4 class="list-heading">
          <%= typeName %>
          <span class="pull-right">¥<%= absAmount %></span>
        </h4>

        <div class="list-body">
          <%= createTime.substr(0, 16) %>
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

    var list = $('.transaction-list').list({
      url: '<?= $url->query('transactions.json') ?>',
      template: template.compile($('.transaction-item-tpl').html()),
      localData: <?= json_encode($ret) ?>
    });
  });
</script>
<?= $block->end() ?>
