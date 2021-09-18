<?php

namespace kdn\attemptsCounter;

/**
 * Class AttemptsCounter.
 * @package kdn\attemptsCounter
 */
class AttemptsCounter
{
    /**
     * @var array<string, positive-int> the maximum numbers of attempts to perform some actions;
     * keys are action names, values are numbers of attempts
     */
    protected $maxAttempts = [];

    /**
     * @var array<string, int> the time in nanoseconds between attempts to perform some actions;
     * keys are action names, values are delays in nanoseconds
     */
    protected $sleepTime = [];

    /**
     * @var array<string, int> the numbers of already completed attempts to perform some actions;
     * keys are action names, values are numbers of attempts
     */
    protected $attemptsCount = [];

    /**
     * Adds a new action to the counter object.
     * @param string $actionName the action name
     * @param positive-int $maxAttempts the maximum number of attempts to perform action, should be >= 1
     * @param int $sleepTime the time in nanoseconds between attempts to perform action, should be >= 0
     * @param bool $overwrite whether to overwrite existing action data in case of name conflict
     * @return $this counter object.
     * @throws InvalidConfigException
     */
    public function addAction($actionName, $maxAttempts, $sleepTime = 0, $overwrite = true)
    {
        if (!is_string($actionName)) {
            throw new InvalidConfigException('The action name must be a string.');
        }
        if (array_key_exists($actionName, $this->attemptsCount) && !$overwrite) {
            return $this;
        }

        if (!is_int($maxAttempts)) {
            throw new InvalidConfigException('The maximum number of attempts must be an integer.');
        }
        if ($maxAttempts < 1) {
            throw new InvalidConfigException('The maximum number of attempts must be a positive integer.');
        }

        if (!is_int($sleepTime)) {
            throw new InvalidConfigException('The time in nanoseconds between attempts must be an integer.');
        }
        if ($sleepTime < 0) {
            throw new InvalidConfigException(
                'The time in nanoseconds between attempts must be a non-negative integer.'
            );
        }

        $this->maxAttempts[$actionName] = $maxAttempts;
        $this->sleepTime[$actionName] = $sleepTime;
        $this->attemptsCount[$actionName] = 0;

        return $this;
    }

    /**
     * Increments the counter for the specified action.
     * If attempts exhausted then throws an exception, otherwise waits according to the action configuration.
     * @param string $actionName the action name
     * @param null|\Throwable $exception an optional exception object that can be attached to AttemptsLimitException
     * as a previous exception
     * @param int $number the number of used attempts to perform the specified action;
     * IMPORTANT: this number multiplies the delay until the next attempt
     * @throws AttemptsLimitException
     */
    public function increment($actionName, $exception = null, $number = 1)
    {
        $this->attemptsCount[$actionName] += $number;
        if ($this->attemptsCount[$actionName] >= $this->maxAttempts[$actionName]) {
            throw new AttemptsLimitException(
                'Cannot perform action "' . $actionName . '" after ' . $this->attemptsCount[$actionName]
                . ' attempt(s) with ' . $this->sleepTime[$actionName] . ' nanosecond(s) interval(s).',
                0,
                $exception
            );
        }

        $sleepTime = $this->sleepTime[$actionName] * $number;
        time_nanosleep((int)($sleepTime / 1000000000), $sleepTime % 1000000000);
    }
}
