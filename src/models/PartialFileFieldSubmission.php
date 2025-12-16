<?php

namespace Firesphere\PartialUserforms\Models;

use Override;
use SilverStripe\Security\Member;
use SilverStripe\UserForms\Model\Submission\SubmittedFileField;

/**
 * Class \Firesphere\PartialUserforms\Models\PartialFileFieldSubmission
 *
 * @property int $SubmittedFormID
 * @method PartialFormSubmission SubmittedForm()
 */
class PartialFileFieldSubmission extends SubmittedFileField
{
    private static $table_name = 'PartialFileFieldSubmission';

    private static $has_one = [
        'SubmittedForm' => PartialFormSubmission::class,
    ];

    /**
     * @param Member $member
     * @param array $context
     * @return boolean
     */
    #[Override]
    public function canCreate($member = null, $context = [])
    {
        return false;
    }

    /**
     * @param Member $member
     *
     * @return bool
     */
    #[Override]
    public function canView($member = null)
    {
        if ($this->SubmittedFormID) {
            return $this->SubmittedForm()->canView($member);
        }

        return parent::canView($member);
    }

    /**
     * @param Member $member
     *
     * @return bool
     */
    #[Override]
    public function canEdit($member = null)
    {
        if ($this->SubmittedFormID) {
            return $this->SubmittedForm()->canEdit($member);
        }

        return parent::canEdit($member);
    }

    /**
     * @param Member $member
     *
     * @return bool
     */
    #[Override]
    public function canDelete($member = null)
    {
        if ($this->SubmittedFormID) {
            return $this->SubmittedForm()->canDelete($member);
        }

        return parent::canDelete($member);
    }
}
