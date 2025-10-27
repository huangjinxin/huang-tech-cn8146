<?php ob_start(); ?>
<style>#scroll-area{overflow:hidden}</style>

<div class="reader-wrap">
  <aside class="reader-toc" id="reader-toc">
    <div class="toc-block">
      <div class="toc-title">目录</div>
      <nav id="toc-list"></nav>
    </div>
    <?php if (!empty($posts ?? [])): ?>
    <div class="toc-block" style="margin-top:12px;">
      <div class="toc-title">所有文章</div>
      <nav id="post-list">
        <?php foreach ($posts as $p): ?>
          <a class="post-item" href="/read/<?= urlencode($p['slug']) ?>">
            <span class="post-title"><?= htmlspecialchars($p['title']) ?></span>
            <?php if (!empty($p['date'])): ?>
              <small class="post-date"><?= htmlspecialchars($p['date']) ?></small>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      </nav>
    </div>
    <?php endif; ?>
  </aside>

  <section class="reader-main">
    <div class="reader-toolbar">
      <div class="left">
        <button id="btn-prev" title="上一页">◀</button>
        <button id="btn-next" title="下一页">▶</button>
      </div>
      <div class="middle">
        <button id="btn-zoom-out" title="缩小">A-</button>
        <span id="zoom-display">100%</span>
        <button id="btn-zoom-in" title="放大">A+</button>
        <button id="btn-cols" title="单/双栏">⎚</button>
        <button id="btn-theme" title="深色/浅色">☾</button>
        <button id="btn-full" title="全屏">⛶</button>
      </div>
      <div class="right">
        <input id="search-input" placeholder="搜索…" />
        <button id="btn-search" title="搜索">🔍</button>
      </div>
    </div>

    <div class="reader-body">
      <article class="reader-content" id="reader-content">
        <?php if (!empty($welcome)): ?>
          <h1>欢迎</h1>
          <p>请选择左边目录打开档案查看。</p>
        <?php else: ?>
          <?= $content /* 已是 Markdown 渲染后的 HTML */ ?>
        <?php endif; ?>
      </article>
    </div>
  </section>
</div>

