<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "auth_rule".
 *
 * @property string $name
 * @property resource $data
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property AuthItem[] $authItems
 */
class AuthRule extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'auth_rule';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name', 'data'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Name',
            'data' => 'Data',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItems()
    {
        return $this->hasMany(AuthItem::className(), ['rule_name' => 'name']);
    }
}
