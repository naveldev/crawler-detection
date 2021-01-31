# Crawler Detection

## Installation

```
composer require naveldev/crawler-detection
```

## Usage

```
use Navel\Crawler\Crawler;

$crawler = new Crawler;

// Check for a crawler OR check a custom header by passing a parameter to the function
$crawler->check();

// Return all the found crawlers
$crawler->matches();
```
