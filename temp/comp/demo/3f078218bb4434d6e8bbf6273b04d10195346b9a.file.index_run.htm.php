<?php /* Smarty version Smarty-3.1.18, created on 2014-08-14 09:41:09
         compiled from "D:\root\git\ezos\apps\demo\view\index_run.htm" */ ?>
<?php /*%%SmartyHeaderCode:3194553eb3ae88c6118-13319008%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '3f078218bb4434d6e8bbf6273b04d10195346b9a' => 
    array (
      0 => 'D:\\root\\git\\ezos\\apps\\demo\\view\\index_run.htm',
      1 => 1407980467,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '3194553eb3ae88c6118-13319008',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_53eb3ae88c8230_14148403',
  'variables' => 
  array (
    'var' => 0,
    'foo' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_53eb3ae88c8230_14148403')) {function content_53eb3ae88c8230_14148403($_smarty_tpl) {?><?php if (!is_callable('smarty_modifier_debug_print_var')) include 'D:\\root\\git\\ezos\\apps\\base\\lib\\vendor\\smarty\\plugins\\modifier.debug_print_var.php';
?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title>测试控制器</title>
</head>
<body>
<h1>控制器默认方法RUN</h1>
<p>这里的是控制器分配过来的变量<?php echo $_smarty_tpl->tpl_vars['var']->value;?>
</p>
<?php $_smarty_tpl->tpl_vars['foo'] = new Smarty_variable(array(1,2,3,4,5,6,7), null, 0);?>
<?php echo smarty_modifier_debug_print_var($_smarty_tpl->tpl_vars['foo']->value);?>

</body>
</html><?php }} ?>
