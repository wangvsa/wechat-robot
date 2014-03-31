Wechat Robot
============
Wechat Robot是一个WordPress插件，实现了WP站点和微信公众号的连接，支持自动回复、文章搜索、订阅回复、微社区入口等功能。
Wechat Robot使用PHP语言，稍作改动可以即可以在非Wordpress环境下使用。



## 功能列表
- 访问WP网站
- 查看最新文章
- 查看随机文章
- 查看热门文章
- 关键字搜索
- 访问FB社区
- 订阅时显示欢迎菜单



## 功能截图
本插件原为[Freebuf](http://www.freebuf.com)编写，在这直接用Freebuf公众号演示，大家也可以微信关注Freebuf自己体验。

- 显示菜单，输入任何不被识别的字符串均可<br>
![菜单](https://raw2.github.com/wangvsa/wechat-robot/master/screenshot/menu.png)
- 回复0可以访问主站<br>
![访问主站](https://raw2.github.com/wangvsa/wechat-robot/master/screenshot/visit_wp.png)
- 回复1查看最新文章<br>
![最新文章](https://raw2.github.com/wangvsa/wechat-robot/master/screenshot/recent.png)
- 回复2查看随机文章<br>
![随机文章](https://raw2.github.com/wangvsa/wechat-robot/master/screenshot/random.png)
- 回复3查看热门文章（本月）<br>
![热门文章](https://raw2.github.com/wangvsa/wechat-robot/master/screenshot/hotest.png)
- 回复4+关键字进行搜索<br>
![搜索文章](https://raw2.github.com/wangvsa/wechat-robot/master/screenshot/search.png)
- 回复5访问FB社区<br>
![访问FB社区](https://raw2.github.com/wangvsa/wechat-robot/master/screenshot/weshequ.png)



## 安装使用
1. 下载本插件，编辑文件wechat-robot.php<br>
代码十分简单，只需要根据需求修改onText函数和onSubscribe函数中的回复内容即可。
2. 将wechat-robot目录上传到`WP目录/wp-content/plugins/`，然后在WP后台开启本插件。
3. 进入微信公众账号后台，开启高级功能－>开发模式，填写token和url<br>
token填写`wechat`，url填写`你的网站地址/?wechat`，比如`http://www.freebuf.com/?wechat`，注意不要省略`http://`

## 详细文档
见[API wiki](https://github.com/wangvsa/wechat-robot/wiki/API文档)


## 使用与贡献
如果你打算使用本插件，希望你可以将公众号发送至我的邮箱wangvsa@163.com，我将在本文底部展示。<br>
同时欢迎提出任何建议意见或者帮助改进本插件，让更多的人从中受益。


## TO DO
1. 增强搜索相关性
2. 增加小黄鸡功能


## 使用Wechat Robot的公众号
- 黑客与极客,微信号Freebuf
