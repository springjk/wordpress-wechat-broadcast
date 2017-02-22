<?php
/*
  Plugin Name: 微信推送图文消息
  Plugin URI: https://wordpress.org/plugins/wechat-broadcast
  Description: 在发布文章时推送图文消息到公众号的订阅用户
  Version: 1.2.0
  Author: JaQuan
  Author URI: http://80to.me
  License: GPL2
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
use Springjk\Broadcast;
use Springjk\Material;

require_once __DIR__ . '/vendor/autoload.php';

new Broadcast();

new Material();