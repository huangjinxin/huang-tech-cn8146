<?php
require_once __DIR__ . '/Parsedown.php';

function parse_markdown(string $md): string {
    static $pd;
    if (!$pd) {
        $pd = new Parsedown();
        $pd->setBreaksEnabled(true);
        // $pd->setSafeMode(true); // 如需更安全可打开
    }
    $html = $pd->text($md);

    // 将相对图片路径（不以 http(s):/data:/开头，也不以 / 开头）统一前缀成 /media/
    // 例如: <img src="images/abc.jpg"> -> <img src="/media/images/abc.jpg">
    $html = preg_replace_callback(
        '/<img\s+[^>]*src=["\']([^"\']+)["\'][^>]*>/i',
        function($m) {
            $src = $m[1];
            // 已是绝对/协议/数据URI的，不改
            if (preg_match('#^(?:https?:|data:|/)#i', $src)) return $m[0];
            $new = '/media/' . ltrim($src, '/');
            return str_replace($src, $new, $m[0]);
        },
        $html
    );

    return $html;
}
