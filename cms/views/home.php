<?php ob_start(); ?>
<h1 style="margin:8px 0 16px;">欢迎</h1>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
  <a href="/play" class="card" style="display:block;">
    <h2>少儿作品</h2>
    <p class="muted">进入练习区，所有自制 HTML 示例会在统一外壳中显示。</p>
  </a>
  <a href="/read/<?= urlencode('usage使用手册') ?>" class="card" style="display:block;">
    <h2>文章列表</h2>
    <p class="muted">查看所有 Markdown 文章（在阅读器中浏览）。</p>
  </a>
  <a href="/document_format" class="card" style="display:block;">
    <h2>拟文格式</h2>
    <p class="muted">公文格式编辑器，支持实时预览和导出功能。</p>
  </a>
</div>
<style>.muted{color:#666}</style>
<?php $content = ob_get_clean(); include __DIR__ . '/layout.php'; ?>
