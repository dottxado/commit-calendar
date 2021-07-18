<?php

namespace App\Command\Display;

use App\Service\GithubService;
use Minicli\App;
use Minicli\Command\CommandController;
use Minicli\Input;
use Minicli\Output\Adapter\FilePrinterAdapter;
use Minicli\Output\Filter\ColorOutputFilter;
use Minicli\Output\Helper\TableHelper;
use Minicli\Output\OutputHandler;

class DefaultController extends CommandController
{
    private int $numberOfResults;
    private int $perPage;
    protected Input $userInput;

    public function boot(App $app)
    {
        parent::boot($app);
        $this->numberOfResults = 30;
        $this->perPage = 30;
        $this->userInput = new Input('CommitCalendar$> ');
    }

    public function handle(): void
    {
        $owner = $this->owner();
        $name = $this->name();
        $this->number();

        $pages = ceil($this->numberOfResults / $this->perPage);

        $this->getPrinter()->info(sprintf('Your input -> OWNER %s and NAME %s', $owner, $name));
        $this->getPrinter()->info(sprintf('Total number of results to get: %s', $this->numberOfResults));

        $output = [];
        for ($i = 1; $i <= $pages; $i++) {
            $numberOfResults = ($this->perPage * $i) <= $this->numberOfResults
                ? $this->perPage
                : $this->numberOfResults % $this->perPage;
            $this->getPrinter()->info(sprintf('Querying GitHub for %s results...', $numberOfResults));
            /**
             * @var GithubService
             */
            $githubService = $this->getApp()->github;
            $output = array_merge($output, $githubService->getCommitList($owner, $name, $i, $numberOfResults) ?? []);
        }

        if (is_null($output)) {
            $this->getPrinter()->error('Ooooops, something has gone bad');
            exit;
        } elseif (empty($output)) {
            $this->getPrinter()->error('Ooooops, the result is empty!');
            exit;
        }

        $this->displayTable($output);
        $this->saveToFile($output);
        $this->displayInBrowser($output, $owner, $name);

        $this->getPrinter()->success('Bye!');
        $this->getPrinter()->newline();
    }

    private function owner(): string
    {
        if ($this->hasParam('owner')) {
            $owner = $this->getParam('owner');
        } else {
            $this->getPrinter()->info('Provide the owner of the repository:');
            $owner = $this->userInput->read();
        }
        return $owner;
    }

    private function name(): string
    {
        if ($this->hasParam('name')) {
            $name = $this->getParam('name');
        } else {
            $this->getPrinter()->info('Provide the name of the repository:');
            $name = $this->userInput->read();
        }
        return $name;
    }

    private function number()
    {
        if ($this->hasParam('number') && ! is_null($this->getParam('number'))) {
            $this->numberOfResults = (int)$this->getParam('number');
        }
    }

    private function displayTable(array $list): void
    {
        $table = new TableHelper();
        $table->addHeader(['Date', 'Message']);
        foreach ($list as $element) {
            $date = $element->commit->author->date ?? '';
            $message = isset($element->commit->message)
                ? str_replace("\n", ' ', trim($element->commit->message))
                : '';
            $table->addRow([$date, $message]);
        }
        $this->getPrinter()->newline();
        $this->getPrinter()->rawOutput($table->getFormattedTable(new ColorOutputFilter()));
        $this->getPrinter()->newline();
    }

    private function saveToFile(array $list): void
    {
        $this->getPrinter()->display('Do you want it saved to a CSV file?[Y/n]', true);
        $logToFile = $this->userInput->read();
        if (strtolower($logToFile) === 'y' || $logToFile === '') {
            $this->getPrinter()->display('Full path and filename?[./commitcalendar.csv]', true);
            $filePath = $this->userInput->read();
            if ($filePath === '') {
                $filePath = 'commitcalendar.csv';
            }
            $backupOutput = $this->getApp()->getPrinter();
            $this->getApp()->setOutputHandler(new OutputHandler(new FilePrinterAdapter($filePath)));
            $content = $this->generateCsv($list);
            $this->getPrinter()->rawOutput($content);
            $this->getApp()->setOutputHandler($backupOutput);
        }
    }

    private function displayInBrowser(array $list, string $owner, string $name): void
    {
        $this->getPrinter()->display('Do you want to see it in a browser? (It may not work if the data are > 2Kb)[Y/n]', true);
        $generateHtml = $this->userInput->read();
        if (strtolower($generateHtml) === 'y' || $generateHtml === '') {
            $link = shell_exec(
                'echo -n "'
                .$this->generateHtml($list, $owner, $name)
                .'" | lzma -9 | base64 | xargs -0 printf "https://itty.bitty.site/#/%s\n"'
            );
            $this->getPrinter()->info('Copy this link to your browser');
            $this->getPrinter()->rawOutput($link);
        }
    }

    private function generateHtml(array $list, string $owner, string $name): string
    {
        $pageTemplate = '<html lang=""><head><title>Commit List</title><style>td{padding:10px;}</style></head><body><h1>%s</h1><table><tr><th>Date</th><th>Message</th></tr>%s</table></body></html>';
        $rowTemplate = '<tr><td>%s</td><td>%s</td></tr>';
        $rows = '';
        foreach ($list as $element) {
            $date = $element->commit->author->date ?? '';
            $message = isset($element->commit->message)
                ? str_replace("\n", ' ', $element->commit->message)
                : '';
            $message = $this->sanitizeMessage($message);
            $rows .= sprintf($rowTemplate, $date, $message);
        }

        return sprintf($pageTemplate, $owner . '/'.$name, $rows);
    }

    private function generateCsv(array $list, string $delimiter = ';'): string
    {
        $result = 'DATE, MESSAGE';
        foreach ($list as $element) {
            $date = $element->commit->author->date ?? '';
            $message = isset($element->commit->message)
                ? str_replace("\n", ' ', $element->commit->message)
                : '';
            $result .= "\n" . $date . $delimiter . $message;
        }
        return $result;
    }

    private function sanitizeMessage(string $message): string
    {
        $message = str_replace('`', "'", $message);
        $message = htmlentities($message);
        return $message;
    }
}
