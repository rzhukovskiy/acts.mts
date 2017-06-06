<?php

namespace common\models;

use yii;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

/**
 * This is the model class for table "company_state".
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $member_id
 * @property integer $author_id
 * @property integer $type
 * @property string $comment
 * @property integer $date
 */
class CompanyState extends ActiveRecord
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
        return '{{%company_state}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'member_id', 'author_id', 'type', 'comment', 'date'], 'required'],
            [['company_id', 'member_id', 'author_id', 'type'], 'integer'],
            [['comment', 'date'], 'string'],
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
            'company_id' => 'Компания',
            'member_id' => 'Сотрудник клиент',
            'author_id' => 'Наш сотрудник',
            'type' => 'Формат общения',
            'comment' => 'Комментарий',
            'date' => 'Дата общения',
            'files' => 'Вложения',
        ];
    }

    public function beforeSave($insert)
    {
        // переводим дату в нужный формат
        $this->date = strtotime($this->date);

        return parent::beforeSave($insert);
    }

    public function upload()
    {
        $filePath = \Yii::getAlias('@webroot/files/attaches/' . $this->company_id . '/');

        if (!file_exists(\Yii::getAlias('@webroot/files/'))) {
            mkdir(\Yii::getAlias('@webroot/files/'), 0775);
        }

        if (!file_exists(\Yii::getAlias('@webroot/files/attaches/'))) {
            mkdir(\Yii::getAlias('@webroot/files/attaches/'), 0775);
        }

        if (!file_exists(\Yii::getAlias('@webroot/files/attaches/' . $this->company_id . '/'))) {
            mkdir(\Yii::getAlias('@webroot/files/attaches/' . $this->company_id . '/'), 0775);
        }

        foreach ($this->files as $file) {

            if($file->baseName != 'attaches.zip') {

                if (!file_exists($filePath . $this->id . '-' . $file->baseName . '.' . $file->extension)) {
                    $file->saveAs($filePath . $this->id . '-' . $file->baseName . '.' . $file->extension);
                } else {

                    $filename = $filePath . $this->id . '-' . $file->baseName . '.' . $file->extension;
                    $i = 1;

                    while (file_exists($filename)) {
                        $filename = $filePath . $this->id . '-' . $file->baseName . '(' . $i . ').' . $file->extension;
                        $i++;
                    }

                    $file->saveAs($filename);

                }

            }

        }

        return true;
    }

}
