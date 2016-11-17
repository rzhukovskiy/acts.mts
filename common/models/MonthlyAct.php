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
 * @property integer $client_id
 * @property integer $type_id
 * @property integer $service_id
 * @property integer $act_id
 * @property integer $profit
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

    const NOT_PARTNER = 0;
    const PARTNER = 1;

    const ACT_WIDTH = 1024;
    const ACT_HEIGHT = 768;


    public static $paymentStatus = [
        self::PAYMENT_STATUS_NOT_DONE => 'Не оплачен',
        self::PAYMENT_STATUS_DONE     => 'Оплачен'
    ];

    public static $actStatus = [
        self::ACT_STATUS_NOT_SIGNED  => 'Не подписан',
        self::ACT_STATUS_SEND_SCAN   => 'Отправлен скан',
        self::ACT_STATUS_SIGNED_SCAN => 'Подписан скан',
        self::ACT_STATUS_SEND_ORIGIN => 'Отправлен оригинал',
        self::ACT_STATUS_DONE        => 'Подписан'
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
                    'profit',
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
     * Проверка, существует ли уже такой акт, перед сохранением
     * @return bool
     */
    public function isUnique()
    {
        $checkedMonthlyAct =
            MonthlyAct::find()
                ->andWhere(['client_id' => $this->client_id])
                ->andWhere(['act_date' => $this->act_date])
                ->andWhere(['type_id' => $this->type_id]);
        if ($this->type_id == Service::TYPE_SERVICE) {
            $checkedMonthlyAct->andWhere(['act_id' => $this->act_id]);
        }
        if ($this->type_id == Service::TYPE_DISINFECT) {
            $checkedMonthlyAct->andWhere(['service_id' => $this->service_id]);
        }

        return !$checkedMonthlyAct->exists();
    }

    /**
     * @param $act \common\models\Act
     */
    public static function redoMonthlyAct($act)   {

        $clientId = $act->client_id;
        $partnerId = $act->partner_id;
        $time = $act->served_at;
        $serviceType = $act->service_type;

        if(!self::isNeedNewAct($time, $serviceType)){
            return false;
        }

        $partnerAct = $clientAct = [];

        //удаляем все месячный акты, где компания - партнер, за заданную дату, заданного типа и не закрытые
        $partnerMonthlyAct = MonthlyAct::find()->byPartner($time, $partnerId)->byType($serviceType)->all();
        if (isset($partnerMonthlyAct)) {
            foreach ($partnerMonthlyAct as $monthlyAct) {
                if ($monthlyAct->act_status != MonthlyAct::ACT_STATUS_DONE) {
                    $monthlyAct->delete();
                }
            }
            $partnerAct = MonthlyAct::getPartnerAct($time, $partnerId, $serviceType);
        }
        //удаляем все месячный акты, где компания - клиент, за заданную дату, заданного типа и не закрытые
        $clientMonthlyAct = MonthlyAct::find()->byClient($time, $clientId)->byType($serviceType)->all();
        if (isset($clientMonthlyAct)) {
            foreach ($clientMonthlyAct as $monthlyAct) {
                if ($monthlyAct->act_status != MonthlyAct::ACT_STATUS_DONE) {
                    $monthlyAct->delete();
                }
            }
            $clientAct = MonthlyAct::getClientAct($time, $clientId, $serviceType);
        }

        $allAct = array_merge($partnerAct, $clientAct);
        if ($allAct) {
            self::massSaveAct($allAct, true);
        }
    }

    /**
     * @param bool $date
     * @param bool $idCompany
     * @param bool $type
     * @return $this|array
     */
    static public function getPartnerAct($date = false, $idCompany = false, $type = false)
    {
        $wash = [];
        $tires = [];
        $service = [];
        $disinfection = [];

        $partnerAct =
            Act::find()
                ->select('partner_id as company_id,service_type')
                ->addSelect(new Expression('date_format(FROM_UNIXTIME(served_at), "%Y-%m-00") as date'))
                ->addSelect(new Expression('1 as is_partner'));


        if ($idCompany) {
            $partnerAct = $partnerAct->andWhere(['partner_id' => $idCompany]);
        }

        //Мойки
        if (!$type || $type == Service::TYPE_WASH) {
            $wash = clone $partnerAct;
            $wash->addSelect('SUM(expense) as profit')->andWhere([
                'in',
                'service_type',
                [Service::TYPE_WASH]
            ])->byMonthlyDate($date)->groupBy('partner_id,service_type,date');
            //var_dump($washAndService->createCommand()->rawSql);
            $wash = $wash->asArray()->all();
        }
        //Сервисы
        if (!$type || $type == Service::TYPE_TIRES) {
            $tires = clone $partnerAct;
            $tires->addSelect('SUM(expense) as profit')->andWhere([
                'in',
                'service_type',
                [Service::TYPE_TIRES]
            ])->byMonthlyDate($date)->groupBy('partner_id,service_type,date');
            //var_dump($washAndService->createCommand()->rawSql);
            $tires = $tires->asArray()->all();
        }
        if (!$type || $type == Service::TYPE_SERVICE) {
            //Шиномонтажи
            $service = clone $partnerAct;
            $service->addSelect(['expense as profit', 'number', 'id as act_id'])
                ->andWhere(['service_type' => Service::TYPE_SERVICE])
                ->byMonthlyDate($date)
                ->orderBy(['company_id' => SORT_DESC]);
            //var_dump($tires->createCommand()->rawSql);
            $service = $service->asArray()->all();
        }
        if (!$type || $type == Service::TYPE_DISINFECT) {
            //Дезинфекция
            $disinfection = clone $partnerAct;
            $disinfection->addSelect(new Expression('SUM(scopes.price*scopes.amount) as profit'))
                ->addSelect('scopes.service_id as service_id')
                ->joinWith('scopes scopes')
                ->andWhere(['service_type' => Service::TYPE_DISINFECT])
                ->andWhere('scopes.company_id=partner_id')
                ->byMonthlyDate($date, true)
                ->groupBy('partner_id,date,service_id')
                ->orderBy(['service_id' => SORT_DESC]);
            //var_dump($disinfection->createCommand()->rawSql);
            $disinfection = $disinfection->asArray()->all();
        }
        $partnerAct = array_merge($wash, $tires, $service, $disinfection);

        return $partnerAct;
    }

    /**
     * @param bool $date
     * @param bool $idCompany
     * @param bool $type
     * @return $this|array
     */
    static public function getClientAct($date = false, $idCompany = false, $type = false)
    {
        $wash = [];
        $tires = [];
        $service = [];
        $disinfection = [];

        $clientAct =
            Act::find()
                ->select('client_id as company_id,service_type')
                ->addSelect(new Expression('date_format(FROM_UNIXTIME(served_at), "%Y-%m-00") as date'))
                ->addSelect(new Expression('0 as is_partner'));

        if ($idCompany) {
            $clientAct = $clientAct->andWhere(['client_id' => $idCompany]);
        }
        //Мойки
        if (!$type || $type == Service::TYPE_WASH) {
            $wash = clone $clientAct;
            $wash->addSelect('SUM(income) as profit')->andWhere([
                'in',
                'service_type',
                [Service::TYPE_WASH]
            ])->byMonthlyDate($date)->groupBy('client_id,service_type,date');
            //var_dump($washAndService->createCommand()->rawSql);
            $wash = $wash->asArray()->all();
        }
        //Сервисы
        if (!$type || $type == Service::TYPE_TIRES) {
            $tires = clone $clientAct;
            $tires->addSelect('SUM(income) as profit')->andWhere([
                'in',
                'service_type',
                [Service::TYPE_TIRES]
            ])->byMonthlyDate($date)->groupBy('client_id,service_type,date');
            //var_dump($washAndService->createCommand()->rawSql);
            $tires = $tires->asArray()->all();
        }
        //Шиномонтажи
        if (!$type || $type == Service::TYPE_SERVICE) {
            $service = clone $clientAct;
            $service->addSelect(['income as profit', 'number', 'id as act_id'])
                ->andWhere(['service_type' => Service::TYPE_SERVICE])
                ->byMonthlyDate($date)
                ->orderBy(['company_id' => SORT_DESC]);
            //var_dump($tires->createCommand()->rawSql);
            $service = $service->asArray()->all();
        }
        //Дезинфекция
        if (!$type || $type == Service::TYPE_DISINFECT) {
            $disinfection = clone $clientAct;
            $disinfection->addSelect(new Expression('SUM(scopes.price*scopes.amount) as profit'))
                ->addSelect('scopes.service_id as service_id')
                ->joinWith('scopes scopes')
                ->andWhere(['service_type' => Service::TYPE_DISINFECT])
                ->andWhere('scopes.company_id=client_id')
                ->byMonthlyDate($date, true)
                ->groupBy('client_id,date,service_id')
                ->orderBy(['service_id' => SORT_DESC]);
            //var_dump($disinfection->createCommand()->rawSql);
            $disinfection = $disinfection->asArray()->all();
        }

        $clientAct = array_merge($wash, $tires, $service, $disinfection);

        return $clientAct;
    }

    /**
     * МАссовое сохранение месячных актов, с опциональной проверкой, существует ли уже такой акт
     * @param $allAct
     * @param bool $checkUnique
     */
    static function massSaveAct($allAct, $checkUnique = false)
    {
        foreach ($allAct as $act) {
            $monthlyAct = new MonthlyAct();
            $monthlyAct->client_id = $act['company_id'];
            $monthlyAct->type_id = $act['service_type'];
            if (isset($act['service_id'])) {
                $monthlyAct->service_id = $act['service_id'];
            }
            if (isset($act['number'])) {
                $monthlyAct->number = $act['number'];
            }
            if (isset($act['act_id'])) {
                $monthlyAct->act_id = $act['act_id'];
            }
            $monthlyAct->profit = $act['profit'];
            $monthlyAct->is_partner = $act['is_partner'];
            $monthlyAct->act_date = $act['date'];

            if ($checkUnique && !$monthlyAct->isUnique()) {
                continue;
            }
            $monthlyAct->save();
        }
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
     * @param $date
     * @param $type
     * @return bool
     */
    private static function isNeedNewAct($date, $type)
    {
        if ($type != Company::TYPE_DISINFECT) {
            $nowDate = new \DateTime();
        } else {
            $nowDate = (new \DateTime())->modify('+2 month');
        }
        $nowDate = $nowDate->format('Y-m-00');
        $actDate = (new \DateTime())->setTimestamp($date)->format('Y-m-00');
        var_dump($actDate);
        var_dump($nowDate);
        var_dump($actDate < $nowDate);
        return $actDate < $nowDate;
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
            self::ACT_STATUS_NOT_SIGNED  => 'monthly-act-danger',
            self::ACT_STATUS_SEND_SCAN   => 'monthly-act-warning',
            self::ACT_STATUS_SEND_ORIGIN => 'monthly-act-warning',
            self::ACT_STATUS_SIGNED_SCAN => 'monthly-act-warning',
            self::ACT_STATUS_DONE        => 'monthly-act-success'
        ];

        return $actStatus[$status];
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
        ];

        return $paymentStatus[$status];
    }


}
