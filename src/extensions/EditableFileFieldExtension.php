<?php

namespace Firesphere\PartialUserforms\Extensions;

use Firesphere\PartialUserforms\Controllers\PartialSubmissionController;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Admin\AdminRootController;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Control\Controller;

class EditableFileFieldExtension extends DataExtension
{
    public function afterUpdateFormField($field)
    {
        if (!Controller::has_curr()) {
            return $field;
        }

        $controller = Controller::curr();
        $request = $controller->getRequest();
        $partialPath = 'partialuserform/';
        $currentPath = rtrim($request->getURL(), '/') . '/';
        $link = $controller->Link();

        if (substr($currentPath, 0, strlen($partialPath)) !== $partialPath) {
            return $field;
        }

        $partialID = $request->getSession()->get(PartialSubmissionController::SESSION_KEY);
        $submission = PartialFormSubmission::get()->byID($partialID);
        $folderName = $field->getFolderName();

        if ($submission->Token) {
            $folderName .= $submission->Token;
        }

        $field->setFolderName(
            preg_replace("/^assets\//", "", $folderName)
        );

        return $field;
    }
}
