<?php

namespace Firesphere\PartialUserforms\Controllers;

use Exception;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use Firesphere\PartialUserforms\Forms\PasswordForm;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Control\Middleware\HTTPCacheControlMiddleware;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\HiddenField;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\UserForms\Control\UserDefinedFormController;
use SilverStripe\UserForms\Form\UserForm;
use SilverStripe\View\Requirements;

/**
 * Class PartialUserFormController
 *
 * @package Firesphere\PartialUserforms\Controllers
 */
class PartialUserFormController extends UserDefinedFormController
{
    /**
     * @var array
     */
    private static $url_handlers = [
        '$Key/$Token' => 'partial',
    ];

    /**
     * @var array
     */
    private static $allowed_actions = [
        'partial',
    ];

    /**
     * Partial form
     *
     * @param HTTPRequest $request
     * @return HTTPResponse|DBHTMLText|void
     * @throws HTTPResponse_Exception
     * @throws Exception
     */
    public function partial(HTTPRequest $request)
    {
        /** @var PartialFormSubmission $partial */
        $partial = $this->validateToken($request);
        $page = $partial->UserDefinedForm();

        // Check if form is locked
        if (static::isLockedOut()) {
            return $this->redirect($page->link('overview'));
        } else {
            // Claim the form session
            PartialSubmissionController::reloadSession($request->getSession(), $partial->ID);
        }

        /** @var self $controller */
        $controller = parent::create($page);
        $controller->doInit();

        Director::set_current_page($controller->data());

        // Verify user
        if ($controller->PasswordProtected &&
            $request->getSession()->get(PasswordForm::PASSWORD_SESSION_KEY) !== $partial->ID
        ) {
            return $this->redirect($page->link('verify'));
        }

        // Add required javascripts
        Requirements::javascript('firesphere/partialuserforms:client/dist/main.js');
        Requirements::javascript('firesphere/partialuserforms:client/dist/onready.js');

        /** @var UserForm $form */
        $form = $controller->Form();
        $form->loadDataFrom($partial->getFields());
        $this->populateData($form, $partial);

        // Copied from {@link UserDefinedFormController}
        if ($controller->Content && $form && !$controller->config()->disable_form_content_shortcode) {
            $hasLocation = stristr($controller->Content, '$UserDefinedForm');
            if ($hasLocation) {
                /** @see Requirements_Backend::escapeReplacement */
                $formEscapedForRegex = addcslashes($form->forTemplate(), '\\$');
                $content = preg_replace(
                    '/(<p[^>]*>)?\\$UserDefinedForm(<\\/p>)?/i',
                    $formEscapedForRegex,
                    $controller->Content
                );

                return $controller->customise([
                    'Content'     => DBField::create_field('HTMLText', $content),
                    'Form'        => ''
                ]);
            }
        }

        return $controller->customise([
            'Content'     => DBField::create_field('HTMLText', $controller->Content),
            'Form'        => $form
        ]);
    }


    /**
     * A little abstraction to be more readable
     * @param HTTPRequest $request
     * @return PartialFormSubmission|void
     * @throws HTTPResponse_Exception
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function validateToken($request)
    {
        // Ensure this URL doesn't get picked up by HTTP caches
        HTTPCacheControlMiddleware::singleton()->disableCache();

        $key = $request->param('Key');
        $token = $request->param('Token');
        if (!$key || !$token) {
            return $this->httpError(404);
        }

        /** @var PartialFormSubmission $partial */
        $partial = PartialFormSubmission::validateKeyToken($key, $token);
        if ($partial === false) {
            return $this->httpError(404);
        }

        // Reload session by checking if the last session has expired
        // or another submission has started
        $sessionID = $request->getSession()->get(PartialSubmissionController::SESSION_KEY);
        if (!$sessionID || $sessionID !== $partial->ID) {
            PartialSubmissionController::reloadSession($request->getSession(), $partial->ID);
        }

        return $partial;
    }

    /**
     * Checks whether this form is currently being used by someone else
     * @return bool
     */
    public static function isLockedOut()
    {
        $session = Controller::curr()->getRequest()->getSession();
        $submissionID = $session->get(PartialSubmissionController::SESSION_KEY);
        $partial = PartialFormSubmission::get()->byID($submissionID);
        $phpSessionID = session_id();

        // If invalid sessions or if the last session was from the same user or that the recent session has expired
        if (
            !$submissionID
            || !$partial
            || !$partial->PHPSessionID
            || ($partial->LockedOutUntil && $partial->dbObject('LockedOutUntil')->InPast())
            || $phpSessionID === $partial->PHPSessionID
        ) {
            return false;
        }

        // Lockout when there's an ongoing session
        return $partial->LockedOutUntil && $partial->dbObject('LockedOutUntil')->InFuture();
    }

    /**
     * Add partial submission and set the uploaded filenames as right title of the file fields
     *
     * @param Form $form
     * @param PartialFormSubmission $partial
     */
    protected function populateData($form, $partial)
    {
        $fields = $form->Fields();
        // Add partial submission ID
        $fields->push(
            HiddenField::create(
                'PartialID',
                null,
                $partial->ID
            )
        );

        // Populate files
        $uploads = $partial->PartialUploads()->filter([
            'UploadedFileID:not'=> 0
        ]);

        if (!$uploads->exists()) {
            return;
        }

        foreach ($uploads as $upload) {
            $file = $upload->UploadedFile();
            $fileAttributes = ['PartialID' => $partial->ID, 'FileID' => $file->ID];
            $linkTag = 'View <a href="%s" target="_blank">%s</a> &nbsp;
                <a class="partial-file-remove" href="javascript:;" data-disabled="" data-file-remove=\'%s\'>Remove &cross;</a>';
            $fileLink = sprintf(
                $linkTag,
                Convert::raw2att($file->AbsoluteLink()),
                Convert::raw2att($file->Name),
                Convert::raw2json($fileAttributes)
            );
            $inputField = $fields->dataFieldByName($upload->Name);
            if ($inputField) {
                $inputField->setRightTitle(DBField::create_field('HTMLText', $fileLink))
                    ->removeExtraClass('requiredField')
                    ->setAttribute('data-rule-required', 'false')
                    ->setAttribute('aria-required', 'false');
            }
        }
    }
}
