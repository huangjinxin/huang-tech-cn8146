<?php
declare(strict_types=1);

$base = dirname(__DIR__);
require_once $base . '/lib/markdown.php';

$uri = strtok($_SERVER['REQUEST_URI'], '?') ?: '/';

function render($view, $vars = []) {
    extract($vars);
    ob_start();
    include __DIR__ . '/../views/' . $view . '.php';
    return ob_get_clean();
}

/* ============== 工具函数 ============== */

/* 支持中文 slug + 防止路径穿越 */
function sanitize_slug(string $slug): string {
    $slug = urldecode($slug);
    $slug = str_replace(['..', '/', '\\'], '', $slug);
    return trim($slug);
}

/* 支持多级路径的清理函数 */
function sanitize_path(string $path): string {
    $path = urldecode($path);
    // 移除危险字符，但保留路径分隔符
    $path = str_replace(['..', '\\'], '', $path);
    // 移除开头和结尾的斜杠
    $path = trim($path, '/');
    return $path;
}

/* /media 相对路径清理 */
function sanitize_relpath(string $p): string {
    $p = urldecode($p);
    $p = ltrim($p, '/');
    $p = str_replace(['..', '\\'], '', $p);
    return trim($p);
}

/* 列出所有文章的元数据（title/date/slug） */
function load_posts_meta(string $dir): array {
    $files = glob($dir . '/*.md') ?: [];
    $out = [];
    foreach ($files as $f) {
        $slug = basename($f, '.md');
        $raw  = file_get_contents($f);
        $title = $slug; $date = '';
        if ($raw !== false && preg_match('/^---\R(.*?)\R---\R/s', $raw, $m)) {
            $yaml = trim($m[1]);
            foreach (preg_split("/\R/", $yaml) as $line) {
                if (!str_contains($line, ':')) continue;
                [$k, $v] = array_map('trim', explode(':', $line, 2));
                if ($k === 'title') $title = trim($v, "\"' ");
                if ($k === 'date')  $date  = trim($v, "\"' ");
            }
        }
        $out[] = ['slug'=>$slug, 'title'=>$title, 'date'=>$date];
    }
    usort($out, fn($a,$b)=> strcmp($b['date']??'', $a['date']??'') ?: strcmp($a['title'],$b['title']));
    return $out;
}

/* Playground：递归列目录（支持多级目录） */
function load_play_dirs(string $playRoot, string $subPath = ''): array {
    $scanPath = $subPath ? $playRoot . '/' . $subPath : $playRoot;
    $items = glob($scanPath . '/*') ?: [];
    $out = [];
    
    foreach ($items as $item) {
        $name = basename($item);
        $relativePath = $subPath ? $subPath . '/' . $name : $name;
        
        if (is_dir($item)) {
            // 获取该目录下的HTML文件数量
            $htmlCount = count(glob($item . '/*.html') ?: []);
            
            // 递归获取子目录
            $subDirs = load_play_dirs($playRoot, $relativePath);
            $subDirCount = count($subDirs);
            
            $out[] = [
                'name' => $name,
                'path' => $relativePath,
                'count' => $htmlCount,
                'type' => 'dir',
                'subdirs' => $subDirs
            ];
        }
    }
    
    usort($out, fn($a,$b)=> strcmp($a['name'], $b['name']));
    return $out;
}

/* 获取指定路径下的目录列表 */
function get_play_dirs_at_path(string $playRoot, string $path = ''): array {
    $scanPath = $path ? $playRoot . '/' . $path : $playRoot;
    $items = glob($scanPath . '/*', GLOB_ONLYDIR) ?: [];
    $out = [];
    
    foreach ($items as $item) {
        $name = basename($item);
        $relativePath = $path ? $path . '/' . $name : $name;
        $count = count(glob($item . '/*.html') ?: []);
        $out[] = ['name'=>$name, 'path'=>$relativePath, 'count'=>$count, 'type'=>'dir'];
    }
    
    usort($out, fn($a,$b)=> strcmp($a['name'], $b['name']));
    return $out;
}

