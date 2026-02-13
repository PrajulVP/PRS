# UnSlay-Shell

A beautiful, web-based terminal interface for Laravel applications. This package provides a secure, interactive shell experience directly within your browser.

## Features

- **Beautiful Branding**: Custom ASCII art header.
- **Auto-completion**: Tab completion for file paths and commands.
- **Streaming Output**: Real-time output for long-running commands (like migrations).
- **Secure Access**: Password protection with session handling.
- **Customizable**: Configurable routes, middleware, and password.

## Installation

### 1. Add to Composer

Since this is a local package (alpha version), add the following to your `composer.json` repositories section:

```json
"repositories": [
    {
        "type": "path",
        "url": "./packages/sreyas-a-s/unslay-shell"
    }
]
```

Then require the package:

```bash
composer require sreyas-a-s/unslay-shell
```

### 2. Publish Configuration

Publish the configuration file to set up your password (`config/unslay-shell.php`):

```bash
php artisan vendor:publish --tag=unslay-shell-config
```

### 3. Configure

Add the following to your `.env` file:

```env
UNSLAY_SHELL_PASSWORD=your_secure_password
UNSLAY_SHELL_ENABLED=true
```

## Usage

Navigate to `/unslay-terminal` (or your configured prefix) in your browser. Enter the password to access the shell.

## commands

- `cd <path>`: Change directory.
- `logout` or `exit`: Log out of the terminal session.
- `clear`: Clear the screen.

## Credits

Developed by [Sreyas-A-S](https://github.com/Sreyas-A-S/).
