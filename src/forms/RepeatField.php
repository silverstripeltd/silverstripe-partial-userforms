<?php

namespace Firesphere\PartialUserforms\Forms;

use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;

class RepeatField extends CompositeField
{
    protected $repeatLabel = 'Repeat';

    public function __construct($name, $title = null, $value = 0)
    {
        parent::__construct(new FieldList());
        $this->defautFields = new FieldList();

        $this->setName($name);
        $this->setValue($value);

        if ($title === null) {
            $this->title = self::name_to_label($name);
        } else {
            $this->title = $title;
        }
    }

    public function hasData()
    {
        return true;
    }

    public function setRepeatLabel($label)
    {
        $this->repeatLabel = $label;
    }

    public function getRepeatLabel()
    {
        return $this->repeatLabel;
    }
}