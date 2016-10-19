<?php

namespace common\models;

use common\traits\JsonTrait;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\Html;
use yii\web\UploadedFile;

/**
 * This is the model class for table "monthly_act".
 *
 * @property integer $id
 * @property string $client_id
 * @property string $type_id
 * @property integer $profit
 * @property integer $payment_status
 * @property integer $payment_date
 * @property integer $act_status
 * @property string $act_date
 * @property array $img
 * @property boolean $is_partner
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
class MonthlyAct extends \yii\db\ActiveRecord
{
    use JsonTrait;

    const PAYMENT_STATUS_NOT_DONE = 0;
    const PAYMENT_STATUS_DONE = 1;

    const ACT_STATUS_NOT_SIGNED = 0;
    const ACT_STATUS_SEND_SCAN = 1;
    const ACT_STATUS_SEND_ORIGIN = 2;
    const ACT_STATUS_SIGNED_SCAN = 3;
    const ACT_STATUS_DONE = 4;

    const ACT_WIDTH = 1024;
    const ACT_HEIGHT = 768;


    public static $paymentStatus = [
        self::PAYMENT_STATUS_NOT_DONE => 'Не оплачен',
        self::PAYMENT_STATUS_DONE     => 'Оплачен'
    ];

    public static $actStatus = [
        self::ACT_STATUS_NOT_SIGNED  => 'Не подписан',
        self::ACT_STATUS_SEND_SCAN   => 'Отправлен скан',
        self::ACT_STATUS_SEND_ORIGIN => 'Отправлен оригинал',
        self::ACT_STATUS_SIGNED_SCAN => 'Подписан скан',
        self::ACT_STATUS_DONE        => 'Исполнен'
    ];

    /**
     * @var UploadedFile
     */
    public $image;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'monthly_act';
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
                    'profit',
                    'payment_status',
                    'act_status',
                    'is_partner',
                    'created_at',
                    'updated_at'
                ],
                'integer'
            ],
            [['img', 'act_date', 'payment_date'], 'string', 'on' => 'default'],
            [['image'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg', 'on' => 'default'],
            [
                [
                    'act_comment',
                    'act_send_date',
                    'act_client_get_date',
                    'act_we_get_date',
                    'payment_comment',
                    'payment_estimate_date'
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
            'profit'                => 'Сумма',
            'payment_status'        => 'Статус оплаты',
            'payment_date'          => 'Дата оплаты',
            'act_status'            => 'Статус акта',
            'act_date'              => 'Дата акта',
            'img'                   => 'Сканы акта',
            'act_comment'           => 'Комментарии к акту',
            'act_send_date'         => 'Дата отправления акта по почте',
            'act_client_get_date'   => 'Предполагаемая дата получения клиентом',
            'act_we_get_date'       => 'Предполагаемая дата получения акта нами',
            'payment_comment'       => 'Комментарии к оплате',
            'payment_estimate_date' => 'Дата предполагаемой оплаты',
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(Company::className(), ['id' => 'client_id']);
    }

    /**
     * @inheritdoc
     * @return \common\models\query\MonthlyActQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\MonthlyActQuery(get_called_class());
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

        if (!empty($this->payment_date) && $this->payment_status == MonthlyAct::PAYMENT_STATUS_NOT_DONE) {
            $this->payment_status = MonthlyAct::PAYMENT_STATUS_DONE;
        }

        return parent::beforeSave($insert);
    }

    /**
     * @param bool $allDate
     * @param bool $idCompany
     * @return array
     */
    static public function getPartnerAct($allDate = false, $idCompany = false)
    {
        $partnerAct =
            Act::find()
                ->select('partner_id as company_id,service_type,SUM(expense) as profit')
                ->addSelect(new Expression('date_format(FROM_UNIXTIME(served_at), "%Y-%m-00") as date'))
                ->addSelect(new Expression('1 as is_partner'))
                ->from('act');
        if (!$allDate) {
            $partnerAct = $partnerAct->where([
                "date_format(FROM_UNIXTIME(served_at), '%Y%m')" => date('Ym',
                    strtotime('-1 month'))
            ]);
        } else {
            $partnerAct = $partnerAct->where([
                "<=",
                "date_format(FROM_UNIXTIME(served_at), '%Y%m')",
                date('Ym', strtotime('-1 month'))
            ]);
        }
        $partnerAct = $partnerAct->groupBy('partner_id,service_type,date')->asArray()->all();

        return $partnerAct;
    }

    /**
     * @param bool $allDate
     * @param bool $idCompany
     * @return array
     */
    static public function getClientAct($allDate = false, $idCompany = false)
    {
        $clientAct =
            Act::find()
                ->select('client_id as company_id,service_type,SUM(income) as profit')
                ->addSelect(new Expression('date_format(FROM_UNIXTIME(served_at), "%Y-%m-00") as date'))
                ->addSelect(new Expression('0 as is_partner'))
                ->from('act');
        if (!$allDate) {
            $clientAct = $clientAct->where([
                "date_format(FROM_UNIXTIME(served_at), '%Y%m')" => date('Ym',
                    strtotime('-1 month'))
            ]);
        } else {
            $clientAct = $clientAct->where([
                "<=",
                "date_format(FROM_UNIXTIME(served_at), '%Y%m')",
                date('Ym', strtotime('-1 month'))
            ]);
        }

        $clientAct = $clientAct->groupBy('client_id,service_type,date')->asArray()->all();

        return $clientAct;
    }

    /**
     * @throws \yii\base\ErrorException
     */
    public function uploadImage()
    {
        if ($this->image) {
            $image = \Yii::$app->image->load($this->image->tempName);
            /**
             * @var $image \yii\image\drivers\Image
             */
            $img = (!$this->img) ? [] : $this->img;
            $count = count($this->img);
            $imageDir = '/files/monthly-check/' . $this->act_date . '/';
            $imageName = $imageDir . $this->id . '_' . ($count + 1) . '.' . $this->image->extension;
            $imageDir = \Yii::getAlias('@webroot' . $imageDir);
            if (!is_dir($imageDir)) {
                mkdir($imageDir, '0777', true);
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
                        'href'  => \yii\helpers\Url::to([
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
            self::ACT_STATUS_NOT_SIGNED  => 'bg-danger',
            self::ACT_STATUS_SEND_SCAN   => 'bg-warning',
            self::ACT_STATUS_SEND_ORIGIN => 'bg-warning',
            self::ACT_STATUS_SIGNED_SCAN => 'bg-warning',
            self::ACT_STATUS_DONE        => 'bg-success'
        ];

        return $actStatus[$status];
    }

}
