# wechat-broadcast

微信群发消息

## 安装

环境要求：PHP >= 5.5.9

1. 使用 [composer](https://getcomposer.org/)

  ```shell
  composer require "springjk/wechat" 
  ```

## 使用

  ```php
  <?php
  //引入Comoser自动加载
  require __DIR__ . '/vendor/autoload.php';

  use Springjk\Wechat\Broadcast;

  // 测试时防止报错使用，实际环境中应为框架或配置设置，无需手动设置。
  date_default_timezone_set('Asia/Shanghai');

  $options = [
      'app_id' => 'your-app-id',     // AppID
      'secret' => 'your-app-secret', // AppSecret
      // 'timeout' => 15             // 可选，默认为10S。
  ];

  $broadcast = new Broadcast($options);

  $thumb_path = 'your-image-path';    // 为防止出错，建议使用绝对地址。

  $upload_image_result = $broadcast->uploadThumb($thumb_path);  // 如需重复使用请进行持久化

  $articles = [
      'title' => 'TITLE',
      'thumb_media_id' => $upload_image_result['media_id'],
      'author' => 'AUTHOR',
      'digest' => 'DIGEST',
      'show_cover_pic' => 1,
      'content' => 'CONTENT',
      'content_source_url' => 'http://google.com'
  ];

  $upload_articles_result = $broadcast->uploadArticles($articles); // 如需重复使用请进行持久化

  // 预览接口，如为测试账号仅能使用预览接口。
  $send_news_status = $broadcast->previewByName($upload_articles_result['media_id'], 'your-wechat-name');

  // $send_news_status= $broadcast->send($upload_articles_result['media_id']);

  var_dump($send_news_status);

  ```

更多请参考 [微信群发消息](http://mp.weixin.qq.com/wiki/15/5380a4e6f02f2ffdc7981a8ed7a40753.html)。

## License

MIT