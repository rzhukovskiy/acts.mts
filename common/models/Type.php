<?php

    namespace common\models;

    use common\models\query\TypeQuery;
    use Yii;
    use yii\db\ActiveRecord;
    use yii\web\UploadedFile;

    /**
     * This is the model class for table "{{%type}}".
     *
     * @property integer $id
     * @property string $name
     * @property string $image
     * @property integer $time
     */
    class Type extends ActiveRecord
    {
        /**
         * @var UploadedFile
         */
        public $imageFile;

        /**
         * @inheritdoc
         */
        public static function tableName()
        {
            return '{{%type}}';
        }

        /**
         * @inheritdoc
         */
        public function rules()
        {
            return [
                [['name', 'time'], 'required'],
                [['name'], 'string', 'max' => 255],
                [['time'], 'integer'],
                [['name'], 'unique'],
                [['imageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'jpg'],
            ];
        }

        /**
         * @inheritdoc
         */
        public function attributeLabels()
        {
            return [
                'id' => 'ID',
                'name' => 'Название',
                'image' => 'Изображение',
                'imageFile' => 'Изображение',
                'time'=> 'Время мойки'
            ];
        }

        /**
         * @inheritdoc
         * @return \common\models\query\TypeQuery the active query used by this AR class.
         */
        public static function find()
        {
            return new TypeQuery( get_called_class() );
        }

        /**
         * Upload image file
         *
         * @return bool
         */
        public function upload()
        {
            if ( $this->validate() && $this->imageFile ) {
                $path = Yii::getAlias( '@webroot' ) . '/images/cars/';
                $fileName = $this->id;
                $this->imageFile->saveAs( $path . $fileName . '.jpg');

                return true;
            }

            return false;
        }

        /**
         * Remove linked file
         *
         * @param $imageName null|string
         * @return bool
         */
        protected function deleteImage( $imageName = null )
        {
            if ( is_null( $imageName ) )
                $imageName = $this->id;

            $path = Yii::getAlias( '@webroot' ) . '/images/cars/';

            return unlink( $path . $imageName . '.jpg' );
        }
    }
