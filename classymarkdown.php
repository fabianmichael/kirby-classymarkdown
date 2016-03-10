<?php

if (!c::get('classymarkdown')) return;

load([
  'classymarkdown\\parsedownclasses'     => __DIR__ . DS . 'lib' . DS . 'parsedownclasses.php',
  'classymarkdown\\classyparsedown'      => __DIR__ . DS . 'lib' . DS . 'classyparsedown.php',
  'classymarkdown\\classyparsedownextra' => __DIR__ . DS . 'lib' . DS . 'classyparsedownextra.php',
]);

$defaults = [
  'link.base'    => 'anchor',
  'link.url'     => 'anchor--url',
  'link.email'   => 'anchor--email',

//  'list'         => 'list--{name}',

  'link' => function($Link, $parsedown) {
    if ($Link['element']['attributes']['href'] === $Link['element']['text']) {
      return trim($parsedown->settings['link.base'] . ' ' . $parsedown->settings['link.url']);
    } else {
      return $parsedown->settings['link.base'];
    }
  },

  'email' => function($Link, $parsedown) {
    return trim($parsedown->settings['link.base'] . ' ' . $parsedown->settings['link.email']);
  },

];

$parsedownSettings = array_merge($defaults, c::get('classymarkdown.classes', []));

$kirby = kirby::instance();

// default markdown parser callback
if (!c::get('markdown.parser')) {
  $kirby->options['markdown.parser'] = function($text) use ($kirby, $parsedownSettings) {

    // initialize the right markdown class
    $parsedown = $kirby->option('markdown.extra') ? new ClassyMarkdown\ClassyParsedownExtra($parsedownSettings) : new ClassyMarkdown\ClassyParsedown($parsedownSettings);

    // set markdown auto-breaks
    $parsedown->setBreaksEnabled(kirby::instance()->option('markdown.breaks'));

    // parse it!
    return $parsedown->text($text);

  };
}
