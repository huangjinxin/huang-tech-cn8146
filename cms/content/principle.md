
---

## 3. `cms/content/principle.md`
```markdown
---
title: 系统运行原理
date: 2025-08-19
tags: [原理, 路由]
---

系统运行的基本原理如下：

1. **统一入口**  
   所有请求进入 `public/index.php`，由 PHP 路由判断路径：
   - `/` 或 `/home` → 首页；
   - `/post/{slug}` → 文章详情；
   - `/play/{name}` → 练习页面。

2. **内容解析**  
   - 文章内容保存在 `content/*.md`；
   - 系统会读取文件 → 分离 Front-Matter 与正文；
   - Front-Matter 里的 `title/date/tags` 用来生成列表和元信息。

3. **模板渲染**  
   - 页面框架由 `views/layout.php` 控制；
   - 中间区域根据路由不同插入 `home.php`、`post.php` 或练习页面 HTML。

4. **输出结果**  
   - 最终生成统一风格的 HTML，返回给浏览器。
