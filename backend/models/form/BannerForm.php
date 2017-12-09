<?php
/**
 * Author: lf
 * Blog: https://blog.feehi.com
 * Email: job@feehi.com
 * Created at: 2017-12-03 22:05
 */

namespace backend\models\form;

use common\models\Options;
use yii;
use common\libs\Constants;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class BannerForm extends \Common\models\Options
{
    public $sign;

    public $img;

    public $target = Constants::TARGET_BLANK;

    public $link;

    public $sort = 0;

    public $status = Constants::Status_Enable;

    public $desc;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => yii::t('app', 'Sign'),
            'tips' => yii::t('app', 'Description'),
            'img' => yii::t('app', 'Image'),
            'target' => yii::t('app', 'Target'),
            'link' => yii::t('app', 'Jump Link'),
            'sort' => yii::t('app', 'Sort'),
            'status' => yii::t('app', 'Status'),
            'desc' => yii::t('app', 'Description'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'unique'],
            [
                ['name'],
                'match',
                'pattern' => '/^[a-zA-Z][0-9_]*/',
                'message' => yii::t('app', 'Must begin with alphabet and can only includes alphabet,_,and number')
            ],
            [['name', 'tips'], 'required'],
            [['sort', 'status'], 'integer'],
            [['sign', 'target', 'link', 'desc'], 'string'],
            [['img'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, gif, webp'],
        ];
    }

    public function scenarios()
    {
        return [
            'type' => ['name', 'group', 'tips'],
            'banner' => ['sign', 'img', 'target', 'link', 'sort', 'desc', 'status'],
        ];
    }

    public function beforeSave($insert)
    {
        $this->type = self::TYPE_BANNER;
        return parent::beforeSave($insert);
    }

    public function beforeDelete()
    {
        if($this->getScenario() == 'type'){
            if( $this->value != '[]' && $this->value != '' ){
                $this->addError('id', yii::t('app', 'This type exits banner'));
                return false;
            }
        }
        return true;
    }

    public function getBannersById($id)
    {
        $model = self::findOne(['id'=>$id, 'type'=>self::TYPE_BANNER]);
        if( $model == '' ) throw new NotFoundHttpException("Cannot find id $id");
        $banners = json_decode($model->value);
        if($banners == null) $banners = [];
        ArrayHelper::multisort($banners, 'sort');
        $models = [];
        foreach ($banners as $banner){
            $models[] = new self([
                'id' => $model->id,
                'sign' => $banner->sign,
                'img' => $banner->img,
                'target' => $banner->target,
                'desc' => $banner->desc,
                'link' => $banner->link,
                'sort' => $banner->sort,
                'status' => $banner->status,
            ]);
        }
        return $models;
    }

    public function getBannerBySign($id, $sign)
    {
        $models = $this->getBannersById($id);
        foreach ($models as $model){
            if( $model->sign == $sign ) {
                return $model;
            }
        }
        throw new NotFoundHttpException("Cannot find id $id img $sign");
    }

    public function saveBanner($id, $sign='')
    {
        $model = self::findOne($id);
        $banners = json_decode($model->value, true);
        if( $banners == null ) $banners = [];
        $insert = $sign == '' ? true : false;

        $temp = explode('\\', __CLASS__);
        $formName = end($temp);

        $upload = UploadedFile::getInstance($this, 'img');
        if ($upload !== null) {
            $uploadPath = yii::getAlias('@uploads/setting/banner/');
            if (! FileHelper::createDirectory($uploadPath)) {
                $this->addError('img', "Create directory failed " . $uploadPath);
                return false;
            }
            $fullName = $uploadPath . uniqid() . '_' . $upload->baseName . '.' . $upload->extension;
            if (! $upload->saveAs($fullName)) {
                $this->addError('img', yii::t('app', 'Upload {attribute} error: ' . $upload->error, ['attribute' => yii::t('app', 'Thumb')]) . ': ' . $fullName);
                return false;
            }
            $this->img = str_replace(yii::getAlias('@frontend/web'), '', $fullName);
            if( !$insert ){
                foreach ($banners as $banner){
                    if( $banner['sign'] == $sign ){
                        $file = yii::getAlias('@frontend/web') . $banner['img'];
                        if( file_exists($file) && is_file($file) ) unlink($file);
                    }
                    break;
                }
            }
        } else {
            foreach ($banners as $banner){
                if( $banner['sign'] == $sign ){
                    if( $this->img !== '' && count( yii::$app->getRequest()->post()[$formName] ) != 1 ){//删除
                        $file = yii::getAlias('@frontend/web') . $banner['img'];
                        if( file_exists($file) && is_file($file) ) unlink($file);
                        $this->img = '';
                    }else {
                        $this->img = $banner['img'];
                    }
                    break;
                }
            }
        }

        if( !$insert ){//修改
            $ifChangeStatus = false;
            $post = yii::$app->getRequest()->post()[$formName];
            count( yii::$app->getRequest()->post()[$formName] ) == 1 && isset( $post['status'] ) && $ifChangeStatus = true;
            foreach ($banners as &$banner){
                if( $banner['sign'] == $sign ){
                    if( $ifChangeStatus ){//首页仅修改状态
                        $banner['status'] = $this->status;
                    }else {
                        $banner = [
                            'sign' => $sign,
                            'img' => $this->img,
                            'target' => $this->target,
                            'desc' => $this->desc,
                            'link' => $this->link,
                            'sort' => $this->sort,
                            'status' => $this->status,
                        ];
                    }
                }
            }
        }else {//新增
            $banners[] = [
                'sign' => uniqid(),
                'img' => $this->img,
                'target' => $this->target,
                'desc' => $this->desc,
                'link' => $this->link,
                'sort' => $this->sort,
                'status' => $this->status,
            ];
        }
        $model->value = json_encode($banners);
        return $model->save(false);
    }

    public function deleteBanner($id, $sign)
    {
        $banners = $this->getBannersById($id);
        $temp = [];
        foreach ($banners as $banner){
            if($banner['sign'] == $sign){
                $file = yii::getAlias('@frontend/web') . $banner['img'];
                if( file_exists($file) && is_file($file) ) unlink($file);
                continue;
            }
            $temp[] = json_decode( json_encode($banner), true);
        }
        $model = self::findOne($id);
        $model->value = json_encode($temp);
        return $model->save(false);
    }

    public function getBannerTypeById($id)
    {
        $model = Options::findOne(['id'=>$id, 'type'=>self::TYPE_BANNER]);
        if( $model == null ) throw new NotFoundHttpException("None banner type id $id");
        return $model;
    }

}