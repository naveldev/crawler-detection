# Crawler Detection

## Installation

```
composer require naveldev/crawler-detection
```

## Usage

```
use Navel\Crawler\Crawler;

$crawler = new Crawler;

// Check if the current request is made by a crawler
$crawler->isCrawler();

// Check if the supplied IP and/or user agent are Crawlers
$crawler->isCrawler( $ip, $userAgent );

// Only check if the IP is from a Crawler
$crawler->dnsSearch( $ip );

// Only check if the User Agent is from a Crawler
$crawler->uaSearch( $userAgent );

// Return all the found matches after executing one of the above functions
$crawler->matches();
```
