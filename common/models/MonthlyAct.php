<?php

namespace common\models;

use common\models\monthlyAct\DisinfectMonthlyAct;
use common\models\monthlyAct\ServiceMonthlyAct;
use common\models\monthlyAct\TiresMonthlyAct;
use common\models\monthlyAct\WashMonthlyAct;
use common\models\query\MonthlyActQuery;
use common\traits\JsonTrait;
use yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\web\UploadedFile;


/**
 * This is the model class for table "monthly_act".
 *
 * @property integer $id
 * @property integer $client_id
 * @property integer $type_id
 * @property integer $service_id
 * @property integer $act_id
 * @property string $number
 * @property integer $payment_status
 * @property integer $payment_date
 * @property integer $act_status
 * @property string $act_date
 * @property array $img
 * @property boolean $is_partner
 * @property string $post_number
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property string $act_comment
 * @property string $act_send_date
 * @property string $act_client_get_date
 * @property string $act_we_get_date
 * @property string $payment_comment
 * @property string $payment_estimate_date
 *
 * @property Company $client
 */
class MonthlyAct extends ActiveRecord
{
    use JsonTrait;

    const PAYMENT_STATUS_NOT_DONE = 0;
    const PAYMENT_STATUS_DONE = 1;
    const PAYMENT_STATUS_CASH = 2;

    const ACT_STATUS_NOT_SIGNED = 0;
    const ACT_STATUS_SEND_SCAN = 1;
    const ACT_STATUS_SEND_ORIGIN = 2;
    const ACT_STATUS_SIGNED_SCAN = 3;
    const ACT_STATUS_DONE = 4;
    const ACT_STATUS_EMPTY = 5;

    const NOT_PARTNER = 0;
    const PARTNER = 1;

    const ACT_WIDTH = 1024;
    const ACT_HEIGHT = 768;


    public static $paymentStatus = [
        self::PAYMENT_STATUS_NOT_DONE => 'Не оплачен',
        self::PAYMENT_STATUS_DONE     => 'Оплачен',
        self::PAYMENT_STATUS_CASH     => 'Наличка',
    ];

    public static $actStatus = [
        self::ACT_STATUS_NOT_SIGNED  => 'Не подписан',
        self::ACT_STATUS_SEND_SCAN   => 'Отправлен скан',
        self::ACT_STATUS_SIGNED_SCAN => 'Подписан скан',
        self::ACT_STATUS_SEND_ORIGIN => 'Отправлен оригинал',
        self::ACT_STATUS_DONE        => 'Подписан',
        self::ACT_STATUS_EMPTY       => 'Без акта',
    ];

