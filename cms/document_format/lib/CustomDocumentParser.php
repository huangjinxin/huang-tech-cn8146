<?php
/**
 * 自定义拟文标记解析器
 */
class CustomDocumentParser {
    
    /**
     * 将自定义标记文本转换为HTML格式
     */
    public static function parseCustomMarkup($text) {
        // 处理日期标记
        $text = preg_replace_callback('/@d\{([^}]*)\}/', function($matches) {
            $format = $matches[1];
            if (empty($format)) {
                return date('Y年n月j日');
            }
            
            // 简单的日期格式替换
            $format = str_replace(['YYYY', 'MM', 'DD'], [date('Y'), date('m'), date('d')], $format);
            return $format;
        }, $text);
        
        // 处理标题标记
        $text = preg_replace('/@0\{([^}]+)\}/', '<h1 class="main-title">$1</h1>', $text);
        $text = preg_replace('/@1\{([^}]+)\}/', '<h2 class="level1-title">$1</h2>', $text);
        $text = preg_replace('/@2\{([^}]+)\}/', '<h3 class="level2-title">$1</h3>', $text);
        $text = preg_replace('/@3\{([^}]+)\}/', '<h4 class="level3-title">$1</h4>', $text);
        $text = preg_replace('/@4\{([^}]+)\}/', '<h5 class="level4-title">$1</h5>', $text);
        $text = preg_replace('/@5\{([^}]+)\}/', '<h6 class="level5-title">$1</h6>', $text);
        
        // 处理加粗
        $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
        
        // 处理斜体
        $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);
        
        // 处理下划线
        $text = preg_replace('/__(.*?)__/', '<u>$1</u>', $text);
        
        // 处理水平线
        $text = preg_replace('/^---$/m', '<hr class="divider">', $text);
        
        // 处理换行（两个空格）
        $text = preg_replace('/  $/m', '<br>', $text);
        
        // 处理段落：将文本按空行分割成段落
        $paragraphs = preg_split('/\n\s*\n/', $text);
        $html = '';
        
        foreach ($paragraphs as $paragraph) {
            // 如果段落不是标题或hr等块级元素，则包装在p标签中
            if (!preg_match('/^<(h[1-6]|hr)/', trim($paragraph))) {
                // 处理段内换行
                $paragraph = preg_replace('/\n/', '<br>', $paragraph);
                $html .= '<p>' . $paragraph . '</p>';
            } else {
                $html .= $paragraph;
            }
        }
        
