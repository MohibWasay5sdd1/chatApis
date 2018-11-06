<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Userinspections;

/**
 * UserinspectionsSearch represents the model behind the search form of `backend\models\Userinspections`.
 */
class UserinspectionsSearch extends Userinspections
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['inspectionId', 'userId', 'categoryId'], 'integer'],
            [['inspectionDescription', 'equipmentInspectedImageUrl', 'equipmentInspectedImageType', 'createdOn', 'modifiedOn'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Userinspections::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'inspectionId' => $this->inspectionId,
            'userId' => $this->userId,
            'categoryId' => $this->categoryId,
            'createdOn' => $this->createdOn,
            'modifiedOn' => $this->modifiedOn,
        ]);

        $query->andFilterWhere(['like', 'inspectionDescription', $this->inspectionDescription])
            ->andFilterWhere(['like', 'equipmentInspectedImageUrl', $this->equipmentInspectedImageUrl])
            ->andFilterWhere(['like', 'equipmentInspectedImageType', $this->equipmentInspectedImageType]);

        return $dataProvider;
    }
}
