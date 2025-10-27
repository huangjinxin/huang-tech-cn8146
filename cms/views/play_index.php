<?php
$dirs = is_array($dirs ?? null) ? $dirs : [];
$files = is_array($files ?? null) ? $files : [];
$path = $path ?? '';
ob_start();
?>
<h1 style="margin:8px 0 16px;">HTML 练习目录</h1>

<?php if ($path): ?>
<div style="margin-bottom: 16px;">
  <a href="/play<?= $path ? '/' . urlencode(dirname($path)) : '' ?>">&larr; 返回上级</a>
</div>
<?php endif; ?>

<?php if (!empty($dirs)): ?>
<h2 style="margin: 24px 0 12px;">目录</h2>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
  <?php foreach ($dirs as $d): ?>
    <a class="card" href="/play/<?= urlencode($d['path']) ?>" style="display:block;">
      <h3 style="margin:0 0 6px;"><?= htmlspecialchars($d['name']) ?></h3>
      <p class="muted" style="margin:0;"><?= (int)$d['count'] ?> 个作品</p>
    </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($files)): ?>
<h2 style="margin: 24px 0 12px;">顶层作品</h2>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
  <?php foreach ($files as $f): ?>
    <div class="card" style="display:block;position:relative;">
      <a href="/play/<?= urlencode($f['path']) ?>" style="display:block; text-decoration:none; color:inherit;">
        <h3 style="margin:0 0 6px;"><?= htmlspecialchars($f['title']) ?></h3>
        <p class="muted" style="margin:0;"><?= htmlspecialchars($f['name']) ?>.html</p>
      </a>
      <div style="position: absolute; bottom: 8px; right: 16px; display: flex; gap: 8px;">
        <a href="/play_raw/<?= urlencode($f['path']) ?>" 
           target="_blank" 
           style="color: #0b62ff; text-decoration: none; font-size: 12px;"
           title="在新窗口中查看源代码">源代码</a>
        <a href="#" 
           onclick="viewSource('<?= urlencode($f['path']) ?>', '<?= urlencode($f['name']) ?>'); return false;"
           style="color: #0b62ff; text-decoration: none; font-size: 12px;"
           title="查看源代码">查看</a>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (empty($dirs) && empty($files)): ?>
<div class="card">
  <p>暂无任何作品或目录。</p>
</div>
<?php endif; ?>

<!-- 源代码模态框 -->
<div id="source-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; overflow: auto;">
  <div style="position: relative; top: 50px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; width: 90%; max-width: 900px; max-height: 85vh; overflow: auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
      <h2 id="source-title" style="margin: 0;">源代码</h2>
      <button id="close-source-modal" style="background: #f44336; color: white; border: none; border-radius: 4px; padding: 6px 12px; cursor: pointer;">关闭</button>
    </div>
    <div style="margin-bottom: 10px; display: flex; gap: 10px;">
      <button id="copy-source" style="background: #0b62ff; color: white; border: none; border-radius: 4px; padding: 8px 12px; cursor: pointer;">复制源代码</button>
      <span id="copy-status" style="line-height: 28px; color: #4CAF50; display: none;">已复制到剪贴板!</span>
    </div>
    <pre id="source-code" style="background: #f4f4f4; padding: 15px; border-radius: 4px; overflow: auto; white-space: pre-wrap; word-wrap: break-word; max-height: 60vh; font-family: monospace; font-size: 14px; margin: 0;"></pre>
  </div>
</div>

<style>.muted{color:#666}</style>

<script>
  // 获取源代码函数
  function viewSource(path, filename) {
    const modal = document.getElementById('source-modal');
    const sourceCode = document.getElementById('source-code');
    const sourceTitle = document.getElementById('source-title');
    
    // 设置标题 - 使用传入的filename参数 which is already just the filename
    sourceTitle.textContent = '源代码: ' + decodeURIComponent(filename) + '.html';
    
    // 获取HTML源代码 - 使用绝对路径以确保局域网访问兼容性
    fetch(window.location.origin + '/play_raw/' + path)
      .then(response => response.text())
      .then(data => {
        // 显示源代码
        sourceCode.textContent = data;
        modal.style.display = 'block';
        
        // 修复滚动条位置，让用户看到完整的代码
        document.body.style.overflow = 'hidden';
      })
      .catch(error => {
        console.error('Error fetching source code:', error);
        sourceCode.textContent = '无法加载源代码: ' + error.message;
        modal.style.display = 'block';
      });
  }

  // 关闭源代码模态框
  document.getElementById('close-source-modal').onclick = () => {
    document.getElementById('source-modal').style.display = 'none';
    document.body.style.overflow = 'auto';
  };

  // 点击模态框外部关闭
  document.getElementById('source-modal').onclick = (e) => {
    if (e.target === document.getElementById('source-modal')) {
      document.getElementById('source-modal').style.display = 'none';
      document.body.style.overflow = 'auto';
    }
  };

  // 复制源代码功能
  document.getElementById('copy-source').onclick = () => {
    const sourceCode = document.getElementById('source-code');
    
    // 检查 navigator.clipboard 是否可用
    if (navigator.clipboard && window.isSecureContext) {
      // 使用现代的 navigator.clipboard API
      navigator.clipboard.writeText(sourceCode.textContent)
        .then(() => {
          // 显示复制成功提示
          const copyStatus = document.getElementById('copy-status');
          copyStatus.style.display = 'inline';
          
          // 2秒后隐藏提示
          setTimeout(() => {
            copyStatus.style.display = 'none';
          }, 2000);
        })
        .catch(err => {
          console.error('无法复制文本（clipboard API）: ', err);
          // 备用方案：使用 document.execCommand
          fallbackCopyTextToClipboard(sourceCode.textContent);
        });
    } else {
      // 备用方案：使用 document.execCommand
      fallbackCopyTextToClipboard(sourceCode.textContent);
    }
  };

  // 备用复制函数
  function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    
    // 将文本区域放在视图之外
    textArea.style.position = 'fixed';
    textArea.style.left = '-9999px';
    textArea.style.top = '-9999px';
    
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
      const successful = document.execCommand('copy');
      if (successful) {
        // 显示复制成功提示
        const copyStatus = document.getElementById('copy-status');
        copyStatus.style.display = 'inline';
        
        // 2秒后隐藏提示
        setTimeout(() => {
          copyStatus.style.display = 'none';
        }, 2000);
      } else {
        console.error('无法复制文本（execCommand）');
        alert('复制失败，请手动选择并复制文本');
      }
    } catch (err) {
      console.error('无法复制文本（execCommand）: ', err);
      alert('复制失败，请手动选择并复制文本');
    }
    
    document.body.removeChild(textArea);
  };
</script>
<?php $content = ob_get_clean(); include __DIR__ . '/layout.php'; ?>
