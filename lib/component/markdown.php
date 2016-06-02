<?php 

namespace ClassyMarkdown\Component;

class Markdown extends \Kirby\Component\Markdown {

  /**
   * Initializes the ClassyMarkdown parser and 
   * transforms the given markdown to HTML
   * 
   * @param string $markdown
   * @return string
   */  
  public function parse($markdown) {
    try {

      // initialize the right markdown class
      $parsedown = $this->kirby->options['markdown.extra'] ?
        new \ClassyMarkdown\MarkdownExtra() :
        new \ClassyMarkdown\Markdown();

      // set markdown auto-breaks
      $parsedown->setBreaksEnabled($this->kirby->options['markdown.breaks']);

      // parse it!
      return $parsedown->text($markdown);

    } catch (Exception $e) {
      return ''; return trigger_error($e);
    }
  }

}
