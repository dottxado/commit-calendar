<?php

namespace App\Command\Help;

use Minicli\Command\CommandController;

class DisplayController extends CommandController
{
    public function handle()
    {
        $this->getPrinter()->info('Display the date and the commit messages of public repositories on GitHub', true);
        $this->getPrinter()->info('./commit-calendar display owner=OWNER name=NAME [number=NUMBER]');
        $this->getPrinter()->display('OWNER is the repository owner. If not provided, the user will be queried.');
        $this->getPrinter()->display('NAME is the repository name. If not provided, the user will be queried.');
        $this->getPrinter()->display('NUMBER is the number of commits you want to extract. Default 30.');

        $this->getPrinter()->info('You will display the commit calendar in the console, and you can choose to save it to a CSV file and display into a browser (thanks to itty.bitty)');

        $this->getPrinter()->newline();
        $this->getPrinter()->info('You can try...', true);
        $this->getPrinter()->info('./commit-calendar display owner=minicli name=minicli');
        $this->getPrinter()->info('./commit-calendar display owner=wordpress name=wordpress number=10');
        $this->getPrinter()->info('./commit-calendar display owner=quarkusio name=quarkus');
        $this->getPrinter()->newline();
    }
}
