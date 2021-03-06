<?php

namespace Acquia\Lightning\Commands;

/**
 * Class to perform operations on a composer constraint.
 */
final class ComposerConstraint {

  /**
   * Raw constraint.
   *
   * E.g. '^2.8 || ^3.0'.
   *
   * @var string
   */
  private $constraint = '';

  /**
   * ComposerConstraint constructor.
   *
   * @param string $constraint
   *   Raw constraint.
   */
  public function __construct($constraint) {
    $this->constraint = $constraint;
  }

  /**
   * Returns the constraint's ranges as an array.
   *
   * @see https://getcomposer.org/doc/articles/versions.md#version-range
   *
   * @return string[]
   *   Constraint's ranges. For example, if the constraint is
   *   '^2.8 || ^3.0', it will be ['^2.8', '^3.0'].
   */
  private function getRanges() {
    preg_match_all('/[0-9a-zA-Z\~\>\=\-\<\.\^\*]+/', $this->constraint, $matches);

    return $matches[0];
  }

  /**
   * Returns the core dev version of the constraint.
   *
   * In the core dev version the last digits of the constraint's ranges are
   * replaced by the string 'x-dev', and their operators are removed.
   *
   * @return string
   *   Core dev version. For example, if the constraint is
   *   '8.4.3 || ^8.5.3', it will be '8.4.x-dev || 8.5.x-dev'.
   */
  public function getCoreDev() {
    return $this->mapRanges([$this, 'coreRangeToDev']);
  }

  /**
   * Returns the lightning dev version of the constraint.
   *
   * In the lightning dev version the first digits of the constraint's ranges are
   * concatenated with the string 'x-dev', and their operators are removed.
   *
   * @return string
   *   Lightning dev version. For example, if the constraint is
   *   '^1.3.0 || ^2.3.0', it will be '1.x-dev || 2.x-dev'.
   */
  public function getLightningDev() {
    return $this->mapRanges([$this, 'lightningRangeToDev']);
  }

  /**
   * Returns the transformed constraint.
   *
   * @param callable $callback
   *   Transformation to apply to each range.
   *
   * @return string
   *   Transformed constraint.
   */
  public function mapRanges(callable $callback) {
    $ranges = $this->getRanges();
    $replace_pairs = [];

    foreach ($ranges as $old_range) {
      $new_range = $callback($old_range);
      $replace_pairs[$old_range] = $new_range;
    }

    return strtr($this->constraint, $replace_pairs);
  }

  /**
   * Returns the core dev version of a given range.
   *
   * In the core dev version the last digits of the range are replaced by
   * the string 'x-dev', and the operators are removed.
   *
   * @param string $range
   *   Range. E.g. '^8.5.3'.
   *
   * @return string
   *   Core dev version of range. E.g. '8.5.x-dev'.
   */
  private function coreRangeToDev($range) {
    $stripped = $this->stripOperators($range);
    $dev = preg_replace('/\.[0-9]+$/', '.x-dev', $stripped);

    return $dev;
  }

  /**
   * Returns the lightning dev version of a given range.
   *
   * In the lightning dev version the first digits of the range are
   * concatenated with the string 'x-dev', and the operators are removed.
   *
   * @param string $range
   *   Range. E.g. '^1.3.0'.
   *
   * @return string
   *   Lightning dev version of range. E.g. '1.x-dev'.
   */
  private function lightningRangeToDev($range) {
    $stripped = $this->stripOperators($range);
    $dev = preg_replace('/^([0-9]+)\..*/', '$1.x-dev', $stripped);

    return $dev;
  }

  /**
   * Returns the operator free version of a given range.
   *
   * @param string $range
   *   Range. E.g. '^1.3.0'.
   *
   * @return string
   *   Operator free version of range. E.g. '1.3.0'.
   */
  private function stripOperators($range) {
    $stripped = preg_replace('/[^0-9\.]+/', NULL, $range);

    return $stripped;
  }

}
