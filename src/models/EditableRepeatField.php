<?php

namespace Firesphere\PartialUserforms\Models;

use SilverStripe\Assets\File;
use SilverStripe\Core\Convert;
use SilverStripe\Assets\Upload;
use SilverStripe\Forms\TextField;
use SilverStripe\View\Requirements;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig;
use Firesphere\PartialUserforms\Forms\RepeatField;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\UserForms\Form\GridFieldAddClassesButton;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use Firesphere\PartialUserforms\Models\SubmittedRepeatField;
use SilverStripe\UserForms\Model\EditableFormField\EditableFormStep;
use SilverStripe\UserForms\Model\EditableFormField\EditableFileField;
use SilverStripe\UserForms\Model\EditableFormField\EditableTextField;
use SilverStripe\UserForms\Model\EditableFormField\EditableFieldGroup;
use Firesphere\PartialUserforms\Controllers\PartialSubmissionController;
use SilverStripe\UserForms\Model\EditableFormField\EditableFieldGroupEnd;

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
    }

    public function getSubmittedFormField()
    {
        return SubmittedRepeatField::create();
    }

    public function getFormField()
    {
        // Add required javascripts
        Requirements::javascript('firesphere/partialuserforms:client/dist/repeatfield.js');
        Requirements::css('firesphere/partialuserforms:client/dist/repeatfield.css');

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
        $field->setAttribute('data-remove-css', $this->ExtraClass);
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

        $editableColumns = new GridFieldEditableColumns();
        $fieldClasses = singleton(EditableFormField::class)->getEditableFieldClasses();
        $editableColumns->setDisplayFields([
            'ClassName' => function ($record, $column, $grid) use ($fieldClasses) {
                if ($record instanceof EditableFormField) {
                    $field = $record->getInlineClassnameField($column, $fieldClasses);
                    if ($record instanceof EditableFileField) {
                        $field->setAttribute('data-folderconfirmed', $record->FolderConfirmed ? 1 : 0);
                    }
                    return $field;
                }
            },
            'Title' => function ($record, $column, $grid) {
                if ($record instanceof EditableFormField) {
                    return $record->getInlineTitleField($column);
                }
            }
        ]);

        $config = GridFieldConfig::create()
            ->addComponents(
                $editableColumns,
                new GridFieldButtonRow(),
                $addButton = new GridFieldAddClassesButton(EditableTextField::class),
                $editButton = new GridFieldEditButton(),
                new GridFieldDeleteAction(),
                new GridFieldToolbarHeader(),
                new GridFieldDetailForm(),
                new GridFieldPaginator(999)
            );

        $addButton->setButtonName(_t(__CLASS__ . '.ADD_FIELD', 'Add Field'))->setButtonClass('btn-primary');
        $editButton->removeExtraClass('grid-field__icon-action--hidden-on-hover');

        $fieldEditor = GridField::create(
            'RepeatFields',
            'Repeat Fields',
            $this->Repeats(),
            $config
        )->addExtraClass('uf-field-editor');

        $fields->addFieldToTab(
            'Root.ChildFields',
            $fieldEditor
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
                    $value = Convert::raw2xml($field->getValueFromData($data));
                } elseif (isset($data[$fieldName])) {
                    $value = Convert::raw2xml($data[$fieldName]);
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
