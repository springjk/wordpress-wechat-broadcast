# 微信群发助手-Wechat Broadcast
Contributors: springjk
Donate link: http://80.me/
Tags: wechat, broadcast, weixin
Requires at least: 3.0.1
Tested up to: 4.7.2
Stable tag: 1.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

将文章内容推送给微信公众号订阅用户。
Send article content to wechat subscribers.

## Description

在发布文章的时候可选同步推送到微信公众号订阅用户(需要已付费认证公众号)。

需求：

* PHP >= 5.5.9

特色：

* 上传文章或页面内容至微信素材库并推送给订阅用户
* 可配置微信测试账号进行预览
* 对虚拟主机空间的支持
* 短标签调用素材库显示在页面
* 不与其他插件及第三方管理系统冲突
* 微信支持为 EasyWeChat

推送使用：

1. 安装插件后启用插件
2. 点击插件下方的插件设置连接或选择后台菜单的设置->微信
3. 配置微信公众号的 APPID 账号相关信息
4. 在文章或页面编辑页右上方微信推送选项勾选群发及是否预览
5. 点击更新或发布按钮

短标签使用：

* 在文章或页面编辑页中选择文本
* 在需要插入的地方填入 `[wechat_material]`

* 可选参数 `col`, 显示列数，默认为 3，可选为 2
* 可选参数 `title`, 是否显示标题，默认为 `true`，`false` 为不显示
* 可选参数 `thumb`, 是否显示缩略图，默认为 `true`，`false` 为不显示
* 可选参数 `digest`, 是否显示简介，默认为 `true`，`false` 为不显示

* 完整示例 `[wechat_material col="3" title="true" thumb="true" digest="true"]`

支持：

如果发现任何 bug 或者有功能建议，欢迎 PR 或 提交 issue。

GitHub: https://github.com/springjk/wordpress-wechat-broadcast.

Mail: chinese.jk@gmail.com