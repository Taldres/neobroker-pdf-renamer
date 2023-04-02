[![MIT License](https://img.shields.io/badge/License-MIT-green.svg)](https://choosealicense.com/licenses/mit/)

# Neobroker PDF Renamer

This tool helps you rename your Neobroker PDF files and converts the ugly names like `2023-01-05 0840 Kauf FTSE All-World High Dividend Yield USD (Dist) 1.pdf` (Example: Trade Republic) into short and beautiful ones like `IE00B8GKDB10_20230105-1.pdf`. 

Optionally you can group them into subfolders by type (crypto and securities) and/or ISIN/abbreviation:

![Example](/screenshots/example_renamed_files.jpg)
![Example](/screenshots/example_console_result.jpg)

## Requirements
#### With Docker
- just Docker 😎

#### Without Docker
- PHP 8.1
- PHP Fileinfo Extension
- Composer

## Installation

1. Clone Repo

```bash
git clone https://github.com/Taldres/traderepublic-pdf-renamer.git
```

2. Install dependencies (if you are not using docker)

```bash
composer install --no-dev
```

## Usage/Examples

1. Copy your Trade Republic files to `./input`.
2. You can easily start renaming with the following command:

- #### Docker variant
```bash
# Start docker containers
docker compose up -d

# Run the app. Available parameters are explained below
docker compose exec app php bin/console rename:run -ct
```

- #### Without Docker
```bash
# Run the app. Available parameters are explained below
php bin/console app:run -ct
```

3. The renamed files are saved in the `./output` folder.

### Parameters

```
-k, --keep-files      Keep already existing files in target directory instead of deleting them at startup.
-t, --group-type      Group files by type like securities or crypto.
-c, --group-code      Group files by code like ISIN or cryptocurrency abbreviation .
-l, --lang[=LANG]     The language in which the Trade Republic files were generated. (two letters, like: de) [default: "de"]
-b, --broker[=BROKER]  The brokers name who generated the files. [default: "traderepublic"]
```

### .env Configuration (optional)
Optionally, you can also save the `language` and `broker` in the `.env` file if you want to deviate from the default values and/or do not want to pass these parameters via the console command.

However, these values are ignored once you pass these parameters via the console command.

#### Create the .env file

```bash
cp .env.example .env
```

#### Set the variables

`LANGUAGE=`

`BROKER=`

## Supported languages

Code | Language
--- |--------
de | German

## Supported brokers

Code | Broker | Supported languages
--- |--------| ---
traderepublic | Trade Republic | de