/* Playground：列某目录下的 html 文件（读取 <title> 作为展示名） */
function load_play_files(string $dirPath): array {
    $files = glob($dirPath . '/*.html') ?: [];
    $out = [];
    foreach ($files as $f) {
        $name = basename($f, '.html');
        $title = $name;
        $raw = @file_get_contents($f);
        if ($raw && preg_match('/<title[^>]*>(.*?)<\/title>/is', $raw, $m)) {
            $title = trim(html_entity_decode($m[1]));
        }
        $out[] = ['name'=>$name, 'title'=>$title];
    }
    usort($out, fn($a,$b)=> strcmp($a['title'], $b['title']));
    return $out;
}

/* 获取指定路径下的文件列表（支持子目录） */
function get_play_files_at_path(string $playRoot, string $path = ''): array {
    $scanPath = $path ? $playRoot . '/' . $path : $playRoot;
    $files = glob($scanPath . '/*.html') ?: [];
    $out = [];
    
    foreach ($files as $f) {
        $name = basename($f, '.html');
        $title = $name;
        $raw = @file_get_contents($f);
        if ($raw && preg_match('/<title[^>]*>(.*?)<\/title>/is', $raw, $m)) {
            $title = trim(html_entity_decode($m[1]));
        }
        $relativePath = $path ? $path . '/' . $name : $name;
        $out[] = ['name'=>$name, 'title'=>$title, 'path'=>$relativePath];
    }
    
    usort($out, fn($a,$b)=> strcmp($a['title'], $b['title']));
    return $out;
}

/* 获取所有路径下的所有文件（递归） */
function get_all_play_files(string $playRoot, string $subPath = ''): array {
    $scanPath = $subPath ? $playRoot . '/' . $subPath : $playRoot;
    $items = glob($scanPath . '/*') ?: [];
    $out = [];
    
    foreach ($items as $item) {
        $name = basename($item);
        $relativePath = $subPath ? $subPath . '/' . $name : $name;
        
        if (is_dir($item)) {
            // 递归获取子目录中的文件
            $subFiles = get_all_play_files($playRoot, $relativePath);
            $out = array_merge($out, $subFiles);
        } else if (pathinfo($item, PATHINFO_EXTENSION) === 'html') {
            // 处理HTML文件
            $fileName = pathinfo($item, PATHINFO_FILENAME);
            $title = $fileName;
            $raw = @file_get_contents($item);
            if ($raw && preg_match('/<title[^>]*>(.*?)<\/title>/is', $raw, $m)) {
                $title = trim(html_entity_decode($m[1]));
            }
            $out[] = ['name'=>$fileName, 'title'=>$title, 'path'=>$relativePath];
        }
    }
    
    usort($out, fn($a,$b)=> strcmp($a['title'], $b['title']));
    return $out;
}

/* ============== 路由 ============== */

/* 首页：九宫格入口 */
if ($uri === '/' || $uri === '/home') {
    echo render('home', ['title' => '首页']);
    exit;
}

/* Playground 顶层：/play 或 /play/ —— 显示所有子目录和所有文件 */
if ($uri === '/play' || $uri === '/play/') {
    $dirs = get_play_dirs_at_path($base . '/playground');
    $files = get_all_play_files($base . '/playground');
    echo render('play_index', ['title'=>'HTML 练习目录', 'dirs'=>$dirs, 'files'=>$files, 'path'=>'']);
    exit;
}

/* 旧链接保护：有人/缓存还点到 /play/hello 时，统一跳回目录页 */
if ($uri === '/play/hello') {
    header('Location: /play', true, 302);
    exit;
}

/* ✅ 保留"iframe 展示"唯一版本：/play/{dir}/{name} */
/* 处理作品展示：/play/path/to/dir/filename (更具体的路由) */
if (preg_match('#^/play/(.+?)/([^/]+)$#u', $uri, $m)) {
    $path = sanitize_path($m[1]);  // 可能包含子目录
    $name = sanitize_slug($m[2]);
    
    // 处理多级目录路径
    $file = $base . '/playground/' . $path . '/' . $name . '.html';
    if (is_file($file)) {
        // 从路径中提取目录名
        $parts = explode('/', $path);
        $dir = end($parts);
        
        echo render('play_show', [
            'title' => "少儿作品 - $path / $name",
            'dir'   => $dir,
            'path'  => $path,
            'name'  => $name,
        ]);
        exit;
    }
}

