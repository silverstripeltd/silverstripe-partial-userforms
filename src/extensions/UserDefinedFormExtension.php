<?php

namespace Firesphere\PartialUserforms\Extensions;

use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataList;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\UserForms\Model\UserDefinedForm;
use SilverStripe\Forms\HTMLEditor\HtmlEditorField;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;

/**
 * Class UserDefinedFormExtension
 *
 * @package Firesphere\PartialUserforms\Extensions
 * @property UserDefinedForm|UserDefinedFormExtension $owner
 * @property boolean $ExportPartialSubmissions
 * @property boolean $PasswordProtected
 * @method DataList|PartialFormSubmission[] PartialSubmissions()
 */
class UserDefinedFormExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $db = [
        'EnablePartialSubmissions' => 'Boolean(false)',
        'ExportPartialSubmissions' => 'Boolean(true)',
        'PasswordProtected'        => 'Boolean(false)',
        'FormIntroduction'         => 'HTMLText',
        'FormOverview'             => 'HTMLText',
        'SaveOnlyLabel'            => 'Varchar(50)',
        'SaveAndLogoutLabel'       => 'Varchar(50)',
        'ShowSubmissionSummary'    => 'Boolean(false)',
        'SaveAndLogoutMessage'     => 'Text',
    ];

    /**
     * @var array
     */
    private static $has_many = [
        'PartialSubmissions' => PartialFormSubmission::class
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        /** @var GridField $submissionField */
        $submissionField = $fields->dataFieldByName('Submissions');
        $list = $submissionField->getList()->exclude(['ClassName' => PartialFormSubmission::class]);
        $submissionField->setList($list);

        $fields->removeByName('PartialSubmissions');
        /** @var GridFieldConfig_RecordEditor $gridfieldConfig */
        $gridfieldConfig = GridFieldConfig_RecordEditor::create();
        $gridfieldConfig->removeComponentsByType(GridFieldAddNewButton::class);

        // We need to manually add the tab
        $fields->addFieldToTab(
            'Root',
            Tab::create('PartialSubmissions', _t(__CLASS__ . '.PartialSubmission', 'Partial submissions'))
        );

        $fields->addFieldToTab(
            'Root.PartialSubmissions',
            GridField::create(
                'PartialSubmissions',
                _t(__CLASS__ . '.PartialSubmission', 'Partial submissions'),
                $this->owner->PartialSubmissions()->sort('Created', 'DESC'),
                $gridfieldConfig
            )
        );

        $enablePartialCheckbox = CheckboxField::create(
            'EnablePartialSubmissions',
            _t(__CLASS__ . '.enablePartialSubmissionsCheckboxLabel', 'Enable partial submissions')
        )->setDescription(_t(
            __CLASS__ . '.enablePartialSubmissionsDescription',
            'If checked, this will allow this form to be shareable and filled out by multiple people'
        ));

        $pwdCheckbox = CheckboxField::create(
            'PasswordProtected',
            _t(__CLASS__ . 'PasswordProtected', 'Password protect resuming partial submissions')
        )->setDescription(_t(
            __CLASS__ . '.PasswordProtectDescription',
            'When resuming a partial submission, require the user to enter a password'
        ));

        $partialCheckbox = CheckboxField::create(
            'ExportPartialSubmissions',
            _t(__CLASS__ . '.partialCheckboxLabel', 'Send partial submissions')
        )->setDescription(_t(
            __CLASS__ . '.partialCheckboxDescription',
            'The configuration and global export configuration can be set in the site Settings'
        ));

        $introTextDescription = _t(__CLASS__ . '.introTextDescription', 'Text to display at the introduction page, before the user has started the form.');
        $overviewTextDescription = _t(__CLASS__ . '.overviewTextDescription', 'Text to display on the overview page of the form, alongside form credentials.');
        $saveAndLogoutDescription = _t(
            __CLASS__ . '.overviewSaveAndLogout',
            'Text to display on the overview page of the form after Save and logout.'
        );

        $fields->addFieldToTab(
            'Root.FormOptions',
            Tab::create('Partial', _t(__CLASS__ . '.partialTab', 'Partial'))
        );
        $fields->addFieldsToTab('Root.FormOptions.Partial', [
            $enablePartialCheckbox,
            $pwdCheckbox,
            $partialCheckbox,
            HtmlEditorField::create('FormIntroduction', 'Form introduction text')
                ->setDescription($introTextDescription)
                ->setRows(3),
            HtmlEditorField::create('FormOverview', 'Form overview text')
                ->setDescription($overviewTextDescription)
                ->setRows(3),
            TextField::create('SaveOnlyLabel', 'Save only Label'),
            TextField::create('SaveAndLogoutLabel', 'Save and logout Label'),
            CheckboxField::create('ShowSubmissionSummary', 'Show form Submission Summary'),
            TextareaField::create('SaveAndLogoutMessage', 'Save and logout message')
                ->setDescription($saveAndLogoutDescription)
                ->setRows(3),
        ]);
    }
}
