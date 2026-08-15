<?php

return [
    'mode'                     => 'utf-8',
    'format'                   => 'A4',
    'default_font_size'        => '12',
    'default_font'             => 'Arial',
    'margin_left'              => 0,
    'margin_right'             => 0,
    'margin_top'               => 10,
    'margin_bottom'            => 0,
    'margin_header'            => 0,
    'margin_footer'            => 0,
    'orientation'              => 'P',
    'title'                    => '',
    'subject'                  => '',
    'author'                   => '',
    'watermark'                => '',
    'show_watermark'           => true,
    'show_watermark_image'     => true,
    'watermark_font'           => 'Oriya',
    'display_mode'             => 'fullpage',
    'watermark_text_alpha'     => 0.1,
    'watermark_image_path'     => '',
    'watermark_image_alpha'    => 0.2,
    'watermark_image_size'     => 'D',
    'watermark_image_position' => 'P',
    'custom_font_dir'  => [], // don't forget the trailing slash!
    'custom_font_data' => [

        // ...add as many as you want.
    ],
    'auto_language_detection'  => false,
    'temp_dir'                 => storage_path('app'),
    'pdfa'                     => false,
    'pdfaauto'                 => false,
    'use_active_forms'         => false,

];