    public static function passActStatus($currentStatus)
    {
        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            return self::$actStatus;
        } else {
            return array_slice(self::$actStatus, $currentStatus);
        }
    }

    public static function passPaymentStatus($currentStatus)
    {
        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            return self::$paymentStatus;
        } else {
            return [$currentStatus => self::$paymentStatus[$currentStatus]];
        }
    }

    /**
     * @var UploadedFile
     */
    public $image;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%monthly_act}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['client_id', 'type_id', 'act_date'], 'required', 'on' => 'default'],
            [
                [
                    'client_id',
                    'type_id',
                    'service_id',
                    'act_id',
                    'payment_status',
                    'act_status',
                    'is_partner',
                    'created_at',
                    'updated_at'
                ],
                'integer'
            ],
            [['img', 'act_date', 'payment_date', 'number'], 'string', 'on' => 'default'],
            [['image'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg', 'maxFiles' => 10, 'on' => 'default'],
            [
                [
                    'act_comment',
                    'act_send_date',
                    'act_client_get_date',
                    'act_we_get_date',
                    'payment_comment',
                    'payment_estimate_date',
                    'post_number'
                ],
                'string',
                'on' => 'detail'
            ],
            [
                ['client_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Company::className(),
                'targetAttribute' => ['client_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                    => 'ID',
            'client_id'             => 'Клиент',
            'type_id'               => 'Тип',
            'service_id'            => 'Услуга',
            'act_id'                => 'Базовый акт',
            'profit'                => 'Сумма',
            'number'                => 'Номер авто',
            'payment_status'        => 'Статус оплаты',
            'payment_date'          => 'Дата оплаты',
            'act_status'            => 'Статус акта',
            'act_date'              => 'Дата акта',
            'img'                   => 'Сканы акта',
            'image'                 => 'Загрузить акт',
            'act_comment'           => 'Комментарии к акту',
            'act_send_date'         => 'Дата отправления акта по почте',
            'act_client_get_date'   => 'Дата получения акта клиентом',
            'act_we_get_date'       => 'Дата получения акта нами',
            'payment_comment'       => 'Комментарии к оплате',
            'payment_estimate_date' => 'Дата предполагаемой оплаты',
            'post_number'           => 'Номер почтового отправления',
            'created_at'            => 'Created At',
            'updated_at'            => 'Updated At',
        ];
    }


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @param $attr
     */
    public function timestampToDate($attr)
    {
        if (isset($this->$attr)) {
            $this->$attr = (new \DateTime())->setTimestamp($this->$attr)->format('d-m-Y');
        }
    }

    /**
     * @param $attr
     */
    public function dateToTimestamp($attr)
    {
        if (!empty($this->$attr)) {
            $this->$attr = \DateTime::createFromFormat('d-m-Y H:i:s', $this->$attr . ' 12:00:00')->getTimestamp();
        }
    }

    public function dateFix()
    {
        return \DateTime::createFromFormat('Y-m-d', $this->act_date)->modify('+1 month')->format('Y-m-01');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(Company::className(), ['id' => 'client_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Service::className(), ['id' => 'service_id']);
    }


    /**
     * @inheritdoc
     * @return \common\models\query\MonthlyActQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MonthlyActQuery(get_called_class());
    }

    /**
     *
     */
    public function afterFind()
    {
        $this->timestampToDate('payment_date');
        $this->timestampToDate('act_send_date');
        $this->timestampToDate('act_client_get_date');
        $this->timestampToDate('act_we_get_date');
        $this->timestampToDate('payment_estimate_date');

        $this->decodeJsonFields([
            'img',
        ]);

        parent::afterFind();
    }

    /**
     * @return bool
     */
    public function beforeValidate()
    {
        $this->encodeJsonFields([
            'img',
        ]);

        return parent::beforeValidate();
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {

        if ($this->isNewRecord) {
            $this->payment_status = self::PAYMENT_STATUS_NOT_DONE;
            $this->act_status = self::ACT_STATUS_NOT_SIGNED;
        }

        $this->dateToTimestamp('payment_date');
        $this->dateToTimestamp('act_send_date');
        $this->dateToTimestamp('act_client_get_date');
        $this->dateToTimestamp('act_we_get_date');
        $this->dateToTimestamp('payment_estimate_date');
        //Если дата изменена - поменять статус
        if (count($this->getDirtyAttributes(['payment_date'])) == 1 &&
            !empty($this->payment_date) &&
            $this->payment_status == MonthlyAct::PAYMENT_STATUS_NOT_DONE
        ) {
            $this->payment_status = MonthlyAct::PAYMENT_STATUS_DONE;
        }
        //Если был изменен аттрибут
        /*
        if (count($this->getDirtyAttributes(['payment_status'])) == 1) {
            if ($this->payment_status == MonthlyAct::PAYMENT_STATUS_NOT_DONE) {
                $this->payment_date=0;
            } else {
                if (empty($this->payment_date)) {
                    $this->payment_date = time();
                }
            }
        }
        */

        return parent::beforeSave($insert);
    }

    /**
     * @param $type
     * @return bool|DisinfectMonthlyAct|ServiceMonthlyAct|TiresMonthlyAct|WashMonthlyAct
     */
    static function getRealObject($type)
    {
        switch ($type) {
            case Service::TYPE_WASH:
                return new WashMonthlyAct();
                break;
            case Service::TYPE_SERVICE:
                return new ServiceMonthlyAct();
                break;
            case Service::TYPE_TIRES:
                return new TiresMonthlyAct();
                break;
            case Service::TYPE_DISINFECT:
                return new DisinfectMonthlyAct();
                break;
        }

        return false;
    }

    /**
     * @param array $row
     * @return bool|DisinfectMonthlyAct|ServiceMonthlyAct|TiresMonthlyAct|WashMonthlyAct|static
     */
    public static function instantiate($row)
    {
        return self::getRealObject($row['type_id']);
    }


    /**
     * @throws \yii\base\ErrorException
     */
    public function uploadImage()
    {
        foreach ($this->image as $uploadImage) {

            $image = \Yii::$app->image->load($uploadImage->tempName);
            /**
             * @var $image \yii\image\drivers\Image
             */
            $img = (!$this->img) ? [] : $this->img;
            $count = count($this->img);
            $imageDir = '/files/monthly-check/' . $this->act_date . '/';
            $imageName = $imageDir . $this->id . '_' . ($count + 1) . '.' . $uploadImage->extension;
            $imageDir = \Yii::getAlias('@webroot' . $imageDir);
            if (!is_dir($imageDir)) {
                mkdir($imageDir, 0775, true);
            }
            $imagePath = \Yii::getAlias('@webroot' . $imageName);
            if ($image->resize(self::ACT_WIDTH, self::ACT_HEIGHT)->save($imagePath)) {
                $img[] = $imageName;
                $this->img = $img;
            }
        }


        return false;
    }

    /**
     * @param $deleteImage
     */
    public function deleteImage($deleteImage)
    {
        $tmpImg = [];
        if ($this->img && is_array($this->img)) {
            foreach ($this->img as $img) {
                if ($img == $deleteImage) {
                    $imagePath = \Yii::getAlias('@webroot' . $img);
                    if (is_file($imagePath)) {
                        unlink($imagePath);
                    }
                } else {
                    $tmpImg[] = $img;
                }
            }
            $this->img = $tmpImg;
        }

    }


    /**
     * Список изображений для галереи
     * @return string
     */
    public function getImageList()
    {
        $allImg = [];
        if (!$this->img) {
            return false;
        }
        foreach ($this->img as $img) {
            $imgName = explode("/", $img);
            $imgName = array_pop($imgName);
            $a = Html::tag('a', $imgName, ['class' => 'fancybox', 'rel' => 'fancybox-' . $this->id, 'href' => $img]);
            if (Yii::$app->user->can(User::ROLE_ADMIN)) {
                $a .= Html::tag('a',
                    '',
                    [
                        'class' => 'glyphicon glyphicon-remove',
                        'href'  => yii\helpers\Url::to([
                            'monthly-act/delete-image',
                            'id'  => $this->id,
                            'url' => $img
                        ])
                    ]);
            }

            $allImg[] = Html::tag('p', $a);
        }

        return implode('', $allImg);
    }

    /**
     * Список классов для статуса
     * @param $status
     * @return mixed
     */
    static function colorForStatus($status)
    {
        $actStatus = [
            self::ACT_STATUS_NOT_SIGNED  => 'monthly-act-danger',
            self::ACT_STATUS_SEND_SCAN   => 'monthly-act-warning',
            self::ACT_STATUS_SEND_ORIGIN => 'monthly-act-warning',
            self::ACT_STATUS_SIGNED_SCAN => 'monthly-act-warning',
            self::ACT_STATUS_DONE        => 'monthly-act-success',
            self::ACT_STATUS_EMPTY       => 'monthly-act-info',
        ];

        return $actStatus[$status];
    }
    
    static function payDis($val)
    {
        
        $currentUser = Yii::$app->user->identity;
        if (($val==1) && ($currentUser) && ($currentUser->role != User::ROLE_ADMIN)) {
            $disabled = 'disabled';
        }else{
            $disabled = 'false';
        }
        return $disabled;
    }
    
    static function actDis($val)
    {        
        $currentUser = Yii::$app->user->identity;
        if ($val == 4 && $currentUser && $currentUser->role != User::ROLE_ADMIN) {
            $disabled = 'disabled';
        }else{
            $disabled = 'false';
        }
        return $disabled;
    }

    /**
     * @param $status
     * @return mixed
     */
    static function colorForPaymentStatus($status)
    {
        $paymentStatus = [
            self::PAYMENT_STATUS_DONE     => 'monthly-act-success',
            self::PAYMENT_STATUS_NOT_DONE => 'monthly-act-danger',
            self::PAYMENT_STATUS_CASH     => 'monthly-act-info',
        ];

        return $paymentStatus[$status];
    }
}
