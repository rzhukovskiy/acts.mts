<?php
    use common\models\Company;
    /**
     * @var $this yii\web\View
     * @var $type null|integer
     */
    echo $this->render( '_tabs' );
?>
    <h1>archive/error</h1>

    <p>
        You may change the content of this page by modifying
        the file <code><?= __FILE__; ?></code>.
    </p>
<?php
    echo $this->render( Company::$listType[ $type ][ 'en' ] . '/_list', [
        'dataProvider' => null,
    ] );