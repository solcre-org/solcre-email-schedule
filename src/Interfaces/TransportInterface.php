<?php

namespace Solcre\EmailSchedule\Interfaces;

use Solcre\EmailSchedule\Entity\ScheduleEmail;

interface TransportInterface
{
    public function send(ScheduleEmail $scheduleEmail): bool;
}

