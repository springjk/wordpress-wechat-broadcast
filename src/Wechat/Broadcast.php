<?php
namespace Springjk\Wechat;

use EasyWeChat\Foundation\Application;
use EasyWeChat\Message\Article;

class Broadcast
{
    private $app;

    public function __construct(array $options)
    {
        $timeout = isset($options['timeout']) ? $options['timeout'] : 10;

        $options = [
            /**
             * Debug 模式，bool 值：true/false
             *
             * 当值为 false 时，所有的日志都不会记录
             */
            'debug' => false,

            /**
             * 账号基本信息，请从微信公众平台/开放平台获取
             */
            'app_id' => $options['app_id'], // AppID
            'secret' => $options['secret'], // AppSecret
            'token' => '',                  // Token
            'aes_key' => '',                // EncodingAESKey

            /**
             * 日志配置
             *
             * level: 日志级别, 可选为：
             * debug/info/notice/warning/error/critical/alert/emergency
             * file：日志文件位置(绝对路径!!!)，要求可写权限
             */
            'log' => [
                'level' => 'debug',
                'file' => '/tmp/easywechat.log',
            ],

            /**
             * Guzzle 全局设置
             *
             * 更多请参考： http://docs.guzzlephp.org/en/latest/request-options.html
             */
            'guzzle' => [
                'timeout' => $timeout, // 超时时间（秒）
                //'verify' => false,   // 关掉 SSL 认证（强烈不建议！！！）
            ],
        ];

        $this->app = new Application($options);

        return $this;
    }

    /**
     * 上传永久图片素材.
     *
     * @param string $image_path
     *
     * @return array
     *
     * @throws Exception
     */
    public function uploadThumb($image_path)
    {
        $material = $this->app->material;

        $result = $material->uploadImage($image_path);  // 请使用绝对路径写法！除非你正确的理解了相对路径（好多人是没理解对的）！

        return $result;
    }

    /**
     * 上传永久单篇图文素材.
     *
     * @param array $articles
     *
     * @return array
     *
     * @throws Exception
     */
    public function uploadArticles(array $articles)
    {
        $material = $this->app->material;

        $articles = new Article($articles);

        $result = $material->uploadArticle($articles);

        return $result;
    }

    /**
     * 根据微信名预览单篇图文素材.
     *
     * @param string $media_id
     * @param string $wechat_name
     *
     * @return array
     *
     * @throws Exception
     */
    public function previewByName($media_id, $wechat_name)
    {
        $broadcast = $this->app->broadcast;

        $result = $broadcast->previewNewsByName($media_id, $wechat_name);

        return $result;
    }

    /**
     * 群发图文素材.
     *
     * @param string $media_id
     *
     * @return array
     *
     * @throws Exception
     */
    public function send($media_id)
    {
        $broadcast = $this->app->broadcast;

        $result = $broadcast->sendNews($media_id);

        return $result;
    }


}
