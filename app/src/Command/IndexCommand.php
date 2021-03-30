<?php

namespace App\Command;

use App\Search\Indexer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class IndexCommand extends Command
{
    protected static $defaultName = 'app:index';

    private SymfonyStyle $io;

    /**
     * @inheritDoc
     */
    public function __construct(
        private Indexer $indexer
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Index Pokedex content');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->indexer->update();

        return self::SUCCESS;
    }
}
