<?php

class WorkflowTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }


    public function testTrue(){
        $flow = new ExampleWorkflow();
        $step = $flow->getCurrentStep();
        $this->assertEquals("Step1", $step->name);

        $this->assertFalse($flow->isStepComplete("Step1"));
        $this->assertFalse($flow->isStepComplete("Step2"));
        $this->assertFalse($flow->isStepComplete("Step3"));
        
        $flow->setCurrentStep("Step2");
        $step = $flow->getCurrentStep();
        $this->assertEquals("Step2", $step->name);

        $this->assertTrue($flow->isStepComplete("Step1"));
        $this->assertFalse($flow->isStepComplete("Step2"));
        $this->assertFalse($flow->isStepComplete("Step3"));
        
        $flow->setCurrentStep("Step3");
        $step = $flow->getCurrentStep();
        $this->assertEquals("Step3", $step->name);
        
        $this->assertTrue($flow->isStepComplete("Step1"));
        $this->assertTrue($flow->isStepComplete("Step2"));
        $this->assertFalse($flow->isStepComplete("Step3"));

        $flow->markStepAsCompleted("Step3");

        $this->assertEquals(100, $flow->getProgress()['percents']);

    }
}

class ExampleWorkflow extends \Sinevia\Workflow\Workflow {
    function __construct() {
        parent::__construct();

        $step = new \Sinevia\Workflow\Step("Step1");
        $step->type = "first";
        $step->title = "Step 1";
        $this->addStep($step);

        $step = new \Sinevia\Workflow\Step("Step2");
        $step->title = "Step 2";
        $this->addStep($step);

        $step = new \Sinevia\Workflow\Step("Step3");
        $step->title = "Step 3";
        $this->addStep($step);
    }
}