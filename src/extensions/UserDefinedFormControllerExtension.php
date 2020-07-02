<?php

namespace Firesphere\PartialUserforms\Extensions;

use Page;
use SilverStripe\Forms\Form;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\FormAction;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\UserForms\Control\UserDefinedFormController;
use Firesphere\PartialUserforms\Models\PartialFieldSubmission;
use Firesphere\PartialUserforms\Models\PartialFileFieldSubmission;
use SilverStripe\UserForms\Model\EditableFormField\EditableFormStep;
use SilverStripe\UserForms\Model\EditableFormField\EditableFileField;
use Firesphere\PartialUserforms\Controllers\PartialSubmissionController;
use Firesphere\PartialUserforms\Controllers\PartialUserFormVerifyController;
use Firesphere\PartialUserforms\Forms\PasswordForm;
use Firesphere\PartialUserforms\Controllers\PartialUserFormController;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\UserForms\Model\Submission\SubmittedFormField;

/**
 * Class UserDefinedFormControllerExtension
 *
 * @package Firesphere\PartialUserforms\Extensions
 * @property UserDefinedFormController|UserDefinedFormControllerExtension $owner
 */
class UserDefinedFormControllerExtension extends Extension
{
    private static $allowed_actions = [
        'partialIndex',
        'start',
        'StartForm',
        'overview',
        'OverviewForm',
        'verify',
        'VerifyForm',
    ];

    private static $url_handlers = [
        '' => 'partialIndex',
    ];

    /**
     * Creates a new partial submission and partial fields.
     *
     * @throws \SilverStripe\ORM\ValidationException
     */
    protected function createPartialSubmission()
    {
        $page = $this->owner;
        // Create partial form
        $partialSubmission = PartialFormSubmission::create([
            'ParentID'              => $page->ID,
            'ParentClass'           => $page->ClassName,
            'UserDefinedFormID'     => $page->ID,
            'UserDefinedFormClass'  => $page->ClassName,
        ]);
        $submissionID = $partialSubmission->write();

        // Create partial fields
        foreach ($page->data()->Fields() as $field) {
            // We don't need literal fields, headers, html, etc
            if ($field::config()->literal === true || $field->ClassName == EditableFormStep::class) {
                continue;
            }

            $newData = [
                'SubmittedFormID'   => $submissionID,
                'Name'              => $field->Name,
                'Title'             => $field->Title ?? $field->Name,
            ];

            if (in_array(EditableFileField::class, $field->getClassAncestry())) {
                $partialFile = PartialFileFieldSubmission::create($newData);
                $partialSubmission->PartialUploads()->add($partialFile);
            } else {
                $partialField = PartialFieldSubmission::create($newData);
                $partialSubmission->PartialFields()->add($partialField);
            }
        }

        // Refresh session on start
        PartialSubmissionController::reloadSession($page->getRequest()->getSession(), $submissionID);
        $page->getRequest()->getSession()->set(PasswordForm::PASSWORD_SESSION_KEY, $submissionID);
    }

    /**
     * Gets the PartialFormSubmission based on the current session
     */
    protected function getPartialFormSubmission()
    {
        $session = Controller::curr()->getRequest()->getSession();
        $partialID = $session->get(PartialSubmissionController::SESSION_KEY);

        return PartialFormSubmission::get()->byID($partialID);
    }

    /**
     * Redirect user to form start if EnablePartialSubmissions is true
     */
    public function partialIndex(HTTPRequest $request = null)
    {
        if (!$this->owner->EnablePartialSubmissions) {
            return $this->owner->index($request);
        }

        return $this->owner->redirect($this->owner->Link('start'));
    }

    /**
     * Verify route, for password entry
     */
    public function verify(HTTPRequest $request = null)
    {
        if (!$this->owner->EnablePartialSubmissions) {
            return $this->owner->redirect($this->owner->Link());
        }

        return $this->owner->customise([
            'Form' => $this->VerifyForm(),
        ])->renderWith([UserDefinedFormController::class . '_start', Page::class]);
    }

    /**
     * Generate the PasswordForm
     * @return Form
     */
    public function VerifyForm()
    {
        $partial = $this->getPartialFormSubmission();
        $controller = PartialUserFormVerifyController::create();
        $controller->setPartialFormSubmission($partial);

        return $controller->getForm()
            ->setFormAction($this->owner->link('VerifyForm'));
    }

