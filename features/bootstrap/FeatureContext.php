<?php

use Behat\Behat\Context\BehatContext;

class FeatureContext extends BehatContext
{
    private $_string;

    /** @Given /^some step with "([^"]*)" argument$/ */
    public function someStepWithArgument($string)
    {
        $this->_string = strtolower($string);
    }

    /**
     * @Then /^i have "([^"]*)"$/
     */
    public function iHave($expected)
    {
        if (0 !== strcmp(strtolower($expected), $this->_string.'!'))
            throw new Exception('Wrong "hello world" received: '.$this->_string );

        return $expected;
    }
}
