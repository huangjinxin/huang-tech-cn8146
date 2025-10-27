<?php
/**
 * 拟文格式处理类
 */
class DocumentFormatter {
    
    /**
     * 将标记文本转换为HTML格式
     */
    public static function parseMarkdown($text) {
        $html = htmlspecialchars($text);
        
        // 转换换行符
        $html = nl2br($html);
        
        // 处理标题
        $html = preg_replace('/^# (.+)$/m', '<h1 class="main-title">$1</h1>', $html);
        $html = preg_replace('/^## (.+)$/m', '<h2 class="level1-title">$1</h2>', $html);
        $html = preg_replace('/^### (.+)$/m', '<h3 class="level2-title">$1</h3>', $html);
        $html = preg_replace('/^#### (.+)$/m', '<h4 class="level3-title">$1</h4>', $html);
        $html = preg_replace('/^##### (.+)$/m', '<h5 class="level4-title">$1</h5>', $html);
        $html = preg_replace('/^###### (.+)$/m', '<h6 class="level5-title">$1</h6>', $html);
        
        // 处理加粗
        $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
        
        // 处理斜体
        $html = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $html);
        
        // 处理下划线
        $html = preg_replace('/__(.*?)__/', '<u>$1</u>', $html);
        
        // 处理水平线
        $html = preg_replace('/^---$/m', '<hr>', $html);
        
        return $html;
    }
    
    /**
     * 生成CSS样式
     */
    public static function generateStyles() {
        return "
        <style>
        @font-face {
            font-family: '方正小标宋简体';
            src: url('/document_format/fonts/FZSSJW.eot'); /* IE9 */
            src: url('/document_format/fonts/FZSSJW.eot?#iefix') format('embedded-opentype'), /* IE6-IE8 */
            url('/document_format/fonts/FZSSJW.woff') format('woff'), /* modern browsers */
            url('/document_format/fonts/FZSSJW.ttf') format('truetype'); /* Safari, Android, iOS */
            font-weight: normal;
            font-style: normal;
        }
        
        @font-face {
            font-family: '仿宋_GB2312';
            src: url('/document_format/fonts/仿宋_GB2312.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        
        @font-face {
            font-family: '楷体_GB2312';
            src: url('/document_format/fonts/楷体_GB2312.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        
        .document-container {
            font-family: '仿宋_GB2312', 'FangSong', serif;
            font-size: 16px;
            line-height: 1.25;
            padding: 3.8cm 2.5cm 3.6cm 2.7cm;
            width: 21cm;
            min-height: 29.7cm;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        @media print {
            .document-container {
                box-shadow: none;
                margin: 0;
                padding: 3.8cm 2.5cm 3.6cm 2.7cm;
            }
        }
        
        .main-title {
            font-family: '方正小标宋简体', 'STSong', serif;
            font-size: 22px;
            text-align: center;
            font-weight: normal;
            margin: 0 0 30px 0;
        }
        
        .level1-title {
            font-family: '黑体', 'SimHei', sans-serif;
            font-size: 16px;
            font-weight: normal;
            margin: 20px 0 10px 0;
        }
        
        .level2-title {
            font-family: '楷体_GB2312', 'KaiTi', serif;
            font-size: 16px;
            font-weight: normal;
            margin: 15px 0 8px 0;
        }
        
        .level3-title {
            font-family: '仿宋_GB2312', 'FangSong', serif;
            font-size: 16px;
            font-weight: bold;
            margin: 12px 0 6px 0;
        }
        
        .level4-title {
            font-family: '仿宋_GB2312', 'FangSong', serif;
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0 5px 0;
        }
        
        .level5-title {
            font-family: '仿宋_GB2312', 'FangSong', serif;
            font-size: 16px;
            font-weight: bold;
            margin: 8px 0 4px 0;
        }
        
        p {
            margin: 0 0 10px 0;
            text-indent: 2em;
        }
        
        hr {
            border: none;
            border-top: 1px solid #000;
            margin: 20px 0;
        }
        </style>
        ";
    }
}