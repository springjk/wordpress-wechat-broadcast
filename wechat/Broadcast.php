<?php
namespace Springjk;

use EasyWeChat\Foundation\Application;
use EasyWeChat\Message\Article;

class Broadcast {

    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_broadcast_hook']);

        add_action('admin_menu', [$this, 'menu_add_wechat_setting']);

        add_action('admin_menu', [$this, 'create_wechat_box']);

        add_action('admin_menu', [$this, 'show_notice']);

        add_filter('plugin_action_links', [$this, 'add_settings_link'], 10, 2 );
    }

    /**
     * Push a news to wechat by publish post.
     *
     * @param integer $post_ID
     * @param mixed $post
     *
     * @return null
     */
    public function wechat_broadcast($post_ID, $post)
    {
        if ($_POST['wechat_push_switch'] == 'on') {

            $article_data =  $this->get_article($post);

            $status = $this->push($article_data);

            if ($status['status'] == 'SUCCESS') {
                setcookie( 'wechat_broadcast_status', 'SUCCESS', time()+100, COOKIEPATH, COOKIE_DOMAIN, false);
            } else {
                setcookie( 'wechat_broadcast_status', $status['message'], time()+100, COOKIEPATH, COOKIE_DOMAIN, false);
            }
        }
    }

    /**
     * Get News info from a publish post.
     *
     * @param mixed $post
     *
     * @return array
     */
    public function get_article($post)
    {
        $title = $post->post_title;
        $content = $post->post_content;
        $digest = get_the_excerpt($post->ID);
        $content_source_url = get_the_permalink($post);

        $thumb_array =  wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'medium');
        $parsed = parse_url($thumb_array[0]);

        $thumb  =  empty($parsed['path']) ? '' : $_SERVER['DOCUMENT_ROOT'] . $parsed['path'];

        $broadcast_data = compact('title', 'content', 'digest', 'content_source_url', 'thumb');

        return $broadcast_data;
    }

    /**
     * Get wechat auth key from options.
     *
     * @return array
     */
    public function get_wechat_key()
    {
        $config = [
            'app_id' => esc_attr(get_option('wechat_app_id')),
            'secret' => esc_attr(get_option('wechat_secret'))
        ];

        return $config;
    }

    /**
     * initialize wechat sdk
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function init_wechat()
    {
        $config = $this->get_wechat_key();

        $options = [
            'app_id' => $config['app_id'], // AppID
            'secret' => $config['secret'], // AppSecret
            'guzzle' => [
                // 'verify' => __DIR__ . '/cacert.pem'
                'verify' => false
            ],
        ];

        $wechat = new Application($options);

        return $wechat;
    }

    /**
     * Get News info from a publish post.
     *
     * @param array $post_data
     *
     * @return array
     *
     * @throws \Exception
     */
    public function push($post_data)
    {
        try {
            $wechat = $this->init_wechat();

            $broadcast = $wechat->broadcast;

            if ($_POST['wechat_preview_switch'] == 'on') {

                $post_data['wechat_preview_user'] = esc_attr(get_option('wechat_preview_user'));

                if (empty($post_data['wechat_preview_user'])) {
                    throw new \Exception('没有设置预览用户');
                }
            }

            // 包含图片时为图文消息
            if (isset($post_data['thumb']) && !empty($post_data['thumb'])) {

                $material = $wechat->material;

                $upload_image_result = $material->uploadImage($post_data['thumb']);

                $post_data['content'] = $this->replace_article_image($post_data['content'], $material);

                $articles = [
                    'title' => $post_data['title'],
                    'thumb_media_id' => $upload_image_result['media_id'],
                    'author' => $post_data['author'],
                    'digest' => $post_data['digest'],
                    'show_cover_pic' => $post_data['show_cover_pic'] ? $post_data['show_cover_pic'] : 0,
                    'content' => $post_data['content'],
                    'content_source_url' => $post_data['content_source_url']
                ];

                $articles = new Article($articles);

                $upload_articles_result = $material->uploadArticle($articles);

                if (isset($post_data['wechat_preview_user'])) {
                    $send_status = $broadcast->previewNewsByName($upload_articles_result['media_id'], $post_data['wechat_preview_user']);
                } else {
                    $send_news_status= $broadcast->sendNews($upload_articles_result['media_id']);
                }

                if ($send_status['errcode'] != 0) {
                    throw new \Exception('send news fail,' . $send_status['errmsg'] . '.');
                }

                $return_data = ['status' => 'SUCCESS', 'message' => 'send news success'];
            } else {
                // 否则为文本消息
                $text = $post_data['title'];
                $text .= PHP_EOL . PHP_EOL;
                $text .= $post_data['digest'];
                $text .= PHP_EOL . PHP_EOL;

                if (!empty($post_data['content_source_url'])) {
                    $text .= '<a href="' . $post_data['content_source_url'] . '">点此访问</a>';
                }

                if (isset($post_data['wechat_preview_user'])) {
                    $send_status = $broadcast->previewTextByName($text, $post_data['wechat_preview_user']);
                } else {
                    $send_status= $broadcast->sendText($text);
                }
            }

            if ($send_status['status'] != 0) {
                throw new \Exception('send text fail,' . $send_status['errmsg'] . '.');
            }

            $return_data = ['status' => 'SUCCESS', 'message' => 'send text success'];
        } catch (\Exception $e) {
            $return_data = ['status' => 'FAIL', 'message' => $e->getMessage()];
        }

        return $return_data;
    }

    /**
     * Replace article content local image to wechat image.
     *
     * @param string $content
     *
     * @param mixed $material
     *
     * @return string
     */
    public function replace_article_image($content, $material)
    {
        preg_match_all('/<img.*? src=\"?(.*?\.(jpg|jpeg|gif|bmp|png))\"?.*?>/i',$content, $match);

        if (!empty($match[1])) {
            foreach ($match[1] as $image) {

                $url_info = parse_url($image);

                $image_url = isset($url_info['host']) ? $image : $_SERVER['HTTP_ORIGIN'] . $url_info['path'];

                // local image
                if (strstr($image_url, $_SERVER['HTTP_ORIGIN'])) {
                    $image_dir = $_SERVER['DOCUMENT_ROOT'] . $url_info['path'];
                } else {
                // remote image
                    $wp_upload_dir = wp_upload_dir();

                    $image_dir = $wp_upload_dir['basedir'] . basename($image_url);

                    file_put_contents($image_dir, file_get_contents($image_url));
                }

                $upload_image_result = $material->uploadImage($image_dir);

                $content = str_replace($image, $upload_image_result['url'], $content);
            }
        }

        return $content;
    }

    /**
     * add plugin setting on system setting menu.
     *
     * @return null
     */
    public function menu_add_wechat_setting()
    {
        add_action('admin_init', [$this, 'register_wechat_settings']);

        add_options_page('微信推送设置', '微信', 'administrator', 'wechat', [$this, 'wechat_setting_page']);
    }

    /**
     * register custom filed to setting.
     *
     * @return null
     */
    public function register_wechat_settings()
    {
        register_setting('Wechat-Settings', 'wechat_app_id');
        register_setting('Wechat-Settings', 'wechat_secret');
        register_setting('Wechat-Settings', 'wechat_preview_user');
    }

    /**
     * build setting page HTML code.
     *
     * @return string
     */
    public function wechat_setting_page()
    {
    ?>
        <div class="wrap">
            <div style="float: left;width: 60%;">
                <h2>微信公众号绑定</h2>
                <form method="post" action="options.php">

                    <?php settings_fields( 'Wechat-Settings' ); ?>
                    <?php do_settings_sections('wechat'); ?>

                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="wechat_app_id">公众号 APP ID</label></th>
                                <td>
                                    <input name="wechat_app_id" type="text" id="wechat_app_id" value="<?php echo esc_attr(get_option('wechat_app_id')); ?>" class="regular-text ltr">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="wechat_secret">公众号 APP Secret</label></th>
                                <td>
                                    <input name="wechat_secret" type="text" id="wechat_secret" value="<?php echo esc_attr(get_option('wechat_secret')) ?>" class="regular-text ltr">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="wechat_secret">预览用户微信号</label></th>
                                <td>
                                    <input name="wechat_preview_user" type="text" id="wechat_preview_user" value="<?php echo esc_attr(get_option('wechat_preview_user')) ?>" class="regular-text ltr">
                                    <p>预览用户微信号被设置时仅发送给预览用户。</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <?php submit_button() ?>
                </form>
            </div>
            <div style="float: left;">
                <h2>相关说明</h2>
                <ul>
                    <li>1.发布内容中的多媒体信息仅支持图片;</li>
                    <li>2.图片与图文消息上传类型为永久素材;</li>
                    <li>3.发布内容中的图片格式支持相对网络路径与绝对网络路径;</li>
                    <li>4.当发布内容被设置了<code>特色图像</code>时推送为图文消息，否则为文本消息;</li>
                    <li>5.选择预览发送时将使用预览接口发送，仅发送给预设的预览微信用户，不浪费推送次数;</li>
                </ul>
            </div>
        </div>
    <?php
    }

    /**
     * build custom post meta filed HTML code.
     *
     * @return null
     */
    public function new_meta_box()
    {
    ?>
        <div>
            <input type="checkbox" name="wechat_push_switch" id="wechat_push_switch"/><label for="wechat_push_switch">微信群发</label>
            <a href="/wp-admin/options-general.php?page=wechat" style="float: right;"><span aria-hidden="true">帮助</span></a>
        </div>
        <div id="wechat_preview_switch_group" style="display: none">
            <input type="checkbox" name="wechat_preview_switch" id="wechat_preview_switch"/><label for="wechat_preview_switch">群发时仅发送预览账号</label>
        </div>

        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#wechat_push_switch').on('click', function(){
                    $('#wechat_preview_switch_group').toggle();
                });
            });
        </script>
    <?php
    }

    /**
     * add a box for all post page.
     *
     * @return null
     */
    public function create_wechat_box()
    {
        $post_types = array_keys(get_post_types());

        add_meta_box('wechat-meta-box', '微信推送选项', [$this, 'new_meta_box'], $post_types, 'side', 'high');
    }

    /**
     * show wechat broadcast status in admin notice.
     *
     * @return null
     */
    public function show_notice()
    {
        if (isset($_COOKIE['wechat_broadcast_status'])) {
            if ($_COOKIE['wechat_broadcast_status'] == 'SUCCESS') {
                add_action('admin_notices', function(){
                    echo '<div class="notice notice-success is-dismissible"><p>微信消息推送成功</p></div>';
                });
            } else {
                add_action('admin_notices', function(){
                    echo '<div class="notice notice-warning is-dismissible"><p>推送微信失败 : ' . $_COOKIE['wechat_broadcast_status'] . '</p></div>';
                });
            }

            setcookie( 'wechat_broadcast_status', '', time(), COOKIEPATH, COOKIE_DOMAIN, false);
        }
    }

    /**
     * add an settings link for plugin.
     *
     * @return String
     */
    public function add_settings_link($links, $file)
    {
        if (basename($file) == 'wechat-broadcast.php'){
            $settings_link = [
                'setting' => '<a href="' . admin_url('options-general.php?page=wechat') . '">设置</a>'
            ];
            $links = array_merge($links, $settings_link);
        }

        return $links;
    }

    /**
     * register broadcast hook to all post type.
     *
     * @return null
     */
    public function register_broadcast_hook()
    {
        foreach (get_post_types() as $key => $value) {
            add_action('publish_' . $value, [$this, 'wechat_broadcast'], 20, 2);
        }
    }
}