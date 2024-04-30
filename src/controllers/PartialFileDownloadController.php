<?php

namespace Firesphere\PartialUserforms\Controllers;

use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use Firesphere\PartialUserforms\traits\PartialSubmissionValidationTrait;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\UserForms\Control\UserDefinedFormController;
use SilverStripe\Versioned\Versioned;

/**
 * Class PartialFileDownloadController
 *
 * @package Firesphere\PartialUserforms\Controllers
 */
class PartialFileDownloadController extends UserDefinedFormController
{
    use PartialSubmissionValidationTrait;

    private static array $url_handlers = [
        '$Key/$Token/$FieldName' => 'partialfiledownload',
    ];

    private static array $allowed_actions = [
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
        }

        // Claim the form session
        PartialSubmissionController::reloadSession($request->getSession(), $partial->ID);

        $fieldName = $request->param('FieldName');

        // Files are stored in draft state for security reasons.
        // At this point the partial form submission session has been validated, we can safely retrieve the file now
        // by temporarily forcing the draft state
        $uploadField = Versioned::withVersionedMode(function () use ($fieldName, $partial) {
            Versioned::set_stage(Versioned::DRAFT);

            return $partial->PartialUploads()->filter(['Name' => $fieldName])->first();
        });


        if (!$uploadField) {
            return $this->httpError(404);
        }

        $file = $uploadField->UploadedFile();

        $filesize = $file->getAbsoluteSize();
        $mime = $file->getMimeType();
        $stream = $file->getStream();

        if ($stream !== null || gettype($stream) === "resource") {
            header('Content-Type: ' . $mime);
            header('Content-Length: ' . $filesize, false);
            if (!empty($mime) && $mime !== "text/html") {
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
}
