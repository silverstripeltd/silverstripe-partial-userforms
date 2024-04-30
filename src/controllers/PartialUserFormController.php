<?php

namespace Firesphere\PartialUserforms\Controllers;

use Exception;
use Firesphere\PartialUserforms\Forms\PasswordForm;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use Firesphere\PartialUserforms\traits\PartialSubmissionValidationTrait;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\HTTPResponse_Exception;
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
    use PartialSubmissionValidationTrait;

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

        if ($page->EnableRecaptcha) {
            Requirements::javascript('https://www.google.com/recaptcha/api.js?render=explicit&onload=loadRecaptcha');
        }

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
                    'Content' => DBField::create_field('HTMLText', $content),
                    'Form' => ''
                ]);
            }
        }

        return $controller->customise([
            'Content' => DBField::create_field('HTMLText', $controller->Content),
            'Form' => $form
        ]);
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
        $uploadFields = $partial->PartialUploads()->filter([
            'UploadedFileID:not' => 0
        ]);

        if (!$uploadFields->exists()) {
            return;
        }

        foreach ($uploadFields as $uploadField) {
            $request = $this->getRequest();
            $file = $uploadField->UploadedFile();
            $fileAttributes = ['PartialID' => $partial->ID, 'FileID' => $file->ID];

            // Generate a unique download path that is specific to the current session, partial submission and field
            $linkSrc = sprintf(
                'partialfiledownload/%s/%s/%s',
                $request->param('Key'),
                $request->param('Token'),
                $uploadField->Name
            );

            $linkTag = 'View <a href="%s" target="_blank">%s</a> &nbsp;
                <a class="partial-file-remove" href="javascript:;" data-disabled="" data-file-remove=\'%s\'>Remove &cross;</a>';
            $fileLink = sprintf(
                $linkTag,
                $linkSrc,
                Convert::raw2att($file->Name),
                json_encode($fileAttributes)
            );
            $inputField = $fields->dataFieldByName($uploadField->Name);
            if ($inputField) {
                $inputField->setRightTitle(DBField::create_field('HTMLText', $fileLink))
                    ->removeExtraClass('requiredField')
                    ->setAttribute('data-rule-required', 'false')
                    ->setAttribute('aria-required', 'false');
            }
        }
    }
}
