<!doctype html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title>EXCEPTION</title>
    <style>
        *{margin:0; padding:0; font:400 12/1.5 "arial"}
    </style>
</head>
<body>
<?php
    $message = isset($e['message']) ? $e['message'] : '';
    $line = isset($e['line']) ? $e['line'] : '';
    $file = isset($e['file']) ? $e['file'] : '';
    $trace = isset($e['trace']) ? $e['trace'] : '';

    echo '<pre style="padding:20px;"><br>';
    echo('message:  '.$message.'<br>');
    echo('line   :  '.$line.'<br>');
    echo('file   :  '.$file.'<br><br>');
    echo($trace);
    echo '</pre>';
?>
</body>
</html>