<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\CaptchaRequest;
use Gregwar\Captcha\CaptchaBuilder;

class CaptchasController extends Controller
{
    /**
     * 图片验证码
     * @param CaptchaRequest $request
     * @param CaptchaBuilder $captchaBuilder
     * @return mixed
     */
    public function store(CaptchaRequest $request, CaptchaBuilder $captchaBuilder)
    {
        $key = 'Captcha-' . str_random('15');
        $phone = $request->phone;

        $captcha = $captchaBuilder->build();

        $expired_at = now()->addMinutes(2);

        \Cache::put($key, ['phone' => $phone, 'code' => $captcha->getPhrase()], $expired_at);

        $result = $result = [
            'captcha_key' => $key,
            'expired_at' => $expired_at->toDateTimeString(),
            'captcha_image_content' => $captcha->inline(),
        ];

        return $this->response->array($result)->setStatusCode(201);
    }
}
