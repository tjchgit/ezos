$(function(){
    $('pre.pre').heightCode();
});
(function($){
    $.fn.extend({
        heightCode : function(opt){
            var language = {
                'js' : {
                    'doc' : /.?(\/\*{2}[\s\S]*?\*\/)/,  //文档注释
                    'com' : /(\/\*(?!\*)[\s\S]*?\*\/|\/\/.*)/,  //普通注释
                    'str' : /('(?:(?:\\'|[^'\r\n])*?)'|"(?:(?:\\"|[^"\r\n])*?)")/, //字符串
                    'key' : /(?:[^\$_@\w])(break|delete|function|return|typeof|arguments|case|do|if|switch|var|catch|else|in|this|void|continue|instanceof|throw|while|debugger|finally|new|with|default|for|null|try)(?![$_@\w])/, //关键字
                    'obj' : /(?:[^\$_@\w])(Array|String|Date|Math|Boolean|Number|Function|Global|Error|RegExp|Object|window|document)(?:[^$_@\w])/,  //内置对象
                    'num' : /\b(\d+(?:\.\d+)?(?:[Ee][-+]?(?:\d)+)?|NaN|Infinity)\b/,  //数字
                    'bol' : /(?:[^$_@\w])(true|false)(?:[^$_@\w])/, //布尔值
                    'ope' : /(==|=|===|\+|-|\+=|-=|\*=|\\=|%=|&lt;|&lt;=|&gt;|&gt;=)/  //操作符
                },
                'css' : {
                    'com' : /(\/\*[\s\S]*?\*\/)/,                   //注释
                    'key' : /([^{\n\$\|]*?){/,                      //选择器
                    'obj' : /(?:([\w-]+?)\s*\:([\w\s"',\-\#]*))/    //属性名：属性值
                },
                'html' :{
                    'com' : /(&lt;\!--[\s\S]*?--&gt;)/,             //注释
                    'mrk' : /(&lt;\/?\w+(?:.*?)&gt;)/               //标签
                },
                'php' : {
                    'com':/(\/\*[\s\S]*?\*\/|\/\/.*|&lt;\!--[\s\S]*?--&gt;)/ ,
                    'mrk':/(&lt;\?php|\?&gt;)/ ,
                    'str':/('(?:(?:\\'|[^'\r\n])*?)'|"(?:(?:\\"|[^"\r\n])*?)")/,
                    'key':/(?:[^$_@a-zA-Z0-9])?(and|or|xor|array|as|break|case|cfunction|class|const|continue|declare|default|die|do|else|elseif|enddeclare|endfor|endforeach|endif|endswitch|endwhile|extends|for|foreach|function|include|include_once|global|if|new|return|static|switch|use|require|require_once|var|while|abstract|interface|public|implements|extends|private|protected|throw)(?![$_@a-zA-Z0-9])/,
                    'var':/(\$[\w][\w\d]*)/,
                    'obj':/(?:[^$_@A-Za-z0-9])?(echo|mail|date)(?:[^$_@A-Za-z0-9])/,
                    'num':/\b(\d+(?:\.\d+)?(?:[Ee][-+]?(?:\d)+)?)\b/,
                    'bol':/(?:[^$_@A-Za-z0-9])?(true|false)(?:[^$_@A-Za-z0-9])/,
                    'ope':/(==|=|===|\+|-|\+=|-=|\*=|\\=|%=|&lt;|&lt;=|&gt;|&gt;=|\.)/
                }
            };
            return this.each(function(){
                var $this   = $(this);
                var lang    = $this.attr('data-language') || 'js';
                var html    = parseHTML($this.html(), lang);
                var htmlArr = html.split("\n");
                var _len    = htmlArr.length;
                var _html   = addInfoHtml(htmlArr, lang);
                var _line   = addLineNumber(_len);
                var newHtml = _html.replace(/@js-linenum@/g, _line);
                $this.html(newHtml);
            });
            function parseHTML(html, lang){
                html = html.replace(/^(\s)|(\s)$/g,'');
                html = html.replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/(\r?\n)$/g,'');
                html = lightCode(html, lang);
                return html;
            };
            function addLineNumber(num){
                var _str = '<div class="js-linenum">';
                for($i=1; $i<=num; $i++){
                    _str += "<span>"+$i+".</span>";
                }
                _str += "</div>";
                return _str;
            };
            function addInfoHtml(arr, lang){
                var _len    = arr.length;
                var _html   = '<div class="js-linebod">';
                for(i=0; i<_len; i++){
                    _html += "<div class='line'>"+arr[i]+"</div>";
                }
                _html += "</div>";
                return _html+"@js-linenum@";
            };
            function lightCode(html, lang){
                if(lang in language){
                    var pattern     = language[lang],
                    type            = ['doc','com','mrk','str','key','var','obj','num','bol','ope'],
                    len             = type.length,
                    cls=[], p=[], i=0;
                    for(; i<len; i++){
                        if(pattern[type[i]]){
                            p.push(pattern[type[i]].source);
                            cls.push(type[i]);
                        }
                    }
                    // 生成的正则
                    pattern = new RegExp(p.join("|"), 'g');
                    html = html.replace(pattern, function(){
                        var args = Array.prototype.slice.call(arguments,0),
                            currArg1 = null,
                            currArg = null,
                            len = args.length - 2,
                            index = len;
                        for(; index>0; index--){
                            currArg = args[index];
                            if(currArg){
                                args[0] = args[0].replace(currArg, '<span class="'+cls[index-1]+'">'+(currArg1 !==null ? currArg1 : currArg)+'</span>');
                            }
                        }
                        return args[0];
                    });

                }
                return html;
            }
        }
    });
})(jQuery);