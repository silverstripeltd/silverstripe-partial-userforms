<?php

namespace Firesphere\PartialUserforms\Extensions;

use Firesphere\PartialUserforms\Controllers\PartialSubmissionController;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataExtension;

class SubmittedFileFieldExtension extends DataExtension
{
    public function onPopulationFromField($field)
    {
        $partial = PartialSubmissionController::getPartialsFromSession();

        if (!$partial) {
            return;
        }

        $partialUpload = $partial->PartialUploads()
            ->filter([
                'UploadedFileID:not' => 0,
                'Name' => $field->Name,
            ])
            ->first();

        if (!$partialUpload) {
            return;
        }

        $file = $partialUpload->UploadedFile();
        $this->owner->UploadedFileID = $file->ID;
    }
}