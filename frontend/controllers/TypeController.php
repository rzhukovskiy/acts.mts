<?php

    namespace frontend\controllers;

    use common\models\search\TypeSearch;
    use common\models\User;
    use Yii;
    use yii\filters\AccessControl;
    use yii\web\Controller;
    use common\models\Type;
    use yii\web\NotFoundHttpException;
    use yii\web\UploadedFile;
    use yii\filters\VerbFilter;

    class TypeController extends Controller
    {
        /**
         * @inheritdoc
         */
        public function behaviors()
        {
            return [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
                'access' => [
                    'class' => AccessControl::className(),
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => [User::ROLE_ADMIN],
                        ],
                        [
                            'actions' => ['list'],
                            'allow' => true,
                            'roles' => [User::ROLE_WATCHER,User::ROLE_MANAGER],
                        ],
                    ],
                ],
            ];
        }

        /**
         * Lists all Mark models.
         * @return mixed
         */
        public function actionList()
        {
            $newTypeModel = new Type();
            $searchModel = new TypeSearch();

            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            $dataProvider->pagination = false;

            if ( $newTypeModel->load( Yii::$app->request->post() ) ) {
                $newTypeModel->imageFile = UploadedFile::getInstance( $newTypeModel, 'imageFile' );

                if ($newTypeModel->save()) {
                    $newTypeModel->upload();
                    Yii::$app->session->setFlash( 'imageController_createForm', 'Model saved&' );

                    $this->redirect( [ 'list' ] );
                } else
                    Yii::$app->session->setFlash( 'imageController_createForm', 'Something went wrong! Can not upload file or save model!' );
            }

            return $this->render( 'list', [
                'dataProvider' => $dataProvider,
                'newTypeModel' => $newTypeModel,
                'searchModel' => $searchModel,
                'admin' => Yii::$app->user->can(User::ROLE_ADMIN),
            ] );
        }

        /**
         * Updates an existing Type model.
         * If update is successful, the browser will be redirected to the 'view' page.
         *
         * @param integer $id
         * @return mixed
         */
        public function actionUpdate( $id )
        {
            $model = $this->findModel( $id );

            if ( $model->load( Yii::$app->request->post() ) ) {
                $model->imageFile = UploadedFile::getInstance( $model, 'imageFile' );
                //Нет свойства image была ошибка сохранения
                //$model->image = $model->imageFile->baseName . '.' . $model->imageFile->extension;
                if($model->save()) {
                    $model->upload();

                    return $this->redirect( [ 'list' ] );
                }
            } else {
                return $this->render( 'update', [
                    'model' => $model,
                ] );
            }
        }

        /**
         * Deletes an existing Mark model.
         * If deletion is successful, the browser will be redirected to the 'index' page.
         *
         * @param integer $id
         * @return mixed
         */
        public function actionDelete($id)
        {
            $this->findModel($id)->delete();

            return $this->redirect(['list']);
        }

        /**
         * Finds the Type model based on its primary key value.
         * If the model is not found, a 404 HTTP exception will be thrown.
         * @param integer $id
         * @return Type the loaded model
         * @throws NotFoundHttpException if the model cannot be found
         */
        protected function findModel( $id )
        {
            if ( ( $model = Type::findOne( $id ) ) !== null )
                return $model;
            else
                throw new NotFoundHttpException( 'The requested page does not exist.' );
        }
    }
