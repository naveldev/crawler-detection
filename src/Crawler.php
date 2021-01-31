<?php

namespace Navel\Crawler;

use Navel\Crawler\Fixtures;

class Crawler
{
    /**
     * [protected description]
     *
     * @var [type]
     */
    protected $userHost;

    /**
     * [protected description]
     *
     * @var [type]
     */
    protected $userAgent;

    /**
     * [protected description]
     *
     * @var [type]
     */
    protected $httpHeaders = array();

    /**
     * [protected description]
     *
     * @var [type]
     */
    protected $matches = array();

    /**
     * [protected description]
     *
     * @var [type]
     */
    protected $crawlers;

    /**
     * [protected description]
     *
     * @var [type]
     */
    protected $hosts;

    /**
     * [protected description]
     *
     * @var [type]
     */
    protected $exclusions;

    /**
     * [protected description]
     *
     * @var [type]
     */
    protected $fixtures;

    /**
     * [__construct description]
     *
     * @param [type] $headers   [description]
     * @param [type] $userAgent [description]
     */
    public function __construct( array $headers = null, $userAgent = null, $userHost = null )
    {
        $this->fixtures = new Fixtures;

        $this->hosts = $this->compileRegex( $this->fixtures->getHosts() );
        $this->crawlers = $this->compileRegex( $this->fixtures->getCrawlers() );
        $this->exclusions = $this->compileRegex( $this->fixtures->getWhitelist() );

        $this->setHttpHeaders( $headers );
        $this->setUserAgent( $userAgent );
        $this->setUserHost( $userHost );
    }

    /**
     * [compileRegex description]
     *
     * @param  [type] $patterns [description]
     * @return [type]           [description]
     */
    public function compileRegex( $patterns )
    {
        return '(' . implode( '|', $patterns ) . ')';
    }

    /**
     * [setHttpHeaders description]
     *
     * @param [type] $httpHeaders [description]
     */
    protected function setHttpHeaders( $httpHeaders = null )
    {
        // Use global _SERVER if $httpHeaders aren't defined.
        if ( !is_array( $httpHeaders ) || ! count( $httpHeaders ) ) {
            $httpHeaders = $_SERVER;
        }

        // Clear existing headers.
        $this->httpHeaders = array();

        // Only save HTTP headers. In PHP land, that means
        // only _SERVER vars that start with HTTP_.
        foreach ( $httpHeaders as $key => $value ) {
            if ( strpos( $key, 'HTTP_' ) === 0 ) {
                $this->httpHeaders[$key] = $value;
            }
        }
    }

    /**
     * [setUserHost description]
     *
     * @param [type] $host [description]
     */
    protected function setUserHost( $host = null )
    {
        $host = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'];

        return $this->userHost = $host;
    }

    /**
     * [setUserAgent description]
     *
     * @param [type] $userAgent [description]
     */
    protected function setUserAgent( $userAgent = null )
    {
        if ( is_null( $userAgent ) ) {
            foreach ( $this->fixtures->getHeaders() as $altHeader ) {
                if ( isset( $this->httpHeaders[$altHeader] ) ) {
                    $userAgent .= $this->httpHeaders[$altHeader] . ' ';
                }
            }
        }

        return $this->userAgent = $userAgent;
    }

    /**
     * [isCrawler description]
     *
     * @param  [type]  $host      [description]
     * @param  [type]  $userAgent [description]
     * @return boolean            [description]
     */
    public function isCrawler( $host = null, $userAgent = null )
    {
        if( !$this->dnsSearch( $host ) ) {
            return true;
        }

        if( !$this->uaSearch( $userAgent ) ) {
            return true;
        }

        return false;
    }

    /**
     * [dnsSearch description]
     *
     * @param  [type] $host [description]
     * @return [type]       [description]
     */
    public function dnsSearch( $host = null )
    {
        return $this->reverseDnsLookup( $host );
    }

    /**
     * [userAgentSearch description]
     *
     * @param  [type] $host [description]
     * @return [type]       [description]
     */
    public function uaSearch( $host = null )
    {
        return $this->reverseDnsLookup( $host );
    }

    /**
     * [dnsLookup description]
     *
     * @param  [type] $host [description]
     * @return [type]       [description]
     */
    protected function reverseDnsLookup( $host = null )
    {
        $host = trim(gethostbyaddr( $host ?: $this->userHost ));

        return (bool) preg_match("/{$this->hosts}/i", $host, $this->matches);
    }

    /**
     * [isCrawler description]
     *
     * @param  [type]  $userAgent [description]
     * @return boolean            [description]
     */
    protected function userAgentLookup( $userAgent = null )
    {
        $agent = trim(preg_replace(
            "/{$this->exclusions}/i",
            '',
            $userAgent ?: $this->userAgent
        ));

        if ( $agent === '' ) {
            return false;
        }

        return (bool) preg_match("/{$this->crawlers}/i", $agent, $this->matches);
    }

    /**
     * [matches description]
     *
     * @return [type] [description]
     */
    public function matches()
    {
        return $this->getMatches();
    }

    /**
     * [getMatches description]
     *
     * @return [type] [description]
     */
    protected function getMatches()
    {
        return isset($this->matches[0]) ? $this->matches[0] : null;
    }
}
