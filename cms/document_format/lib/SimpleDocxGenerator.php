<?php
/**
 * 简单的DOCX生成类
 */
class SimpleDocxGenerator {
    
    /**
     * 将HTML内容转换为DOCX格式并输出
     */
    public static function generateDocx($htmlContent, $filename = 'document.docx') {
        // 设置响应头
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // 创建一个简单的HTML文档作为DOCX的基础，包含严格的拟文格式样式
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: "仿宋_GB2312", "FangSong", serif;
                    font-size: 16px;
                    line-height: 28pt;
                    margin: 3.8cm 2.5cm 3.6cm 2.7cm;
                    word-wrap: break-word;
                    word-break: break-all;
                }
                
                h1 {
                    font-family: "方正小标宋简体", "FZSSJW", serif;
                    font-size: 22px;
                    text-align: center;
                    font-weight: normal;
                    margin: 0 0 30px 0;
                }
                
                h2 {
                    font-family: "黑体", "SimHei", sans-serif;
                    font-size: 16px;
                    font-weight: normal;
                    margin: 20px 0 10px 0;
                }
                
                h3 {
                    font-family: "楷体_GB2312", "KaiTi", serif;
                    font-size: 16px;
                    font-weight: normal;
                    margin: 15px 0 8px 0;
                }
                
                h4, h5, h6 {
                    font-family: "仿宋_GB2312", "FangSong", serif;
                    font-size: 16px;
                    font-weight: bold;
                    margin: 12px 0 6px 0;
                }
                
                p {
                    margin: 0 0 10px 0;
                    text-indent: 2em;
                    line-height: 28pt;
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
        </head>
        <body>
            ' . $htmlContent . '
        </body>
        </html>';
        
        // 输出HTML内容（在实际应用中，这里应该使用真正的DOCX库）
        echo $html;
    }
}