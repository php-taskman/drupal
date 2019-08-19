# PHP Taskman Drupal extension

## Installation

```
composer require phptaskman/drupal
```

## Usage

```bash
./vendor/bin/taskman

Taskman dev-master

Usage:
  command [options] [arguments]

Options:
  -h, --help                           Display this help message
  -q, --quiet                          Do not output any message
  -V, --version                        Display this application version
      --ansi                           Force ANSI output
      --no-ansi                        Disable ANSI output
  -n, --no-interaction                 Do not ask any interactive question
      --simulate                       Run in simulated mode (show what would have happened).
      --progress-delay=PROGRESS-DELAY  Number of seconds before progress bar is displayed in long-running task collections. Default: 2s. [default: 2]
  -D, --define=DEFINE                  Define a configuration item value. (multiple values allowed)
      --working-dir=WORKING-DIR        Working directory, defaults to current working directory. [default: "/home/ec2-user/environment/phptaskman/drupal"]
  -v|vv|vvv, --verbose                 Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  help                      Displays help for a command
  list                      Lists commands
 drupal
  drupal:disable-cache      Disable aggregation and clear cache.
  drupal:drush-setup        Write Drush configuration files to given directories.
  drupal:permissions-setup  Setup Drupal permissions.
  drupal:run-server         [drupal:rs] Run PHP server.
  drupal:settings-setup     Setup Drupal settings overrides.
  drupal:site-install       [drupal:si|dsi] Install target site.
  drupal:site-post-install  Run Drupal post-install commands.
  drupal:site-pre-install   Run Drupal pre-install commands.
```