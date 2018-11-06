<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Inspections;

/**
 * InspectionsSearch represents the model behind the search form of `backend\models\Inspections`.
 */
class InspectionsSearch extends Inspections
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['inspectionId', 'inspectedEqSubCategoryId', 'userId'], 'integer'],
            [['inspectionDescription', 'inspectedEqImage', 'createdOn', 'modifiedOn', 'finalRemark', 'faultImage', 'faultDescription'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
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
        $query = Inspections::find();

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
            'inspectedEqSubCategoryId' => $this->inspectedEqSubCategoryId,
            'createdOn' => $this->createdOn,
            'modifiedOn' => $this->modifiedOn,
            'userId' => $this->userId,
        ]);

        $query->andFilterWhere(['like', 'inspectionDescription', $this->inspectionDescription])
            ->andFilterWhere(['like', 'inspectedEqImage', $this->inspectedEqImage])
            ->andFilterWhere(['like', 'finalRemark', $this->finalRemark])
            ->andFilterWhere(['like', 'faultImage', $this->faultImage])
            ->andFilterWhere(['like', 'faultDescription', $this->faultDescription]);

        return $dataProvider;
    }
}
