<?php
require_once __DIR__ . '/../../document_format/lib/CustomDocumentParser.php';

ob_start();
?>
<div class="editor-container" style="display: flex; height: calc(100vh - var(--header-h) - var(--footer-h) - 32px);">
  <!-- 左侧编辑区域 -->
  <div class="editor-panel" style="flex: 1; padding: 16px; border-right: 1px solid #ddd; display: flex; flex-direction: column;">
    <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
      <h2 style="margin: 0;">拟文格式编辑器</h2>
      <div>
        <button id="toggle-mode-btn" style="padding: 6px 12px; background: #17a2b8; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 8px;">切换到自由输入模式</button>
        <button id="help-btn" style="padding: 6px 12px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">帮助</button>
      </div>
    </div>
    
    <!-- 表格形式的编辑器（默认显示） -->
    <div id="table-mode" style="flex: 1; display: flex; flex-direction: column;">
      <div style="margin-bottom: 12px;">
        <button id="add-row-btn" style="padding: 6px 12px; background: #17a2b8; color: white; border: none; border-radius: 4px; cursor: pointer;">添加行</button>
      </div>
      
      <div id="document-editor" style="flex: 1; overflow: auto; border: 1px solid #ddd; border-radius: 4px;">
        <table style="width: 100%; border-collapse: collapse;">
          <thead>
            <tr style="background: #f8f9fa;">
              <th style="width: 60px; border: 1px solid #ddd; padding: 8px; text-align: center;">序号</th>
              <th style="width: 150px; border: 1px solid #ddd; padding: 8px; text-align: center;">标记类型</th>
              <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">内容</th>
              <th style="width: 80px; border: 1px solid #ddd; padding: 8px; text-align: center;">操作</th>
            </tr>
          </thead>
          <tbody id="editor-rows">
            <!-- 初始行 -->
            <tr>
              <td style="border: 1px solid #ddd; padding: 4px; text-align: center;">1</td>
              <td style="border: 1px solid #ddd; padding: 4px;">
                <select class="marker-select" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;">
                  <option value="@0{">主标题 (@0)</option>
                  <option value="@1{">一级标题 (@1)</option>
                  <option value="@2{">二级标题 (@2)</option>
                  <option value="@3{">三级标题 (@3)</option>
                  <option value="@4{">四级标题 (@4)</option>
                  <option value="@5{">五级标题 (@5)</option>
                  <option value="text">普通文本</option>
                  <option value="**">加粗 (**)</option>
                  <option value="*">斜体 (*)</option>
                  <option value="__">下划线 (__)</option>
                  <option value="---">水平线 (---)</option>
                  <option value="@d{}">日期 (@d{})</option>
                </select>
              </td>
              <td style="border: 1px solid #ddd; padding: 4px;">
                <input type="text" class="content-input" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;" placeholder="请输入内容">
              </td>
              <td style="border: 1px solid #ddd; padding: 4px; text-align: center;">
                <button class="delete-row-btn" style="padding: 2px 6px; background: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer;">删除</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
    <!-- 自由输入模式编辑器（默认隐藏） -->
    <div id="free-mode" style="flex: 1; display: none; flex-direction: column;">
      <textarea 
        id="editor" 
        style="flex: 1; width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Courier New', monospace; font-size: 14px;"
        placeholder="请输入拟文内容，支持自定义标记语法：
@0{主标题}
@1{一、一级标题}
@2{(一)二级标题}
@3{1.三级标题}
@4{(1)四级标题}
@5{①五级标题}

**加粗文本**
*斜体文本*
__下划线文本__

普通段落内容，自动首行缩进...

@d{}  // 当前日期
@d{YYYY年MM月DD日}  // 自定义日期格式

---
"></textarea>
    </div>
  </div>
  
  <!-- 右侧预览区域 -->
  <div class="preview-panel" style="flex: 1; padding: 16px; display: flex; flex-direction: column;">
    <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
      <h2 style="margin: 0;">预览效果</h2>
      <div>
        <button id="export-btn" style="padding: 6px 12px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 8px;">导出DOCX</button>
        <button id="print-btn" style="padding: 6px 12px; background: #0b62ff; color: white; border: none; border-radius: 4px; cursor: pointer;">打印</button>
      </div>
    </div>
    
    <div id="preview-container" style="flex: 1; overflow: auto; border: 1px solid #ddd; border-radius: 4px; padding: 16px;">
      <div id="preview" class="document-container">
        <p>在此处输入内容，预览将实时显示在这里</p>
      </div>
    </div>
  </div>
</div>

<?= CustomDocumentParser::generateStrictStyles() ?>

