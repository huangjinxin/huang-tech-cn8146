---
title: 练习页面接入
date: 2025-08-20
tags: [playground, 练习]
---

本系统支持将 **练习 HTML 页面** 嵌入主框架，保证风格统一。

### 使用步骤
1. 在 `cms/playground/` 下放置一个 HTML 文件，例如 `test.html`；
2. 浏览器访问 `/play/test`；
3. 系统会读取 `test.html` 的内容，嵌入到 `layout.php` 的 `<main>` 区域；
4. Header 和 Footer 自动保持一致。

这让你可以 **快速实验 HTML/CSS/JS**，而不用重复写头部和尾部。

