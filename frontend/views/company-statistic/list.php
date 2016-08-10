<?php
    /**
     * @var $this yii\web\View
     */
    echo $this->render( '_tabs' );
?>
<h1>company-statistic/list</h1>

<p>
    You may change the content of this page by modifying
    the file <code><?= __FILE__; ?></code>.
</p>
<?php
    echo $this->render(\common\models\Company::$listType[$type]['en'] . '/_list', [
        'dataProvider' => null,
    ]);
