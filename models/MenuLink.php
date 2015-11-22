<?php

namespace yeesoft\models;

use omgdef\multilingual\MultilingualQuery;
use yeesoft\behaviors\MultilingualBehavior;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "menu_link".
 *
 * @property string $id
 * @property string $menu_id
 * @property string $link
 * @property string $label
 * @property string $parent_id
 * @property integer $alwaysVisible
 * @property string $image
 * @property integer $order
 *
 * @property Menu $menu
 */
class MenuLink extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'menu_link';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'multilingual' => [
                'class' => MultilingualBehavior::className(),
                'langForeignKey' => 'link_id',
                'tableName' => "{{%menu_link_lang}}",
                'attributes' => [
                    'label'
                ]
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'menu_id', 'label'], 'required'],
            [['order', 'alwaysVisible'], 'integer'],
            [['id', 'menu_id', 'parent_id'], 'string', 'max' => 64],
            [['link', 'label'], 'string', 'max' => 255],
            [['image'], 'string', 'max' => 128],
            [['id'], 'match', 'pattern' => '/^[a-z0-9_-]+$/', 'message' => Yii::t('yee', 'Link ID can only contain lowercase alphanumeric characters, underscores and dashes.') ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('yee', 'ID'),
            'menu_id' => Yii::t('yee','Menu'),
            'link' => Yii::t('yee', 'Link'),
            'label' => Yii::t('yee', 'Label'),
            'parent_id' => Yii::t('yee','Parent Link'),
            'alwaysVisible' => Yii::t('yee','Always Visible'),
            'image' => Yii::t('yee', 'Icon'),
            'order' => Yii::t('yee', 'Order'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMenu()
    {
        return $this->hasOne(Menu::className(), ['id' => 'menu_id'])->joinWith('translations');
    }

    /**
     * Get list of link siblings
     * @return array
     */
    public function getSiblings()
    {
        $siblings = MenuLink::find()->joinWith('translations')
            ->andFilterWhere(['like', 'menu_id', $this->menu_id])
            ->andFilterWhere(['!=', 'menu_link.id', $this->id])
            ->all();

        $list = ArrayHelper::map(
            $siblings, 'id',
            function ($array, $default) {
                return $array->label . ' [' . $array->id . ']';
            });

        return ArrayHelper::merge([NULL => Yii::t('yee','No Parent')], $list);
    }

    /**
     * @inheritdoc
     * @return MultilingualQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MultilingualQuery(get_called_class());
    }
}