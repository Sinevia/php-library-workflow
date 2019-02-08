<?php

// ========================================================================= //
// SINEVIA PUBLIC                                        http://sinevia.com  //
// ------------------------------------------------------------------------- //
// COPYRIGHT (c) 2008-2019 Sinevia Ltd                   All rights reserved //
// ------------------------------------------------------------------------- //
// LICENCE: All information contained herein is, and remains, property of    //
// Sinevia Ltd at all times.  Any intellectual and technical concepts        //
// are proprietary to Sinevia Ltd and may be covered by existing patents,    //
// patents in process, and are protected by trade secret or copyright law.   //
// Dissemination or reproduction of this information is strictly forbidden   //
// unless prior written permission is obtained from Sinevia Ltd per domain.  //
//===========================================================================//

namespace Sinevia\Workflow;

class Workflow {

    private $state = [
        'current_step' => null,
        'history' => [],
        'step_details' => [],
    ];
    public $steps = [];

    function __construct() {
        
    }

    /**
     * Adds a step to the workflow
     */
    public function addStep($step) {
        $this->steps[$step->name] = $step;
        if ($this->state['current_step'] == null) {
            $this->setCurrentStep($step->name);
        }
    }

    /**
     * Returns the current step of the workflow
     * @return Step|null the current step or null otherwise
     */
    function getCurrentStep() {
        $stepName = $this->state['current_step'];
        if ($stepName == null) {
            return null;
        }
        return $this->steps[$stepName];
    }

    /**
     * Sets the current step of the workflow
     * @param Step|string the Step instance or the name (key) of the Step
     * @return void
     */
    function setCurrentStep($nextStep) {
        /* Old step log as completed */
        $currentStep = $this->getCurrentStep();
        if ($currentStep != null) {
            $this->state['step_details'][$currentStep->name]['completed'] = date('Y-m-d H:i:s');
        }

        /* New step log as started */
        $nextStepName = is_string($nextStep) ? $nextStep : $nextStep->name;
        if (isset($this->steps[$nextStepName]) == false) {
            throw new \RuntimeException('Step ' . $nextStepName . ' DOES NOT exist in workflow');
        }
        $this->state['current_step'] = $nextStepName;
        $this->state['history'][] = $nextStepName;
        $this->state['step_details'][$nextStepName]['started'] = date('Y-m-d');
    }

    /**
     * Returns true if the specified step is current, false otherwise
     * @param Step|string the Step instance or the name (key) of the Step
     * @return void
     */
    function isStepCurrent($step) {
        $stepName = is_string($step) ? $step : $step->name;
        if ($this->state['current_step'] == $stepName) {
            return true;
        }
        return false;
    }

    function isStepComplete($step) {
        $stepName = is_string($step) ? $step : $step->name;
        $currentStepPosition = array_search($this->state['current_step'], array_keys($this->steps));
        $stepPosition = array_search($stepName, array_keys($this->steps));
        // Are we at next step? Yes => Then this step is complete.
        if ($stepPosition < $currentStepPosition) {
            return true;
        }
        // Is this step marked as compete? Yes => Then this step is complete.
        if (isset($this->state['step_details'][$stepName]['completed'])) {
            return true;
        }
        return false;
    }

    function getProgress() {
        $stepName = $this->state['current_step'];
        $currentStepPosition = array_search($stepName, array_keys($this->steps));
        $pending = count($this->steps) - ($currentStepPosition);
        if ($this->isStepComplete($stepName)) {
            $pending++;
        }
        $percents = ($currentStepPosition / count($this->steps)) * 100;
        if ($this->isStepComplete($stepName)) {
            $percents = (($currentStepPosition + 1) / count($this->steps)) * 100;
        }
        return [
            'total' => count($this->steps),
            'completed' => $currentStepPosition,
            'current' => $currentStepPosition,
            'pending' => count($this->steps) - ($currentStepPosition),
            'percents' => $percents
        ];
    }

    public function getSteps() {
        return $this->steps;
    }

    public function getStep($name) {
        if (isset($this->steps[$name])) {
            return $this->steps[$name];
        }
        return null;
    }

    public function getStepMeta($step, $key) {
        $stepName = is_string($step) ? $step : $step->name;
        if (isset($this->state['step_details'][$stepName]) == false) {
            return null;
        }
        if (isset($this->state['step_details'][$stepName][$key]) == false) {
            return null;
        }
        return $this->state['step_details'][$stepName][$key];
    }

    /**
     * Sets the current step of the workflow
     * @param Step|string the Step instance or the name (key) of the Step
     * @return void
     */
    function markStepAsCompleted($step) {
        $stepName = is_string($step) ? $step : $step->name;
        if (isset($this->state['step_details'][$stepName]) == false) {
            return false;
        }

        $this->state['step_details'][$stepName]['completed'] = date('Y-m-d H:i:s');
        return true;
    }

    public function setStepMeta($step, $key, $value) {
        $stepName = is_string($step) ? $step : $step->name;
        if (isset($this->state['step_details'][$stepName]) == false) {
            $this->state['step_details'][$stepName] = [];
        }
        $this->state['step_details'][$stepName][$key] = $value;
    }

    function getState() {
        return $this->memory['state'];
    }

    function fromString($string) {
        $this->state = json_decode($string, true);
    }

    function toString() {
        return json_encode($this->state);
    }

}