<!-- 帮助模态框 -->
<div id="help-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
  <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 10px; width: 80%; max-width: 800px; max-height: 80%; overflow: auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
      <h2 style="margin: 0;">拟文格式编辑器使用说明</h2>
      <button id="close-help" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
    </div>
    <div id="help-content">
      <h3>两种输入模式</h3>
      <p>编辑器支持两种输入模式，可以通过左上角的"切换模式"按钮进行切换：</p>
      
      <h4>1. 表格模式（默认）</h4>
      <ul>
        <li>每行通过下拉选择框选择标记类型</li>
        <li>在内容列输入相应的内容</li>
        <li>使用"添加行"按钮添加新行</li>
        <li>使用每行的"删除"按钮删除不需要的行</li>
      </ul>
      
      <h4>2. 自由输入模式</h4>
      <ul>
        <li>直接在文本区域输入内容</li>
        <li>使用自定义标记语法格式化内容</li>
        <li>支持的标记语法如下：</li>
      </ul>
      
      <h3>标记类型说明</h3>
      <ul>
        <li><strong>主标题 (@0)</strong> - 二号方正小标宋简体，居中显示</li>
        <li><strong>一级标题 (@1)</strong> - 三号黑体，如"一、内容"</li>
        <li><strong>二级标题 (@2)</strong> - 三号楷体_GB2312，如"(一)内容"</li>
        <li><strong>三级标题 (@3)</strong> - 三号仿宋_GB2312加粗，如"1.内容"</li>
        <li><strong>四级标题 (@4)</strong> - 三号仿宋_GB2312加粗，如"(1)内容"</li>
        <li><strong>五级标题 (@5)</strong> - 三号仿宋_GB2312加粗，如"①内容"</li>
        <li><strong>普通文本</strong> - 三号仿宋_GB2312，自动首行缩进</li>
        <li><strong>加粗 (**)</strong> - 文本加粗显示</li>
        <li><strong>斜体 (*)</strong> - 文本斜体显示</li>
        <li><strong>下划线 (__)</strong> - 文本下划线显示</li>
        <li><strong>水平线 (---)</strong> - 插入水平分隔线</li>
        <li><strong>日期 (@d{})</strong> - 插入当前日期</li>
      </ul>
      
      <h3>打印和导出</h3>
      <ul>
        <li>使用右上角的"打印"按钮打印预览区域的内容</li>
        <li>使用右上角的"导出DOCX"按钮导出为Word文档</li>
        <li>打印和导出均严格按照拟文格式要求排版</li>
      </ul>
    </div>
  </div>
</div>

<script>
// 全局变量
let rowCount = 1;
let currentMode = 'table'; // 'table' 或 'free'

// 切换输入模式
document.getElementById('toggle-mode-btn').addEventListener('click', function() {
  if (currentMode === 'table') {
    // 切换到自由输入模式
    document.getElementById('table-mode').style.display = 'none';
    document.getElementById('free-mode').style.display = 'flex';
    this.textContent = '切换到表格模式';
    currentMode = 'free';
    
    // 从表格数据生成自由输入内容
    generateFreeInputFromTable();
  } else {
    // 切换到表格模式
    document.getElementById('free-mode').style.display = 'none';
    document.getElementById('table-mode').style.display = 'flex';
    this.textContent = '切换到自由输入模式';
    currentMode = 'table';
    
    // 从自由输入内容生成表格数据
    generateTableFromFreeInput();
  }
});

// 从表格数据生成自由输入内容
function generateFreeInputFromTable() {
  const rows = document.querySelectorAll('#editor-rows tr');
  let customMarkup = '';
  
  rows.forEach(row => {
    const select = row.querySelector('.marker-select');
    const input = row.querySelector('.content-input');
    
    const marker = select.value;
    const content = input.value;
    
    switch (marker) {
      case '@0{':
      case '@1{':
      case '@2{':
      case '@3{':
      case '@4{':
      case '@5{':
        if (content) {
          customMarkup += `${marker}${content}}\n\n`;
        }
        break;
      case '**':
        if (content) {
          customMarkup += `**${content}**\n\n`;
        }
        break;
      case '*':
        if (content) {
          customMarkup += `*${content}*\n\n`;
        }
        break;
      case '__':
        if (content) {
          customMarkup += `__${content}__\n\n`;
        }
        break;
      case '---':
        customMarkup += '---\n\n';
        break;
      case '@d{}':
        customMarkup += '@d{}\n\n';
        break;
      case 'text':
      default:
        if (content) {
          customMarkup += `${content}\n\n`;
        }
        break;
    }
  });
  
  document.getElementById('editor').value = customMarkup;
  updatePreview();
}

// 从自由输入内容生成表格数据
function generateTableFromFreeInput() {
  // 这个功能比较复杂，需要解析自由输入内容并转换为表格行
  // 为了简化，我们只更新预览
  updatePreview();
}

