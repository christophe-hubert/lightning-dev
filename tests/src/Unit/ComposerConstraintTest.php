<?php

namespace Drupal\Tests\lightning_dev\Unit;

use Drupal\lightning_dev\ComposerConstraint;
use Drupal\Tests\UnitTestCase;

/**
 * @group lightning
 * @group lightning_dev
 */
class ComposerConstraintTest extends UnitTestCase {

  /**
   * @dataProvider getLightningDevProvider
   */
  public function testGetLightningDev($constraint, $expected) {
    $actual = (new ComposerConstraint($constraint))->getLightningDev();
    $this->assertEquals($expected, $actual);
  }

  public function getLightningDevProvider() {
    return [
      'first digits and x-dev' => [
        'constraint' => '10.3.0',
        'expected' => '10.x-dev',
      ],
      'strip operators' => [
        'constraint' => '^1.3.0',
        'expected' => '1.x-dev',
      ],
      'multiple ranges' => [
        'constraint' => '^1.3.0 || ~2.3.0',
        'expected' => '1.x-dev || 2.x-dev',
      ],
    ];
  }

  /**
   * @dataProvider getCoreDevProvider
   */
  public function testGetCoreDev($constraint, $expected) {
    $actual = (new ComposerConstraint($constraint))->getCoreDev();
    $this->assertEquals($expected, $actual);
  }

  public function getCoreDevProvider() {
    return [
      'last digits to x-dev' => [
        'constraint' => '8.5.31',
        'expected' => '8.5.x-dev',
      ],
      'strip operators' => [
        'constraint' => '^8.5.3',
        'expected' => '8.5.x-dev',
      ],
      'multiple ranges' => [
        'constraint' => '~8.5.3 || ^8.6.3',
        'expected' => '8.5.x-dev || 8.6.x-dev',
      ],
      'previously inserted not replaced' => [
        'constraint' => '8.5.3 || 8.5',
        'expected' => '8.5.x-dev || 8.x-dev',
      ],
    ];
  }

}
