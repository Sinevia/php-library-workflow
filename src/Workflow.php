<?php
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

    public function addStep($step) {
        $this->steps[$step->name] = $step;
        if ($this->state['current_step'] == null) {
            $this->setCurrentStep($step->name);
        }
    }

    function getCurrentStep() {
        $stepName = $this->state['current_step'];
        if ($stepName == null) {
            return null;
        }
        return $this->steps[$stepName];
    }

    function setCurrentStep($nextStep) {
        /* Old step log as completed */
        $currentStep = $this->getCurrentStep();
        if ($currentStep != null) {
            $this->state['step_details'][$currentStep->name]['completed'] = date('Y-m-d');
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
        if ($stepPosition < $currentStepPosition) {
            return true;
        }
        return false;
    }

    function getProgress() {
        $currentStepPosition = array_search($this->state['current_step'], array_keys($this->steps));
        $percents = ($currentStepPosition / count($this->steps)) * 100;
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