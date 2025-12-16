<?php

namespace Firesphere\PartialUserforms\Extensions;

use Firesphere\PartialUserforms\Controllers\PartialSubmissionController;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Core\Extension;
use SilverStripe\Control\Controller;

class EditableFileFieldExtension extends Extension
{
    public function afterUpdateFormField($field)
    {
        if (!Controller::curr()) {
            return $field;
        }

        $controller = Controller::curr();
        $request = $controller->getRequest();
        $partialPath = 'partialuserform/';
        $currentPath = rtrim($request->getURL(), '/') . '/';
        $link = $controller->Link();

        if (!str_starts_with($currentPath, $partialPath)) {
            return $field;
        }

        $partialID = $request->getSession()->get(PartialSubmissionController::SESSION_KEY);
        $submission = PartialFormSubmission::get()->byID($partialID);
        $folderName = $field->getFolderName();

        if ($submission->Token) {
            $folderName .= $submission->Token;
        }

        $field->setFolderName(
            preg_replace("/^assets\//", "", (string) $folderName)
        );

        return $field;
    }
}
