<?php

namespace app\modules\api\controllers;

use app\models\Account;
use app\services\OutputService;
use app\services\TransferService;
use yii\web\Controller;

/**
 * Account controller for the `api` module
 */
class AccountController extends Controller
{
    /**
     * Open account
     * @return string
     */
    public function actionOpen()
    {
        $user_id = \Yii::$app->request->get('user_id');
        if (!$user_id || !is_numeric($user_id)) {
            return OutputService::fail('user_id error');
        }
        $ret = Account::open($user_id);
        if (!$ret) {
            return OutputService::fail('system error, try again later');
        }
        return OutputService::success(['card_id' => $ret->card_id]);
    }

    /**
     * Close account
     * @return string
     */
    public function actionClose()
    {
        $user_id = \Yii::$app->request->get('user_id');
        $card_id = \Yii::$app->request->get('card_id');
        if (!$user_id || !is_numeric($user_id)) {
            return OutputService::fail('user_id error');
        }
        if (!$card_id || strlen($card_id) != 16) {
            return OutputService::fail('card_id error');
        }
        try {
            $ret = Account::close($user_id, $card_id);
            if (!$ret) {
                return OutputService::fail('no record');
            }
        } catch (\Exception $e) {
            return OutputService::fail($e->getMessage());
        }

        return OutputService::success();
    }

    /**
     * Query balance
     * @return string
     */
    public function actionBalance() {
        $card_id = \Yii::$app->request->get('card_id');
        if (!$card_id || strlen($card_id) != 16) {
            return OutputService::fail('card_id error');
        }

        $ret = Account::findOne(compact('card_id'));
        if (!$ret) {
            return OutputService::fail('no record');
        }

        return OutputService::success(['balance' => $ret->balance]);
    }

    /**
     * Withdraw money
     * @return string
     */
    public function actionWithdraw() {
        $card_id = \Yii::$app->request->get('card_id');
        $amount = \Yii::$app->request->get('amount');
        if (!$card_id || strlen($card_id) != 16) {
            return OutputService::fail('card_id error');
        }
        if (!$amount || !is_numeric($amount) || $amount != sprintf('%.2f', $amount)) {
            return OutputService::fail('amount error');
        }

        try {
            $ret = Account::withdraw($card_id, $amount);
            if (!$ret) {
                return OutputService::fail();
            }
        } catch (\Exception $e) {
            return OutputService::fail($e->getMessage());
        }

        return OutputService::success(['balance' => $ret->balance]);
    }

    /**
     * Deposit money
     * @return string
     */
    public function actionDeposit() {
        $card_id = \Yii::$app->request->get('card_id');
        $amount = \Yii::$app->request->get('amount');
        if (!$card_id || strlen($card_id) != 16) {
            return OutputService::fail('card_id error');
        }
        if (!$amount || !is_numeric($amount) || $amount != sprintf('%.2f', $amount)) {
            return OutputService::fail('amount error');
        }

        try {
            $ret = Account::deposit($card_id, $amount);
            if (!$ret) {
                return OutputService::fail();
            }
        } catch (\Exception $e) {
            return OutputService::fail($e->getMessage());
        }

        return OutputService::success(['balance' => $ret->balance]);
    }

    /**
     * Transfer money
     * @return string
     */
    public function actionTransfer() {
        $from_card_id = \Yii::$app->request->get('from_card_id');
        $to_card_id = \Yii::$app->request->get('to_card_id');
        $amount = \Yii::$app->request->get('amount');
        if (!$from_card_id || strlen($from_card_id) != 16) {
            return OutputService::fail('from_card_id error');
        }
        if (!$to_card_id || strlen($to_card_id) != 16) {
            return OutputService::fail('to_card_id error');
        }
        if ($from_card_id == $to_card_id) {
            return OutputService::fail('same card forbidden');
        }
        if (!$amount || !is_numeric($amount) || $amount != sprintf('%.2f', $amount)) {
            return OutputService::fail('amount error');
        }

        try {
            $ret = TransferService::transfer($from_card_id, $to_card_id, $amount);
            if (!$ret) {
                return OutputService::fail();
            }
        } catch (\Exception $e) {
            return OutputService::fail($e->getMessage());
        }

        return OutputService::success($ret);
    }
}
