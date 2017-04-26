<?php

use Behat\Behat\Context\BehatContext;

class FeatureContext extends BehatContext
{
    /** @Given /^some step with "([^"]*)" argument$/ */
    public function someStepWithArgument($argument1)
    {
        return $argument1;
    }

    /** @Given /^number step with (\d+)$/ */
    public function numberStepWith($argument1)
    {
        return $argument1;
    }
}
