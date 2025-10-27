<?php
$dir  = $dir  ?? '';
$path = $path ?? '';
$name = $name ?? '';
$src  = '/play_raw/' . urlencode($path) . '/' . urlencode($name);
ob_start();
?>
<div class="play-shell">
  <div class="play-toolbar">
    <div class="left">
      <a class="btn" href="/play/<?= urlencode($path) ?>">← 返回列表</a>
    </div>
    <div class="right">
      <button class="btn" id="btn-source">查看源代码</button>
      <a class="btn" href="<?= htmlspecialchars($src) ?>" target="_blank" rel="noopener">新窗口打开</a>
      <button class="btn" id="btn-full">全屏</button>
    </div>
  </div>

  <div class="play-card">
    <iframe id="play-frame"
            src="<?= htmlspecialchars($src) ?>"
            sandbox="allow-scripts allow-same-origin allow-forms"
            referrerpolicy="no-referrer"
            loading="lazy"></iframe>
  </div>
  
  <!-- 源代码模态框 -->
  <div id="source-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; overflow: auto;">
    <div style="position: relative; top: 50px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; width: 90%; max-width: 900px; max-height: 85vh; overflow: auto;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <h2 style="margin: 0;">源代码: <?= htmlspecialchars($name) ?>.html</h2>
        <button id="close-source-modal" style="background: #f44336; color: white; border: none; border-radius: 4px; padding: 6px 12px; cursor: pointer;">关闭</button>
      </div>
      <div style="margin-bottom: 10px; display: flex; gap: 10px;">
        <button id="copy-source" style="background: #0b62ff; color: white; border: none; border-radius: 4px; padding: 8px 12px; cursor: pointer;">复制源代码</button>
        <span id="copy-status" style="line-height: 28px; color: #4CAF50; display: none;">已复制到剪贴板!</span>
      </div>
      <pre id="source-code" style="background: #f4f4f4; padding: 15px; border-radius: 4px; overflow: auto; white-space: pre-wrap; word-wrap: break-word; max-height: 60vh; font-family: monospace; font-size: 14px; margin: 0;"></pre>
    </div>
  </div>
</div>

<style>
  /* 让中间区域滚动即可，沿用全站外壳的固定头/尾 */
  .play-shell { max-width: 1200px; margin: 0 auto; }
  .play-toolbar {
    display:flex; align-items:center; justify-content:space-between; gap:8px;
    background:#fff; border:1px solid #eee; border-radius:12px; padding:8px 10px; margin-bottom:12px;
    position:sticky; top:0; z-index:5;
  }
  .btn {
    display:inline-block; padding:6px 12px; border:1px solid #ddd; border-radius:8px; background:#f7f7f7;
    color:#222; text-decoration:none; cursor:pointer;
  }
  .btn:hover { background:#eee; }

  /* 居中卡片：作品只在这个"框"里运行 */
  .play-card {
    background:#fff; border:1px solid #eee; border-radius:16px;
    padding:12px; box-shadow:0 2px 12px rgba(0,0,0,.06);
  }
  .play-card iframe {
    width:100%;
    /* 让作品区域在可视高度内，避免撑满整站；可根据需要调节  */
    height: min(72vh, 900px);
    border:0; border-radius:12px; background:#fff; display:block;
  }

  /* 深色模式适配（按你的 read.php 主题规则） */
  .dark .play-toolbar { background:#1b1b1b; border-color:#333; }
  .dark .btn { background:#222; border-color:#444; color:#ddd; }
  .dark .play-card { background:#0f0f0f; border-color:#333; }
  .dark .play-card iframe { background:#111; }
  
  /* 源代码显示样式 */
  #source-code {
    background: #f8f8f8;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    font-family: 'Courier New', Consolas, Monaco, monospace;
    font-size: 14px;
    line-height: 1.4;
  }
</style>

<script>
  (function(){
    const frame = document.getElementById('play-frame');
    const fullBtn = document.getElementById('btn-full');
    const sourceBtn = document.getElementById('btn-source');
    const modal = document.getElementById('source-modal');
    const closeModal = document.getElementById('close-source-modal');
    const copySourceBtn = document.getElementById('copy-source');
    const sourceCode = document.getElementById('source-code');
    const copyStatus = document.getElementById('copy-status');
    
    // 获取源代码的路径 - 使用绝对路径以确保局域网访问兼容性
    const sourcePath = window.location.origin + '/play_raw/<?= urlencode($path) ?>/<?= urlencode($name) ?>';

    // 全屏 iframe
    fullBtn.onclick = () => {
      if (frame.requestFullscreen) frame.requestFullscreen();
      else if (frame.webkitRequestFullscreen) frame.webkitRequestFullscreen();
      else window.open(frame.src, '_blank');
    };

    // 查看源代码功能
    sourceBtn.onclick = () => {
      // 获取HTML源代码
      fetch(sourcePath)
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
    };

    // 关闭源代码模态框
    closeModal.onclick = () => {
      modal.style.display = 'none';
      document.body.style.overflow = 'auto';
    };

    // 点击模态框外部关闭
    modal.onclick = (e) => {
      if (e.target === modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
      }
    };

    // 复制源代码功能
    copySourceBtn.onclick = () => {
      // 检查 navigator.clipboard 是否可用
      if (navigator.clipboard && window.isSecureContext) {
        // 使用现代的 navigator.clipboard API
        navigator.clipboard.writeText(sourceCode.textContent)
          .then(() => {
            // 显示复制成功提示
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

    // （可选）自动高度：如果你未来给作品里加 postMessage，可在此根据内容高度设置 iframe 高度
    // window.addEventListener('message', (e)=>{
    //   if (e.data && e.data.type === 'PLAY_IFRAME_HEIGHT') {
    //     frame.style.height = Math.min(e.data.height, 1200) + 'px';
    //   }
    // });
  })();
</script>
<?php $content = ob_get_clean(); include __DIR__ . '/layout.php'; ?>
