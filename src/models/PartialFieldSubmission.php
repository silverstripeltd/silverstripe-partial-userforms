<?php

namespace Firesphere\PartialUserforms\Models;

use Override;
use SilverStripe\Security\Member;
use SilverStripe\UserForms\Model\Submission\SubmittedFormField;

/**
 * Class PartialFieldSubmission
 *
 * @package Firesphere\PartialUserforms\Models
 * @property int $SubmittedFormID
 * @method PartialFormSubmission SubmittedForm()
 */
class PartialFieldSubmission extends SubmittedFormField
{
    /**
     * @var string
     */
    private static $table_name = 'PartialFieldSubmission';

    /**
     * @var array
     */
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
     * @return boolean|string
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
     * @return boolean|string
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
     * @return boolean|string
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
