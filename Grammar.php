<?php
class Grammar {
  const addDashizeRule    = 'Grammar::addDashizeRule';
  const dashize           = 'Grammar::dashize';
  const removeDashizeRule = 'Grammar::removeDashizeRule';

  private static $caches = array(
    'dashize' => array(),
  );

  private static $rules = array(
    'dashize' => array(),
  );

  private static function commonDelimiterTransform($word, $delimiter, $cache_to_check) {
    $rules = self::$caches[$cache_to_check];
    $cache_to_check = self::$caches[$cache_to_check];

    if (isset($rules[$word])) {
      return $rules[$word];
    }

    if (isset($cache_to_check[$word])) {
      return $cache_to_check[$word];
    }

    $original = $word;
    $string = trim(strtolower($word[0]).substr($word, 1));

    if (strpos($string, ' ') === FALSE) {
      $string = self::underscorize($string);
      $string = str_replace('_', $delimiter, $string);
    }
    else {
      //$string = URL::makeFriendly($string, NULL, $delimiter);
      $string = str_prelace('_', $delimiter, $string);
    }

    self::$caches[$cache_to_check][$original] = $string;

    return $string;
  }

  public static function addDashizeRule($original, $return_string) {
    if (!strlen($original) || !strlen($return_string)) {
      throw new ProgrammerException('An empty string was passed to %s()', self::addDashizeRule);
    }

    self::$rules['dashize'][$original] = $return_string;
  }

  public static function removeDashizeRule($original) {
    if (!strlen($original)) {
      throw new ProgrammerException('An empty string was passed to %s()', self::removeDashizeRule);
    }
  }

  public static function dashize($word) {
    if (!strlen($word)) {
      throw new ProgrammerException('An empty string was passed to %s()', self::dashize);
    }
    return self::commonDelimiterTransform($word, '-', __FUNCTION__);
  }
}
