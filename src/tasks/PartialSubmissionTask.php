<?php

namespace Firesphere\PartialUserforms\Tasks;

use Firesphere\PartialUserforms\Jobs\PartialSubmissionJob;
use SilverStripe\Control\Email\Email;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use SilverStripe\Security\Security;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class PartialSubmissionTask extends BuildTask
{
    private static string $segment = 'partialsubmissiontask';

    public function __construct()
    {
        $this->title = _t(__CLASS__ . '.Title', 'Export partial form submissions to email address');
        parent::__construct();
    }

    /**
     * Implement this method in the task subclass to
     * execute via the TaskRunner
     */
    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        $currentUser = Security::getCurrentUser();
        /** @var PartialSubmissionJob $job */
        $job = new PartialSubmissionJob();
        $job->setup();

        if ($currentUser && Email::is_valid_address($currentUser->Email)) {
            $job->addAddress($currentUser->Email);
        }

        $job->process();

        return Command::SUCCESS;
    }
}
