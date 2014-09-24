<?php

/**
 * 微信公众平台 PHP SDK
 *
 * WangChen
 *
 */

define('WEIXIN_ROBOT_PLUGIN_DIR', WP_PLUGIN_DIR.'/'. dirname(plugin_basename(__FILE__)));
include(WEIXIN_ROBOT_PLUGIN_DIR.'/functions.php');

/**
 * 微信公众平台处理类
 */
class Wechat {

  // 调试模式，将错误通过文本消息回复显示
  private $debug;

  // 以数组的形式保存微信服务器每次发来的请求
  private $request;

  // 用户获取token，目前仅用在创建菜单
  private $appid, $secret;

  /**
   * 初始化，判断此次请求是否为验证请求，并以数组形式保存
   *
   * @param string $token 验证信息
   * @param boolean $debug 调试模式，默认为关闭
   */
  public function __construct($token, $appid, $secret, $debug = FALSE) {
    $this->appid = $appid;
    $this->secret = $secret;
    if (!$this->validateSignature($token)) {
      exit('签名验证失败');
    }

    if ($this->isValid()) {
      // 网址接入验证
      exit($_GET['echostr']);
    }

    if (!isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
      exit('缺少数据');
    }

    $this->debug = $debug;
    set_error_handler(array(&$this, 'errorHandler'));
    // 设置错误处理函数，将错误通过文本消息回复显示

    $xml = (array) simplexml_load_string($GLOBALS['HTTP_RAW_POST_DATA'], 'SimpleXMLElement', LIBXML_NOCDATA);

    $this->request = array_change_key_case($xml, CASE_LOWER);
    // 将数组键名转换为小写，提高健壮性，减少因大小写不同而出现的问题
  }

  /**
   * 判断此次请求是否为验证请求
   *
   * @return boolean
   */
  private function isValid() {
    return isset($_GET['echostr']);
  }

  /**
   * 验证此次请求的签名信息
   *
   * @param  string $token 验证信息
   * @return boolean
   */
  private function validateSignature($token) {
    $signature = $_GET["signature"];
    $timestamp = $_GET["timestamp"];
    $nonce = $_GET["nonce"];

    $tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );

