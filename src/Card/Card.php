<?php

namespace EasyWeChat\Card;

use EasyWeChat\Core\AbstractAPI;
use EasyWeChat\Support\Arr;

/**
* Class Menu.
*/
class Card extends AbstractAPI
{
  // 卡券类型
  const TYPE_GENERAL_COUPON = 'GENERAL_COUPON';   // 通用券
  const TYPE_GROUPON = 'GROUPON';          // 团购券
  const TYPE_DISCOUNT = 'DISCOUNT';         // 折扣券
  const TYPE_GIFT = 'GIFT';             // 礼品券
  const TYPE_CASH = 'CASH';             // 代金券
  const TYPE_MEMBER_CARD = 'MEMBER_CARD';      // 会员卡
  const TYPE_SCENIC_TICKET = 'SCENIC_TICKET';    // 景点门票
  const TYPE_MOVIE_TICKET = 'MOVIE_TICKET';     // 电影票
  const TYPE_BOARDING_PASS = 'BOARDING_PASS';    // 飞机票
  const TYPE_LUCKY_MONEY = 'LUCKY_MONEY';      // 红包
  const TYPE_MEETING_TICKET = 'MEETING_TICKET';   // 会议门票

  const CARD_STATUS_NOT_VERIFY = 'CARD_STATUS_NOT_VERIFY';   // 待审核
  const CARD_STATUS_VERIFY_FAIL = 'CARD_STATUS_VERIFY_FAIL';   //审核失败
  const CARD_STATUS_VERIFY_OK = 'CARD_STATUS_VERIFY_OK';     //通过审核
  const CARD_STATUS_USER_DELETE = 'CARD_STATUS_USER_DELETE';   //卡券被商户删除
  const CARD_STATUS_USER_DISPATCH = 'CARD_STATUS_USER_DISPATCH'; //在公众平台投放过的卡券

  const API_CREATE = 'https://api.weixin.qq.com/card/create';
  const API_DELETE = 'https://api.weixin.qq.com/card/delete';
  const API_GET = 'https://api.weixin.qq.com/card/get';
  const API_UPDATE = 'https://api.weixin.qq.com/card/update';
  const API_LIST = 'https://api.weixin.qq.com/card/batchget';
  const API_CONSUME = 'https://api.weixin.qq.com/card/code/consume';
  const API_UNAVAILABLE = 'https://api.weixin.qq.com/card/code/unavailable';
  const API_CODE_GET = 'https://api.weixin.qq.com/card/code/get';
  const API_CODE_UPDATE = 'https://api.weixin.qq.com/card/code/update';
  const API_CODE_DECRYPT = 'https://api.weixin.qq.com/card/code/decrypt';
  const API_UPDATE_STOCK = 'https://api.weixin.qq.com/card/modifystock';
  const API_MEMBER_CARD_ACTIVE = 'https://api.weixin.qq.com/card/membercard/activate';
  const API_MEMBER_CARD_TRADE = 'https://api.weixin.qq.com/card/membercard/updateuser';
  const API_MEMBER_CARD_USER_INFO = 'https://api.weixin.qq.com/card/membercard/userinfo/get';
  const API_MOVIE_TICKET_UPDATE = 'https://api.weixin.qq.com/card/movieticket/updateuser';
  const API_BOARDING_PASS_CHECKIN = 'https://api.weixin.qq.com/card/boardingpass/checkin';
  const API_MEETING_TICKET_UPDATE = 'https://api.weixin.qq.com/card/meetingticket/updateuser';
  const API_TICKET = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=wx_card';
  const API_TESTWHITELIST = 'https://api.weixin.qq.com/card/testwhitelist/set';
  const API_USER_CARD_LIST = 'https://api.weixin.qq.com/card/user/getcardlist';
  const API_LANDINGPAGE_CREATE = 'https://api.weixin.qq.com/card/landingpage/create';

  /**
   * 卡券详情.
   *
   * @param string $cardId
   *
   * @return \EasyWeChat\Support\Collection
   */
  public function get($cardId)
  {
    return $this->parseJSON('json', [self::API_GET, ['card_id' => $cardId]]);
  }

