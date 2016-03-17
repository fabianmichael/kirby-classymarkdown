<?php

namespace ClassyMarkdown;

class Defaults {
  public static function get() {
    return [

      'prefix'              => '',
      'utility.align'       => 'align--{%align%}',

      // Markdown Features:

      // Block Elements
      'paragraph'           => '',
      'header'              => '{prefix}graf--{%name%}', // Available placeholders: {%level%} = 1-6
      'blockquote'          => '{prefix}graf--blockquote',
      'code.block'          => '{prefix}graf--codeblock',
      'rule'                => '{prefix}graf--{%name%}',

      'list.base'           => '{prefix}list',
      'list'                => '{list.base} {list.base}--{%name%}',
      'list.item'           => '{list.base}__item', // use {%parent%} for list-type

      // Inline Elements
      'emphasis'            => '', // Example: {prefix}markup--{%name%} => markup--em / markup--strong
      'strikethrough'       => '',
      'image'               => '{prefix}markup--image',
      'code.inline'         => '',

      // Hyperlinks
      'link.base'           => '{prefix}markup--link',
      'link.url'            => '{link.base} {link.base}--url',
      'email'               => '{link.base} {link.base}--email',

      'link'                => function($Link, $parser) {
        // A closure can return a template string with placeholders, but cannot
        // be used as a placeholder.
        if ($Link['element']['attributes']['href'] === $Link['element']['text']) {
          return $parser->settings['link.url'];
        } else {
          return $parser->settings['link.base'];
        }
      },

      // Tables
      'table.base'               => 'table',
      'table.cell.base'          => '{table.base}__cell',
      'table'                    => '{prefix}{table.base}',

      'table.body'               => '{table.base}__body',
      'table.body.row'           => '{table.base}__row {table.base}__row--body',
      'table.body.cell'          => '{table.cell.base} {table.cell.base}--body',
      'table.body.cell.aligned'  => '{table.body.cell} / {utility.align}',
      'table.head'               => '{table}__head',
      'table.head.row'           => '{table.base}__row {table.base}__row--head',
      'table.head.cell'          => '{table.cell.base} {table.cell.base}--head',
      'table.head.cell.aligned'  => '{table.head.cell} / {utility.align}', // leave empty, to keep style attribute (text-align: …)

      // Markdown Extra Features:

      'abbreviation'             => '',

      // Definition Lists

      'definition.base'          => '{prefix}definition',
      'definition.list'          => '{definition.base}',
      'definition.term'          => '{definition.base}__term',
      'definition.description'   => '{definition.base}__description',

      // Footnotes                                                // ParsedownExtra Defaults:

      'footnote.marker.wrapper'  => '{prefix}footnote',           // -
      'footnote.marker.link'     => '{prefix}footnote__link',     // footnote-ref

      'footnotes.base'           => '{prefix}footnotes',          // n/a
      'footnotes.container'      => '{footnotes.base}',           // footnotes
      'footnotes.separator'      => '{rule}',                     // -
      'footnotes.list'           => '{list}',                     // -
      'footnotes.list.item'      => '{list.item}',                // -
      'footnotes.backref'        => '{prefix}footnotes__backref', // footnote-backref
    ];
  }
}
