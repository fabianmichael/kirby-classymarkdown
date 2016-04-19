<?php 

namespace ClassyMarkdown\Component;
use C;

class Markdown extends \Kirby\Component\Markdown {

  /**
   * Initializes the ClassyMarkdown parser and 
   * transforms the given markdown to HTML
   * 
   * @param string $markdown
   * @return string
   */  
  public function parse($markdown) {
    static $classyMarkdownSettings;
    
    try {
      if (is_null($classyMarkdownSettings)) {
        $classyMarkdownSettings = c::get('classymarkdown.classes', []);  
      }

      // initialize the right markdown class
      $parsedown = $this->kirby->options['markdown.extra'] ?
        new \ClassyMarkdown\MarkdownExtra($classyMarkdownSettings) :
        new \ClassyMarkdown\Markdown($classyMarkdownSettings);

      // set markdown auto-breaks
      $parsedown->setBreaksEnabled($this->kirby->options['markdown.extra']);

      // parse it!
      return $parsedown->text($markdown);

    } catch (Exception $e) {
      return ''; return trigger_error($e);
    }
  }

}
