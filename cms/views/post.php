<?php ob_start(); ?>
<article>
  <h1><?= htmlspecialchars($title) ?></h1>
  <div><?= $content ?></div>
</article>
<?php $content = ob_get_clean(); include __DIR__ . '/layout.php'; ?>
