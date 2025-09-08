<?php

namespace Firesphere\PartialUserforms\traits;

use Firesphere\PartialUserforms\Controllers\PartialSubmissionController;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Control\Middleware\HTTPCacheControlMiddleware;
use SilverStripe\ORM\ValidationException;

trait PartialSubmissionValidationTrait
{

    /**
     * A little abstraction to be more readable
     * @param HTTPRequest $request
     * @return PartialFormSubmission|void
     * @throws HTTPResponse_Exception
     * @throws ValidationException
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