/* Playground 多级目录：/play/path/to/dir (更通用的路由) */
if (preg_match('#^/play/(.+)$#u', $uri, $m)) {
    $path = sanitize_path($m[1]);
    
    // 如果是目录，显示该目录下的内容
    $dirPath = $base . '/playground/' . $path;
    if (is_dir($dirPath)) {
        // 获取子目录
        $dirs = get_play_dirs_at_path($base . '/playground', $path);
        // 获取HTML文件
        $files = get_play_files_at_path($base . '/playground', $path);
        
        // 获取父级路径用于导航
        $parentPath = dirname($path);
        $parentPath = $parentPath === '.' ? '' : $parentPath;
        
        echo render('play_dir', [
            'title'=>"HTML 练习 - " . basename($path),
            'dir'=>basename($path),
            'path'=>$path,
            'parentPath'=>$parentPath,
            'dirs'=>$dirs,
            'files'=>$files
        ]);
        exit;
    }
}

/* ✅ 保留"iframe 展示"唯一版本：/play/{dir}/{name} */
if (preg_match('#^/play/(.+?)/([^/]+)$#u', $uri, $m)) {
    $path = sanitize_path($m[1]);  // 可能包含子目录
    $name = sanitize_slug($m[2]);
    
    // 处理多级目录路径
    $file = $base . '/playground/' . $path . '/' . $name . '.html';
    if (is_file($file)) {
        // 从路径中提取目录名
        $parts = explode('/', $path);
        $dir = end($parts);
        
        echo render('play_show', [
            'title' => "少儿作品 - $path / $name",
            'dir'   => $dir,
            'path'  => $path,
            'name'  => $name,
        ]);
        exit;
    }
}

/* Playground 原始内容（供 iframe 使用）：/play_raw/{dir}/{name} */
if (preg_match('#^/play_raw/(.+?)/([^/]+)$#u', $uri, $m)) {
    $path = sanitize_path($m[1]);  // 可能包含子目录
    $name = sanitize_slug($m[2]);
    
    $file = $base . '/playground/' . $path . '/' . $name . '.html';
    if (is_file($file)) {
        header('Content-Type: text/html; charset=utf-8');
        header("X-Content-Type-Options: nosniff");
        readfile($file);
        exit;
    }
    http_response_code(404); echo "Not Found"; exit;
}

/* 阅读器：/read/{slug} —— 左侧统一目录 + 右侧内容（usage 页显示欢迎文案） */
if (preg_match('#^/read/([^/]+)$#u', $uri, $m)) {
    $slug = sanitize_slug($m[1]);
    $file = $base . '/content/' . $slug . '.md';
    if (is_file($file)) {
        $raw = file_get_contents($file);
        $meta = ['title' => $slug, 'date' => '', 'tags' => []];
        if (preg_match('/^---\R(.*?)\R---\R(.*)$/s', $raw, $mm)) {
            $yaml = trim($mm[1]); $body = trim($mm[2]);
            foreach (preg_split("/\R/", $yaml) as $line) {
                if (!str_contains($line, ':')) continue;
                [$k, $v] = array_map('trim', explode(':', $line, 2));
                if ($k === 'tags' && preg_match('/\[(.*?)\]/', $v, $mm2)) {
                    $meta['tags'] = array_values(array_filter(array_map('trim', explode(',', $mm2[1]))));
                } else {
                    $meta[$k] = trim($v, "\"' ");
                }
            }
        } else {
            $body = $raw;
        }
        $html = parse_markdown($body);

        $allPosts = load_posts_meta($base . '/content');
        $welcome  = ($slug === 'usage使用手册');

        echo render('read', [
            'title'   => $meta['title'] ?: $slug,
            'content' => $html,
            'meta'    => $meta,
            'posts'   => $allPosts,
            'welcome' => $welcome,
        ]);
        exit;
    }
}

/* 旧链接 /post/{slug} -> /read/{slug} */
if (preg_match('#^/post/([^/]+)$#u', $uri, $m)) {
    $slug = sanitize_slug($m[1]);
    header("Location: /read/" . rawurlencode($slug), true, 301);
    exit;
}

/* 静态资源映射：/media/{relpath} -> content/{relpath} */
if (preg_match('#^/media/(.+)$#', $uri, $m)) {
    $rel = sanitize_relpath($m[1]);
    $full = $base . '/content/' . $rel;
    if (is_file($full)) {
        $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
        $mime = [
            'jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','webp'=>'image/webp','svg'=>'image/svg+xml',
            'bmp'=>'image/bmp','avif'=>'image/avif',
            'txt'=>'text/plain','md'=>'text/markdown','pdf'=>'application/pdf'
        ][$ext] ?? 'application/octet-stream';
        header('Content-Type: '.$mime);
        header('Cache-Control: public, max-age=86400');
        readfile($full);
        exit;
    }
    http_response_code(404); echo "Not Found"; exit;
}

