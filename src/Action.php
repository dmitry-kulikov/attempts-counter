<?php

namespace kdn\attemptsCounter;

/**
 * Class Action.
 * @package kdn\attemptsCounter
 */
class Action
{
    /**
     * @var int|string the action name
     */
    protected $name;

    /**
     * @var positive-int the maximum number of attempts to perform the action
     */
    protected $maxAttempts;

    /**
     * @var int the time interval in nanoseconds between attempts to perform the action
     */
    protected $sleepTime = 0;

    /**
     * @var int the number of already completed attempts to perform the action
     */
    protected $attemptsCount = 0;

    /**
     * Constructor.
     * @param int|string $name the action name
     * @param positive-int $maxAttempts the maximum number of attempts to perform the action
     * @param int $sleepTime the time interval in nanoseconds between attempts to perform the action
     */
    public function __construct($name, $maxAttempts, $sleepTime = 0)
    {
        $this->name = $name;
        $this->maxAttempts = $maxAttempts;
        $this->sleepTime = $sleepTime;
        $this->validate();
    }

    /**
     * Get the action name.
     * @return int|string the action name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Increments the counter for the action.
     * If attempts exhausted then throws an AttemptsLimitException,
     * otherwise waits according to the action configuration.
     * @param null|\Throwable $exception an optional exception object that can be attached to AttemptsLimitException
     * as a previous exception
     * @param int $number the number of used attempts to perform the action;
     * IMPORTANT: this number multiplies the delay until the next attempt
     * @throws AttemptsLimitException
     */
    public function increment($exception = null, $number = 1)
    {
        $this->attemptsCount += $number;
        if ($this->attemptsCount >= $this->maxAttempts) {
            throw new AttemptsLimitException(
                'Cannot perform action "' . $this->name . '" after ' . $this->attemptsCount
                . ' attempt(s) with ' . $this->sleepTime . ' nanosecond(s) interval(s).',
                0,
                $exception
            );
        }

        $totalSleepTime = $this->sleepTime * $number;
        time_nanosleep((int)($totalSleepTime / 1000000000), $totalSleepTime % 1000000000);
    }

    /**
     * Validate values of the action properties.
     * @throws InvalidConfigException
     */
    protected function validate()
    {
        if (!is_string($this->name) && !is_int($this->name)) {
            throw new InvalidConfigException('The action name must be a string or an integer.');
        }

        if (!is_int($this->maxAttempts)) {
            throw new InvalidConfigException('The maximum number of attempts must be an integer.');
        }
        if ($this->maxAttempts < 1) {
            throw new InvalidConfigException('The maximum number of attempts must be a positive integer.');
        }

        if (!is_int($this->sleepTime)) {
            throw new InvalidConfigException('The time interval in nanoseconds between attempts must be an integer.');
        }
        if ($this->sleepTime < 0) {
            throw new InvalidConfigException(
                'The time interval in nanoseconds between attempts must be a non-negative integer.'
            );
        }
    }
}
