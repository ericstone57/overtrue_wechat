<?php

/*
 * This file is part of the overtrue/wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Js.php.
 *
 * @author    overtrue <i@overtrue.me>
 * @copyright 2015 overtrue <i@overtrue.me>
 *
 * @link      https://github.com/overtrue
 * @link      http://overtrue.me
 */
namespace EasyWeChat\Js;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use EasyWeChat\Core\AbstractAPI;
use EasyWeChat\Support\Str;
use EasyWeChat\Support\Url as UrlHelper;
use EasyWeChat\Core\AccessToken;

/**
 * Class Js.
 */
class Js extends AbstractAPI
{
    /**
     * Cache.
     *
     * @var Cache
     */
    protected $cache;

    /**
     * Current URI.
     *
     * @var string
     */
    protected $url;

    /**
     * Ticket cache prefix.
     */
    const TICKET_CACHE_PREFIX = 'overtrue.wechat.jsapi_ticket.';

    /**
     * Ticket type.
     */
    const TICKET_TYPE_JSAPI = 'jsapi';
    const TICKET_TYPE_CARD = 'wx_card';

    /**
     * Api of ticket.
     */
    const API_TICKET = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket';

    /**
     * Get config json for jsapi.
     *
     * @param array $APIs
     * @param bool  $debug
     * @param bool  $beta
     * @param bool  $json
     *
     * @return array|string
     */
    public function config(array $APIs, $debug = false, $beta = false, $json = true)
    {
        $signPackage = $this->signature();

        $base = [
                 'debug' => $debug,
                 'beta' => $beta,
                ];
        $config = array_merge($base, $signPackage, ['jsApiList' => $APIs]);

        return $json ? json_encode($config) : $config;
    }

    /**
     * Return jsapi config as a PHP array.
     *
     * @param array $APIs
     * @param bool  $debug
     * @param bool  $beta
     *
     * @return array
     */
    public function getConfigArray(array $APIs, $debug = false, $beta = false)
    {
        return $this->config($APIs, $debug, $beta, false);
    }

    /**
     * Get jsticket.
     *
     * @param string $type
     * @return string
     */
    public function ticket($type = self::TICKET_TYPE_JSAPI)
    {
        $key = self::TICKET_CACHE_PREFIX.$this->getAccessToken()->getAppId().$type;

        if ($ticket = $this->getCache()->fetch($key)) {
            return $ticket;
        }

        $result = $this->parseJSON('get', [self::API_TICKET, ['type' => $type]]);

        $this->getCache()->save($key, $result['ticket'], $result['expires_in'] - 500);

        return $result['ticket'];
    }

    /**
     * Build signature.
     *
     * @param string $url
     * @param string $nonce
     * @param int    $timestamp
     *
     * @return array
     */
    public function signature($url = null, $nonce = null, $timestamp = null)
    {
        $url = $url ? $url : $this->getUrl();
        $nonce = $nonce ? $nonce : Str::quickRandom(10);
        $timestamp = $timestamp ? $timestamp : time();
        $ticket = $this->ticket();

        $sign = [
                 'appId' => $this->getAccessToken()->getAppId(),
                 'nonceStr' => $nonce,
                 'timestamp' => $timestamp,
                 'url' => $url,
                 'signature' => $this->getSignature($ticket, $nonce, $timestamp, $url),
                ];

        return $sign;
    }

    /**
     * Sign the params.
     *
     * @param string $ticket
     * @param string $nonce
     * @param int    $timestamp
     * @param string $url
     *
     * @return string
     */
    public function getSignature($ticket, $nonce, $timestamp, $url)
    {
        return sha1("jsapi_ticket={$ticket}&noncestr={$nonce}&timestamp={$timestamp}&url={$url}");
    }


    /**
     * CardExt.
     *
     * @param string $card_id
     * @param string $code
     * @param string $openid
     * @param int $outer_id
     *
     * @return array
     */
    public function getCardExt($card_id, $code = "", $openid = "", $outer_id = 0)
    {
        $timestamp = time();
//        $nonce = Str::quickRandom(10);
        $nonce = "";
        $ticket = $this->ticket(self::TICKET_TYPE_CARD);
        $sign = $this->getCardSignature($ticket, $timestamp, $card_id, $code, $openid, $nonce);

        $cardExt = [
          'code' => $code,
          'openid' => $openid,
//          'outer_id' => $outer_id,
//          'nonce_str' => $nonce,
          'timestamp' => $timestamp,
          'signature' => $sign,
        ];

        return $cardExt;
    }

    public function getChooseCardData($card_id = "", $card_type = "", $location_id = "")
    {
        $timestamp = time();
        $nonce = Str::quickRandom(10);
        $ticket = $this->ticket(self::TICKET_TYPE_CARD);
        $app_id = $this->getAccessToken()->getAppId();

        $sign = $this->getCardSignature(
            $ticket,
            $app_id,
            $location_id,
            $timestamp,
            $nonce,
            $card_id,
            $card_type
        );

        return [
            'shopId' => $location_id,
            'cardType' => $card_type,
            'cardId' => $card_id,
            'timestamp' => $timestamp,
            'nonceStr' => $nonce,
            'signType' => 'SHA1',
            'cardSign' => $sign
        ];
    }

    /**
     * 生成签名.
     *
     * @return string
     */
    public function getCardSignature()
    {
        $params = func_get_args();

        sort($params, SORT_STRING);

        return sha1(implode($params));
    }


    /**
     * Set current url.
     *
     * @param string $url
     *
     * @return Js
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get current url.
     *
     * @return string
     */
    public function getUrl()
    {
        if ($this->url) {
            return $this->url;
        }

        return UrlHelper::current();
    }

    /**
     * Set cache manager.
     *
     * @param \Doctrine\Common\Cache\Cache $cache
     *
     * @return $this
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Return cache manager.
     *
     * @return \Doctrine\Common\Cache\Cache
     */
    public function getCache()
    {
        return $this->cache ?: $this->cache = new FilesystemCache(sys_get_temp_dir());
    }
}
