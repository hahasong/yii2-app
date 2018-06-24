<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "transfer_log".
 *
 * @property int $id
 * @property string $from_card_id
 * @property string $to_card_id
 * @property double $amount
 * @property double $charge
 * @property string $created_time
 */
class TransferLog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'transfer_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['from_card_id', 'to_card_id'], 'required'],
            [['amount', 'charge'], 'number'],
            [['created_time'], 'safe'],
            [['from_card_id', 'to_card_id'], 'string', 'max' => 16],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'from_card_id' => 'From Card ID',
            'to_card_id' => 'To Card ID',
            'amount' => 'Amount',
            'charge' => 'Charge',
            'created_time' => 'Created Time',
        ];
    }

    /**
     * @param $card_id
     * @param $date Y-m-d
     * @return int|mixed
     */
    public static function getDailyTransferAmount($card_id, $date) {
        $res = self::find()->select('SUM(amount) AS sum_amount')
            ->where(['from_card_id' => $card_id])
            ->andWhere(['>=', 'created_time', date('Y-m-d 00:00:00', strtotime($date))])
            ->andWhere(['<', 'created_time', date('Y-m-d 00:00:00', strtotime('next day', strtotime($date)))])
            ->asArray()
            ->one();
        if ($res) {
            return $res['sum_amount'] ?: 0;
        }
        return 0;
    }
}
