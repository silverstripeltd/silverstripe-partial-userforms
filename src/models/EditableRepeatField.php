<?php

namespace Firesphere\PartialUserforms\Models;

use Firesphere\PartialUserforms\Controllers\PartialSubmissionController;
use Firesphere\PartialUserforms\Forms\RepeatField;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Upload;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\EditableFormField\EditableFileField;
use SilverStripe\View\Requirements;

class EditableRepeatField extends EditableFormField
{
    private static $singular_name = 'Repeat Field';
    private static $plural_name = 'Repeat Fields';
    private static $table_name = 'EditableRepeatField';

    private static $db = [
        'Maximum' => 'Int',
        'RepeatLabel' => 'Varchar(100)',
        'RemoveLabel' => 'Varchar(100)',
    ];

    private static $many_many = [
        'Repeats' => EditableFormField::class
    ];

    private static $owns = [
        'Repeats'
    ];

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if (!$this->Maximum) {
            $this->Maximum = 1;
        }

        if (!$this->RepeatLabel) {
            $this->RepeatLabel = 'Repeat';
        }

        if (!$this->RemoveLabel) {
            $this->RemoveLabel = 'Remove';
        }
    }

    public function getSubmittedFormField()
    {
        return SubmittedRepeatField::create();
    }

    public function getFormField()
    {
        // Add required javascripts
        Requirements::javascript('firesphere/partialuserforms:client/dist/repeatfield.js');

        $field = RepeatField::create($this->Name, $this->Title ?: null)
            ->setRightTitle($this->RightTitle)
            ->setFieldHolderTemplate(self::class . '_holder')
            ->setTemplate(self::class);

        $duplicates = [];
        $fieldData = [];

        foreach ($this->Repeats() as $index => $editableField) {
            $childField = $editableField->getFormField();
            $fieldData[$childField->getName()] = $this->Maximum;
            $field->push($childField);

            for ($index = 1; $index <= $this->Maximum; $index++) {
                $clonedChild = clone $childField;
                $childName = $childField->getName() . '__' . $index;
                $clonedChild->setName($childName);
                $duplicates[$index][] = $clonedChild;
            }
        }

        foreach ($duplicates as $duplicatedFields) {
            foreach ($duplicatedFields as $childField) {
                $field->push($childField);
            }
        }

        $field->setAttribute('data-maximum', $this->Maximum);
        $field->setAttribute('data-fields', $fieldData);
        $field->setAttribute('data-remove-label', $this->RemoveLabel);
        $field->setRepeatLabel($this->RepeatLabel);
        $this->doUpdateFormField($field);
        return $field;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldsToTab(
            'Root.Main',
            [
                TextField::create('Maximum', 'Maximum repeats', $this->Maximum),
                TextField::create('RepeatLabel', 'Repeat button label', $this->RepeatLabel),
                TextField::create('RemoveLabel', 'Remove button label', $this->RemoveLabel),
            ]
        );

        $fields->addFieldToTab(
            'Root.ChildFields',
            GridField::create(
                'RepeatFields',
                'Repeat Fields',
                $this->Repeats(),
                GridFieldConfig_RecordEditor::create()
            )
        );

        return $fields;
    }

    public function getValueFromData($data)
    {
        $partialFiles = [];
        if (!empty($data['PartialID'])) {
            $partialFiles = PartialSubmissionController::getUploadLinks($data['PartialID']);
        }

        $submissions = [];
        $repeatValues = $data[$this->Name] ? array_filter(explode(',', $data[$this->Name])) : [];
        array_unshift($repeatValues, 0); // The original repeated field children

        for ($index = 0; $index <= $this->Maximum; $index++) {
            if (!in_array($index, $repeatValues)) {
                continue;
            }
            foreach ($this->Repeats() as $field) {
                $fieldName = $index ? $field->Name . '__' . $index : $field->Name;
                $field->Name = $fieldName;
                $title = $field->getField('Title') ?: $fieldName;

                if ($field->hasMethod('getValueFromData')) {
                    $value = $field->getValueFromData($data);
                } elseif (isset($data[$fieldName])) {
                    $value = $data[$fieldName];
                } else {
                    $value = null;
                }

                if (!$value && isset($partialFiles[$fieldName])) {
                    $value = $partialFiles[$fieldName];
                }

                if (!empty($data[$fieldName])) {
                    if (in_array(EditableFileField::class, $field->getClassAncestry())) {
                        if (!empty($_FILES[$fieldName]['name'])) {
                            $foldername = $field->getFormField()->getFolderName();

                            // create the file from post data
                            $upload = Upload::create();
                            $file = File::create();
                            $file->ShowInSearch = 0;
                            $upload->loadIntoFile($_FILES[$fieldName], $file, $foldername);
                            $value = sprintf(
                                '%s - <a href="%s" target="_blank">%s</a>',
                                Convert::raw2att($file->Name),
                                Convert::raw2att($file->AbsoluteLink()),
                                $file->AbsoluteLink()
                            );
                        }
                    }
                }

                $submissions[$index][$title] = $value;
            }
        }

        return json_encode($submissions);
    }
}