// 添加行功能
document.getElementById('add-row-btn').addEventListener('click', function() {
  rowCount++;
  const tbody = document.getElementById('editor-rows');
  const newRow = document.createElement('tr');
  
  newRow.innerHTML = `
    <td style="border: 1px solid #ddd; padding: 4px; text-align: center;">${rowCount}</td>
    <td style="border: 1px solid #ddd; padding: 4px;">
      <select class="marker-select" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;">
        <option value="@0{">主标题 (@0)</option>
        <option value="@1{">一级标题 (@1)</option>
        <option value="@2{">二级标题 (@2)</option>
        <option value="@3{">三级标题 (@3)</option>
        <option value="@4{">四级标题 (@4)</option>
        <option value="@5{">五级标题 (@5)</option>
        <option value="text">普通文本</option>
        <option value="**">加粗 (**)</option>
        <option value="*">斜体 (*)</option>
        <option value="__">下划线 (__)</option>
        <option value="---">水平线 (---)</option>
        <option value="@d{}">日期 (@d{})</option>
      </select>
    </td>
    <td style="border: 1px solid #ddd; padding: 4px;">
      <input type="text" class="content-input" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;" placeholder="请输入内容">
    </td>
    <td style="border: 1px solid #ddd; padding: 4px; text-align: center;">
      <button class="delete-row-btn" style="padding: 2px 6px; background: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer;">删除</button>
    </td>
  `;
  
  tbody.appendChild(newRow);
  
  // 绑定新行的事件
  bindRowEvents(newRow);
  
  // 更新预览
  updatePreview();
});

// 绑定行事件
function bindRowEvents(row) {
  // 绑定选择框变化事件
  const select = row.querySelector('.marker-select');
  const input = row.querySelector('.content-input');
  const deleteBtn = row.querySelector('.delete-row-btn');
  
  select.addEventListener('change', updatePreview);
  input.addEventListener('input', updatePreview);
  
  // 绑定删除按钮事件
  deleteBtn.addEventListener('click', function() {
    if (document.querySelectorAll('#editor-rows tr').length > 1) {
      row.remove();
      updateRowNumbers();
      updatePreview();
    } else {
      alert('至少保留一行');
    }
  });
}

// 更新行号
function updateRowNumbers() {
  const rows = document.querySelectorAll('#editor-rows tr');
  rows.forEach((row, index) => {
    row.cells[0].textContent = index + 1;
  });
  rowCount = rows.length;
}

// 更新预览
function updatePreview() {
  let customMarkup = '';
  
  if (currentMode === 'table') {
    // 表格模式
    const rows = document.querySelectorAll('#editor-rows tr');
    
    rows.forEach(row => {
      const select = row.querySelector('.marker-select');
      const input = row.querySelector('.content-input');
      
      const marker = select.value;
      const content = input.value;
      
      switch (marker) {
        case '@0{':
        case '@1{':
        case '@2{':
        case '@3{':
        case '@4{':
        case '@5{':
          if (content) {
            customMarkup += `${marker}${content}}\n\n`;
          }
          break;
        case '**':
          if (content) {
            customMarkup += `**${content}**\n\n`;
          }
          break;
        case '*':
          if (content) {
            customMarkup += `*${content}*\n\n`;
          }
          break;
        case '__':
          if (content) {
            customMarkup += `__${content}__\n\n`;
          }
          break;
        case '---':
          customMarkup += '---\n\n';
          break;
        case '@d{}':
          customMarkup += '@d{}\n\n';
          break;
        case 'text':
        default:
          if (content) {
            customMarkup += `${content}\n\n`;
          }
          break;
      }
    });
  } else {
    // 自由输入模式
    customMarkup = document.getElementById('editor').value;
  }
  
  // 使用现有的解析器解析标记
  document.getElementById('preview').innerHTML = parseCustomMarkup(customMarkup);
}