/* 字体文件路由 */
if (preg_match('#^/document_format/fonts/(.+)$#', $uri, $m)) {
    $fontFile = sanitize_relpath($m[1]);
    $fullPath = $base . '/document_format/fonts/' . $fontFile;
    
    if (is_file($fullPath)) {
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mime = [
            'eot'=>'application/vnd.ms-fontobject',
            'woff'=>'font/woff',
            'woff2'=>'font/woff2',
            'ttf'=>'font/ttf',
            'otf'=>'font/otf'
        ][$ext] ?? 'application/octet-stream';
        
        header('Content-Type: '.$mime);
        header('Cache-Control: public, max-age=31536000'); // 1年缓存
        readfile($fullPath);
        exit;
    }
    http_response_code(404); echo "Not Found"; exit;
}

/* 拟文格式页面 */
if ($uri === '/document_format') {
    require_once $base . '/document_format/lib/CustomDocumentParser.php';
    echo render('document_format/editor', ['title' => '拟文格式编辑器']);
    exit;
}

/* 拟文格式API：保存自定义标记文档 */
if ($uri === '/document_format/save_custom') {
    // 只允许POST请求
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }
    
    // 获取JSON数据
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['content'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid request data']);
        exit;
    }
    
    $content = $input['content'];
    $fileName = $input['fileName'] ?? 'document_' . date('Ymd_His');
    
    // 确保文件名以.docx结尾
    if (substr($fileName, -5) !== '.docx') {
        $fileName .= '.docx';
    }
    
    // 生成DOCX文件并直接输出
    require_once $base . '/document_format/lib/CustomDocumentParser.php';
    require_once $base . '/document_format/lib/SimpleDocxGenerator.php';
    
    // 如果content是数组（表格数据），则转换为标记文本
    if (is_array($content)) {
        $content = CustomDocumentParser::generateCustomMarkupFromTable($content);
    }
    
    $htmlContent = CustomDocumentParser::parseCustomMarkup($content);
    SimpleDocxGenerator::generateDocx($htmlContent, $fileName);
    exit;
}

/* 拟文格式API：生成打印内容 */
if ($uri === '/document_format/print') {
    // 只允许POST请求
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }
    
    // 获取JSON数据
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['content'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid request data']);
        exit;
    }
    
    $content = $input['content'];
    
    // 生成打印内容
    require_once $base . '/document_format/lib/CustomDocumentParser.php';
    
    // 如果content是数组（表格数据），则转换为标记文本
    if (is_array($content)) {
        $content = CustomDocumentParser::generateCustomMarkupFromTable($content);
    }
    
    $htmlContent = CustomDocumentParser::parseCustomMarkup($content);
    $printHtml = CustomDocumentParser::generatePrintHtml($htmlContent);
    
    // 返回打印HTML
    header('Content-Type: text/html; charset=utf-8');
    echo $printHtml;
    exit;
}

/* API路由：保存文件 */
if ($uri === '/play_api/save_file') {
    // 只允许POST请求
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }
    
    // 获取JSON数据
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['fileName']) || !isset($input['content'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid request data']);
        exit;
    }
    
    $path = sanitize_path($input['path'] ?? '');
    $fileName = sanitize_slug($input['fileName']);
    $content = $input['content'];
    
    // 确保文件名以.html结尾
    if (substr($fileName, -5) !== '.html') {
        $fileName .= '.html';
    }
    
    // 构建完整路径
    $fullPath = $base . '/playground/' . ($path ? $path . '/' : '') . $fileName;
    
    // 确保目录存在
    $dirPath = dirname($fullPath);
    if (!is_dir($dirPath)) {
        mkdir($dirPath, 0755, true);
    }
    
    // 保存文件
    if (file_put_contents($fullPath, $content) !== false) {
        echo json_encode(['success' => true, 'message' => 'File saved successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to save file']);
    }
    exit;
}

/* 兜底 */
http_response_code(404);
echo "Not Found";
