<?php
/**
 * Author: lf
 * Blog: https://blog.feehi.com
 * Email: job@feehi.com
 * Created at: 2018-02-24 23:42
 */
use yii\widgets\DetailView;

/** @var $model backend\models\form\Rbac */
?>
<?=DetailView::widget([
    'model' => $model,
    'attributes' => [
        'name',
        'description',
        'sort',
        [
            'attribute' => 'permissions',
            'format' => 'raw',
            'value' => function($model){
                $str = '';
                foreach ($model->getPermissionsByGroup('form') as $key => $value){
                    foreach ($value as $k => $val){
                        foreach ($val as $v) {
                             $str .= $v['description'] . '<br>';
                        }
                    }
                }
                return $str;
            }
        ]
    ],
])?>
