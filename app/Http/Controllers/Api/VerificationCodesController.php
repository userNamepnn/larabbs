<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\VerificationCodeRequest;
use Overtrue\EasySms\EasySms;

class VerificationCodesController extends Controller
{
    public function store(VerificationCodeRequest $request, EasySms $easySms)
    {
        $phone = $request->phone;

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
        $key = 'verificationCOde_' . str_random(15);
        $expire_at = now()->addMinutes(10);
        \Cache::put($key, ['phone' => $phone, 'code' => $code], $expire_at);
        return $this->response->array([
            'key' => $key,
            'expire_at' => $expire_at->toDateTimeString(),
        ])->setStatusCode(201);
    }
}
