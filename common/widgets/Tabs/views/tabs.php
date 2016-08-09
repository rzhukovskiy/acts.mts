<?php
    /**
     * Created by PhpStorm.
     * User: ruslanzh
     * Date: 09/08/16
     * Time: 20:01
     *
     * @var $this \yii\web\View;
     * @var $items array;
     * @var $active string;
     */
?>
<ul class="maintabmenu">
    <?php
        foreach ( $items as $key => $value ) {
            $canAccess = !isset( $value[ 'role' ] ) || Yii::$app->user->checkAccess( $value[ 'role' ]);
            if ( $canAccess )
                if ( $key == $active )
                    $this->render( '_item_active', array( 'key' => $key, 'value' => $value ) );
                else
                    $this->render( '_item', array( 'key' => $key, 'value' => $value ) );
        }
    ?>
</ul><!--maintabmenu-->