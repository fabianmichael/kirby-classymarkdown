<?php

namespace ClassyMarkdown;

class MarkdownExtra extends \ParsedownExtra {
  use Transformer;

  # ParsedownExtra Features

  # Abbreviations

  protected function unmarkedText($text)
  {
      $text = \Parsedown::unmarkedText($text);

      if (isset($this->DefinitionData['Abbreviation']))
      {
          foreach ($this->DefinitionData['Abbreviation'] as $abbreviation => $meaning)
          {
              $pattern = '/\b'.preg_quote($abbreviation, '/').'\b/';

              $className = $this->getClassName(
                $this->getFakeElement('abbr', $abbreviation),
                $this->settings['abbreviation']
              );

              $classAttribute = !empty($className) ? " class=\"$className\"" : '';
              $text = preg_replace($pattern, '<abbr title="'.$meaning.'"' . $classAttribute . '>'.$abbreviation.'</abbr>', $text);
          }
      }

      return $text;
  }

  # Definition Lists

  public function text($text) {
    $markup = parent::text($text);

    $className = $this->getClassName(
      $this->getFakeElement('dl'),
      $this->settings['definition.list']
    );

    if (!empty($className)) {
      $markup = preg_replace('/<\/dl>\s+<dl class="' . $className . '">\s+/', '', $markup);
    }

    return $markup;
  }

  protected function blockDefinitionList($Line, $Block) {
    $Block = $this->setElementClass(
      parent::blockDefinitionList($Line, $Block),
      $this->settings['definition.list']
    );

    if (!$Block) return;

    $Block['element']['text'][0] = $this->setElementClass(
      ['element' => $Block['element']['text'][0]],
      $this->settings['definition.term']
    )['element'];

    return $Block;
  }

  protected function addDdElement(array $Line, array $Block) {
    $Block = parent::addDdElement($Line, $Block);

    if (!$Block) return;

    $Block['dd'] = $this->setElementClass(
      ['element' => $Block['dd']],
      $this->settings['definition.description']
    )['element'];

    return $Block;
  }

  # Footnote Marker

  protected function inlineFootnoteMarker($Excerpt) {
    $Inline = parent::inlineFootnoteMarker($Excerpt);

    if (!$Inline) return;

    // <sup>
    $Inline = $this->setElementClass(
      $Inline,
      $this->settings['footnote.marker.wrapper']
    );

    // <a>
    if (!empty($this->settings['footnote.marker.link'])) {
      unset($Inline['element']['text']['attributes']['class']);
      $Inline['element']['text'] = $this->setElementClass(
        ['element' => $Inline['element']['text']],
        $this->settings['footnote.marker.link']
      )['element'];
    }

    return $Inline;
  }

  # Footnotes List

  protected function buildFootnoteElement() {
    $Element = parent::buildFootnoteElement();


    // Container
    $containerClass = $this->getClassName(
      [ 'element' => $Element ],
      $this->settings['footnotes.container']
    );

    if ($containerClass !== 'footnotes') {
      $Element['attributes']['class'] = $containerClass;
    }

    // Separator
    $separatorClass = $this->getClassName(
      ['element' => $Element['text'][0]],
      $this->settings['footnotes.separator']
    );

    if (!empty($containerClass)) {
      $Element['text'][0]['attributes'] = ['class' => $separatorClass];
    }

    // List
    $listClass = $this->getClassName(
      ['element' => $Element['text'][1]],
      $this->settings['footnotes.list']
    );

    if (!empty($listClass)) {
      $Element['text'][1]['attributes'] = ['class' => $listClass];
    }

    // List Items
    $parser = $this;

    foreach ($Element['text'][1]['text'] as $i => $item) {

      // List Item Class
      $Element['text'][1]['text'][$i] = $this->setElementClass(
        ['element' => $Element['text'][1]['text'][$i]],
        $this->settings['footnotes.list.item'],
        [ 'parent' => $Element['text'][1]['name'] ]
      )['element'];

      // Backref Class
      $Element['text'][1]['text'][$i]['text'] = preg_replace_callback(
        '/<a href="(#fnref(\d+):(\d+))" rev="footnote" class="([^"]+)">(?:&#8617;(?:&#xFE0E;)?|' . mb_convert_encoding('&#8617;&#xFE0E;', 'UTF-8', 'HTML-ENTITIES') . ')<\/a><\/p>\n$/',
        function ($matches) use ($parser) {
          $backrefClass = $this->getClassName([ 'element' => [
              'name' => 'a',
              'attributes' => [
                'href'  => $matches[1],
                'rev'   => 'footnote',
                'class' => $matches[4],
              ],
              'text' => '&#8617;',
            ]],
            $this->settings['footnotes.backref']
          );

          return '<a href="#fnref' . $matches[2] . ':' . $matches[3] . '" rev="footnote" class="' . $backrefClass . '">&#8617;</a>';
        },
        $Element['text'][1]['text'][$i]['text']
      );
    }

    return $Element;
  }
}
