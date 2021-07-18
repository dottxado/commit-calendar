# commit-calendar

Display a list of dates and commits from public GitHub repositories.
You will display the list in the terminal, and you can choose to save it to a CSV file (";" separated) and display into a browser (thanks to [itty.bitty](https://itty.bitty.site))

Commit Calendar is a CLI utility based on [Minicli](https://github.com/minicli/minicli).

## Installation

Requirements:
- `php-cli` >= 7.3
- Composer

Installation:

1. Clone this repository
2. Run `composer install`

## How to use

In a terminal, in the project folder:

```bash
./commit-calendar display owner=OWNER name=NAME [number=NUMBER]
```

- OWNER: is the repository owner
- NAME: is the repository name
- NUMBER: the number of commits you want to extract

For example: from this url https://github.com/dottxado/pico-macro-pad the OWNER is "dottxado" and the name is "pico-macro-pad", resulting in
```bash
./commit-calendar display owner=dottxado name=pico-macro-pad
```

While OWNER and NAME are required, and if not given they will be queried by the CLI, NUMBER has a default value of 30.

## Help
In a terminal, in the project folder:

```bash
./commit-calendar help
```
will display the available commands, and

```bash
./commit-calendar help display
```
will display the help for the display command.
