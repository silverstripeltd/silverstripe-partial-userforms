<?php

namespace Firesphere\PartialUserforms\Forms;

use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;

class RepeatField extends CompositeField
{
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
}