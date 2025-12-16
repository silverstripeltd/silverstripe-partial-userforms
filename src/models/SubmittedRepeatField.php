<?php

namespace Firesphere\PartialUserforms\Models;

use Override;
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
    #[Override]
    public function getFormattedValue()
    {
        $submissions = json_decode((string) $this->dbObject('Value')->RAW());
        $html = '';

        foreach ($submissions as $items) {
            $html .= '<p>';
            foreach ($items as $key => $value) {
                $html .= sprintf('<b>%s</b>: %s<br />', $key, $value);
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
    #[Override]
    public function getExportValue()
    {
        return $this->getFormattedValue()->Plain();
    }
}