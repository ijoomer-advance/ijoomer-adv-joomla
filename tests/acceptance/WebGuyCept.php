<?php
use \WebGuy;

class Mouse_And_Enter_Cest
{

  /**
   * Test Case: test move mouse over
   *
   * @param WebGuy   $I        WebGuy object
   * @param Scenario $scenario Scenario object
   *
   * @group moveMouseOver
   *
   * @return null
   */
  public function testMoveMouseOver(WebGuy $I, $scenario) 
  {
    $I->amOnPage("/");
    $I->moveMouseOver('//input[@id="gbqfq"]');
    $I->moveMouseOver('//input[@id="gbqfq"]');
    $I->moveMouseOver('//input[@id="gbqfq"]');
    $I->moveMouseOver('//input[@id="gbqfq"]');
    $I->moveMouseOver('//input[@id="gbqfq"]');
    $I->moveMouseOver('//input[@id="gbqfq"]');
    $I->moveMouseOver('//input[@id="gbqfq"]');
  }

  /**
   * Test Case: press button
   *
   * @param WebGuy   $I        WebGuy object
   * @param Scenario $scenario Scenario object
   *
   * @group pressButton
   *
   * @return null
   */
  public function testPressButton(WebGuy $I, $scenario) 
  {
    $I->amOnPage("/");
    $I->fillField("//input[@id='gbqfq']", "codeception");
    $I->pressKey("//input[@id='gbqfq']", "enter");
    $I->see("codeception");
  }
}