    /**
     * Start route
     */
    public function start(HTTPRequest $request = null)
    {
        if (!$this->owner->EnablePartialSubmissions) {
            return $this->owner->redirect($this->owner->Link());
        }

        return $this->owner->customise([
            'Form' => $this->StartForm(),
        ]);
    }

    /**
     * Generate the StartForm
     * @return Form
     */
    public function StartForm()
    {
        $actions = FieldList::create(
            FormAction::create('goToOverview')->setTitle('Start')
        );

        $form = Form::create(
            $this->owner,
            'StartForm',
            FieldList::create(),
            $actions
        )->addExtraClass('userform');

        return $form;
    }

    /**
     * Redirect to the overview page, creating a new PartialFormSubmission if necessary
     */
    public function goToOverview($data, $form)
    {
        // Create a new session each time a user starts the form
        $this->createPartialSubmission();
        return $this->owner->redirect($this->owner->Link('overview'));
    }

    /**
     * Overview route
     */
    public function overview(HTTPRequest $request = null)
    {
        if (!$this->owner->EnablePartialSubmissions) {
            return $this->owner->redirect($this->owner->Link());
        }

        $formLocked = PartialUserFormController::isLockedOut();
        $form = $this->OverviewForm($request);
        if ($formLocked) {
            $form->unsetAllActions();
        } else {
            // Clear session if it's not locked (e.g. session belongs to the user)
            PartialSubmissionController::clearLockSession();
        }

        return $this->owner->customise([
            'Form' => $form,
            'FormLocked' => $formLocked,
            'PartialForm' => $this->getPartialFormSubmission(),
        ]);
    }

    /**
     * Creates the overview form to display the form link and password
     * @return Form
     */
    public function OverviewForm(HTTPRequest $request = null)
    {
        $partialID = $request->getSession()->get(PartialSubmissionController::SESSION_KEY);
        $password = $request->getSession()->get(PartialUserFormVerifyController::PASSWORD_KEY);
        $submission = PartialFormSubmission::get()->byID($partialID);

        if (!$partialID || !$submission) {
            return $this->owner->redirect($this->owner->Link('start'));
        }

        $fields = FieldList::create(
            TextField::create('FormLink', 'Form link', $submission->getPartialLink())
                ->setReadonly(true)
        );

        if ($this->owner->PasswordProtected) {
            $fields->push(
                TextField::create('Password', 'Password', $password)
                    ->setReadonly(true)
            );
        }

        $actions = FieldList::create(
            FormAction::create('goToForm')
                ->setTitle('Go to form')
                ->addExtraClass('primary'),
            FormAction::create('goToLogout')
                ->setTitle('Logout')
                ->addExtraClass('log-out')
        );

        return Form::create(
            $this->owner,
            'OverviewForm',
            $fields,
            $actions
        )->addExtraClass('userform');
    }

    /**
     * Navigate to the partial form
     */
    public function goToForm($data, $form)
    {
        $link = $data['FormLink'];

        return $this->owner->redirect($link);
    }

    /**
     * Clear session and go to start page
     */
    public function goToLogout($data, $form)
    {
        $request = $this->owner->getRequest();
        $session = $request->getSession();
        $session->clear(PartialSubmissionController::SESSION_KEY);
        $session->clear(PasswordForm::PASSWORD_SESSION_KEY);
        $session->clear(PartialUserFormVerifyController::PASSWORD_KEY);
        return $this->owner->redirect($this->owner->Link('start'));
    }

    /**
     * Share link from session
     * Template helper @see UserFormStepNav.ss
     * @return void|string URL
     */
    public function getShareLink()
    {
        $partialSubmission = $this->getPartialFormSubmission();
        if ($partialSubmission) {
            return $partialSubmission->getPartialLink();
        }
    }

    /**
     * When form is submitted finished() function is called in
     * vendor/silverstripe/userforms/code/Control/UserDefinedFormController.php
     *
     * SubmissionSummary is added to $data (Pass by reference) which is shown in template
     * vendor/silverstripe/userforms/templates/SilverStripe/UserForms/Control/UserDefinedFormController_ReceivedFormSubmission.ss
     * this template is overridden in MBIE site
     */
    public function updateReceivedFormSubmissionData(&$data)
    {
        if ($this->owner->ShowSubmissionSummary) {
            $formID = $this->owner->ID;
            $submissionID = $this->owner->getRequest()
                ->getSession()
                ->get('userformssubmission' . $formID);
            $data["SubmissionSummary"] = $this->fetchSubmittedData($formID, $submissionID);
        }
        return true;
    }

