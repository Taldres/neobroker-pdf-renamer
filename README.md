[![MIT License](https://img.shields.io/badge/License-MIT-green.svg)](https://choosealicense.com/licenses/mit/)

# Trade Republic PDF Renamer

This package helps you rename your Trade Republic PDF files and optionally group them into subfolders by type (crypto
and securities) and/or ISIN/abbreviation.

![Example](https://taldres.dev/i/traderepublic_example.png)

## Installation

1. Clone Repo

```bash
git clone https://github.com/Taldres/traderepublic-pdf-renamer.git
```

2. Install dependencies

```bash
composer install --no-dev
```

## Usage/Examples

1. Copy your Trade Republic files to `./input`.
2. You can easily start renaming with the following command:

```bash
php bin/console app:run -ct
```

3. The renamed files are saved in the `./output` folder.

### Parameters

- `-t, --group-type`

  Group files by type like stocks or crypto.


- `-c, --group-code`

  Group files by code like ISIN or cryptocurrency abbreviation.


- `-l, --lang[=LANG]`

  The language in which the Trade Republic files were generated. (two letters, like: de) [default: "de"]


- `-k, --keep-file`

  Keep already existing files in target directory instead of deleting them at startup.


- `-i, --input-dir[=INPUT-DIR]`

  Real path to the input directory


- `-o, --output-dir[=OUTPUT-DIR]`

  Real path to the output directory

## Supported languages

- `de` German

## Environment Variables (optional)

1. Create .env file

```bash
cp .env.example .env
```

2. Fill the variables if you want to deviate from the default folders and do not want to specify them as parameters
   every time.

- `INPUT_DIR`

- `OUTPUT_DIR`

