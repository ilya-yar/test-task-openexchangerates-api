<?php

namespace app\models;

use yii\db\ActiveRecord;

class Ingredient extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return 'ingredient';
    }
}