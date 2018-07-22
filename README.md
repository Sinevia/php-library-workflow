# PHP Workflow

An easy and very pragmatic multistep workflow implementation with minimum requirements. 

## Workflow Steps ##

The step class is very basic and can be easily extended with what is needed from each workflow. This can be done either by using dynamic properties, or extending the Step class. The step name SHOULD be unique.

The steps are not sequential, though can be. However, when you add the steps to the workflow add them as they are logically sequenced. The logical sequence is used to calculate the overall progress of the workflow.

As in real live steps can be skipped. For example you do not need to go to the river to fetch watter, if its already waiting in a bottle.

## Workflow Progress ##

The worflow progress is a nice feature, which allows to show the progress in a progress bar, or a pie chart. It is calculated using the sequence in which the steps are added to the workflow. For instance, if a workflow has 100 steps, and you are on the 70th step, all the steps added before are considered "completed" and the overall completion of the workflow is at 70%.

## Features ##

- Multistep support
- Current step support
- Export state to string
- Import from saved state
- Extendable
- Unassuming
- Step metadata

## Example ##

This is just an example implementation. Its probably too much for simpler workflows.

```php
// Create application
$aplication = new Application;

// Create application workflow
$applicationWorkflow = new ApplicationWorkflow($application);

// Send email and update workflow
if(sendEmailToApplicant() == true){
    $applicationWorkflow->completeStepStartApplication();
}

// Check what is the current step
echo $applicationWorkflow->getCurrentStep()->title;

// Check what is the current step
echo $applicationWorkflow->getProgress();


class ApplicationWorkflow extends Workflow {

    protected $application = null;

    function __construct($application) {
        parent::__construct();

        $step = new Step("StartApplication");
        $step->type = "first";
        $step->title = "Start application process";
        $step->responsible = "Applicant";
        $this->addStep($step);
        
        $step = new Step("SignAgreement");
        $step->title = "Signing agreement";
        $step->responsible = "Applicant";
        $this->addStep($step);

        $step = new Step("SelectCourses");
        $step->title = "Select Courses";
        $step->responsible = "Applicant";
        $this->addStep($step);
        
        $step = new Step("UploadDiploma");
        $step->title = "Upload Diploma";
        $step->responsible = "Applicant";
        $this->addStep($step);

        $step = new Step("ConfirmFitsRequirements");
        $step->title = "Confirm if applicant fits the requirements. If not – advise other suitable programs.";
        $step->responsible = "Manager";
        $this->addStep($step);

        $step = new Step("SendApplicationForward");
        $step->title = "Send application forward";
        $step->responsible = "Manager";
        $this->addStep($step);
        
        $step = new Step("RecieveConfirmationLetter");
        $step->title = "Receive the letter of confirmation";
        $step->responsible = "Manager";
        $this->addStep($step);

        $step = new Step("NotifyApplicantOfAcceptance");
        $step->title = "Notify applicant has been accepted";
        $step->responsible = "Manager";
        $this->addStep($step);

        $step = new Step("RecieveLetterOfApprovalOfAcomodation");
        $step->title = "Receive official letter for approval of accommodation";
        $step->responsible = "Manager";
        $this->addStep($step);
        
        $step = new Step("NotifyApplicantHasBeenApprovedForAccomodation");
        $step->title = "Notify applicant is approved for accomodation";
        $step->responsible = "Manager";
        $this->addStep($step);

        $step = new Step("SendAccommodationApprovalLetterToApplicant");
        $step->title = "Send the accommodation approval letter to applicant - needed for visa application";
        $step->responsible = "Manager";
        $this->addStep($step);

        $step = new Step("PreparePapersForVisaApplication");
        $step->title = "Prepare papers needed for visa application";
        $step->responsible = "Manager";
        $this->addStep($step);

        $step = new Step("ApplyForVisa");
        $step->title = "Apply for Visa";
        $step->responsible = "Applicant";
        $this->addStep($step);

        $step = new Step("ReceiveDecisionForVisa");
        $step->title = "Receive decision for the visa";
        $step->responsible = "Manager";
        $this->addStep($step);
        
        $step = new Step("FindAccommodation");
        $step->title = "Find sutable accomodation";
        $step->responsible = "Applicant";
        $this->addStep($step);

        $step = new Step("PrepareFilesForResidenceCard");
        $step->title = "Prepare files for residence card.";
        $step->responsible = "Applicant";
        $this->addStep($step);

        $step = new Step("ApplyForResidenceCard");
        $step->title = "Apply for residence card";
        $step->responsible = "Applicant";
        $this->addStep($step);

        $this->application = $application;
        if ($application != null) {
            $this->fromString($application->State);
        }
    }
    
    function save() {
        if ($this->application != null) {
            $this->application->State = $this->toString();
            return $this->application->save();
        }
        return false;
    }
    
    function completeStepStartApplication() {
        $this->setCurrentStep("SignAgreement");
        return $this->save();
    }
    
    function completeStepSignAgreement() {
        $this->setCurrentStep("SelectCourses");
        return $this->save();
    }
}
```
