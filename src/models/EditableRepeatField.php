<?php

namespace Firesphere\PartialUserforms\Models;

use Firesphere\PartialUserforms\Forms\RepeatField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\UserForms\Model\EditableFormField;

class EditableRepeatField extends EditableFormField
{
    private static $singular_name = 'Repeat Field';
    private static $plural_name = 'Repeat Fields';
    private static $table_name = 'EditableRepeatField';

    private static $has_many = array(
        'RepeatFields' => EditableFormField::class
    );

    public function getFormField()
    {
        $field = RepeatField::create($this->Name, $this->Title, $this->Default)
            ->setRightTitle($this->RightTitle)
            ->setFieldHolderTemplate(self::class . '_holder')
            ->setTemplate(self::class);

        $fieldNames = [];
        foreach ($this->RepeatFields() as $editableField) {
            $childField = $editableField->getFormField();
            $field->push($childField);
            $fieldNames[$childField->getName()] = [];
        }
        $field->setAttribute('data', $fieldNames);
        $field->setValue(json_encode($fieldNames));

        $this->doUpdateFormField($field);
        return $field;
    }

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function ($fields) {
            $config = GridFieldConfig_RelationEditor::create();
            $config->removeComponentsByType(GridFieldAddNewButton::class);
            $fields->addFieldToTab('Root.ChildFields',
                GridField::create(
                    'RepeatFields',
                    'Repeat Fields',
                    $this->RepeatFields(),
                    $config
                )
            );
        });
        return parent::getCMSFields();
    }

    public function getValueFromData($data)
    {
        foreach ($this->RepeatFields() as $field) {
            $field->toMap();
        }

        return 'hello';
    }
}