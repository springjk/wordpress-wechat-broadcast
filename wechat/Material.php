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

        $attr['col'] = isset($attr['col']) ? $attr['col'] : 3;
        $attr['title'] = isset($attr['title']) ? $attr['title'] : true;
        $attr['thumb'] = isset($attr['thumb']) ? $attr['thumb'] : true;
        $attr['digest'] = isset($attr['digest']) ? $attr['digest'] : true;

        $output = '
        <style>
            .material-item {
                display: inline-block;
                float: left;
                margin-top: 1em;
                margin-right: 4.799999999%;
                vertical-align: top;
            }

            .material-item.col-3{
                width: calc(28% - 4px);
            }

            .material-item.col-2{
                width: calc(45% - 4px);
            }

            .material-item h3 {
                margin: 1.25em 0 .6em;
                font-size: 1.25em;
                line-height: 1.5;
                min-height: 48px;
                max-height: 48px;
                overflow: hidden;
            }
            .material-item p {
                margin-top: .6em;
                min-height: 40px;
                max-height: 40px;
                overflow: hidden;
            }
            .material-item p.image-container {
              max-width: 100%;
              height: 0;
              padding-bottom: 56%;
              overflow: hidden;
            }
            .material-item img {
                width: 100%;
            }
        </style>
        ';

        $output .= '<div class="wechat-material-list">';
        $this_dir = str_replace($_SERVER['DOCUMENT_ROOT'], '', __DIR__);

        foreach ($news['item'] as $key => $value) {
            foreach ($value['content']['news_item'] as $k => $v) {

                $output .= '<div class="material-item col-' . $attr['col'] . '">';

                $output .= '<a href="' . $v['url'] . '">';

                if ($attr['title'] !== 'false') {
                    $output .= '<h3>' . $v['title'] . '</h3>';
                }

                if ($attr['thumb'] !== 'false') {
                    $output .= '<p class="image-container"><img src="' . $this_dir . '/Image.php?url=' . $v['thumb_url'] . '"></p>';
                }

                $output .= '</a>';

                if ($attr['digest'] !== 'false') {
                    $output .= '<p>' . $v['digest'] . '</p>';
                }

                $output .= '</div>';
            }
        }

        $output .= '</div>';

        return $output;
    }
}