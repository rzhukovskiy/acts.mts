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
                [ [ 'name' ], 'required' ],
                [ [ 'name' ], 'string', 'max' => 255 ],
                [ [ 'name' ], 'unique' ],
                [ [ 'image'], 'string', 'max' => 150 ],
                [ [ 'imageFile' ], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg' ],
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
            if ( $this->validate() ) {
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

        public function beforeDelete()
        {
            if ( parent::beforeDelete() ) {
                $this->deleteImage();

                return true;
            }

            return false;
        }

        public function beforeSave( $insert )
        {
            if ( parent::beforeSave( $insert ) ) {
                if ( !$this->isNewRecord ) {
                    if ( $this->getOldAttribute( 'image' ) != $this->image ) {
                        // не останавливаю если ошибка при удалени файла,
                        // надеюсь на эксепшн в вызываемой функции
                        // хотя картинка пусть валяется на сервере, главное данные заменить.
                        $this->deleteImage( $this->getOldAttribute( 'image' ) );
                    }
                }

                return true;
            }

            return false;
        }


    }
