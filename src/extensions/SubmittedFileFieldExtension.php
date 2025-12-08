<?php

namespace Firesphere\PartialUserforms\Extensions;

use Firesphere\PartialUserforms\Controllers\PartialSubmissionController;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Extension;

class SubmittedFileFieldExtension extends Extension
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
