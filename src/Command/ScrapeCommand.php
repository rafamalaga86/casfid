<?php

declare(strict_types=1);

namespace App\Command;

use App\DTO\NewsArticleDTO;
use App\Service\ScrapingService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * A console command to run all registered scrapers.
 */
#[AsCommand(
    name: 'app:scrape-news',
    description: 'Runs all registered scrapers to fetch full news articles (title, URL, and body).'
)]
class ScrapeCommand extends Command
{
    public function __construct(
        /**
         * The service responsible for running the scrapers.
         */
        private readonly ScrapingService $scrapingService
    ) {
        parent::__construct();
    }

    /**
     * Executes the command to run all registered scrapers and displays the results.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Running all news scrapers...');

        $report = $this->scrapingService->scrape();
        $results = $report['results'];
        $errors = $report['errors'];

        if (empty($results) && empty($errors)) {
            $io->warning('No scrapers were executed or found.');
            return Command::SUCCESS;
        }

        foreach ($results as $scraperClass => $result) {
            $io->section(sprintf('Success: <info>%s</info>', $scraperClass));
            $io->comment(sprintf('Found %d articles:', $result['count']));

            /** @var NewsArticleDTO $article */
            foreach ($result['articles'] as $index => $article) {
                $io->writeln(sprintf('  <info>#%d: %s</info>', $index + 1, $article->title));
                $io->writeln(sprintf('  <fg=gray>URL:</> %s', $article->url));
                $io->writeln('  <fg=gray>Body:</>');
                // Using a block to nicely format the body text with indentation.
                $io->block($article->body);
                $io->newLine();
            }
        }

        if (!empty($errors)) {
            $io->newLine();
            $io->section('Failures and Warnings');
            $errorRows = [];
            foreach ($errors as $scraperClass => $errorMessage) {
                $errorRows[] = [$scraperClass, $errorMessage];
            }
            $io->table(['Scraper', 'Message'], $errorRows);
        }

        $io->newLine();
        $io->success('All scrapers finished.');

        return Command::SUCCESS;
    }
}
