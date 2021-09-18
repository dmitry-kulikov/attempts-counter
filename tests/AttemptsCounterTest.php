<?php

namespace kdn\attemptsCounter;

/**
 * Class AttemptsCounterTest.
 * @package kdn\attemptsCounter
 * @uses \kdn\attemptsCounter\Action
 */
class AttemptsCounterTest extends TestCase
{
    /**
     * @var AttemptsCounter
     */
    protected $counter;

    /**
     * @before
     */
    protected function prepare()
    {
        $this->counter = new AttemptsCounter();
    }

    /**
     * @covers \kdn\attemptsCounter\AttemptsCounter::addAction
     * @covers \kdn\attemptsCounter\AttemptsCounter::getAction
     * @small
     */
    public function testAddActionAndGetAction()
    {
        $actionName = 'foo';
        $action = new Action($actionName, 1);
        $this->assertSame($action, $this->counter->addAction($action)->getAction($actionName));
    }

    /**
     * @covers \kdn\attemptsCounter\AttemptsCounter::addAction
     * @covers \kdn\attemptsCounter\AttemptsCounter::getAction
     * @small
     */
    public function testAddActionOverwrite()
    {
        $actionName = 'foo';
        $action = new Action($actionName, 1);
        $this->assertSame(
            $action,
            $this->counter->addAction((new Action($actionName, 2)))->addAction($action)->getAction($actionName)
        );
    }

    /**
     * @covers \kdn\attemptsCounter\AttemptsCounter::addAction
     * @covers \kdn\attemptsCounter\AttemptsCounter::getAction
     * @small
     */
    public function testAddActionNoOverwrite()
    {
        $actionName = 'foo';
        $action = new Action($actionName, 1);
        $this->assertSame(
            $action,
            $this->counter->addAction($action)->addAction((new Action($actionName, 2)), false)->getAction($actionName)
        );
    }

    /**
     * @covers \kdn\attemptsCounter\AttemptsCounter::getAction
     * @small
     */
    public function testGetActionNonexistent()
    {
        $actualErrorMessage = null;
        try {
            $this->counter->getAction('baz');
        } catch (Exception $e) {
            $actualErrorMessage = $e->getMessage();
        }
        $this->assertEquals('Unknown action name "baz" specified.', $actualErrorMessage);
    }
}
