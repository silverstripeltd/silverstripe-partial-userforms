<?php

namespace Firesphere\PartialUserforms\Controllers;

use Exception;
use Firesphere\PartialUserforms\Models\PartialFieldSubmission;
use Firesphere\PartialUserforms\Models\PartialFileFieldSubmission;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Upload;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Control\Session;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\ValidationException;
use SilverStripe\UserForms\Model\EditableFormField;

/**
 * Class PartialSubmissionController
 *
 * @package Firesphere\PartialUserforms\Controllers
 */
class PartialSubmissionController extends ContentController
{
    /**
     * Session key name
     */
    public const SESSION_KEY = 'PartialSubmissionID';

    /**
     * @var array
     */
    private static $url_handlers = [
        'save' => 'savePartialSubmission',
        'remove-file' => 'removeUploadedFile',
    ];

    /**
     * @var array
     */
    private static $allowed_actions = [
        'savePartialSubmission',
        'removeUploadedFile',
    ];

    /**
     * @param HTTPRequest $request
     * @return HTTPResponse
     * @throws ValidationException
     * @throws HTTPResponse_Exception
     */
    public function savePartialSubmission(HTTPRequest $request)
    {
        $postVars = $request->postVars();

        // We don't want SecurityID and/or the process Action stored as a thing
        unset($postVars['SecurityID'], $postVars['action_process']);

        /** @var PartialFormSubmission $partialSubmission */
        $partialSubmission = $this->checkFormSession($request);
        $submissionID = $request->getSession()->get(self::SESSION_KEY);

        $editableField = null;
        foreach ($postVars as $field => $value) {
            /** @var EditableFormField $editableField */
            $editableField = $this->createOrUpdateSubmission([
                'Name'            => $field,
                'Value'           => $value,
                'SubmittedFormID' => $submissionID
            ]);
        }

        if ($editableField instanceof EditableFormField && !$partialSubmission->UserDefinedFormID) {
            // Updates parent class to the correct DataObject
            $partialSubmission->update([
                'UserDefinedFormID'    => $editableField->Parent()->ID,
                'ParentID'             => $editableField->Parent()->ID,
                'ParentClass'          => $editableField->Parent()->ClassName,
                'UserDefinedFormClass' => $editableField->Parent()->ClassName
            ]);
            $partialSubmission->write();
        }

        $return = $partialSubmission->exists();

        return new HTTPResponse($return, ($return ? 201 : 400));
    }

    /**
     * @param HTTPRequest $request
     * @return HTTPResponse
     * @throws ValidationException
     * @throws HTTPResponse_Exception
     */
    public function removeUploadedFile(HTTPRequest $request)
    {
        $postVars = $request->postVars();
        /** @var PartialFormSubmission $partialSubmission */
        $partialSubmission = $this->checkFormSession($request);
        $submissionID = $request->getSession()->get(self::SESSION_KEY);

        $uploadedFile = File::create();
        $partialUploads = $partialSubmission->PartialUploads();
        $fileSubmission = $partialUploads->find('UploadedFileID', $postVars['fileID']);
        if ($fileSubmission) {
            $partialUploads->remove($fileSubmission);
            $uploadedFile = $fileSubmission->UploadedFile();
        }

        if ($uploadedFile->exists()) {
            $uploadedFile->deleteFile();
            $uploadedFile->doArchive();
        }

        return new HTTPResponse(1, 200);
    }

    /**
     * Reload session for partial submissions
     * @param Session $session
     * @param int $sessionID Partial form submission ID
     * @throws ValidationException
     */
    public static function reloadSession($session, $sessionID)
    {
        $partial = PartialFormSubmission::get()->byID($sessionID);
        if (!$partial) {
            return;
        }

        $session->set(self::SESSION_KEY, $partial->ID);

        $now = new \DateTime(DBDatetime::now()->getValue());
        $now->add(new \DateInterval('PT30M'));

        $phpSessionID = session_id();
        if (!$phpSessionID) {
            return;
        }

        $partial->LockedOutUntil = $now->format('Y-m-d H:i:s');
        $partial->PHPSessionID = $phpSessionID;
        $partial->write();
    }

    /**
     * Clear lock session (e.g. when the user navigates to form overview)
     */
    public static function clearLockSession()
    {
        $partialID = Controller::curr()->getRequest()->getSession()->get(self::SESSION_KEY);
        $partial = PartialFormSubmission::get()->byID($partialID);

        if (!$partial) {
            return;
        }

        $partial->LockedOutUntil = null;
        $partial->PHPSessionID = null;
        $partial->write();
    }

    public static function getUploadLinks($partialID)
    {
        $partialFiles = [];
        $session = Controller::curr()->getRequest()->getSession();
        $submissionID = $session->get(PartialSubmissionController::SESSION_KEY);

        if ($submissionID === intval($partialID)) {
            $partial = PartialFormSubmission::get()->byID($submissionID);
            $uploads = $partial->PartialUploads()->filter('UploadedFileID:not', 0);
            foreach ($uploads as $partialUpload) {
                $file = $partialUpload->UploadedFile();
                $partialFiles[$partialUpload->Name] = sprintf(
                    '%s - <a href="%s" target="_blank">%s</a>',
                    Convert::raw2att($file->Name),
                    Convert::raw2att($file->AbsoluteLink()),
                    $file->AbsoluteLink()
                );
            }
        }

        return $partialFiles;
    }

