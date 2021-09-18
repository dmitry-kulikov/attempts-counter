<?php

namespace kdn\attemptsCounter;

/**
 * Class ActionTest.
 * @package kdn\attemptsCounter
 */
class ActionTest extends TestCase
{
    /**
     * @covers \kdn\attemptsCounter\Action::__construct
     * @covers \kdn\attemptsCounter\Action::getName
     * @covers \kdn\attemptsCounter\Action::validate
     * @small
     */
    public function testGetName()
    {
        $name = 'foo';
        $this->assertEquals($name, (new Action($name, 1))->getName());
    }

    /**
     * @covers \kdn\attemptsCounter\Action::__construct
     * @covers \kdn\attemptsCounter\Action::increment
     * @covers \kdn\attemptsCounter\Action::validate
     * @small
     */
    public function testIncrement()
    {
        $action = new Action('foo', 2);
        $action->increment(); // this is fine
        $actualErrorMessage = null;
        try {
            $action->increment();
        } catch (AttemptsLimitException $e) {
            $actualErrorMessage = $e->getMessage();
        }
        $this->assertEquals(
            'Cannot perform action "foo" after 2 attempt(s) with 0 nanosecond(s) interval(s).',
            $actualErrorMessage
        );
    }

    /**
     * @covers \kdn\attemptsCounter\Action::__construct
     * @covers \kdn\attemptsCounter\Action::increment
     * @covers \kdn\attemptsCounter\Action::validate
     * @small
     */
    public function testIncrementExceptionChaining()
    {
        $action = new Action('foo', 1);
        $expectedException = new \Exception('Something wrong.');
        $actualException = null;
        try {
            $action->increment($expectedException);
        } catch (AttemptsLimitException $e) {
            $actualException = $e->getPrevious();
        }
        $this->assertEquals($expectedException, $actualException);
    }

    /**
     * @covers \kdn\attemptsCounter\Action::__construct
     * @covers \kdn\attemptsCounter\Action::increment
     * @covers \kdn\attemptsCounter\Action::validate
     * @small
     */
    public function testIncrementNumber()
    {
        $maxAttempts = 3;
        $action = new Action('foo', $maxAttempts);
        $actualErrorMessage = null;
        try {
            $action->increment(null, $maxAttempts);
        } catch (AttemptsLimitException $e) {
            $actualErrorMessage = $e->getMessage();
        }
        $this->assertEquals(
            'Cannot perform action "foo" after 3 attempt(s) with 0 nanosecond(s) interval(s).',
            $actualErrorMessage
        );
    }

    /**
     * @covers \kdn\attemptsCounter\Action::__construct
     * @covers \kdn\attemptsCounter\Action::increment
     * @covers \kdn\attemptsCounter\Action::validate
     * @medium
     */
    public function testIncrementDelay()
    {
        $sleepTime = 2;
        $action = new Action('foo', 3, $sleepTime * 1000000000);
        $wastedAttemptsNumber = 2;
        $startTime = time();
        $action->increment(null, $wastedAttemptsNumber);
        $this->assertGreaterThanOrEqual($startTime + $wastedAttemptsNumber * $sleepTime, time());
    }

    /**
     * @return array[]
     */
    public static function validateProvider()
    {
        return [
            'action name is array' => [[], 1, 0, 'The action name must be a string or an integer.'],
            'action name is float' => [1.5, 1, 0, 'The action name must be a string or an integer.'],

            'maximum number of attempts is float' => [
                'foo',
                1.5,
                0,
                'The maximum number of attempts must be an integer.',
            ],
            'maximum number of attempts is zero' => [
                'foo',
                0,
                0,
                'The maximum number of attempts must be a positive integer.',
            ],
            'maximum number of attempts is negative' => [
                'foo',
                -1,
                0,
                'The maximum number of attempts must be a positive integer.',
            ],

            'time interval between attempts is float' => [
                'foo',
                1,
                1.5,
                'The time interval in nanoseconds between attempts must be an integer.',
            ],
            'time interval between attempts is negative' => [
                'foo',
                1,
                -1,
                'The time interval in nanoseconds between attempts must be a non-negative integer.',
            ],
        ];
    }

    /**
     * @param mixed $name
     * @param mixed $maxAttempts
     * @param mixed $sleepTime
     * @param string $expectedErrorMessage
     * @covers       \kdn\attemptsCounter\Action::__construct
     * @covers       \kdn\attemptsCounter\Action::validate
     * @dataProvider validateProvider
     * @small
     */
    public function testValidate($name, $maxAttempts, $sleepTime, $expectedErrorMessage)
    {
        $actualErrorMessage = null;
        try {
            new Action($name, $maxAttempts, $sleepTime);
        } catch (InvalidConfigException $e) {
            $actualErrorMessage = $e->getMessage();
        }
        $this->assertEquals($expectedErrorMessage, $actualErrorMessage);
    }
}