    /**
     * Returns ArrayList which is compilation of all Submitted Form Field and values
     */
    public function fetchSubmittedData($formPageID, $submissionID)
    {
        // Ignore following form fields as they are not part of submission
        $ignoreFields = [
            "SilverStripe\SpamProtection\EditableSpamProtectionField",
            "SilverStripe\UserForms\Model\EditableFormField\EditableFormHeading",
            "SilverStripe\UserForms\Model\EditableFormField\EditableLiteralField",
            "SilverStripe\UserForms\Model\EditableFormField\EditableMemberListField",
        ];

        $formFields = EditableFormField::get()
            ->where(['ParentID' => $formPageID])
            ->exclude(['ClassName' => $ignoreFields])
            ->sort('Sort'); // Order by fields as they appear in Form
        $submittedData = SubmittedFormField::get()
            ->where(['ParentID' => $submissionID]);

        $stepsList = new ArrayList();
        $fieldsList = new ArrayList();
        $groupsList = new ArrayList();

        $_fields = 0;
        $groupTitle = '';

        foreach ($formFields as $formField) {
            if ($formField->ClassName == "SilverStripe\UserForms\Model\EditableFormField\EditableFormStep") {
                // Store title so when new step is encountered this title will be saved
                // For first step do not add data just continue store title in currentTitle
                if ($_fields == 0) {
                    $stepTitle = $formField->Title ?? 'Section';
                    $_fields++;
                    continue;
                }

                if ($fieldsList->count() != 0) {
                    $groupsList->add([
                        'FieldGroupTitle' => '',
                        'FieldsList' => $fieldsList
                    ]);
                    $fieldsList = new ArrayList();
                }

                $stepsList->add([
                    'StepTitle' => $stepTitle,
                    'FieldGroup' => $groupsList,
                ]);
                $stepTitle = $formField->Title ?? 'Section';
                $groupsList = new ArrayList();
                continue;
            }

            if ($formField->ClassName == "SilverStripe\UserForms\Model\EditableFormField\EditableFieldGroup") {
                // New group start detected - Store values of previous fields as ungrouped data
                if ($fieldsList->Count()) {
                    $groupsList->add([
                        'FieldGroupTitle' => '',
                        'FieldsList' => $fieldsList
                    ]);
                }
                $groupTitle = $formField->Title ?? "";
                $fieldsList = new ArrayList();
                continue;
            }
            if ($formField->ClassName == "SilverStripe\UserForms\Model\EditableFormField\EditableFieldGroupEnd") {
                $groupsList->add([
                    'FieldGroupTitle' => $groupTitle,
                    'FieldsList' => $fieldsList
                ]);
                $fieldsList = new ArrayList();
                continue;
            }

            // Process the current field
            $submission = $submittedData->where(['Name' => $formField->Name])->first();

            // Check if file is uploaded
            if ($formField->ClassName == "SilverStripe\UserForms\Model\EditableFormField\EditableFileField") {
                $fieldsList->add([
                    'FieldTitle' => $formField->Title ?? "Field Generic",
                    // 'FieldValue' => $submission->getFileName(),  <--- Show filename - TBD
                    'FieldValue' =>  $submission->getFileName() ? 'File uploaded' : '',
                    'FieldClassName' => $formField->ClassName,
                ]);
                $_fields++;
                continue;
            }

            $fieldsList->add([
                'FieldTitle' => $formField->Title ?? '',
                'FieldValue' => $submission->getFormattedValue(),
                'FieldClassName' => $formField->ClassName,
            ]);
            $_fields++;
        }

        // Finalise data with remaning group values
        if ($fieldsList->count() != 0) {
            $groupsList->add([
                'FieldGroupTitle' => '',
                'FieldsList' => $fieldsList
            ]);
            $fieldsList = new ArrayList();
        }

        $stepsList->add([
            'StepTitle' => $stepTitle,
            'FieldGroup' => $groupsList,
        ]);

        return $stepsList;
    }
}
