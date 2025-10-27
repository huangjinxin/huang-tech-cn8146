---
title: 多级目录使用指南
date: 2025-09-19
---

# 多级目录功能使用指南

## 功能介绍

现在系统支持多级目录结构，你可以在 `playground` 目录下创建任意层级的目录结构，系统会自动识别并展示。

## 如何使用

### 1. 创建多级目录结构

你可以在 `playground` 目录下创建任意层级的目录，例如：

```
playground/
├── 班级A/
│   ├── 学生1/
│   │   ├── 作品集1/
│   │   │   └── 作品1.html
│   │   ├── 作品集2/
│   │   │   └── 作品2.html
│   │   └── 简介.html
│   └── 学生2/
│       └── 作品.html
├── 班级B/
│   └── 学生3/
│       └── 作品.html
└── 公共作品/
    └── 示例.html
```

### 2. 添加HTML作品

在任意层级的目录中添加HTML文件：

```bash
# 创建目录
mkdir -p playground/班级A/学生1/作品集1

# 创建HTML文件
echo "<html><head><title>我的作品</title></head><body><h1>作品展示</h1></body></html>" > playground/班级A/学生1/作品集1/mywork.html
```

### 3. 浏览作品

1. 访问 `/play` 查看顶层目录
2. 点击目录进入子目录
3. 点击作品查看具体内容
4. 使用"返回上级"链接导航到上一级目录

## 注意事项

1. 系统会自动识别所有层级的目录和HTML文件
2. 目录名称和文件名称支持中文
3. HTML文件中的`<title>`标签内容将作为作品标题显示
4. 如果HTML文件没有`<title>`标签，文件名将作为标题显示
5. 系统完全向后兼容，原有的单级目录结构仍然可以正常工作

## 示例

### 创建一个完整的多级结构示例：

```bash
# 创建班级目录
mkdir -p playground/三年级/1班/张三/优秀作品

# 添加作品
cat > playground/三年级/1班/张三/index.html << EOF
<!DOCTYPE html>
<html>
<head>
    <title>张三的作品集</title>
</head>
<body>
    <h1>欢迎来到我的作品集</h1>
    <p>这里展示了我的所有作品</p>
</body>
</html>
EOF

cat > playground/三年级/1班/张三/优秀作品/画画作品.html << EOF
<!DOCTYPE html>
<html>
<head>
    <title>我的画画作品</title>
</head>
<body>
    <h1>画画作品展示</h1>
    <p>这是我最近的画画作品</p>
</body>
</html>
EOF
```

创建完成后，访问 `/play` 就可以看到完整的目录结构，并可以逐级浏览到具体的作品。