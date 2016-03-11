<?php

namespace ClassyMarkdown;
use c;
use kirby;

if (!c::get('classymarkdown')) return;

load([
  'classymarkdown\\transformer'   => __DIR__ . DS . 'lib' . DS . 'transformer.php',
  'classymarkdown\\defaults'      => __DIR__ . DS . 'lib' . DS . 'defaults.php',
  'classymarkdown\\markdown'      => __DIR__ . DS . 'lib' . DS . 'markdown.php',
  'classymarkdown\\markdownextra' => __DIR__ . DS . 'lib' . DS . 'markdownextra.php',
]);


$parsedownSettings = c::get('classymarkdown.classes', []);

$kirby = kirby::instance();

// default markdown parser callback
if (!c::get('markdown.parser')) {
  $kirby->options['markdown.parser'] = function($text) use ($kirby, $parsedownSettings) {

    // initialize the right markdown class
    $parsedown = $kirby->option('markdown.extra') ? new MarkdownExtra($parsedownSettings) : new Markdown($parsedownSettings);

    // set markdown auto-breaks
    $parsedown->setBreaksEnabled(kirby::instance()->option('markdown.breaks'));

    // parse it!
    return $parsedown->text($text);

  };
}
