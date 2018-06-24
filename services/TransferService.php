<?php
/**
 * Created by PhpStorm.
 * User: starry
 * Date: 2018/6/24
 * Time: 下午23:50
 */
namespace app\services;

use app\models\Account;
use app\models\TransferLog;
use GuzzleHttp\Client;
use Yii;
use yii\db\Exception;

class TransferService extends \yii\base\BaseObject
{
    const DAILY_TRANSFER_LIMIT = 10000;
    const CROSS_TRANSFER_CHARGE = 100;
    const OBTAIN_APPROVAL_URL = 'http://handy.travel/test/success.json';
    /**
     * @param $from_card_id
     * @param $to_card_id
     * @param $amount
     * @throws Exception
     */
    public static function transfer($from_card_id, $to_card_id, $amount) {
        $from_account = Account::findOne(['card_id' => $from_card_id]);
        if ($from_card_id == $to_card_id) {
            throw new Exception('same card forbidden');
        }
        if (!$from_account) {
            throw new Exception('from_card_id not found');
        }
        $to_account = Account::findOne(['card_id' => $to_card_id]);
        if (!$to_account) {
            throw new Exception('to_card_id not found');
        }
        if ($amount > $from_account->balance) {
            throw new Exception('amount over balance');
        }

        $quota_used = TransferLog::getDailyTransferAmount($from_card_id, date('Y-m-d'));
        if(bcadd($amount, $quota_used, 2) > self::DAILY_TRANSFER_LIMIT) {
            throw new Exception('amount over daily limit');
        }

        $charge = 0;
        if ($from_account->user_id != $to_account->user_id) {
            $charge = self::CROSS_TRANSFER_CHARGE;
            $is_approved = self::obtainApproval();
            if (!$is_approved) {
                throw new Exception('transfer rejected');
            }
        }
        if (bcadd($amount, $charge, 2) > $from_account->balance) {
            throw new Exception('insufficient balance');
        }

        //start transfer
        $trans = Yii::$app->getDb()->beginTransaction();
        try {
            $from_account->balance = (double)bcsub(bcsub($from_account->balance, $charge, 2), $amount, 2);
            $to_account->balance = (double)bcadd($to_account->balance, $amount, 2);
            $from_account->save();
            $to_account->save();

            $transfer_log = new TransferLog();
            $transfer_log->from_card_id = $from_card_id;
            $transfer_log->to_card_id = $to_card_id;
            $transfer_log->amount = $amount;
            $transfer_log->charge = $charge;
            $transfer_log->save();

            $trans->commit();
        } catch (Exception $e) {
            $trans->rollBack();
            throw $e;
        }
        return [
            'balance' => $from_account->balance,
            'charge' => $charge,
        ];
    }

    /**
     * @return bool
     */
    public  static function obtainApproval() {
        $client = new Client();
        $res = $client->request('GET', self::OBTAIN_APPROVAL_URL);
        $status_code = $res->getStatusCode();
        if ($status_code == 200) {
            $data = $res->getBody()->getContents();
            $data = json_decode($data, true);
            if (isset($data['status']) && $data['status'] == 'success') {
                return true;
            }

        }
        return false;

    }
}