  /**
   * 创建卡券.
   *
   * @param array  $base
   * @param array  $properties
   * @param string $type
   *
   * @return \EasyWeChat\Support\Collection
   */
  public function create(array $base, array $properties = array(), $type = self::TYPE_GENERAL_COUPON)
  {
    $key = strtolower($type);
    $card = array_merge(array('base_info' => $base), $properties);
    $params = array(
      'card' => array(

        'card_type' => $type,
        $key => $card,
      ),
    );

    return $this->parseJSON('json', [self::API_CREATE, $params]);
  }

  /**
   * code 解码
   *
   * @param string $encryptedCode
   *
   * @return string
   */
  public function getRealCode($encryptedCode)
  {
    return $this->parseJSON('json', [self::API_CODE_DECRYPT, ['encrypt_code' => $encryptedCode]]);
  }

  /**
   * 激活/绑定会员卡
   *
   * <pre>
   * $data:
   * {
   *      "init_bonus": 100,
   *      "init_balance": 200,
   *      "membership_number": "AAA00000001", "code": "12312313",
   *      "card_id": "xxxx_card_id"
   * }
   * </pre>
   *
   * @param string $cardId
   * @param array  $data
   *
   * @return \EasyWeChat\Support\Collection
   */
  public function memberCardActivate($cardId, array $data)
  {
    $params = array_merge(array('card_id' => $cardId), $data);

    return $this->parseJSON('json', [self::API_MEMBER_CARD_ACTIVE, $params]);
  }

  /**
   * Get Member Card user info
   *
   * <pre>
   * {
   *    "card_id": "pbLatjtZ7v1BG_ZnTjbW85GYc_E8",
   *    "code": "916679873278"
   * }
   * </pre>
   *
   * @param string $cardId
   * @param string $code
   *
   * @return \EasyWeChat\Support\Collection
   */
  public function memberCardUserInfo($cardId, $code)
  {
    $params = [
      'card_id' => $cardId,
      'code' => $code
    ];
    return $this->parseJSON('json', [self::API_MEMBER_CARD_USER_INFO, $params]);
  }

  /**
   * 会员卡交易.
   *
   * <pre>
   * $data:
   * {
   *     "code": "12312313",
   *     "card_id":"p1Pj9jr90_SQRaVqYI239Ka1erkI",
   *     "record_bonus": "消费30元，获得3积分",
   *     "add_bonus": 3,
   *     "add_balance": -3000
   *     "record_balance": "购买焦糖玛琪朵一杯，扣除金额30元。"
   *     "custom_field_value1": "xxxxx",
   * }
   * </pre>
   *
   * @param string $cardId
   * @param array  $data
   *
   * @return \EasyWeChat\Support\Collection
   */
  public function memberCardTrade($cardId, array $data)
  {
    $params = array_merge(array('card_id' => $cardId), $data);

    return $this->parseJSON('json', [self::API_MEMBER_CARD_TRADE, $params]);
  }

  /**
   * 设置测试白名单.
   *
   * <pre>
   * $data:
   * {
   *     "openIds": {
   *          "openid1",
   *          "openid2",
   *          "openid3"...
   *     }
   *     "usernames": {
   *          "username1",
   *          "username2",
   *          "username3"...
   *     }
   * }
   * </pre>
   *
   * @param array $data
   *
   * @return mixed
   */
  public function setWhitelist(array $data)
  {
    $data = array_merge(array('openIds' => array(), 'usernames' => array()), $data);
    $params = array_merge(array('openid' => $data['openIds']), array('username' => $data['usernames']));

    return $this->parseJSON('json', [self::API_TESTWHITELIST, $params]);
  }

  /**
   * 通过openId设置测试白名单.
   *
   * $data:
   * {
   *     "openid1",
   *     "openid2",
   *     "openid3"...
   * }
   *
   * @param array $data
   *
   * @return mixed
   */
  public function setWhitelistByOpenId(array $data)
  {
    return $this->setWhitelist(array('openIds' => $data));
  }

  /**
   * 通过username设置测试白名单.
   *
   * $data:
   * {
   *     "username1",
   *     "username2",
   *     "username3"...
   * }
   *
   * @param array $data
   *
   * @return mixed
   */
  public function setWhitelistByUsername(array $data)
  {
    return $this->setWhitelist(array('usernames' => $data));
  }

}