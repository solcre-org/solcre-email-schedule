<?php

namespace Solcre\EmailSchedule\Repository;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Exception;

class ScheduleEmailRepository extends EntityRepository
{
    private function isSqlDriver(): bool
    {
        return $this->_em->getConnection()->getDriver() instanceof \Doctrine\DBAL\Driver\SQLSrv\Driver;
    }

    public function fetchAvailableScheduledEmails($retried): ?array
    {
        try {
            $connection = $this->_em->getConnection();
            $query = 'SELECT * FROM schedule_emails as se WHERE se.send_at IS NULL AND se.retried < :retried AND se.sending_date IS NULL';
            if ($this->isSqlDriver()) {
                $query = 'SELECT * FROM schedule_emails as se  WITH (TABLOCKX) WHERE se.send_at IS NULL AND se.retried < :retried AND se.sending_date IS NULL;';
            }

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
            $query = 'UPDATE schedule_emails as se SET se.sending_date = :date WHERE se.id IN(' . implode(',', $emailsIds) . ')';
            if ($this->isSqlDriver()) {
                $query = "UPDATE schedule_emails  SET schedule_emails.sending_date = :date WHERE schedule_emails.id IN(" . implode(',', $emailsIds) . ")";
            }
            $rowAffected = $connection->executeStatement(
                $query,
                [
                    'date' => $date->format('Y-m-d H:i:s')
                ]
            );

            if ($rowAffected === \count($emailsIds)) {
                return true;
            }

            $rollBackQuery = 'UPDATE schedule_emails as se SET se.sending_date = NULL WHERE se.id IN(' . implode(',', $emailsIds) . ')';
            if ($this->isSqlDriver()) {
                $rollBackQuery = "UPDATE schedule_emails SET schedule_emails.sending_date = NULL WHERE schedule_emails.id IN(" . implode(',', $emailsIds) . ")";
            }

            $connection->executeStatement($rollBackQuery);

            return false;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function processDelayedEmails($delayedTime, $delayedTimeMinutes): void
    {
        $connection = $this->_em->getConnection();
        $query = 'UPDATE schedule_emails se SET se.sending_date = null WHERE se.send_at IS NULL AND TIMESTAMPDIFF(MINUTE, sending_date, :date) > :minutes';
        if ($this->isSqlDriver()) {
            $query = 'UPDATE schedule_emails SET schedule_emails.sending_date = null WHERE schedule_emails.send_At IS NULL AND DATEDIFF(MINUTE, sending_date, :date) > :minutes';
        }

        $connection->executeStatement(
            $query,
            [
                'date'    => $delayedTime,
                'minutes' => $delayedTimeMinutes
            ]
        );
    }
}
