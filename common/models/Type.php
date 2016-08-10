<?php

    namespace common\models;

    use common\models\query\TypeQuery;
    use Yii;
    use yii\web\UploadedFile;

    /**
     * This is the model class for table "{{%type}}".
     *
     * @property integer $id
     * @property string $name
     * @property string $image
     */
    class Type extends \yii\db\ActiveRecord
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
                [ [ 'image' ], 'string', 'max' => 45 ],
                [ [ 'name' ], 'unique' ],
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
                $fileName = $this->image;
                $this->imageFile->saveAs( $path . $fileName );

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
                $imageName = $this->image;

            $path = Yii::getAlias( '@webroot' ) . '/images/cars/';

            return unlink( $path . $imageName );
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
