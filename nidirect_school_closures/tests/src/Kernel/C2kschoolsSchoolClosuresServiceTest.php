<?php

namespace Drupal\Tests\nidirect_school_closures;

use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass Drupal\nidirect_school_closures\Service\C2kschoolsSchoolClosuresService
 *
 * @group nidirect_school_closures
 * @group nidirect
 */
class C2kschoolsSchoolClosuresServiceTest extends KernelTestBase {

  protected $closureService;

  protected $strictConfigSchema = FALSE;

  public static $modules = [
    'nidirect_school_closures',
  ];

  /**
   * Test setup function.
   */
  public function setUp() {
    parent::setUp();

    $this->installConfig(['nidirect_school_closures']);

    $this->closureService = \Drupal::service('nidirect_school_closures.source.cskschools');
  }

  /**
   * Test 2 current and 1 expired closure.
   */
  public function testReturnCorrectNumberofClosures() {
    $xml = simplexml_load_file(__DIR__ . '/data/closures.xml');

    $this->closureService->setXml($xml);
    $this->closureService->processData();
    $data = $this->closureService->getData();

    $expected = 2;
    $output = count($data);

    $this->assertEquals($expected, $output);
  }

  /**
   * Test when no closures are in effect.
   */
  public function testNoClosures() {
    $xml = simplexml_load_file(__DIR__ . '/data/noclosures.xml');

    $this->closureService->setXml($xml);
    $this->closureService->processData();
    $data = $this->closureService->getData();

    $expected = 0;
    $output = count($data);

    $this->assertEquals($expected, $output);
  }

  /**
   * Test error state when valid XML is present.
   */
  public function testErrorStateFalse() {
    $xml = simplexml_load_file(__DIR__ . '/data/closures.xml');
    $this->closureService->setXml($xml);
    $this->closureService->processData();

    $expected = FALSE;
    $output = $this->closureService->hasErrors();

    $this->assertEquals($expected, $output);
  }

  /**
   * Test error state when invalid XML is retrived.
   */
  public function testErrorStateTrue() {
    $xml = simplexml_load_file(__DIR__ . '/data/blank.xml');
    $this->closureService->setXml($xml);
    $this->closureService->processData();

    $expected = TRUE;
    $output = $this->closureService->hasErrors();

    $this->assertEquals($expected, $output);
  }

}