    public static function getPartialsFromSession()
    {
        $session = Controller::curr()->getRequest()->getSession();
        $submissionID = $session->get(PartialSubmissionController::SESSION_KEY);
        return $submissionID ? PartialFormSubmission::get()->byID($submissionID) : null;
    }

    /**
     * @param $formData
     * @return DataObject|EditableFormField
     * @throws ValidationException
     */
    protected function createOrUpdateSubmission($formData)
    {
        $filter = [
            'Name'            => $formData['Name'],
            'SubmittedFormID' => $formData['SubmittedFormID'],
        ];

        /** @var EditableFormField $editableField */
        $editableField = EditableFormField::get()->find('Name', $formData['Name']);
        if (is_null($editableField)) {
            $nameParts = explode('__', $formData['Name']);
            $editableField = EditableFormField::get()->find('Name', reset($nameParts));
        }
        if ($editableField instanceof EditableFormField\EditableFileField) {
            $this->savePartialFile($formData, $filter, $editableField);
        } elseif ($editableField instanceof EditableFormField) {
            $this->savePartialField($formData, $filter, $editableField);
        }

        // Return the ParentID to link the PartialSubmission to it's proper thingy
        return $editableField;
    }

    /**
     * @param $formData
     * @param array $filter
     * @param EditableFormField $editableField
     * @throws ValidationException
     */
    protected function savePartialField($formData, array $filter, EditableFormField $editableField)
    {
        $partialSubmission = PartialFieldSubmission::get()->filter($filter)->first();
        if (is_array($formData['Value'])) {
            $formData['Value'] = implode(', ', $formData['Value']);
        }
        if ($editableField) {
            $formData['Title'] = $editableField->Title;
        }
        if (!$partialSubmission) {
            $partialSubmission = PartialFieldSubmission::create($formData);
        } else {
            $partialSubmission->update($formData);
        }
        $partialSubmission->write();
    }

    /**
     * @param $formData
     * @param array $filter
     * @param EditableFormField\EditableFileField $editableField
     * @throws ValidationException
     * @throws Exception
     */
    protected function savePartialFile($formData, array $filter, EditableFormField\EditableFileField $editableField)
    {
        $partialFileSubmission = PartialFileFieldSubmission::get()->filter($filter)->first();
        if (!$partialFileSubmission && $editableField) {
            $partialData = [
                'Name'            => $formData['Name'],
                'SubmittedFormID' => $formData['SubmittedFormID'],
                'Title'           => $editableField->Title,
            ];
            $partialFileSubmission = PartialFileFieldSubmission::create($partialData);
            $partialFileSubmission->write();
        }

        if (is_array($formData['Value'])) {
            $file = $this->uploadFile($formData, $editableField, $partialFileSubmission);
            $partialFileSubmission->UploadedFileID = $file->ID ?? 0;
            $partialFileSubmission->write();
        }
    }

    /**
     * @param array $formData
     * @param EditableFormField\EditableFileField $field
     * @param PartialFileFieldSubmission $partialFileSubmission
     * @return bool|File
     * @throws Exception
     */
    protected function uploadFile($formData, $field, $partialFileSubmission)
    {
        if (!empty($formData['Value']['name'])) {
            $foldername = $field->getFormField()->getFolderName();

            if (!$partialFileSubmission->UploadedFileID) {
                $file = File::create([
                    'ShowInSearch' => 0
                ]);
            } else {
                // Allow overwrite existing uploads
                $file = $partialFileSubmission->UploadedFile();
            }

            // Upload the file from post data
            $upload = Upload::create();
            if ($upload->loadIntoFile($formData['Value'], $file, $foldername)) {
                return $file;
            }
        }

        return false;
    }

    /**
     * @param HTTPRequest $request
     * @return PartialFormSubmission
     * @throws HTTPResponse_Exception
     * @throws ValidationException
     */
    protected function checkFormSession(HTTPRequest $request)
    {
        $postVars = $request->postVars();

        if (!$request->isPOST()) {
            return $this->httpError(404);
        }

        // Check for partial params so the submission doesn't rely on session for partial page
        if (empty($postVars['PartialID'])) {
            return $this->httpError(404);
        }

        $submissionID = $request->getSession()->get(self::SESSION_KEY);
        if (!$submissionID || (int) $postVars['PartialID'] !== (int) $submissionID) {
            return $this->httpError(404);
        }

        // Check if form is locked
        if (PartialUserFormController::isLockedOut()) {
            return $this->httpError(409,
                'Your session has timed out and this form is currently being used by someone else. Please try again later.'
            );
        } else {
            // Claim the form session
            PartialSubmissionController::reloadSession($request->getSession(), $submissionID);
        }

        /** @var PartialFormSubmission $partialSubmission */
        $partialSubmission = PartialFormSubmission::get()->byID($submissionID);

        if (!$partialSubmission) {
            // New sessions are created when a user clicks "Start" from the start form
            // {@link UserDefinedFormControllerExtension::StartForm()}
            return $this->httpError(404);
        }

        return $partialSubmission;
    }
}
