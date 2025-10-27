#!/bin/bash
# 一键启动 PHP 开发服务器 + 确保 Markdown 渲染器

BASE_DIR="$(cd "$(dirname "$0")"; pwd)"
CMS_DIR="$BASE_DIR/cms"
LIB_DIR="$CMS_DIR/lib"
PD_FILE="$LIB_DIR/Parsedown.php"

# 1. 确保 lib 目录存在
mkdir -p "$LIB_DIR"

# 2. 检查 Parsedown.php 是否存在，不存在就下载
if [ ! -f "$PD_FILE" ]; then
  echo "[INFO] 未找到 Parsedown.php，正在下载..."
  curl -L -o "$PD_FILE" https://raw.githubusercontent.com/erusev/parsedown/1.7.4/Parsedown.php
  if [ $? -ne 0 ]; then
    echo "[ERROR] 下载 Parsedown.php 失败，请检查网络。"
    exit 1
  fi
  echo "[INFO] Parsedown.php 已下载到 $PD_FILE"
fi

# 3. 启动 PHP 内置服务器
echo "[INFO] 启动 PHP 内置服务器，监听 http://0.0.0.0:8146"
echo "[INFO] 根目录: $CMS_DIR/public"
#php -S 0.0.0.0:8146 -t "$CMS_DIR/public" "$CMS_DIR/public/index.php"
#php -d error_reporting=E_ALL^E_DEPRECATED -S 0.0.0.0:8146 -t "$CMS_DIR/public" "$CMS_DIR/public/index.php"
php -d error_reporting=E_ALL^E_DEPRECATED -S 0.0.0.0:8146 -t "$CMS_DIR/public"