		if( $tmpStr == $signature ){
			return true;
		} else {
			return false;
		}
  }

  /**
   * 获取本次请求中的参数，不区分大小
   *
   * @param  string $param 参数名，默认为无参
   * @return mixed
   */
  protected function getRequest($param = FALSE) {
    if ($param === FALSE) {
      return $this->request;
    }

    $param = strtolower($param);

    if (isset($this->request[$param])) {
        return $this->request[$param];
    }

    return NULL;
  }

  /**
   *  获取token，使用文件缓存机制
   *  首先检查文件是否存在，存在则检查token是否过期
   *  若过期或文件不存在，则向服务器请求，然后存入文件
   *
   */
  public function get_access_token() {
    $path = WEIXIN_ROBOT_PLUGIN_DIR."/access_token.json";
    // 检查文件并查看token是否过期
    if(file_exists($path)) {
        $json = file_get_contents($path);
        if(!isset($json['access_token']) || !isset($json['time']) || !isset($json['expires_in']))
            return false;

        $array = json_decode($json, true);
        $expires_time = intval($array["time"]) + intval($array["expires_in"]) - 100;
        $now = time();
        if($now < $expires_time)
            return $array["access_token"];
    }

    // 如果文件不存在或者token已经过期则向服务器请求
    $result = $this->http_get("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appid."&secret=".$this->secret);
    if($result) {
        $json = json_decode($result, true);
        if(!$json || isset($json['errcode']))
            return false;

        $json["time"] = time();
        $json = json_encode($json);

        // 写入文件
        $file = fopen($path, "wb");
        if($file!==false) {
          fwrite($file, $json);
          fclose($file);
        }
        return $json["access_token"];
    }
    return false;

  }


  /**
   * 使用curl实现GET请求
   */
    private function http_get($url) {
        $oCurl = curl_init();
        if(stripos($url, "https://")!==FALSE) {
          curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
          curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($oCurl);
        $status = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($status["http_code"])==200)
            return $content;
        else
            return false;
    }


    // 创建菜单，根据微信api传入菜单json
    public function create_menu($menu_json) {
        $access_token = $this->get_access_token();
        $res = http_post("https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token, $menu_json);
        return $res;
    }

    public function fetch_menu() {
        $access_token = $this->get_access_token();
        $res = http_get("https://api.weixin.qq.com/cgi-bin/menu/get?access_token=".$access_token);
        return $res;
    }



  /**
   * 用户关注时触发，用于子类重写
   *
   * @return void
   */
  protected function onSubscribe() {}

  /**
   * 用户取消关注时触发，用于子类重写
   *
   * @return void
   */
  protected function onUnsubscribe() {}

  /**
   * 收到文本消息时触发，用于子类重写
   *
   * @return void
   */
  protected function onText() {}

  /**
   * 收到图片消息时触发，用于子类重写
   *
   * @return void
   */
  protected function onImage() {}

  /**
   * 收到地理位置消息时触发，用于子类重写
   *
   * @return void
   */
  protected function onLocation() {}

  /**
   * 收到链接消息时触发，用于子类重写
   *
   * @return void
   */
  protected function onLink() {}

  /**
   * 收到自定义菜单消息时触发，用于子类重写
   *
   * @return void
   */
  protected function onClick() {}

  /**
   * 收到地理位置事件消息时触发，用于子类重写
   *
   * @return void
   */
  protected function onEventLocation() {}

  /**
   * 收到语音消息时触发，用于子类重写
   *
   * @return void
   */
  protected function onVoice() {}

  /**
   * 扫描二维码时触发，用于子类重写
   *
   * @return void
   */
  protected function onScan() {}

  /**
   * 收到未知类型消息时触发，用于子类重写
   *
   * @return void
   */
  protected function onUnknown() {}

  /**
   * 回复文本消息
   *
   * @param  string  $content  消息内容
   * @param  integer $funcFlag 默认为0，设为1时星标刚才收到的消息
   * @return void
   */
  protected function responseText($content, $funcFlag = 0) {
    exit(new TextResponse($this->getRequest('fromusername'), $this->getRequest('tousername'), $content, $funcFlag));
  }

  /**
   * 回复音乐消息
   *
   * @param  string  $title       音乐标题
   * @param  string  $description 音乐描述
   * @param  string  $musicUrl    音乐链接
   * @param  string  $hqMusicUrl  高质量音乐链接，Wi-Fi 环境下优先使用
   * @param  integer $funcFlag    默认为0，设为1时星标刚才收到的消息
   * @return void
   */
  protected function responseMusic($title, $description, $musicUrl, $hqMusicUrl, $funcFlag = 0) {
    exit(new MusicResponse($this->getRequest('fromusername'), $this->getRequest('tousername'), $title, $description, $musicUrl, $hqMusicUrl, $funcFlag));
  }

  /**
   * 回复图文消息
   * @param  array   $items    由单条图文消息类型 NewsResponseItem() 组成的数组
   * @param  integer $funcFlag 默认为0，设为1时星标刚才收到的消息
   * @return void
   */
  protected function responseNews($items, $funcFlag = 0) {
    exit(new NewsResponse($this->getRequest('fromusername'), $this->getRequest('tousername'), $items, $funcFlag));
  }

  /**
   * 分析消息类型，并分发给对应的函数
   *
   * @return void
   */
  public function run() {
    switch ($this->getRequest('msgtype')) {
      case 'event':
        switch ($this->getRequest('event')) {
          case 'subscribe':
            $this->onSubscribe();
            break;

          case 'unsubscribe':
            $this->onUnsubscribe();
            break;

          case 'SCAN':
            $this->onScan();
            break;

          case 'LOCATION':
            $this->onEventLocation();
            break;

          case 'CLICK':
            $this->onClick();
            break;
      }
      break;

      case 'text':
        $this->onText();
        break;

      case 'image':
        $this->onImage();
        break;

      case 'location':
        $this->onLocation();
        break;

      case 'link':
        $this->onLink();
        break;

      case 'voice':
        $this->onVoice();
        break;

      default:
        $this->onUnknown();
      break;
    }
  }


    /**
     * 自定义的错误处理函数，将 PHP 错误通过文本消息回复显示
     * @param  int $level   错误代码
     * @param  string $msg  错误内容
     * @param  string $file 产生错误的文件
     * @param  int $line    产生错误的行数
     * @return void
     */
    protected function errorHandler($level, $msg, $file, $line) {
        if ( ! $this->debug) {
            return;
        }

        $error_type = array(
            // E_ERROR             => 'Error',
            E_WARNING           => 'Warning',
            // E_PARSE             => 'Parse Error',
            E_NOTICE            => 'Notice',
            // E_CORE_ERROR        => 'Core Error',
            // E_CORE_WARNING      => 'Core Warning',
            // E_COMPILE_ERROR     => 'Compile Error',
            // E_COMPILE_WARNING   => 'Compile Warning',
            E_USER_ERROR        => 'User Error',
            E_USER_WARNING      => 'User Warning',
            E_USER_NOTICE       => 'User Notice',
            E_STRICT            => 'Strict',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED        => 'Deprecated',
            E_USER_DEPRECATED   => 'User Deprecated',
        );

        $template = <<<ERR
PHP 报错啦！

%s: %s
File: %s
Line: %s
ERR;

        $this->responseText(sprintf($template,
            $error_type[$level],
            $msg,
            $file,
            $line
        ));
    }

}

  /**
   * 用于回复的基本消息类型
   */
    abstract class WechatResponse {

        protected $toUserName;
        protected $fromUserName;
        protected $funcFlag;
        protected $template;

        public function __construct($toUserName, $fromUserName, $funcFlag) {
            $this->toUserName = $toUserName;
            $this->fromUserName = $fromUserName;
            $this->funcFlag = $funcFlag;
        }

        abstract public function __toString();
    }

  /**
   * 用于回复的文本消息类型
   */
    class TextResponse extends WechatResponse {

        protected $content;

        public function __construct($toUserName, $fromUserName, $content, $funcFlag = 0) {
            parent::__construct($toUserName, $fromUserName, $funcFlag);

            $this->content = $content;
            $this->template = <<<XML
<xml>
  <ToUserName><![CDATA[%s]]></ToUserName>
  <FromUserName><![CDATA[%s]]></FromUserName>
  <CreateTime>%s</CreateTime>
  <MsgType><![CDATA[text]]></MsgType>
  <Content><![CDATA[%s]]></Content>
  <FuncFlag>%s</FuncFlag>
</xml>
XML;
        }

        public function __toString() {
            return sprintf($this->template,
                $this->toUserName,
                $this->fromUserName,
                time(),
                $this->content,
                $this->funcFlag
            );
        }

    }

  /**
   * 用于回复的音乐消息类型
   */
    class MusicResponse extends WechatResponse {

        protected $title;
        protected $description;
        protected $musicUrl;
        protected $hqMusicUrl;

        public function __construct($toUserName, $fromUserName, $title, $description, $musicUrl, $hqMusicUrl, $funcFlag) {
            parent::__construct($toUserName, $fromUserName, $funcFlag);

            $this->title = $title;
            $this->description = $description;
            $this->musicUrl = $musicUrl;
            $this->hqMusicUrl = $hqMusicUrl;
            $this->template = <<<XML
<xml>
  <ToUserName><![CDATA[%s]]></ToUserName>
  <FromUserName><![CDATA[%s]]></FromUserName>
  <CreateTime>%s</CreateTime>
  <MsgType><![CDATA[music]]></MsgType>
  <Music>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
    <MusicUrl><![CDATA[%s]]></MusicUrl>
    <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
  </Music>
  <FuncFlag>%s</FuncFlag>
</xml>
XML;
        }

        public function __toString() {
            return sprintf($this->template,
                $this->toUserName,
                $this->fromUserName,
                time(),
                $this->title,
                $this->description,
                $this->musicUrl,
                $this->hqMusicUrl,
                $this->funcFlag
            );
        }

    }

  /**
   * 用于回复的图文消息类型
   */
    class NewsResponse extends WechatResponse {

        protected $items = array();

        public function __construct($toUserName, $fromUserName, $items, $funcFlag) {
            parent::__construct($toUserName, $fromUserName, $funcFlag);

            $this->items = $items;
            $this->template = <<<XML
<xml>
  <ToUserName><![CDATA[%s]]></ToUserName>
  <FromUserName><![CDATA[%s]]></FromUserName>
  <CreateTime>%s</CreateTime>
  <MsgType><![CDATA[news]]></MsgType>
  <ArticleCount>%s</ArticleCount>
  <Articles>
    %s
  </Articles>
  <FuncFlag>%s</FuncFlag>
</xml>
XML;
        }

        public function __toString() {
            return sprintf($this->template,
                $this->toUserName,
                $this->fromUserName,
                time(),
                count($this->items),
                implode($this->items),
                $this->funcFlag
            );
        }

    }

    /**
    * 单条图文消息类型
   */
    class NewsResponseItem {

        protected $title;
        protected $description;
        protected $picUrl;
        protected $url;
        protected $template;

        public function __construct($title, $description, $picUrl, $url) {
            $this->title = $title;
            $this->description = $description;
            $this->picUrl = $picUrl;
            $this->url = $url;
            $this->template = <<<XML
<item>
  <Title><![CDATA[%s]]></Title>
  <Description><![CDATA[%s]]></Description>
  <PicUrl><![CDATA[%s]]></PicUrl>
  <Url><![CDATA[%s]]></Url>
</item>
XML;
        }

        public function __toString() {
            return sprintf($this->template,
                $this->title,
                $this->description,
                $this->picUrl,
                $this->url
            );
        }

    }

?>
