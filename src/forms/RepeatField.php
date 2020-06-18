<?php

namespace Firesphere\PartialUserforms\Forms;

use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FieldList;

class RepeatField extends CompositeField
{
    /**
     * @var FieldList
     */
    protected $children;

    public function __construct($name, $title = null, $value = '')
    {
        parent::__construct(new FieldList());

        $this->setName($name);

        if ($title === null) {
            $this->title = self::name_to_label($name);
        } else {
            $this->title = $title;
        }

        if ($value !== null) {
            $this->setValue($value);
        }
    }
}