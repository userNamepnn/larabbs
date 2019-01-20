<?php
/**
 * Created by PhpStorm.
 * User: Panninan
 * Date: 2019/1/17 0017
 * Time: 16:50
 */

namespace App\Handlers;

use GuzzleHttp\Client;
use Overtrue\Pinyin\Pinyin;

class SlugTranslateHandler
{
    public function translate($text)
    {
        //实例化HTTP客户端
        $http = new Client();

        //初始化配置信息
        $api = 'http://api.fanyi.baidu.com/api/trans/vip/translate?';
        $appid = config('services.baidu_translate.appid');
        $key = config('services.baidu_translate.key');
        $salt = time();

        //如果没有配置百度翻译，自动使用拼音方案
        if (empty($appid) || empty($key)) {
            return $this->pinyin($text);
        }

        // 根据文档，生成 sign
        // http://api.fanyi.baidu.com/api/trans/product/apidoc
        // appid+q+salt+密钥 的MD5值
        $sign = md5($appid . $text . $salt . $key);

        $query = http_build_query([
            "q" => $text,
            "from" => "zh",
            "to" => "en",
            "appid" => $appid,
            "salt" => $salt,
            "sign" => $sign,
        ]);

        //发送http请求
        $response = $http->get($api . $query);
        $result = json_decode($response->getBody(), true);

        if (isset($result['trans_result'][0]['dst'])) {
            return str_slug($result['trans_result'][0]['dst']);
        } else {
            return $this->pinyin($text);
        }
    }

    /**
     * [pinyin description]
     * @param  [type] $text [description]
     * @return [type]       [description]
     */
    public function pinyin($text)
    {
        return str_slug(app(Pinyin::class)->permalink($text));
    }
}
