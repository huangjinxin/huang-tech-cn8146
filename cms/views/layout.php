<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8" />
  <title><?= htmlspecialchars($title ?? '练习CMS') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    :root{
      --header-h: 64px;   /* 顶部导航区高度 */
      --footer-h: 48px;   /* 底部栏高度 */
      --page-pad: 16px;   /* 中间区域左右内边距 */
      --bg: #f5f5f7;
      --fg: #222;
      --brand: #111;
      --accent: #0b62ff;
    }
    *{box-sizing:border-box}
    html,body{height:100%;margin:0;padding:0;background:var(--bg);color:var(--fg);font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial}
    body{position:relative}

    /* 固定头尾 */
    header.site-header{
      position:fixed;left:0;right:0;top:0;height:var(--header-h);
      background:var(--brand);color:#fff;
      display:flex;align-items:center;justify-content:space-between;
      padding:0 16px;z-index:1000;box-shadow:0 2px 8px rgba(0,0,0,.15);
    }
    header .brand{font-weight:700;letter-spacing:.3px}
    header nav a{color:#fff;text-decoration:none;margin-right:14px}
    header nav a:hover{opacity:.85}

    /* 固定底部栏 */
    footer.site-footer{
      position:fixed;left:0;right:0;bottom:0;height:var(--footer-h);
      background:#eee;color:#555;display:flex;align-items:center;justify-content:center;
      padding:0 12px;z-index:1000;border-top:1px solid #ddd;
    }

    /* 仅中间区域可滚动的容器 */
    main#scroll-area{
      position:fixed;left:0;right:0;
      top:var(--header-h);bottom:var(--footer-h);
      overflow:auto; /* 默认让中间区域可滚动；阅读模式可被子页覆盖为 hidden */
      padding: 16px var(--page-pad);
    }

    a{color:var(--accent);text-decoration:none}
    .card{background:#fff;border:1px solid #eee;border-radius:12px;padding:16px;margin:12px 0;box-shadow:0 1px 6px rgba(0,0,0,.05)}
  </style>
</head>
<body>
  <header class="site-header">
    <div class="brand">苹湖少儿创客空间</div>
    <nav>
      <a href="/">首页</a>
      <a href="/play">少儿作品</a>
      <a href="/read/usage使用手册">博客</a>
      <a href="#" onclick="redirectToPort3250(); return false;">AI工坊</a>
    </nav>
  </header>

  <script>
    function redirectToPort3250() {
      const currentUrl = new URL(window.location);
      currentUrl.port = '3250';
      window.location.href = currentUrl.toString();
    }
  </script>

  <main id="scroll-area">
    <?= $content ?? '' ?>
  </main>

  <footer class="site-footer">
    <small>© 2025 苹湖少儿创客空间</small>
  </footer>
</body>
</html>
