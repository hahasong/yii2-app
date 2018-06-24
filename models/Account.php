<?php

namespace app\models;

use Yii;
use yii\db\Exception;

/**
 * This is the model class for table "account".
 *
 * @property int $id
 * @property string $card_id
 * @property double $balance
 * @property int $user_id
 */
class Account extends \yii\db\ActiveRecord
{
    // unique bank account prefix. i.e. 6330 xxxx xxxx xxxx
    const CARD_ID_PREFIX = '6330';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'account';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['card_id', 'user_id'], 'required'],
            [['balance'], 'number'],
            [['user_id'], 'integer'],
            [['card_id'], 'string', 'max' => 16],
            [['card_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'card_id' => 'Card ID',
            'balance' => 'Balance',
            'user_id' => 'User ID',
        ];
    }

    /**
     * open a new card by $user_id
     */
    public static function open($user_id) {
        $row = new self();
        $row->card_id = self::generate_card_id();
        $row->user_id = $user_id;
        return $row->save() ? $row : false;
    }

    /**
     * Generate a new card id
     * @return string
     */
    public static function generate_card_id() {
        $row = self::find()->orderBy(['card_id' => SORT_DESC])->limit(1)->one();
        if (!$row) {
            return self::CARD_ID_PREFIX . '000000000001';
        }
        $card_id = $row->card_id;
        $max_card_id = substr($card_id, 4);
        $new_card_id = sprintf('%012d', ++$max_card_id);
        return self::CARD_ID_PREFIX . $new_card_id;

    }

    /**
     * close user's $card
     */
    public static function close($user_id, $card_id) {
        $row = self::findOne([
            'user_id' => $user_id,
            'card_id' => $card_id,
        ]);
        if (!$row) {
            return false;
        }
        return $row->delete();
    }

    /**
     * withdraw
     * @throws \Yii\db\Exception
     */
    public static function withdraw($card_id, $amount) {
        $row = self::findOne([
            'card_id' => $card_id,
        ]);
        if (!$row) {
            throw new Exception('card_id not found');
        }
        if ($amount > $row->balance) {
            throw new Exception('amount over balance');
        }
        $db = self::getDb();
        $trans = $db->beginTransaction();
        try {
            $row->balance = (double)bcsub($row->balance, $amount, 2);
            $row->save();
            $trans->commit();
            return $row;
        } catch (Exception $e) {
            $trans->rollBack();
            throw $e;
        }
    }

    /**
     * deposit
     * @throws \Yii\db\Exception
     */
    public static function deposit($card_id, $amount) {
        $row = self::findOne([
            'card_id' => $card_id,
        ]);
        if (!$row) {
            throw new Exception('card_id not found');
        }
        if ($amount <= 0) {
            throw new Exception('amount error');
        }
        $db = self::getDb();
        $trans = $db->beginTransaction();
        try {
            $row->balance = (double)bcadd($row->balance, $amount, 2);
            $row->save();
            $trans->commit();
            return $row;
        } catch (Exception $e) {
            $trans->rollBack();
            throw $e;
        }
    }
}
