<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\VerificationCodeRequest;
use Overtrue\EasySms\EasySms;

class VerificationCodesController extends Controller
{
    /**
     * 发送验证码
     * @param VerificationCodeRequest $request
     * @param EasySms $easySms
     * @throws \Overtrue\EasySms\Exceptions\InvalidArgumentException
     */
    public function store(VerificationCodeRequest $request, EasySms $easySms)
    {
        //获取缓存验证码数据
        $captchaData = \Cache::get($request->captcha_key);
        if (!$captchaData) {
            return $this->response->error('图片验证码已失效', 422);
        }

        //验证验证码输入
        if (!hash_equals($captchaData['code'], $request->captcha_code)) {
            return $this->response->errorUnauthorized('验证码错误');
        }

        $phone = $captchaData['phone'];

        //若非配置发送真实短信,则发送1234
        if (false === env('REALLY_SEND')) {
            $code = '1234';
        } else {
            //六位数随机验证码,左补零
            $code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);
            try {
                $result = $easySms->send($phone, [
                    'content' => "【Lbbs社区】您的验证码是{$code}。如非本人操作，请忽略本短信",
                ]);
            } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
                $message = $exception->getException('yunpian')->getMessage();
                return $this->response->errorInternal($message ?: '短信发送异常');
            }
        }

        //缓存验证码,10分钟
        $key = 'verificationCode_' . str_random(15);
        $expire_at = now()->addMinutes(10);
        \Cache::put($key, ['phone' => $phone, 'code' => $code], $expire_at);

        //清除图片验证码缓存
        \Cache::forget($request->captcha_key);
        return $this->response->array([
            'key' => $key,
            'expire_at' => $expire_at->toDateTimeString(),
        ])->setStatusCode(201);
    }
}