        return $html;
    }
    
    /**
     * 生成用于打印的HTML内容（严格A4格式）
     */
    public static function generatePrintHtml($htmlContent) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset=\"UTF-8\">
            <title>拟文格式文档打印</title>
            " . self::generatePrintStyles() . "
        </head>
        <body>
            <div class=\"document-container\">
                {$htmlContent}
            </div>
        </body>
        </html>";
    }
    
    /**
     * 从表格数据生成自定义标记文本
     */
    public static function generateCustomMarkupFromTable($tableData) {
        $markup = '';
        
        foreach ($tableData as $row) {
            $marker = $row['marker'];
            $content = $row['content'];
            
            switch ($marker) {
                case '@0{':
                case '@1{':
                case '@2{':
                case '@3{':
                case '@4{':
                case '@5{':
                    if (!empty($content)) {
                        $markup .= "{$marker}{$content}}\n\n";
                    }
                    break;
                case '**':
                    if (!empty($content)) {
                        $markup .= "**{$content}**\n\n";
                    }
                    break;
                case '*':
                    if (!empty($content)) {
                        $markup .= "*{$content}*\n\n";
                    }
                    break;
                case '__':
                    if (!empty($content)) {
                        $markup .= "__{$content}__\n\n";
                    }
                    break;
                case '---':
                    $markup .= "---\n\n";
                    break;
                case '@d{}':
                    $markup .= "@d{}\n\n";
                    break;
                case 'text':
                default:
                    if (!empty($content)) {
                        $markup .= "{$content}\n\n";
                    }
                    break;
            }
        }
        
        return $markup;
    }
    
    /**
     * 生成严格的拟文格式CSS样式
     */
    public static function generateStrictStyles() {
        return "
        <style>
        @font-face {
            font-family: 'FZSSJW';
            src: url('/document_format/fonts/FZSSJW.ttf') format('truetype');
        }
        
        @font-face {
            font-family: 'FangSong_GB2312';
            src: url('/document_format/fonts/仿宋_GB2312.ttf') format('truetype');
        }
        
        @font-face {
            font-family: 'KaiTi_GB2312';
            src: url('/document_format/fonts/楷体_GB2312.ttf') format('truetype');
        }
        
        .document-container {
            font-family: 'FangSong_GB2312', '仿宋_GB2312', serif;
            font-size: 16px; /* 三号字体 */
            line-height: 28pt; /* 固定值28磅行距 */
            padding: 3.8cm 2.5cm 3.6cm 2.7cm; /* 上3.8cm，下3.6cm，左2.7cm，右2.5cm */
            width: 21cm; /* A4宽度 */
            min-height: 29.7cm; /* A4高度 */
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            word-wrap: break-word;
            word-break: break-all;
        }
        
        @media print {
            .document-container {
                box-shadow: none;
                margin: 0;
                padding: 3.8cm 2.5cm 3.6cm 2.7cm;
            }
            
            .site-header, .site-footer, .editor-panel {
                display: none;
            }
            
            .preview-panel {
                padding: 0;
                width: 100%;
            }
        }
        
        .main-title {
            font-family: 'FZSSJW', '方正小标宋简体', serif;
            font-size: 22px; /* 二号字体 */
            text-align: center;
            font-weight: normal;
            margin: 0 0 30px 0;
            line-height: 28pt;
        }
        
        .level1-title {
            font-family: 'SimHei', '黑体', sans-serif;
            font-size: 16px; /* 三号字体 */
            font-weight: normal;
            margin: 20px 0 10px 0;
            line-height: 28pt;
        }
        
        .level2-title {
            font-family: 'KaiTi_GB2312', '楷体_GB2312', serif;
            font-size: 16px; /* 三号字体 */
            font-weight: normal;
            margin: 15px 0 8px 0;
            line-height: 28pt;
        }
        
        .level3-title {
            font-family: 'FangSong_GB2312', '仿宋_GB2312', serif;
            font-size: 16px; /* 三号字体 */
            font-weight: bold;
            margin: 12px 0 6px 0;
            line-height: 28pt;
        }
        
        .level4-title {
            font-family: 'FangSong_GB2312', '仿宋_GB2312', serif;
            font-size: 16px; /* 三号字体 */
            font-weight: bold;
            margin: 10px 0 5px 0;
            line-height: 28pt;
        }
        
        .level5-title {
            font-family: 'FangSong_GB2312', '仿宋_GB2312', serif;
            font-size: 16px; /* 三号字体 */
            font-weight: bold;
            margin: 8px 0 4px 0;
            line-height: 28pt;
        }
        
        p {
            margin: 0 0 10px 0;
            text-indent: 2em; /* 首行缩进2字符 */
            line-height: 28pt; /* 固定值28磅行距 */
        }
        
        .divider {
            border: none;
            border-top: 1px solid #000;
            margin: 20px 0;
        }
        
        strong {
            font-weight: bold;
        }
        
        em {
            font-style: italic;
        }
        
        u {
            text-decoration: underline;
        }
        </style>
        ";
    }
    
    /**
     * 生成打印专用的CSS样式（严格A4格式）
     */
    public static function generatePrintStyles() {
        return "
        <style>
        @font-face {
            font-family: 'FZSSJW';
            src: url('/document_format/fonts/FZSSJW.ttf') format('truetype');
        }
        
        @font-face {
            font-family: 'FangSong_GB2312';
            src: url('/document_format/fonts/仿宋_GB2312.ttf') format('truetype');
        }
        
        @font-face {
            font-family: 'KaiTi_GB2312';
            src: url('/document_format/fonts/楷体_GB2312.ttf') format('truetype');
        }
        
        body {
            font-family: 'FangSong_GB2312', '仿宋_GB2312', serif;
            font-size: 16px; /* 三号字体 */
            line-height: 28pt; /* 固定值28磅行距 */
            margin: 0;
            padding: 3.8cm 2.5cm 3.6cm 2.7cm; /* 上3.8cm，右2.5cm，下3.6cm，左2.7cm */
            width: 21cm;
            min-height: 29.7cm;
            word-wrap: break-word;
            word-break: break-all;
        }
        
        @media print {
            body {
                padding: 3.8cm 2.5cm 3.6cm 2.7cm;
            }
            
            @page {
                margin: 3.8cm 2.5cm 3.6cm 2.7cm;
            }
        }
        
        .document-container {
            font-family: 'FangSong_GB2312', '仿宋_GB2312', serif;
            font-size: 16px;
            line-height: 28pt; /* 固定值28磅行距 */
            width: 100%;
            min-height: 100%;
        }
        
        .main-title {
            font-family: 'FZSSJW', '方正小标宋简体', serif;
            font-size: 22px; /* 二号字体 */
            text-align: center;
            font-weight: normal;
            margin: 0 0 30px 0;
            line-height: 28pt;
        }
        
        .level1-title {
            font-family: 'SimHei', '黑体', sans-serif;
            font-size: 16px; /* 三号字体 */
            font-weight: normal;
            margin: 20px 0 10px 0;
            line-height: 28pt;
        }
        
        .level2-title {
            font-family: 'KaiTi_GB2312', '楷体_GB2312', serif;
            font-size: 16px; /* 三号字体 */
            font-weight: normal;
            margin: 15px 0 8px 0;
            line-height: 28pt;
        }
        
        .level3-title {
            font-family: 'FangSong_GB2312', '仿宋_GB2312', serif;
            font-size: 16px; /* 三号字体 */
            font-weight: bold;
            margin: 12px 0 6px 0;
            line-height: 28pt;
        }
        
        .level4-title {
            font-family: 'FangSong_GB2312', '仿宋_GB2312', serif;
            font-size: 16px; /* 三号字体 */
            font-weight: bold;
            margin: 10px 0 5px 0;
            line-height: 28pt;
        }
        
        .level5-title {
            font-family: 'FangSong_GB2312', '仿宋_GB2312', serif;
            font-size: 16px; /* 三号字体 */
            font-weight: bold;
            margin: 8px 0 4px 0;
            line-height: 28pt;
        }
        
        p {
            margin: 0 0 10px 0;
            text-indent: 2em; /* 首行缩进2字符 */
            line-height: 28pt; /* 固定值28磅行距 */
        }
        
        .divider {
            border: none;
            border-top: 1px solid #000;
            margin: 20px 0;
        }
        
        strong {
            font-weight: bold;
        }
        
        em {
            font-style: italic;
        }
        
        u {
            text-decoration: underline;
        }
        </style>
        ";
    }
}