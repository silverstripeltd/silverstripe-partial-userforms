<?php

namespace Firesphere\PartialUserforms\Models;

use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\UserForms\Model\Submission\SubmittedFormField;

class SubmittedRepeatField extends SubmittedFormField
{
    /**
     * Return the value of this field for inclusion into things such as
     * reports.
     *
     * @return string
     */
    public function getFormattedValue()
    {
        $submissions = json_decode($this->dbObject('Value')->RAW());
        $template = '<b>%s</b>: %s<br />';
        $html = '';
        foreach ($submissions as $items) {
            $html .= '<p>';
            foreach ($items as $key => $value) {
                $html .= sprintf($template, $key, $value);
            }
            $html .= '</p>';
        }

        return DBField::create_field(DBHTMLText::class, $html);;
    }

    /**
     * Return the value for this field in the CSV export.
     *
     * @return string
     */
    public function getExportValue()
    {
        $html = $this->getFormattedValue();
        return $html->Plain();
        $submissions = json_decode($this->dbObject('Value')->RAW());
        $csv = '||';
        foreach ($submissions as $items) {
            foreach ($items as $key => $value) {
                $csv .= sprintf('%s:%s||', $key, $value);
            }
        }
        return $csv;
    }
}