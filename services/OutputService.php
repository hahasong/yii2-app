<?php
/**
 * Created by PhpStorm.
 * User: starry
 * Date: 2018/6/24
 * Time: 下午7:50
 */
namespace app\services;

use Yii;
use yii\web\Response;

class OutputService extends \yii\base\BaseObject
{
    public static function success($data = null, $msg = 'success', $code = 0) {
        return self::outputJson($code, $msg, $data ?? new \ArrayObject());
    }

    public static function fail($msg = 'fail', $code = -1, $data = null) {
        return self::outputJson($code, $msg, $data ?? new \ArrayObject());
    }

    private static function outputJson($code, $msg, $data) {
        $response = Yii::$app->getResponse();
        $response->format = Response::FORMAT_JSON;
        $response->data = compact('code', 'msg', 'data');
        return $response;
    }
}