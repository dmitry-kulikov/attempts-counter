<?php

namespace kdn\attemptsCounter;

/**
 * Class AttemptsCounter.
 * @package kdn\attemptsCounter
 */
class AttemptsCounter
{
    /**
     * @var array<string, Action> actions;
     * keys are action names, values are action objects
     */
    protected $actions = [];

    /**
     * Adds a new action to the counter object.
     * @param Action $action the action
     * @param bool $overwrite whether to overwrite existing action data in case of name conflict
     * @return $this the counter object.
     */
    public function addAction($action, $overwrite = true)
    {
        if (array_key_exists($action->getName(), $this->actions) && !$overwrite) {
            return $this;
        }

        $this->actions[$action->getName()] = $action;

        return $this;
    }

    /**
     * Get an action by name.
     * @param int|string $actionName the action name
     * @return Action the action object.
     * @throws Exception
     */
    public function getAction($actionName)
    {
        if (!array_key_exists($actionName, $this->actions)) {
            throw new Exception('Unknown action name "' . $actionName . '" specified.');
        }

        return $this->actions[$actionName];
    }
}
