<!doctype html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title>消息调试</title>
    <style>
        *{
            margin:0;
            padding:0;
            font:400 12px/1.5 "arial";
        }
        body{
            background:#F8F8F8;
        }
        pre{
            color:#333;
        }
    </style>
</head>
<body>
<?php
    $message = isset($e['message']) ? $e['message'] : '';
    $line = isset($e['line']) ? $e['line'] : '';
    $file = isset($e['file']) ? $e['file'] : '';
    $trace = isset($e['trace']) ? $e['trace'] : '';

    echo '<pre style="padding:20px;"><br>';
    echo('消息：  '.$message.'<br>');
    echo('位置：  '.$line.'<br>');
    echo('文件：  '.$file.'<br><br>');
    echo($trace);
    echo '</pre>';
?>
</body>
</html>