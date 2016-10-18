<?php
namespace Springjk;

use Springjk\Broadcast;

class Material {

    public function __construct()
    {
        add_shortcode('wechat_material', [$this, 'material_shortcode']);
    }

    public function material_shortcode($attr = [], $content = '')
    {
        $Broadcast = new Broadcast();

        $wechat = $Broadcast->init_wechat();

        $material = $wechat->material;

        $news = $material->lists('news', 0, 20);

        $output = '<ul class="wechat-material-list">';

        foreach ($news['item'] as $key => $value) {
            foreach ($value['content']['news_item'] as $k => $v) {
                $output .= '<li><a href="' . $v['url'] . '">'.$v['title'].'</a></li>';
            }
        }

        $output .= '</ul>';

        return $output;
    }
}

new Material();