<style>
  .reader-wrap{ height:100%; display:flex; gap:16px; }
  .reader-toc{
    width:260px; flex:0 0 260px; background:#fafafa; border:1px solid #eee;
    border-radius:12px; padding:12px; height:100%; overflow:auto;
  }
  .toc-block + .toc-block{ border-top:1px solid #eee; padding-top:10px; }
  .toc-title{ font-weight:700; margin:4px 0 8px; }
  #toc-list a, #post-list a{
    display:flex; align-items:center; justify-content:space-between;
    gap:8px; padding:6px 8px; border-radius:8px; color:#333; text-decoration:none;
  }
  #toc-list a:hover, #post-list a:hover{ background:#eee; }
  #toc-list .h1{ font-weight:700; }
  #toc-list .h2{ padding-left:10px; }
  #toc-list .h3{ padding-left:20px; font-size:.95em; }
  #post-list .post-item .post-title{ flex:1 1 auto; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
  #post-list .post-item .post-date{ color:#888; flex:0 0 auto; margin-left:8px; }

  .reader-main{ flex:1; min-width:0; height:100%; display:flex; flex-direction:column; }
  .reader-toolbar{ flex:0 0 48px; height:48px; display:flex; align-items:center; justify-content:space-between; gap:8px; background:#fff; border:1px solid #eee; border-radius:12px; padding:6px 10px; margin-bottom:10px; }
  .reader-toolbar button{ border:1px solid #ddd; background:#f7f7f7; border-radius:8px; padding:6px 10px; cursor:pointer; }
  .reader-toolbar input{ border:1px solid #ddd; border-radius:8px; padding:6px 8px; min-width:180px; }

  .reader-body{ flex:1; min-height:0; overflow:hidden; }
  .reader-content{
    background:#fff; border:1px solid #eee; border-radius:12px; padding:24px;
    height:100%; overflow:auto;
    column-width: 720px; column-gap: 48px;
    font-size:16px; line-height:1.72;
  }
  .reader-content img{ max-width:100%; height:auto; display:block; margin:8px 0; break-inside: avoid; }
  .reader-content h1, .reader-content h2, .reader-content h3 { break-after: avoid; }
  .reader-content pre { background:#f6f8fa; padding:12px 14px; border-radius:8px; overflow:auto; }
  .reader-content code { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }

  /* 深色模式 */
  .dark .reader-toc { background:#111; border-color:#333; color:#ddd; }
  .dark #toc-list a, .dark #post-list a { color:#ddd; }
  .dark #toc-list a:hover, .dark #post-list a:hover { background:#222; }
  .dark .reader-toolbar { background:#1b1b1b; border-color:#333; }
  .dark .reader-toolbar input, .dark .reader-toolbar button { background:#222; border-color:#444; color:#ddd; }
  .dark .reader-content { background:#0f0f0f; color:#ddd; border-color:#333; }
</style>

<script>
(function(){
  const root = document.documentElement;
  const content = document.getElementById('reader-content');
  const tocList = document.getElementById('toc-list');
  const zoomDisplay = document.getElementById('zoom-display');
  let zoom = 100, twoCols = true, dark = false;

  /* 目录（H1~H3） */
  const headings = content.querySelectorAll('h1, h2, h3');
  headings.forEach((h, i) => {
    if (!h.id) h.id = 'h-' + i;
    const a = document.createElement('a');
    a.href = '#' + h.id;
    a.textContent = h.textContent;
    a.className = h.tagName.toLowerCase();
    tocList.appendChild(a);
  });

  /* 翻页 */
  function page(delta){ content.scrollBy({ left: delta * (content.clientWidth - 32), behavior:'smooth' }); }
  document.getElementById('btn-prev').onclick = () => page(-1);
  document.getElementById('btn-next').onclick = () => page(+1);

  /* 缩放 */
  function applyZoom(){ content.style.fontSize = zoom + '%'; zoomDisplay.textContent = zoom + '%'; }
  document.getElementById('btn-zoom-in').onclick  = () => { zoom = Math.min(200, zoom + 10); applyZoom(); };
  document.getElementById('btn-zoom-out').onclick = () => { zoom = Math.max(70,  zoom - 10); applyZoom(); };
  applyZoom();

  /* 单/双栏 */
  document.getElementById('btn-cols').onclick = () => {
    twoCols = !twoCols;
    content.style.columnWidth = twoCols ? '720px' : '1200px';
  };

  /* 深色 */
  document.getElementById('btn-theme').onclick = () => {
    dark = !dark;
    (dark ? root.classList.add('dark') : root.classList.remove('dark'));
  };

  /* 全屏 */
  document.getElementById('btn-full').onclick = () => {
    const el = document.documentElement;
    if (!document.fullscreenElement) el.requestFullscreen?.();
    else document.exitFullscreen?.();
  };

  /* 搜索 */
  const input = document.getElementById('search-input');
  function clearHighlights(){
    content.querySelectorAll('mark.__hit').forEach(m=>{
      const text = document.createTextNode(m.textContent);
      m.replaceWith(text);
    });
  }
  function doSearch(){
    clearHighlights();
    const q = input.value.trim();
    if (!q) return;
    const reg = new RegExp(q, "gi");
    content.querySelectorAll("p, li, h1, h2, h3, h4, h5, h6").forEach(el => {
      if (!el.textContent.match(reg)) return;
      const frag = document.createDocumentFragment();
      let lastIdx = 0;
      el.textContent.replace(reg, (match, offset) => {
        const before = el.textContent.slice(lastIdx, offset);
        if (before) frag.appendChild(document.createTextNode(before));
        const mark = document.createElement("mark");
        mark.className = "__hit";
        mark.textContent = match;
        frag.appendChild(mark);
        lastIdx = offset + match.length;
      });
      const after = el.textContent.slice(lastIdx);
      if (after) frag.appendChild(document.createTextNode(after));
      el.innerHTML = ""; el.appendChild(frag);
    });
    const first = content.querySelector("mark.__hit");
    if (first) first.scrollIntoView({behavior:"smooth", block:"center"});
  }
  document.getElementById("btn-search").onclick = doSearch;
  input.addEventListener("keydown", e => { if (e.key === "Enter") doSearch(); });
})();
</script>
<?php $content = ob_get_clean(); include __DIR__ . '/layout.php'; ?>
