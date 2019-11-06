<?php

namespace Solcre\EmailSchedule\Repository;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Exception;

class ScheduleEmailRepository extends EntityRepository
{
    public function fetchAvailableScheduledEmails($retried): ?array
    {
        try {
            $connection = $this->_em->getConnection();
            $query = 'SELECT * FROM schedule_emails as se WHERE se.send_at IS NULL AND se.retried < :retried AND se.sending_date IS NULL';
            $stmt = $connection->executeQuery(
                $query,
                [
                    'retried' => $retried
                ]
            );
            $rsm = new ResultSetMappingBuilder($this->_em);
            $rsm->addRootEntityFromClassMetadata($this->_entityName, 'se');

            return $this
                ->_em
                ->newHydrator(Query::HYDRATE_OBJECT)
                ->hydrateAll($stmt, $rsm);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function markEmailAsSending($emailsIds): ?bool
    {
        try {
            $date = new DateTime();
            $connection = $this->_em->getConnection();
            $query = "UPDATE schedule_emails as se SET se.sending_date = :date WHERE se.id IN(" . implode(',', $emailsIds) . ")";
            $rowAffected = $connection->executeUpdate(
                $query,
                [
                    'date' => $date->format('Y-m-d H:i:s')
                ]
            );
            if ($rowAffected === \count($emailsIds)) {
                return true;
            }
            $rollBackQuery = "UPDATE schedule_emails as se SET se.sending_date = NULL WHERE se.id IN(" . implode(',', $emailsIds) . ")";
            $connection->executeUpdate($rollBackQuery);
            return false;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function processDelayedEmails($delayedTime, $delayedTimeMinutes): void
    {
        $connection = $this->_em->getConnection();
        $query = 'UPDATE schedule_emails se SET se.sending_date = null WHERE se.send_at IS NULL AND TIMESTAMPDIFF(MINUTE, sending_date, :date) > :minutes';
        $connection->executeUpdate(
            $query,
            [
                'date'    => $delayedTime,
                'minutes' => $delayedTimeMinutes
            ]
        );
    }
}
