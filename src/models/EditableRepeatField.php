<?php

namespace Firesphere\PartialUserforms\Models;

use Firesphere\PartialUserforms\Forms\RepeatField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\TextField;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\View\Requirements;

class EditableRepeatField extends EditableFormField
{
    private static $singular_name = 'Repeat Field';
    private static $plural_name = 'Repeat Fields';
    private static $table_name = 'EditableRepeatField';

    private static $db = [
        'Maximum' => 'Int',
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

    public function getFormField()
    {
        // Add required javascripts
        Requirements::javascript('firesphere/partialuserforms:client/dist/repeatfield.js');

        $field = RepeatField::create($this->Name, $this->Title ?: false)
            ->setRightTitle($this->RightTitle)
            ->setFieldHolderTemplate(self::class . '_holder')
            ->setTemplate(self::class);

        $duplicates = [];
        $fieldData = [];

        foreach ($this->Repeats() as $index => $editableField) {
            $childField = $editableField->getFormField();
            $fieldData[$childField->getName()] = $this->Maximum;
            $field->push($childField);

            for ($i = 1; $i <= $this->Maximum; $i++) {
                $clonedChild = clone $childField;
                $childName = $childField->getName() . '__' . $i;
                $clonedChild->setName($childName);
                $duplicates[$i][] = $clonedChild;
            }
        }

        foreach ($duplicates as $duplicatedFields) {
            foreach ($duplicatedFields as $childField) {
                $field->push($childField);
            }
        }

        $field->setAttribute('data', $fieldData);
        $this->doUpdateFormField($field);
        return $field;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab(
            'Root.Main',
            TextField::create('Maximum', 'Maximum repeats', $this->Maximum ?: 1)
        );

        $fields->addFieldToTab(
            'Root.ChildFields',
            GridField::create(
                'RepeatFields',
                'Repeat Fields',
                $this->Repeats(),
                GridFieldConfig_RelationEditor::create()
            )
        );

        return $fields;
    }

    public function getValueFromData($data)
    {
        foreach ($this->Repeats() as $field) {
            $field->toMap();
        }

        return 'hello';
    }
}