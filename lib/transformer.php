<?php

namespace ClassyMarkdown;
use c;
use Exception;
use str;

trait Transformer {

  public $settings;

  private $maxClassNameResolveIterations = 3;

  public function __construct(array $settings = []) {

    if ('ClassyMarkdown\\MarkdownExtra' === get_class($this)) {
      // Only call parent constructor, when using ParsedownExtra,
      // because the basic Parsedown class does not have one.
      parent::__construct();
    }

    // var_dump(get_class_methods($this));
    // exit;

    $this->settings = array_merge(
      Defaults::get(),
      $settings
    );
  }

  protected function getClassTemplateVars($additional = []) {
    static $vars;

    if (!$vars) {
      $vars = array_filter($this->settings, function($v) { return !is_callable($v); });
    }

    return array_merge($vars, $additional);
  }

  public function getClassName($Block, $className) {

    if (is_callable($className)) {
      $className = call_user_func_array($className, [$Block, $this]);
    }

    if (!empty($className)) {

      $templateVars = $this->getClassTemplateVars(['name'   => $Block['element']['name']]);

      for ($i = 0; $i < $this->maxClassNameResolveIterations && strstr($className, '{'); $i++) {
        $className = str::template($className, $templateVars);
      }

      if (c::get('debug') && strstr($className, '{')) {
        throw new Exception("Class name could not be resolved, because it still
          contains placeholders after {$this->maxClassNameResolveIterations}
          iterations of replacement. Aborting here to avoid infinitive recursion.
          Final class name is \"$className\"");
      }
    }

    return $className;
  }

  protected function setElementClass($Block, $className) {
    if (!$Block || empty($className)) return $Block;

    $className = $this->getClassName($Block, $className);

    if (isset($Block['element']['attributes'])) {
      if (!isset($Block['element']['attributes']['class'])) {
        $Block['element']['attributes']['class'] = $className;
      } else {
        $classes = array_merge(
          explode(' ', $Block['element']['attributes']['class']),
          explode(' ', $className));
        $Block['element']['attributes']['class'] = implode(' ', array_unique($classes));
      }
    } else if (!empty($className)) {
      $Block['element']['attributes'] = [ 'class' => $className ];
    }

    return $Block;
  }

  protected function inlineLink($Excerpt) {
    return $this->setElementClass(
      parent::inlineLink($Excerpt),
      $this->settings['link']
    );
  }

  protected function inlineUrl($Excerpt) {
    return $this->setElementClass(
      parent::inlineUrl($Excerpt),
      $this->settings['link']
    );
  }

  protected function inlineUrlTag($Excerpt) {
    return $this->setElementClass(
      parent::inlineUrl($Excerpt),
      $this->settings['link']
    );
  }

  protected function inlineEmailTag($Excerpt) {
    return $this->setElementClass(
      parent::inlineEmailTag($Excerpt),
      $this->settings['email']
    );
  }

  protected function blockList($Line) {
    $Block = $this->setElementClass(
      parent::blockList($Line),
      $this->settings['list']
    );

    if (!$Block) return;

    $Block['element']['text'][0] = $this->setElementClass(
      ['element' => $Block['element']['text'][0]],
      $this->settings['list.item']
    )['element'];

    return $Block;
  }

  protected function blockListContinue($Line, array $Block) {
    $Block = parent::blockListContinue($Line, $Block);

    if (!$Block) return;

    $last = sizeof($Block['element']['text']) - 1;
    $Block['element']['text'][$last] = $this->setElementClass(
      ['element' => $Block['element']['text'][$last]],
      $this->settings['list.item']
    )['element'];

    return $Block;
  }

  public function text($text) {
    return parent::text($text);
  }

  protected function getFakeParagraph() {
    return parent::paragraph(['text' => 'A fake paragraph!']);
  }

  protected function li($lines) {

    $markup = parent::li($lines);

    if (empty($this->settings['paragraph'])) {
      return $markup;
    };

    $trimmedMarkup      = trim($markup);

    $paragraphClassName = $this->getClassName(
      $this->getFakeParagraph(),
      $this->settings['paragraph']
    );

    if (empty($paragraphClassName))
      return;

    $paragraphOpen       = "<p class=\"$paragraphClassName\">";
    $paragraphOpenLength = strlen($paragraphOpen);

    if (!in_array('', $lines) && substr($trimmedMarkup, 0, $paragraphOpenLength) === $paragraphOpen) {
      $markup = $trimmedMarkup;
      $markup = substr($markup, $paragraphOpenLength);

      $position = strpos($markup, "</p>");

      $markup = substr_replace($markup, '', $position, 4);
    }

    return $markup;
  }

  protected function paragraph($Line) {
    return $this->setElementClass(
      parent::paragraph($Line),
      $this->settings['paragraph']
    );
  }

  protected function blockHeader($Line) {
    return $this->setElementClass(
      parent::blockHeader($Line),
      $this->settings['header']
    );
  }

  protected function blockCode($Line, $Block = null) {
    return $this->setElementClass(
      parent::blockCode($Line, $Block),
      $this->settings['code.block']
    );
  }

  protected function blockRule($Line) {
    return $this->setElementClass(
      parent::blockRule($Line),
      $this->settings['rule']
    );
  }


  protected function inlineEmphasis($Excerpt) {
    return $this->setElementClass(
      parent::inlineEmphasis($Excerpt),
      $this->settings['emphasis']
    );
  }

  protected function inlineStrikethrough($Excerpt) {
    return $this->setElementClass(
      parent::inlineStrikethrough($Excerpt),
      $this->settings['strikethrough']
    );
  }

  protected function inlineCode($Excerpt) {
    return $this->setElementClass(
      parent::inlineCode($Excerpt),
      $this->settings['code.inline']
    );
  }

  protected function blockQuote($Line) {
    return $this->setElementClass(
      parent::blockQuote($Line),
      $this->settings['blockquote']
    );
  }

  protected function inlineImage($Excerpt) {
    $Inline = parent::inlineImage($Excerpt);

    if (!$Inline) return;

    if (isset($Inline['element']['attributes']['class'])) {
      // Images will be passes through the inlineLink() method first,
      // so they will probably get an anchor class. We need to reset this.
      unset($Inline['element']['attributes']['class']);
    }

    return $this->setElementClass(
      $Inline,
      $this->settings['image']
    );
  }
}
