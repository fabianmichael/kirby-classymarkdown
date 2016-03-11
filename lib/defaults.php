<?php

namespace ClassyMarkdown;

class Defaults {
  public static function get($preset = null) {
    return [

      'prefix'              => '',

      // Block Elements
      'paragraph'           => '',
      'blockquote'          => '',
      'header'              => '{prefix}graf--{name}',
      'code.block'          => '',
      'rule'                => '{prefix}graf--{name}',

      'list'                => '{prefix}list--{name}',
      'list.item'           => '{prefix}list__item',

      // Inline Elements
      'emphasis'            => '{prefix}markup--{name}',
      'strikethrough'       => '{prefix}markup--strikethrough',
      'link'                => '{prefix}markup--{name}',
      'image'               => '{prefix}markup--image',
      'code.inline'         => '',

      // Hyperlinks
      'link.base' => '{prefix}anchor',
      'link.url'  => '{link.base} {link.base}--url',
      'email'     => '{link.base} {link.base}--email',

      'link' => function($Link, $parser) {
        if ($Link['element']['attributes']['href'] === $Link['element']['text']) {
          return $parser->settings['link.url'];
        } else {
          return $parser->settings['link.base'];
        }
      },
    ];
  }
}
