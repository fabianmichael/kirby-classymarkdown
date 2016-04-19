<?php

namespace ClassyMarkdown;
use c;
use kirby;

if (!c::get('classymarkdown')) return;

load([
  'classymarkdown\\transformer'         => __DIR__ . DS . 'lib' . DS . 'transformer.php',
  'classymarkdown\\defaults'            => __DIR__ . DS . 'lib' . DS . 'defaults.php',
  'classymarkdown\\markdown'            => __DIR__ . DS . 'lib' . DS . 'markdown.php',
  'classymarkdown\\markdownextra'       => __DIR__ . DS . 'lib' . DS . 'markdownextra.php',
  
  // Markdown Component for Kirby 2.3.0+
  'classymarkdown\\component\\markdown' => __DIR__ . DS . 'lib' . DS . 'component' . DS . 'markdown.php',
]);

$kirby = kirby();

if (version_compare($kirby->version(), '2.3.0', '>=')) {
  // Register ClassyMarkdown as component for Kirby 2.3.0+
  $kirby->set('component', 'markdown', '\\ClassyMarkdown\\Component\\Markdown');
} else {
  // Register as closure for older versions of Kirby
  if (!c::get('markdown.parser')) {
    $classyMarkdownSettings = c::get('classymarkdown.classes', []);
    
    $kirby->options['markdown.parser'] = function($markdown) use ($kirby, $classyMarkdownSettings) {

      // initialize the right markdown class
      $parsedown = $kirby->option('markdown.extra') ? new MarkdownExtra($classyMarkdownSettings) : new Markdown($classyMarkdownSettings);

      // set markdown auto-breaks
      $parsedown->setBreaksEnabled($kirby->option('markdown.breaks'));

      // parse it!
      return $parsedown->text($markdown);

    };
  }
}
