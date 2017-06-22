<?php

namespace common\models;

use yii;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

/**
 * This is the model class for table "email".
 *
 * @property integer $id
 * @property string $name
 * @property integer $type
 * @property string $title
 * @property string $text
 */
class Email extends ActiveRecord
{

    /**
     * @var UploadedFile
     */
    public $files;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%email}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'text', 'title'], 'required'],
            [['type'], 'integer'],
            [['text'], 'string', 'max' => 5000],
            [['name', 'title'], 'string', 'max' => 255],
            [['files'], 'file', 'skipOnEmpty' => true, 'maxFiles' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название шаблона',
            'type' => 'Тип компаний',
            'title' => 'Заголовок письма',
            'text' => 'Текс письма',
            'files' => 'Вложения',
        ];
    }

    public function upload()
    {
        $filePath = \Yii::getAlias('@webroot/files/email/' . $this->id . '/');

        if (!file_exists(\Yii::getAlias('@webroot/files/'))) {
            mkdir(\Yii::getAlias('@webroot/files/'), 0775);
        }

        if (!file_exists(\Yii::getAlias('@webroot/files/email/'))) {
            mkdir(\Yii::getAlias('@webroot/files/email/'), 0775);
        }

        if (!file_exists(\Yii::getAlias('@webroot/files/email/' . $this->id . '/'))) {
            mkdir(\Yii::getAlias('@webroot/files/email/' . $this->id . '/'), 0775);
        }

        foreach ($this->files as $file) {

                if (!file_exists($filePath . $file->baseName . '.' . $file->extension)) {
                    $file->saveAs($filePath . $file->baseName . '.' . $file->extension);
                } else {

                    $filename = $filePath . $file->baseName . '.' . $file->extension;
                    $i = 1;

                    while (file_exists($filename)) {
                        $filename = $filePath . $file->baseName . '(' . $i . ').' . $file->extension;
                        $i++;
                    }

                    $file->saveAs($filename);

                }

        }

        return true;
    }

}
