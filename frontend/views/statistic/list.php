<?php
    use common\models\Company;

    /**
     * @var $this yii\web\View
     * @var $type null|string
     */

    echo $this->render( '_tabs' );
?>
<h1>statistic/list</h1>

<p>
    <?php
        echo $this->render( Company::$listType[ $type ][ 'en' ] . '/_list', [
            'dataProvider' => null,
        ] );
    ?>
</p>
