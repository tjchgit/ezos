<h3>EZOS 简介</h3>
<p>EZOS是一个免费的开源的，快速，简单的面向对象的轻量级PHP开发框架，是为了敏捷WEB应用开发和简化项目为目的而开发的，注重代码易用性。目前EZOS代码的开发和维护只有我自己一人，这个框架其实更多的是为了学习PHP而诞生的。</p>
====

<h3>EZOS 时间线</h3>
<p> <em>20140803`</em>&nbsp;&nbsp;添加数据库缓存驱动，实现利用数据库缓存。</p>
<p> <em>20140731`</em>&nbsp;&nbsp;添加了子域名部署模式。</p>
<p> <em>20140729`</em>&nbsp;&nbsp;添加了对静态和正则路由的支持。</p>
<p> <em>20140727`</em>&nbsp;&nbsp;上传EZOS框架，开始在GITHUB上进行维护。</p>
===
<h3>EZOS特性</h3>
<h4>全面的WEB开发特性支持</h4>
<ol>
    <li>MVC支持 - 基于多层模型(M)，视图(V)，控制器(C)的设计模式</li>
    <li>ORM支持 - 使用Thinkphp数据库操作方式目前进支持Mysql和Mysqli</li>
    <li>模版引擎 - 采用知名的Smarty模板引擎，减小学习成本，保证性能</li>
    <li>缓存支持 - 提供了包括文件 数据库 Memcache Redis 等多种类型的缓存支持</li>
    <li>模块化 - 开发采用模块化方式，减小之间依赖。</li>
    <li>子域名部署 - 支持单项目子域名部署。</li>
    <li>路由功能支持 - 支持正则路由和静态路由两种部署方式。</li>
</ol>
<h4>安全性</h4>
<ol>
    <li>XSS安全防护</li>
    <li>表单自动验证</li>
    <li>强制数据类型转换</li>
    <li>输入数据过滤</li>
    <li>表单令牌验证</li>
    <li>防SQL注入</li>
    <li>图像上传检测</li>
</ol>
<h4>如何使用</h4>
<p>index.php是站点的唯一入口，写入一下内容。</p>
<pre>
<code>
&lt;?php
include './apps/base/kernel.php';
kernel::boot();
</code>
</pre>
