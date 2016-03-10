<?php

namespace ClassyMarkdown;

trait ParsedownClasses {

  public $settings;
  public static $defaults = [

    // Paragraphs
    'paragraph'           => null, // 'graf--{name}',
    'blockquote'          => null, // 'graf--{name}',

    'header'              => null, // 'graf--{name}',
    'codeBlock'           => null, // 'graf--{name}',
    'rule'                => null, // 'graf--{name}',
    'list'                => null, // 'graf--{name}',
    'listItem'            => null, // 'graf--{name}',

    'emphasis'            => null, // 'markup--{name}',
    'strikethrough'       => null, // 'markup--{name}',
    'code'                => null, // 'markup--{name}',
    'link'                => null, // 'anchor',
  ];

  public function __construct(array $settings = []) {

    if ('ClassyMarkdown\\ClassyParsedownExtra' === get_class($this)) {
      // Only call parent constructor, when using ParsedownExtra,
      // because the basic Parsedown class does not have one.
      parent::__construct();
    }

    $this->settings = array_merge(
      self::$defaults,
      $settings
    );
  }

  protected function setElementClass($Block, $className) {
    if (!$Block || is_null($className)) return $Block;

    if (is_callable($className)) {
      $className = $className($Block, $this);
    } else {
      $className = str::template($className, [
        'name' => $Block['element']['name'],
      ]);
    }

    if (isset($Block['element']['attributes'])) {
      $Block['element']['attributes']['class'] =
        isset($Block['element']['attributes']['class']) ?
          $Block['element']['attributes']['class'] . " $className" :
          $className;
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
    return $this->setElementClass(
      parent::blockList($Line),
      $this->settings['list']
    );
  }

  protected function blockListContinue($Line, array $Block) {
    $Block = parent::blockListContinue($Line, $Block);

    if(!$Block) return;

    foreach ($Block['element']['text'] as $i => $item) {
      $Block['element']['text'][$i] = $this->setElementClass(
        $Block['element']['text'][$i],
        $this->settings['listItem']
      );
    }

    return $Block;
  }

  protected function li($lines) {

    $markup = parent::li($lines);

    $token = '<p class="' . $this->settings['paragraph'] . '">';
    $tokenLength = strlen($token);

    if ( ! in_array('', $lines) and substr($markup, 0, $tokenLength) === $token) {
      $markup = substr($markup, $tokenLength);
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
      $this->settings['codeBlock']
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
      $this->settings['code']
    );
  }

  protected function blockQuote($Line) {
    return $this->setElementClass(
      parent::blockQuote($Line),
      $this->settings['blockquote']
    );
  }
}
