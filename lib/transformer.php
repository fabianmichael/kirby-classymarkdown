<?php

namespace ClassyMarkdown;
use C;
use Exception;
use Str;

trait Transformer {

  public $settings;
  protected $maxClassNameResolveIterations = 5;

  public function __construct(array $settings = []) {

    if ('ClassyMarkdown\\MarkdownExtra' === get_class($this)) {
      // Only call parent constructor, when using ParsedownExtra,
      // because the basic Parsedown class does not have one.
      parent::__construct();
    }

    $this->settings = Settings::instance();
  }
  
  public function text($text) {
    return parent::text($text);
  }

  protected function getClassTemplateSettingVars() {
    static $vars;

    if (!$vars) {
      // Filter out closures from settings, because they cannot be used as
      // placeholders in class names
      $vars = array_filter($this->settings->classes, function($v) { return !is_callable($v); });
    }

    return $vars;
  }

  public function getClassName(array $Block, $className, array $variables = []) {

    if (is_callable($className)) {
      $className = call_user_func_array($className, [$Block, $this]);
    }

    if (!empty($className)) {

      $variables['name'] = $Block['element']['name'];
      $variablesWrapped = [];

      foreach ($variables as $key => $val) {
        $variablesWrapped["%{$key}%"] = $val;
      }

      $templateVars = array_merge(
        $this->getClassTemplateSettingVars(),
        $variablesWrapped
      );

      for ($i = 0; $i < $this->maxClassNameResolveIterations && !is_bool(strpos($className, '{')); $i++) {
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

  protected function setElementClass($Block, $className, array $variables = []) {
    if (!$Block || empty($className)) return $Block;

    $className = $this->getClassName($Block, $className, $variables);

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
      $this->settings->className('link')
    );
  }

  protected function inlineUrl($Excerpt) {
    return $this->setElementClass(
      parent::inlineUrl($Excerpt),
      $this->settings->className('link')
    );
  }

  protected function inlineUrlTag($Excerpt) {
    return $this->setElementClass(
      parent::inlineUrl($Excerpt),
      $this->settings->className('link')
    );
  }

  protected function inlineEmailTag($Excerpt) {
    return $this->setElementClass(
      parent::inlineEmailTag($Excerpt),
      $this->settings->className('email')
    );
  }

  protected function blockList($Line) {
    $Block = $this->setElementClass(
      parent::blockList($Line),
      $this->settings->className('list')
    );

    if (!$Block) return;

    $variables = [
      'parent' => $Block['element']['name'],
    ];

    $Block['element']['text'][0] = $this->setElementClass(
      ['element' => $Block['element']['text'][0]],
      $this->settings->className('list.item'),
      $variables
    )['element'];

    return $Block;
  }

  protected function blockListContinue($Line, array $Block) {
    $Block = parent::blockListContinue($Line, $Block);

    if (!$Block) return;

    $last = sizeof($Block['element']['text']) - 1;
    $Block['element']['text'][$last] = $this->setElementClass(
      ['element' => $Block['element']['text'][$last]],
      $this->settings->className('list.item'),
      [ 'parent' => $Block['element']['name'] ]
    )['element'];

    return $Block;
  }

  protected function getFakeElement($name = 'p', $text = 'A fake element …') {
    return [
        'element' => [
            'name' => $name,
            'text' => $text,
            'handler' => 'line',
        ],
    ];
  }

  protected function li($lines) {

    $markup = parent::li($lines);

    if (empty($this->settings->className('paragraph'))) {
      return $markup;
    };

    $trimmedMarkup      = trim($markup);

    $paragraphClassName = $this->getClassName(
      $this->getFakeElement('p'),
      $this->settings->className('paragraph')
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

  public function line($text) {
    $markup = parent::line($text);
    return $markup;
  }

  protected function paragraph($Line) {
    $Block = $this->setElementClass(
      parent::paragraph($Line),
      $this->settings->className('paragraph')
    );

    return $Block;
  }

  protected function blockHeader($Line) {
    $Block = parent::blockHeader($Line);

    if (!$Block) return;

    $Block = $this->setElementClass(
      $Block,
      $this->settings->className('header'),
      [ 'level' => substr($Block['element']['name'], 1) ]
    );

    return $Block;
  }

  protected function blockCode($Line, $Block = null) {
    return $this->setElementClass(
      parent::blockCode($Line, $Block),
      $this->settings->className('code.block')
    );
  }

  protected function blockFencedCode($Line) {
    return $this->setElementClass(
      parent::blockFencedCode($Line),
      $this->settings->className('code.block')
    );
  }

  protected function blockRule($Line) {
    return $this->setElementClass(
      parent::blockRule($Line),
      $this->settings->className('rule')
    );
  }

  protected function inlineEmphasis($Excerpt) {
    return $this->setElementClass(
      parent::inlineEmphasis($Excerpt),
      $this->settings->className('emphasis')
    );
  }

  protected function inlineStrikethrough($Excerpt) {
    return $this->setElementClass(
      parent::inlineStrikethrough($Excerpt),
      $this->settings->className('strikethrough')
    );
  }

  protected function inlineCode($Excerpt) {
    return $this->setElementClass(
      parent::inlineCode($Excerpt),
      $this->settings->className('code.inline')
    );
  }

  protected function blockQuote($Line) {
    return $this->setElementClass(
      parent::blockQuote($Line),
      $this->settings->className('blockquote')
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
      $this->settings->className('image')
    );
  }

  protected function blockTable($Line, array $Block = null) {
    $Block = $this->setElementClass(
      parent::blockTable($Line, $Block),
      $this->settings->className('table')
    );

    if (!$Block) return;

    // thead
    $Block['element']['text'][0] = $this->setElementClass(
      [ 'element' => $Block['element']['text'][0] ],
      $this->settings->className('table.head')
    )['element'];

    // thead > tr {
    $Block['element']['text'][0]['text'][0] = $this->setElementClass(
      [ 'element' => $Block['element']['text'][0]['text'][0] ],
      $this->settings->className('table.head.row')
    )['element'];

    // thead > th
    foreach ($Block['element']['text'][0]['text'][0]['text'] as $i => $th) {

      if ($Block['alignments'][$i] !== null && !empty($this->settings->className('table.head.cell.aligned'))) {
        // convert alignment inline style to class
        $th = $this->setElementClass(
          [ 'element' => $th ],
          $this->settings->className('table.head.cell.aligned'),
          [ 'align' => $Block['alignments'][$i] ]
        )['element'];
        unset($th['attributes']['style']);
      } else {
        $th = $this->setElementClass(
          [ 'element' => $th ],
          $this->settings->className('table.head.cell')
        )['element'];
      }

      $Block['element']['text'][0]['text'][0]['text'][$i] = $th;
    }

    // tbody
    $Block['element']['text'][1] = $this->setElementClass(
      [ 'element' => $Block['element']['text'][1] ],
      $this->settings->className('table.body')
    )['element'];

    return $Block;
  }

  protected function blockTableContinue($Line, array $Block) {
    $Block = parent::blockTableContinue($Line, $Block);

    if (!$Block) return;

    // tbody > tr
    foreach ($Block['element']['text'][1]['text'] as $i => $tr) {
      $tr = $this->setElementClass(
        ['element' => $tr ],
        $this->settings->className('table.body.row')
      )['element'];

      // td
      foreach ($tr['text'] as $j => $td) {

        if ($Block['alignments'][$j] !== null && !empty($this->settings->className('table.body.cell.aligned'))) {
          // convert alignment inline style to class
          $td = $this->setElementClass(
            [ 'element' => $td ],
            $this->settings->className('table.body.cell.aligned'),
            [ 'align' => $Block['alignments'][$j] ]
          )['element'];
          unset($td['attributes']['style']);
        } else {
          $td = $this->setElementClass(
            ['element' => $td ],
            $this->settings->className('table.body.cell')
          )['element'];
        }

        $tr['text'][$j] = $td;
      }

      $Block['element']['text'][1]['text'][$i]  = $tr;
    }

    return $Block;
  }  
}
