<?php

namespace App\Command;

use AurimasNiekis\SchedulerBundle\ScheduledJobInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateRouteListsCommand extends Command implements ScheduledJobInterface
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:update-route-lists';

    /**
     * @var ManagerRegistry
     */
    private ManagerRegistry $doctrine;

    /**
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        parent::__construct(self::$defaultName);

        $this->doctrine = $doctrine;
    }

    /**
     *
     */
    public function __invoke()
    {
        $this->execute();
    }

    /**
     * @return string
     */
    public function getSchedulerExpresion(): string
    {
        return '30 1 * * *';
    }

    /**
     *
     */
    protected function configure(): void
    {
        $this->setDescription('Update route-lists');
    }

    /**
     * @param InputInterface|null $input
     * @param OutputInterface|null $output
     * @return int
     */
    protected function execute(InputInterface $input = null, OutputInterface $output = null): int
    {
        $connection = $this->doctrine->getManager()->getConnection();

        $query = 'INSERT IGNORE INTO `somda_tdr_trein_treinnummerlijst` (`treinnummerlijst_id`, `treinid`)
        SELECT `l`.`id`, `tr`.`treinid`
            FROM `somda_tdr_treinnummerlijst` `l`
            JOIN `somda_trein` `tr` ON `tr`.`treinnr` BETWEEN `l`.`nr_start` AND `l`.`nr_eind`
            JOIN `somda_tdr_s_e` `t` ON `t`.`treinid` = `tr`.`treinid` AND `t`.`tdr_nr` = `l`.`tdr_nr`
            GROUP BY `tr`.`treinid`, `l`.`id`';
        $statement = $connection->prepare($query);
        $statement->execute();

        // Remove all routes that no longer exist
        $query = 'DELETE FROM `somda_tdr_trein_treinnummerlijst`
			    WHERE `treinnummerlijst_id` NOT IN (SELECT `id` FROM `somda_tdr_treinnummerlijst`)';
        $statement = $connection->prepare($query);
        $statement->execute();

        return 0;
    }
}