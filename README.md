[![MIT License](https://img.shields.io/badge/License-MIT-green.svg)](https://choosealicense.com/licenses/mit/)

# Trade Republic PDF Renamer

This tool helps you rename your Trade Republic PDF files and converts the ugly names like `2023-01-05 0840 Kauf FTSE All-World High Dividend Yield USD (Dist) 1.pdf` into short and beautiful ones like `IE00B8GKDB10_20230105-1.pdf`. 

Optionally you can group them into subfolders by type (crypto and securities) and/or ISIN/abbreviation:


![Example](https://taldres.dev/i/traderepublic_example.png)

## Requirements
#### With Docker
- just Docker 😎

#### Without Docker
- PHP 8.2
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
docker exec app php bin/console app:run -ct
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
```

## Supported languages

- `de` German
