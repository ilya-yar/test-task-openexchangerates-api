<?php

namespace app\models;

use yii\db\ActiveRecord;

class IngredientType extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return 'ingredient_type';
    }
}