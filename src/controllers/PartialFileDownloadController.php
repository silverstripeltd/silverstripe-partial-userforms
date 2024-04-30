<?php

namespace Firesphere\PartialUserforms\Controllers;

use Exception;
use Firesphere\PartialUserforms\Forms\PasswordForm;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Control\Middleware\HTTPCacheControlMiddleware;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Environment;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\HiddenField;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\UserForms\Control\UserDefinedFormController;
use SilverStripe\UserForms\Form\UserForm;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\Requirements;

/**
 * Class PartialUserFormController
 *
 * @package Firesphere\PartialUserforms\Controllers
 */
class PartialFileDownloadController extends UserDefinedFormController
{
    /**
     * @var array
     */
    private static $url_handlers = [
        '$Key/$Token/$ID' => 'partialfiledownload',
    ];

    /**
     * @var array
     */
    private static $allowed_actions = [
        'partialfiledownload',
    ];


    /**
     * Form users should be able to download files already attached to the form even though the files are in
     * a draft state. validating the token ensures that only attached files can be downloaded.
     */
    public function partialfiledownload(HTTPRequest $request)
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

        $fieldName = $request->param('FieldName');

        // Files are stored in draft state for security reasons.
        // At this point the partial form submission session has been validated, we can safely retrieve the file now
        // by temporarily forcing the draft state
        $upload =  Versioned::withVersionedMode(function () use ($fieldName, $partial) {
            Versioned::set_stage(Versioned::DRAFT);

            return $partial->PartialUploads()->filter(['Name' => $fieldName])->first();
        });


        if (!$upload) {
            return $this->httpError(404);
        }

        $file = $upload->UploadedFile();

        $filesize = $file->getAbsoluteSize();
        $mime = $file->getMimeType();
        $stream = $file->getStream();

        if ($stream != null || gettype($stream) == "resource") {
            header('Content-Type: ' . $mime);
            header('Content-Length: ' . $filesize, false);
            if (!empty($mime) && $mime != "text/html") {
                header('Content-Disposition: inline; filename="' . $file->Name . '"');
            }
            header('Content-transfer-encoding: 8bit');
            header('Expires: 0');
            header('Pragma: cache');
            header('Cache-Control: private');


            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            flush();

            //Actually output the file stream
            rewind($stream);
            fpassthru($stream);
        }
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
}