// 自定义标记解析函数（复制自编辑器中的函数）
function parseCustomMarkup(text) {
  // 处理日期标记
  text = text.replace(/@d\{([^}]*)\}/g, function(match, format) {
    if (!format) {
      return new Date().getFullYear() + '年' + (new Date().getMonth() + 1) + '月' + new Date().getDate() + '日';
    }
    
    // 简单的日期格式替换
    const now = new Date();
    return format.replace('YYYY', now.getFullYear())
                 .replace('MM', String(now.getMonth() + 1).padStart(2, '0'))
                 .replace('DD', String(now.getDate()).padStart(2, '0'));
  });
  
  // 处理标题标记
  text = text.replace(/@0\{([^}]+)\}/g, '<h1 class="main-title">$1</h1>');
  text = text.replace(/@1\{([^}]+)\}/g, '<h2 class="level1-title">$1</h2>');
  text = text.replace(/@2\{([^}]+)\}/g, '<h3 class="level2-title">$1</h3>');
  text = text.replace(/@3\{([^}]+)\}/g, '<h4 class="level3-title">$1</h4>');
  text = text.replace(/@4\{([^}]+)\}/g, '<h5 class="level4-title">$1</h5>');
  text = text.replace(/@5\{([^}]+)\}/g, '<h6 class="level5-title">$1</h6>');
  
  // 处理加粗
  text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
  
  // 处理斜体
  text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
  
  // 处理下划线
  text = text.replace(/__(.*?)__/g, '<u>$1</u>');
  
  // 处理水平线
  text = text.replace(/^---$/gm, '<hr class="divider">');
  
  // 处理换行（两个空格）
  text = text.replace(/  $/gm, '<br>');
  
  // 处理段落：将文本按空行分割成段落
  const paragraphs = text.split(/\n\s*\n/);
  let html = '';
  
  for (let i = 0; i < paragraphs.length; i++) {
    let paragraph = paragraphs[i];
    // 如果段落不是标题或hr等块级元素，则包装在p标签中
    if (!paragraph.match(/^<(h[1-6]|hr)/)) {
      // 处理段内换行
      paragraph = paragraph.replace(/\n/g, '<br>');
      html += '<p>' + paragraph + '</p>';
    } else {
      html += paragraph;
    }
  }
  
  return html;
}

// 初始化绑定第一行事件
bindRowEvents(document.querySelector('#editor-rows tr'));

// 初始化预览
updatePreview();

// 自由输入模式的实时预览
document.getElementById('editor').addEventListener('input', updatePreview);

// 打印功能（仅打印预览区域）
document.getElementById('print-btn').addEventListener('click', function() {
  let content = '';
  
  if (currentMode === 'table') {
    // 表格模式
    const rows = document.querySelectorAll('#editor-rows tr');
    const tableData = [];
    
    rows.forEach(row => {
      const select = row.querySelector('.marker-select');
      const input = row.querySelector('.content-input');
      
      tableData.push({
        marker: select.value,
        content: input.value
      });
    });
    
    content = tableData;
  } else {
    // 自由输入模式
    content = document.getElementById('editor').value;
  }
  
  // 发送到后端生成打印内容
  fetch('/document_format/print', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      content: content
    })
  })
  .then(response => response.text())
  .then(html => {
    // 创建打印窗口
    const printWindow = window.open('', '_blank');
    printWindow.document.write(html);
    printWindow.document.close();
    printWindow.focus();
    
    // 等待内容加载完成后打印
    setTimeout(() => {
      printWindow.print();
      printWindow.close();
    }, 250);
  })
  .catch(error => {
    console.error('Error:', error);
    alert('打印失败，请重试');
  });
});

// 导出功能
document.getElementById('export-btn').addEventListener('click', function() {
  let content = '';
  
  if (currentMode === 'table') {
    // 表格模式
    const rows = document.querySelectorAll('#editor-rows tr');
    const tableData = [];
    
    rows.forEach(row => {
      const select = row.querySelector('.marker-select');
      const input = row.querySelector('.content-input');
      
      tableData.push({
        marker: select.value,
        content: input.value
      });
    });
    
    content = tableData;
  } else {
    // 自由输入模式
    content = document.getElementById('editor').value;
  }
  
  const fileName = prompt('请输入文件名:', '拟文文档_' + new Date().toISOString().slice(0, 10));
  
  if (fileName) {
    // 创建表单数据并提交到后端
    fetch('/document_format/save_custom', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        content: content,
        fileName: fileName
      })
    })
    .then(response => {
      if (response.ok) {
        // 获取文件并触发下载
        return response.blob();
      }
      throw new Error('Network response was not ok.');
    })
    .then(blob => {
      // 创建下载链接
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = fileName.endsWith('.docx') ? fileName : fileName + '.docx';
      document.body.appendChild(a);
      a.click();
      a.remove();
      window.URL.revokeObjectURL(url);
    })
    .catch(error => {
      console.error('Error:', error);
      alert('导出失败，请重试');
    });
  }
});

// 帮助功能
document.getElementById('help-btn').addEventListener('click', function() {
  document.getElementById('help-modal').style.display = 'block';
});

document.getElementById('close-help').addEventListener('click', function() {
  document.getElementById('help-modal').style.display = 'none';
});

// 点击模态框外部关闭
document.getElementById('help-modal').addEventListener('click', function(e) {
  if (e.target === this) {
    this.style.display = 'none';
  }
});
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>